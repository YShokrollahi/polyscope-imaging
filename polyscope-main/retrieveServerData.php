<?php
/*
	Desc: Retrieves and returns the collected server data.
	Author:	Sebastian Schmittner
	Date: - 
	Last Author: Sebastian Schmittner
	Last Date: 2015.07.29 12:00:04 (+02:00)
	Version: 0.0.3
*/

require_once 'isNotEmpty.php';
require_once 'getCpuMemUsage.php';
require_once 'getDiskUsage.php';
require_once 'getDateTime.php';
require_once 'polyzoomerGlobals.php';

function retrieveServerData()
{
    $serverData = collectServerData();
 
    echo json_encode($serverData);
}

function collectServerData()
{
  $dateTime = getDateTime();
  
  if( file_exists(dataGrave())  ){
      $diskUsage = getDiskUsage(dataGrave());
  }
  else {
      $diskUsage = getDiskUsage('/');
  }

  $cpuMemUsage = Polyzoomer\getCpuMemUsage();

  $serverInfo = array("dateTime" => $dateTime, 
                      "diskUsage" => $diskUsage, 
                      "cpuMemUsage" => $cpuMemUsage);

  return $serverInfo;
}

retrieveServerData();
?>

