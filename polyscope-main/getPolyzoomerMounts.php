<?php
/*
	Desc: Returns the available mount points in the polyzoomer media folder.
	Author:	Sebastian Schmittner
	Date: - 
	Last Author: Sebastian Schmittner
	Last Date: 2015.05.11 10:46:54 (+02:00)
	Version: 0.0.2
*/

require_once __DIR__ . '/polyzoomerGlobals.php';
require_once __DIR__ . '/getMountedMedia.php';

echo json_encode(getMountedMedia( mediaDirectory(), mediaExcludes() ));

?>

