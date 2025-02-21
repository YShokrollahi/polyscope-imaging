<?php
/*
	Desc: Functions to return the contents of a directory in a specified format.
	Author:	Sebastian Schmittner
	Date: - 
	Last Author: Sebastian Schmittner
	Last Date: 2015.04.21 15:37:23 (+02:00)
	Version: 0.0.6
*/

require_once 'fileFormats.php';
require_once 'sanitizer.php';

echo json_encode(getDirectoryContents());

function getDirectoryContents()
{
	$contents = json_decode($_POST["path"]);
	$link = json_decode($_POST["link"]);
	$first = $_POST["first"];
		
	$first = ($first == "true");
	
	$directoryPath = $contents;
	
	if ( $first == true ) {
		$directoryPath = "/media/" . $directoryPath;
	}
		
	$fileData = formatDirectoriesAsHtml( new DirectoryIterator( $directoryPath ), $link, $contents, $directoryPath, $first );

	return $fileData;
}

function formatDirectoriesAsHtml( DirectoryIterator $dir, $htmlLink, $linkPath, $rootPath, $isFirst = true )
{
	global $pz_fileFormats;
	
	$dirTree = array();
	
	$dirTree = "<ul";
	
	if ( $isFirst ) {
		$dirTree .= " class=\"fileList\"";
	}
	else
	{
		$dirTree .= " class=\"subDirectory\"";	
	}
	
	$dirTree .= ">";
	
	$dirEntries = "";
	$fileEntries = "";

	if( $isFirst ) {
		$dirEntries .= "<li class='directoryRoot'>";
		$dirEntries .= "<input type='checkbox' class='selectedRoot toBeCentered'/>"; // checkbox
		$dirEntries .= "<a href='#' id='root' class='toBeCentered'>/</a>";
		$dirEntries .= "</li>";
	}
	
	foreach ( $dir as $dirEntry ) {
		
		if ( $dirEntry->isDir() ) {
			$key = $dirEntry->getFilename();
			if ( substr( basename( $key ), 0, 1 ) != '.' ) {
				if( isSane( $key ) ) {
					$dirEntries .= "<li class='directoryClass toload'>";
					$filepath = str_replace('/', '___SLASH___', $rootPath . '/' . $key);
					$dirEntries .= "<input type='checkbox' name='selectedDirs[]' class='selectedDir toBeCentered' value='$filepath'/>"; // checkbox
					$dirEntries .= "<a href='#' id='$filepath' class='toBeCentered'>" . htmlspecialchars($key) . "</a>";
					$dirEntries .= "</li>";
				}
			}
		}
		else {
			$name = $dirEntry->getFilename();
			$extBase = substr($name, strrpos($name, ".") + 1);
			
			if ( substr( basename( $name ), 0, 1 ) != '.' ) {
				if ( isSane( $name ) ) {
					
					$extBase = strtolower($extBase);
					
					if ( in_array( $extBase, $pz_fileFormats ) ) {
						$ext = "fe-" . $extBase;
						$link = str_replace("[link]", $rootPath . "/" . urlencode($name), $htmlLink);
						$fileEntries .= "<li class='extClass " . strtolower($ext) . "'>"; // element and class
						$filepath = str_replace('/', '___SLASH___', $rootPath . '/' . $name);
						$fileEntries .= "<input type='checkbox' name='selectedFiles[]' class='selectedFile toBeCentered' value='$filepath'/>"; // checkbox
						$fileEntries .= "<a href='#' class='toBeCentered'>" . htmlspecialchars($name) . "</a>"; // name and link
						$fileEntries .= "</li>";
					}
				} 
			}
		}
	}

	$dirTree .= $dirEntries;
	$dirTree .= $fileEntries;
	
	$dirTree .= "</ul>";
	
	return $dirTree;
}

?>
