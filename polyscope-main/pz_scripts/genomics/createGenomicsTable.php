<?php
/*
	Desc: Function to create a specified genomics table
	Author:	Sebastian Schmittner (stp.schmittner@gmail.com)
	Date: 2015.10.08 22:58:56 (+02:00)
	Last Author: Sebastian Schmittner (stp.schmittner@gmail.com)
	Last Date: 2015.10.12 15:10:05 (+02:00)
	Version: 0.0.9
*/

/*
    Call: createGenomicsTable genomicsTableFile.cfg user
*/

require_once 'genomicsTableFunctions.php';

$configFile = json_decode($_POST['file']);
$email = json_decode($_POST['email']);

$tableConfig = tryToLoadTableConfig($configFile, $email);

if($tableConfig === NULL) {
	$tableConfig = createGenomicsTableFromCsv($configFile);
}

if($tableConfig !== NULL) {
	$tableConfig = gatherZooms($tableConfig, $email);
}

echo json_encode($tableConfig);

///////////////////////////////////////////////////////////////////////////////

function tryToLoadTableConfig($configFile, $email) {
	return NULL;
}

?>
