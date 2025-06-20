<?php
/*
	Desc: Performs the PZ side of things for issuing an analysis
	Author:	Sebastian Schmittner (stp.schmittner@gmail.com)
	Date: 2015.05.25 09:05:29 (+02:00)
	Last Author: Sebastian Schmittner
	Last Date: 2015.07.30 08:51:08 (+02:00)
	Version: 0.0.9
	
*/

require_once 'guid.php';
require_once '../../jobRepresentationAnalysis.php';
require_once '../../polyzoomerGlobals.php';
require_once '../../logging.php';
require_once '../../tools.php';
require_once '../../addLineToFile.php';

function useApp( $app, $sampleNames, $paths, $email, $parameter ) {
	
	$result = array();
	
	for( $i = 0; $i < count($paths); ++$i ) {
		
		$fileResult = useAppOnFile( $app, $sampleNames[$i], $paths[$i], $email, $parameter );
		
		array_push( $result, $fileResult );
	}
	
	return $result;
}

function useAppOnFile( $app, $sampleName, $path, $email, $parameter ) {
	$files = array();
	$result = array();
	
	if( !file_exists($path) ) {
		doLog('[FATAL] useApp: [' . $path . '] does not exist!', logfile());
		$result['message'] = '[FATAL] useApp: [' . $path . '] does not exist!';
		return $result;
	}
	else {
		
		if( is_dir( $path ) ) {
			$files = dirToArray( $path, DTA_DIRS );
			$lastFolder = count( $files ) - 1;
			$files = dirToArray( $path . '/' . $lastFolder . '/', DTA_FILES );
			
			for( $i = 0; $i < count( $files ); ++$i ) {
				$files[$i] = originalFileFromDzi( $files[$i] );
			}
		}
		else {
			array_push($files, originalFileFromDzi( $path ));
		}
	}
	
	// generate safe GUID
	$guid = GUID();
	while( file_exists( analysisInPath() . $guid . '/' ) ||
		   file_exists( analysisOutPath() . $guid . '/' ) ) {
		$guid = GUID();
	}
	
	// create transfer folder structure
	createFolderStructure( $guid );
	

	if( file_exists( $parameter ) ) {
		array_push( $files, $parameter );
	}
	else {
		jobLog($guid, '[INFO] No parameters were specified default will be used!');
	}
	
	$transferSpecs = array(
		'guid' => $guid,
		'to' => enclose( analysisInPath() . $guid . '/' ),
		'preStartState' => '1;transfer',
		'endState' => '1;pending',
		'files' => $files
	);
	
	//$sampleName = removeDeepzoomInFileExtension($sampleName);
	
	$job = new AnalysisJob();
	$job->withParameters($guid, 1, 'transfer', $path, cleanFileName(basename($files[0])), '__MD5__', $email, $app, $parameter);
	$jobFile = analysisJobFile( $guid );
	
	file_put_contents( $jobFile, $job->reassemble() );	
	
	$commandBuffer = 'php doTransferFiles.php ' . enclose( base64_encode( json_encode( $transferSpecs ) ) );

	// copy sample
	executeSync( $commandBuffer );
	
	addLineToFile( analysisJobMasterFile(), $guid );
	
	$result['message'] = '[INFO] useApp: [' . $path . '] was started!';
	return $result;
}

function createFolderStructure( $guid ) {
	
	$inPath = analysisInPath() . $guid . '/';
	$outPath = analysisOutPath() . $guid . '/';

	mkdir( $inPath );
	mkdir( $outPath );
	
}

function removeDeepzoomInFileExtension($filename) {
	$filenameElements = explode('.', $filename);
	$extension = array_pop($filenameElements);
	$extension = str_replace('deepzoom', '', $extension);
	array_push($filenameElements, $extension);
	$filename = implode('.', $filenameElements);
	return $filename;
}

function cleanFileName( $filename ) {
	
	$filename = str_replace('.dzi', '', $filename);
	$filename = str_replace('.deepzoom', '', $filename);
	
	return $filename;
}

function originalFileFromDzi( $dziPath ) {
		
	$elements = array_filter( explode('/', $dziPath) );

	// filename
	$filename = end(array_values($elements));
	$filenameElements = explode('.', $filename);
	array_pop($filenameElements); // remove 'dzi'
	$filename = implode('.', $filenameElements);
	$filename = removeDeepzoomInFileExtension($filename);
	
	// path
	$pagePosition = array_search('page', $elements);
	$pathPosition = $pagePosition - 2;
	$pathElements = array_slice($elements, 0, $pathPosition);
	
	array_push($pathElements, $filename);
	
	$originalPath = '/' . implode('/', $pathElements);
	return $originalPath;
}

//////////////////////////////////////////////////////////////////

?>
