<?php
/*
	Desc: Returns the current date in a specified format.
	Author:	Sebastian Schmittner
	Date: - 
	Last Author: Sebastian Schmittner
	Last Date: 2014.07.19
	Version: 0.0.1
*/

function getDateTime()
{
  return date_parse(date('Y-m-d H:i:s'));
}

?>

