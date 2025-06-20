<?php
/*
	Desc: Executes the analysis supervisor 1 time
	Author:	Sebastian Schmittner
	Date: 2015.05.25
	Last Author: Sebastian Schmittner
	Last Date: 2015.06.04 09:37:32 (+02:00)
	Version: 0.0.1
*/

require_once 'polyzoomerGlobals.php';
require_once 'taskSupervisor.php';

chdir(dirname(__FILE__));

$supervisor = new TaskSupervisor( analysisJobMasterFile() );
$supervisor->update();

?>
