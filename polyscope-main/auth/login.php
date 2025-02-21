<?php
session_start();
require 'ldap_functions.php';
require 'db_functions.php';

// Current nonce generation remains the same
$nonce = base64_encode(random_bytes(16));
$_SESSION['csp_nonce'] = $nonce;

// Final updated CSP header with proper backward compatibility
header("Content-Security-Policy: ".
    "default-src 'self'; ".
    "style-src 'self' 'nonce-{$nonce}'; ".
    "script-src 'unsafe-inline' 'strict-dynamic' 'nonce-{$nonce}' https: http:; ".
    "frame-src 'none'; ".
    "frame-ancestors 'none'; ".
    "form-action 'self'; ".
    "base-uri 'self'; ".
    "require-trusted-types-for 'script'; ".
    "object-src 'none'; ".
    "img-src 'self' data:; ".
    "font-src 'self';"
);
header("X-Content-Type-Options: nosniff");
header("X-XSS-Protection: 1; mode=block");
header("Strict-Transport-Security: max-age=31536000; includeSubDomains; preload");
header("Referrer-Policy: strict-origin-when-cross-origin");
header("Permissions-Policy: geolocation=(), camera=(), microphone=()");
header("Cross-Origin-Embedder-Policy: require-corp");
header("Cross-Origin-Opener-Policy: same-origin");
header("Cross-Origin-Resource-Policy: same-origin");

// Regenerate session ID to prevent session fixation
if (!isset($_SESSION['initiated'])) {
    session_regenerate_id(true);
    $_SESSION['initiated'] = true;
}

$error = '';

// Check if the user is already logged in
if (isset($_SESSION['loggedin']) && $_SESSION['loggedin'] === true) {
    header('Location: /index.php');
    exit;
}

// Check if form is submitted
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = filter_input(INPUT_POST, 'username', FILTER_SANITIZE_STRING);
    $password = $_POST['password'];
    $ip_address = $_SERVER['REMOTE_ADDR'];
    $additional_info = json_encode(['user_agent' => $_SERVER['HTTP_USER_AGENT']]);

    // Implement basic password policy check
    if (strlen($password) < 8) {
        $error = "Password must be at least 8 characters long";
        log_audit(null, "Login failure - Weak password", $ip_address, $additional_info);
    } else {
        // First, try LDAP authentication
        $user_dn = find_user_dn($username);
        if ($user_dn && verify_password($user_dn, $password)) {
            // LDAP authentication successful
            session_regenerate_id(true);
            $_SESSION['loggedin'] = true;
            $_SESSION['username'] = $username;
            $_SESSION['last_activity'] = time();

            // Check if the user exists in the local database
            $stmt = $conn->prepare("SELECT id FROM users WHERE username = ?");
            $stmt->bind_param("s", $username);
            $stmt->execute();
            $stmt->store_result();

            if ($stmt->num_rows == 0) {
                $stmt->close();
                create_db_user($username, $password);
                $stmt = $conn->prepare("SELECT id FROM users WHERE username = ?");
                $stmt->bind_param("s", $username);
                $stmt->execute();
                $stmt->bind_result($user_id);
                $stmt->fetch();
            } else {
                $stmt->bind_result($user_id);
                $stmt->fetch();
            }
            $stmt->close();

            // Create a folder for the new user if it doesn't exist
            $user_folder = "/media/Users/" . strtolower($username);
            if (!file_exists($user_folder)) {
                mkdir($user_folder, 0700, true);
            }

            log_audit($user_id, "Login success", $ip_address, $additional_info);
            header('Location: /index.php');
            exit;
        }

        // If LDAP authentication fails, try local database authentication
        $db_auth = authenticate_db_user($username, $password);
        if ($db_auth['status']) {
            session_regenerate_id(true);
            $_SESSION['loggedin'] = true;
            $_SESSION['username'] = $username;
            $_SESSION['last_activity'] = time();

            $user_folder = "/media/Users/" . strtolower($username);
            if (!file_exists($user_folder)) {
                mkdir($user_folder, 0700, true);
            }

            log_audit($db_auth['id'], "Login success", $ip_address, $additional_info);
            header('Location: /index.php');
            exit;
        } else {
            $error = $db_auth['message'];
            log_audit(null, "Login failure - " . $error, $ip_address, $additional_info);
        }
    }
}

// Set secure cookie parameters
session_set_cookie_params([
    'lifetime' => 0,
    'path' => '/',
    'domain' => '',
    'secure' => true,
    'httponly' => true,
    'samesite' => 'Strict'
]);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <title>Login</title>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="icon" type="image/png" href="images/icons/favicon.ico"/>
    <link rel="stylesheet" type="text/css" href="vendor/bootstrap/css/bootstrap.min.css">
    <link rel="stylesheet" type="text/css" href="fonts/font-awesome-4.7.0/css/font-awesome.min.css">
    <link rel="stylesheet" type="text/css" href="vendor/animate/animate.css">
    <link rel="stylesheet" type="text/css" href="vendor/css-hamburgers/hamburgers.min.css">
    <link rel="stylesheet" type="text/css" href="vendor/select2/select2.min.css">
    <link rel="stylesheet" type="text/css" href="css/util.css">
    <link rel="stylesheet" type="text/css" href="css/main.css">
    
    <style nonce="<?php echo htmlspecialchars($nonce); ?>">
        body {
            font-family: 'Roboto', sans-serif;
        }
        .navbar {
            display: flex;
            align-items: center;
            background: linear-gradient(to right, #007bff, #6699ff);
            box-shadow: 0 2px 5px rgba(0,0,0,0.2);
            padding: 10px;
            overflow: hidden;
        }
        .navbar img {
            height: 50px;
            margin-right: 20px;
        }
        .navbar ul {
            list-style-type: none;
            margin: 0;
            padding: 0;
            display: flex;
            align-items: center;
            margin-right: auto;
        }
        .navbar ul li {
            margin-left: 20px;
        }
        .navbar ul li a {
            color: white;
            text-decoration: none;
            font-weight: bold;
        }
        .navbar ul li a:hover {
            text-decoration: underline;
        }
        .footer {
            position: absolute;
            bottom: 20px;
            width: 100%;
            text-align: center;
            font-size: 14px;
        }
        .password-requirements {
            font-size: 12px;
            color: #666;
            margin-top: 5px;
        }
    </style>
</head>
<body>
    <div class="navbar">
        <img src="images/logo.png" alt="Logo">
        <ul>
            <li><a href="/index.php">Home</a></li>
            <li><a href="/docs/index.html">Documentation</a></li>
        </ul>
    </div>

    <div class="limiter">
        <div class="container-login100">
            <div class="wrap-login100">
                <div class="login100-pic js-tilt" data-tilt>
                    <img src="images/img-01.png" alt="IMG">
                </div>

                <form class="login100-form validate-form" method="POST" action="login.php">
                    <span class="login100-form-title">
                        Welcome to Polyscope
                    </span>

                    <?php if (!empty($error)): ?>
                        <p style="color: red;"><?php echo htmlspecialchars($error); ?></p>
                    <?php endif; ?>

                    <div class="wrap-input100 validate-input" data-validate="Valid username is required: abc">
                        <input class="input100" type="text" name="username" placeholder="Username" required>
                        <span class="focus-input100"></span>
                        <span class="symbol-input100">
                            <i class="fa fa-user" aria-hidden="true"></i>
                        </span>
                    </div>

                    <div class="wrap-input100 validate-input" data-validate="Password is required">
                        <input class="input100" type="password" name="password" placeholder="Password" required 
                               pattern=".{8,}" title="Password must be at least 8 characters long">
                        <span class="focus-input100"></span>
                        <span class="symbol-input100">
                            <i class="fa fa-lock" aria-hidden="true"></i>
                        </span>
                        <div class="password-requirements">
                            Password must be at least 8 characters long
                        </div>
                    </div>

                    <div class="container-login100-form-btn">
                        <button class="login100-form-btn">
                            Login
                        </button>
                    </div>

                    <div class="text-center p-t-12">
                        <span class="txt1">
                            Forgot
                        </span>
                        <a class="txt2" href="recovery.php">
                            Username / Password?
                        </a>
                    </div>

                    <div class="text-center p-t-136">
                        <a class="txt2" href="signup.php">
                            Create your Account
                            <i class="fa fa-long-arrow-right m-l-5" aria-hidden="true"></i>
                        </a>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <div class="footer">
        Developed by Yinyin Yuan Lab @ 2024
    </div>

    <!-- External scripts with nonce -->
    <script nonce="<?php echo htmlspecialchars($nonce); ?>" src="vendor/jquery/jquery-3.2.1.min.js"></script>
    <script nonce="<?php echo htmlspecialchars($nonce); ?>" src="vendor/bootstrap/js/popper.js"></script>
    <script nonce="<?php echo htmlspecialchars($nonce); ?>" src="vendor/bootstrap/js/bootstrap.min.js"></script>
    <script nonce="<?php echo htmlspecialchars($nonce); ?>" src="vendor/select2/select2.min.js"></script>
    <script nonce="<?php echo htmlspecialchars($nonce); ?>" src="vendor/tilt/tilt.jquery.min.js"></script>
    
    <!-- Inline script with nonce -->
    <script nonce="<?php echo htmlspecialchars($nonce); ?>">
        $('.js-tilt').tilt({
            scale: 1.1
        })
    </script>
    
    <script nonce="<?php echo htmlspecialchars($nonce); ?>" src="js/main.js"></script>
</body>
</html>