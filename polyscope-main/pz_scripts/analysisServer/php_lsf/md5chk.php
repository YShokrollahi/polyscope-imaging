<?php
/*
	Desc: Function to create a MD5 checksum using the BASH.
	Author:	Sebastian Schmittner (stp.schmittner@gmail.com)
	Date: 2014.12.21 12:46:29 (+01:00)
	Last Author: Sebastian Schmittner
	Last Date: 2015.08.13 13:38:04 (+02:00)
	Version: 0.0.7
*/

function md5chk( $filename )
{
    return trim(shell_exec("md5sum '" . $filename . "' | awk '{ print $1 }'"));
	
	// for Darwin (Apple/Linux)
//	return trim(shell_exec("md5sum '" . $filename . "' | awk '{ print $4 }'"));
}

?>
