<?php
/*
	Desc: Executes the supervisor 1 time
	Author:	Sebastian Schmittner
	Date: 2014.09.02 23:24:39 (+02:00)
	Last Author: Sebastian Schmittner
	Last Date: 2015.01.31 09:19:57 (+01:00)
	Version: 0.0.2
*/

require_once 'polyzoomerGlobals.php';
require_once 'taskSupervisor.php';

$supervisor = new TaskSupervisor( jobFile() );
$supervisor->update();

?>
