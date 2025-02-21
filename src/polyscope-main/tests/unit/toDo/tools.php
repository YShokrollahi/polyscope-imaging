<?php
/*
	Desc: Misc Tools.
	Author:	Sebastian Schmittner (stp.schmittner@gmail.com)
	Date: 2015.01.30 15:05:05 (+01:00)
	Last Author: Sebastian Schmittner
	Last Date: 2015.01.30 15:05:05 (+01:00)
	Version: 0.0.1
*/

function uniqueId() {
	$randomKey = rand(0, 99999999);
	return padNumber( $randomKey, 8 );
}

function padNumber( $number, $digits ) {
	$format = "%0" . $digits . "d";
	return sprintf($format, $number);
}

function cleanString($stringToClean) 
{
	return preg_replace("/[^a-zA-Z0-9_-]/s", '-', $stringToClean);
}

?>
