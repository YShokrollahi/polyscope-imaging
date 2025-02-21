<?php
/*
	Desc: Retrieves and returns the collected server data.
	Author:	Sebastian Schmittner
	Date: - 
	Last Author: Sebastian Schmittner
	Last Date: 2014.09.02 22:40:24 (+02:00)
	Version: 0.0.2
*/

require_once 'isNotEmpty.php';
require_once 'getCpuMemUsage.php';
require_once 'getDiskUsage.php';
require_once 'getDateTime.php';

function retrieveServerData()
{
  $serverData = collectServerData();
 
  echo json_encode($serverData);
}

function collectServerData()
{
  $dateTime = getDateTime();
  $diskUsage = getDiskUsage();
  $cpuMemUsage = getCpuMemUsage();

  $serverInfo = array("dateTime" => $dateTime, "diskUsage" => $diskUsage, "cpuMemUsage" => $cpuMemUsage);

  return $serverInfo;
}

retrieveServerData();
?>

