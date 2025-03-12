<?php
/*
* Author: Sebastian Schmittner
* Modified for security
* Date: 2014.10.14 10:24:00
* Version: 0.03.22
*/

session_start();
require_once '/var/www/auth/session_timeout.php';

// Check if user is logged in
if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true) {
    header('Location: /auth/login.php');
    exit;
}
$logged_in_user = $_SESSION['username'];

// Extract the requested username from the URL
$requested_url = $_SERVER['REQUEST_URI'];
preg_match('/\/customers\/([a-zA-Z0-9_]+)-mdanderson-org\//', $requested_url, $matches);

// Debugging
error_log("Requested URL: " . $requested_url);
error_log("Extracted username: " . ($matches[1] ?? 'No match found'));
error_log("Logged-in user: " . $logged_in_user);

if (!isset($matches[1])) {
    http_response_code(403);
    echo "Access Denied: Invalid URL.";
    exit;
}

$requested_user = $matches[1];

// Ensure that the logged-in user matches the requested user
if ($logged_in_user !== $requested_user) {
    http_response_code(403);
    echo "Access Denied: You do not have permission to access this page.";
    exit;
}
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge" />
    <link type="text/css" rel="stylesheet" href="./jquery-ui.css"/>
    <link type="text/css" rel="stylesheet" href="./css/main.css"/>
    <link type="text/css" rel="stylesheet" href="./css/style.css"/>
    <script type="text/javascript" src="./jquery-1.11.3.js"></script>
    <script type="text/javascript" src="./jquery-ui.js"></script>
    <script type="text/javascript" src="./jquery.ui.touch-punch.js"></script>
    <script type="text/javascript" src="./js/underscore.js"></script>
    <script type="text/javascript" src="./js/serveraccess.js"></script>
    <script type="text/javascript" src="./js/raster.js"></script>
    <script type="text/javascript" src="./js/fileManager.js"></script>
    <script type="text/javascript" src="./js/jquery.ui-contextmenu.js"></script>
    <script type="text/javascript" src="./js/scrollWidth.js"></script>
    <title>Polyscope - <?php echo htmlspecialchars($username); ?></title>
</head>
<body>
<script type="text/javascript">
jQuery('body').load('./index_med1.php', function() {
    jQuery.getScript('./js/userpage.js')
    .done(function(){
        onLoad();
    })
    .fail(function(){
        onLoad();
    });
});
</script>
</body>
</html>
