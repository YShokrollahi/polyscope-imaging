<?php
/*
* Author: Sebastian Schmittner
* Date: 2014.10.14 10:24:00
* LastAuthor: Sebastian Schmittner
* LastDate: 2015.08.06 23:06:14 (+02:00)
* Version: 0.03.21
* Version Key: VERSIONKEY
*/
session_start();
require_once '/var/www/auth/session_timeout.php';
// Check if user is logged in
if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true) {
    header('Location: /auth/login.php');
    exit;
}
$logged_in_user = $_SESSION['username'];

// This file is now just a fallback
header('Location: /index.php');
exit;
?>
<!DOCTYPE html>
<html>
<head>
<meta charset='utf-8'>
<meta http-equiv='X-UA-Compatible' content='IE=edge' />
<!-- <meta name='viewport' content='width=device-width, initial-scale=1'> -->
<title>Userpage</title>
<link type='text/css' rel='stylesheet' href='./fonts/opensans.css'/>
<link type='text/css' rel='stylesheet' href='./css/jquery-ui.css'/>
<link type='text/css' rel='stylesheet' href='./css/main.css'/>
<link type='text/css' rel='stylesheet' href='./css/style.css'/>
<style type='text/css'>
.ui-dialog-buttonpane { text-align: center; }
</style>
</head>
<body>
<div id='main' class='main'>
<div id='header' class='header'>
<div id="topMenu">
<ul>
<li><a href="../../index.php">Polyzoomer</a></li>
<li><a href="#">Customers</a></li>
<li><a href="../../docs/index.html" target="_blank" >Documentation/Help</a></li>
<li><a href="#">Contact</a></li>
<li><a href="#">Login</a></li>
<li><a href="#">Profile</a></li>
</ul>
</div>
</div>
<div id='pageContainer' class='pageContainer'>
<!-- left pane !-->
<div id='leftpane' class='leftpane'>
<div id='sortingmenu' class='sortingmenu'>
<div id='deleteZooms' class='button deleteZooms' style='height:48px;width:48px;' title='Delete zooms'></div>
<div id='createFolder' class='button' style='height:48px;width:48px;' title='Create new folder'></div>
<div id='sort' class='button sort' style='height:48px;width:48px;' title='Sort samples'></div>
<div id='filter' class='button filter' style='height:48px;width:48px;' title='Filter samples'></div>
<div id='createZoom' class='button' style='height:48px;width:48px;' title='Create multizoom'></div>
<div id='applyTemplate' class='button' style='height:48px;width:48px;display:none;' title='Apply multizoom template'></div>
<div id='sendCode' class='button' style='height:48px;width:48px;' title='Resend usercode'></div>
<!-- <div id='startAnalysis' class='button' style='height:48px;width:48px;' title='Analysis Apps'></div> !-->
<!-- <div id='zoomJobs' class='button' style='height:48px;width:48px;' title='Job Information'></div> !-->
<div id='imageSizeSlider' class='verticalSlider'></div>
</div>
<div id='samplelist' class='samplelist'>
<table id='sampleTable'><tbody></tbody></table>
<table id='multizooms' style='display:none;'><tbody></tbody></table>
</div>
<!-- <div id='status' class='status'></div> !-->
</div>
<!-- right pane !-->
<div id='rightpane' class='rightpane'>
<div id='centerPage' class='centerPage'>
<div id='raster' class='raster'></div>
</div>
</div>
<!-- <div id='currentPath' class='currentPath'>
 </div> -->
</div>
</div>
</body>
</html>