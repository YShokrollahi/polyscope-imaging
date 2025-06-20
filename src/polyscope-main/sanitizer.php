<?php
/*
	Desc: Sanitize a string.
	Author:	Sebastian Schmittner
	Date: - 
	Last Author: Sebastian Schmittner
	Last Date: 2014.07.19
	Version: 0.0.1
*/

function isSane($toBeSanitized) {
	$sanitizerSet = "0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz-_+.,@ ";
	
	$sanitizedLen = strspn($toBeSanitized, $sanitizerSet);
	$realLen = strlen($toBeSanitized);

	return $sanitizedLen == $realLen;
}
 
?>

