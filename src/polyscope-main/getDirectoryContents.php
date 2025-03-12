<?php
/*
	Desc: Functions to return the contents of a directory in a specified format.
	Author:	Sebastian Schmittner
	Date: - 
	Last Author: Sebastian Schmittner
	Last Date: 2015.04.21 15:37:23 (+02:00)
	Version: 0.0.6
*/

require_once __DIR__ . '/fileFormats.php';
require_once __DIR__ . '/sanitizer.php';

echo json_encode(getDirectoryContents());

function getDirectoryContents() {
    session_start();
    $username = isset($_SESSION['username']) ? $_SESSION['username'] : 'Guest';
    
    $contents = json_decode($_POST["path"]);
    $link = json_decode($_POST["link"]);
    $first = $_POST["first"];
    $first = ($first == "true");
    
    $directoryPath = $contents;
    if ($first == true) {
        $directoryPath = "/media/" . $directoryPath;
        
        // Only scan user's directory if under /media/Users/
        if (strpos($directoryPath, "/media/Users/") === 0) {
            $userPath = "/media/Users/" . $username;
            if (strpos($directoryPath, $userPath) !== 0) {
                return "<ul class=\"fileList\"></ul>";
            }
        }
    }
    
    $fileData = formatDirectoriesAsHtml(new DirectoryIterator($directoryPath), $link, $contents, $directoryPath, $first);
    return $fileData;
}

function formatDirectoriesAsHtml(DirectoryIterator $dir, $htmlLink, $linkPath, $rootPath, $isFirst = true) {
    session_start();
    $username = isset($_SESSION['username']) ? $_SESSION['username'] : 'Guest';
    
    global $pz_fileFormats;
    $dirTree = "<ul" . ($isFirst ? " class=\"fileList\"" : " class=\"subDirectory\"") . ">";
    
    $dirEntries = "";
    $fileEntries = "";
    
    if($isFirst) {
        $dirEntries .= "<li class='directoryRoot'>";
        $dirEntries .= "<input type='checkbox' class='selectedRoot toBeCentered'/>";
        $dirEntries .= "<a href='#' id='root' class='toBeCentered'>/</a>";
        $dirEntries .= "</li>";
    }
    
    foreach ($dir as $dirEntry) {
        if ($dirEntry->isDir()) {
            $key = $dirEntry->getFilename();
            if (substr(basename($key), 0, 1) != '.' && isSane($key)) {
                // Only filter at the Users directory level
                if ($rootPath === "/media/Users" && $key !== $username) {
                    continue;
                }
                
                $dirEntries .= "<li class='directoryClass toload'>";
                $filepath = str_replace('/', '___SLASH___', $rootPath . '/' . $key);
                $dirEntries .= "<input type='checkbox' name='selectedDirs[]' class='selectedDir toBeCentered' value='$filepath'/>";
                $dirEntries .= "<a href='#' id='$filepath' class='toBeCentered'>" . htmlspecialchars($key) . "</a>";
                $dirEntries .= "</li>";
            }
        } else {
            $name = $dirEntry->getFilename();
            if (substr(basename($name), 0, 1) != '.' && isSane($name)) {
                $extBase = strtolower(substr($name, strrpos($name, ".") + 1));
                if (in_array($extBase, $pz_fileFormats)) {
                    $ext = "fe-" . $extBase;
                    $link = str_replace("[link]", $rootPath . "/" . urlencode($name), $htmlLink);
                    $fileEntries .= "<li class='extClass " . strtolower($ext) . "'>";
                    $filepath = str_replace('/', '___SLASH___', $rootPath . '/' . $name);
                    $fileEntries .= "<input type='checkbox' name='selectedFiles[]' class='selectedFile toBeCentered' value='$filepath'/>";
                    $fileEntries .= "<a href='#' class='toBeCentered'>" . htmlspecialchars($name) . "</a>";
                    $fileEntries .= "</li>";
                }
            }
        }
    }
    
    $dirTree .= $dirEntries . $fileEntries . "</ul>";
    return $dirTree;
}


?>
