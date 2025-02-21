<?php
/*
	Desc: Returns the available mount points in the specified folder.
	Author:	Sebastian Schmittner
	Date: 2015.05.11 10:46:29 (+02:00)
	Last Author: Sebastian Schmittner
	Last Date: 2015.05.11 10:46:29 (+02:00)
	Version: 0.0.1
*/

function getMountedMedia( $directory, $excludes )
{
  $directories = array();

  if (is_dir($directory)) {
    $dh = opendir($directory);
	while(false !== ($file = readdir($dh)))
	{
	  if( is_dir($directory . "/" . $file) )
	  {
        array_push($directories, $file);
	  }
	}
  }

  return array_values(array_diff($directories, $excludes));
}

?>

