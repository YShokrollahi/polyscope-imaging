<?php
/*
	Desc: Returns the current used disk space.
	Author:	Sebastian Schmittner
	Date: - 
	Last Author: Sebastian Schmittner
	Last Date: 2014.09.02 22:34:50 (+02:00)
	Version: 0.0.2
*/

require_once __DIR__ . '/isNotEmpty.php';

function getDiskUsage( $pathToCheck )
{
  $executeString = 'df -h "' . $pathToCheck . '"';

  $output = shell_exec( $executeString );
  $aOutput = explode("\n", $output);

  $diskUsage = getPercentUsageFromDfOutput($aOutput);

  return $diskUsage;
}

function getPercentUsageFromDfOutput( $dfOutput ) {
  $usageLine = $dfOutput[1];

  $aLine = explode(" ", $usageLine);

  $aLine = array_filter($aLine, "isNotEmpty");
  
  $keys = array_keys($aLine);
  $key = $keys[4];

  $diskUsage = $aLine[$key];

  return $diskUsage;
}

?>
 
