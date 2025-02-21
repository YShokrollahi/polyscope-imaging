<?php
/*
	Desc: Reads a cache file from the given user and adds all
              Scopes to the User File System
	Author:	Sebastian Schmittner (stp.schmittner@gmail.com)
	Date: 2016.02.16
	Last Author: Sebastian Schmittner
	Last Date: 2016.02.16
	Version: 0.0.1
*/

require_once './polyzoomerGlobals.php';
require_once './pz_scripts/userpage/userFileSystem.php';

class WrongArgumentCountException extends Exception {}

if($argc != 2) {
    fwrite(STDERR, 'Argument count: ' . $argc . '\n');
    fwrite(STDOUT, 'Argument count: ' . $argc . '\n');
    throw new WrongArgumentCountException( json_encode( $argv ) );
}

$cleanMail = $argv[1];

$userPath = userPath($cleanMail);
$userCache = cacheFile($cleanMail);
$multiCache = multiCacheFile($cleanMail);

if(!file_exists( $userPath )){
    fwrite(STDERR, "Path does not exist [" . $userPath . "]\n");
    fwrite(STDOUT, "Path does not exist [" . $userPath . "]\n");
    throw new Exception( "Path does not exist [" . $userPath . "]" );
}

$singleProjects = getProjectsFromFile( $userCache );
$multiProjects = getProjectsFromFile( $multiCache );

$projects = array_merge($singleProjects, $multiProjects);

$ufs = UserFileSystem::fromDefault($cleanMail);

if($ufs != NULL){
    
    for($i = 0; $i < count($projects); ++$i){
        
        $name = $projects[$i]['name'];
        
        if(!$ufs->doesItemExist($name)){
            $path = '///';
            $item = projectToItem($projects[$i]);
            $result = $ufs->addItem($path, $item);
        }
        else {
            fwrite(STDERR, "Project does already exist [" . $name . "]\n");
            fwrite(STDOUT, "Project does already exist [" . $name . "]\n");            
        }
    }
}

function projectToItem($project) {
    $item = array(
        'name' => $project['name'],
        'creationDate' => $project['fileDate']['date'],
        'type' => 'FILE'
    );
    
    return $item;
}

function getProjectsFromFile( $filename ){
    
    if(!file_exists( $filename )){
        fwrite(STDERR, "Cachefile does not exist [" . $filename . "]\n");
        fwrite(STDOUT, "Cachefile does not exist [" . $filename . "]\n");
        throw new Exception( "Cachefile does not exist [" . $filename . "]" );
    }

    $cacheFile = new TaskFileManipulator($filename);

    $cache = $cacheFile->getContents();

    $projects = array();

    foreach( $cache as $line ) {
            $project = json_decode($line, true);
            if($project != null) {
                    array_push($projects, $project);
            }
    }
    
    return $projects;
}

?>

