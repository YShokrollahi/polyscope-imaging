<?php
/*
	Desc: Functions to get the cpu and memory usage of the system.
	Author:	Sebastian Schmittner
	Date: - 
	Last Author: Sebastian Schmittner
	Last Date: 2015.05.03 20:20:51 (+02:00)
	Version: 0.0.3
*/

namespace Polyzoomer;

function getLoadAverage() {
	
	$loadAverage = sys_getloadavg();
	
	$weightedAverage = $loadAverage[0] * 1.0 +
					   $loadAverage[1] * 5.0 +
					   $loadAverage[2] * 15.0;
	
	$weightedAverage = $weightedAverage / 21.0;
	
	return array( 'loadAverage' => $loadAverage,
				  'weightedAverage' => $weightedAverage );
}

function getCpuMemUsage()
{
  $loadAverage = getLoadAverage();
  
  $usage = array("cpu" => $loadAverage);
  return $usage;
}

?>

