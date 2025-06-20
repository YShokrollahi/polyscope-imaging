<?php
/*
	Desc: Functions for locked file access.
	Author:	Sebastian Schmittner
	Date: 2014.07.24 15:04:19 (+02:00)
	Last Author: Sebastian Schmittner
	Last Date: 2015.01.31 09:20:30 (+01:00)
	Version: 0.0.9
*/

require_once 'md5chk.php';

class FileReadException extends Exception {};

// tries to lock the file and write the text to it
// return values
// 0 - ok 		- everything went fine
// 1 - error  	- the file could not be locked
// 2 - error 	- the write seems to have failed
function lockedFileWrite( $filename, $text, $expectedMd5 = 0) {
	//var_dump("--- WRITE ---");

	$waitIfLocked = true;
	$returnValue = createReturnValue(0, "ok", "");
	//var_dump(filesize($filename));
	
	$file = fopen( $filename, 'c+' );
	
	if ( flock($file, LOCK_EX, $waitIfLocked) ) {
		
		$bytesWritten = 0;
		$buffer = "";
		
		// write ONLY if the file is not yet changed
		if ( $expectedMd5 == 0 || $expectedMd5 == md5chk($filename) ) {

			if(filesize($filename) > 0) {
				$buffer = fread($file, filesize($filename));
	//var_dump(filesize($filename));

				if(strlen($buffer) == 0) {
					throw new Exception();
				}
	//var_dump(filesize($filename));
				
				ftruncate($file, 0);
				fseek($file, 0, SEEK_SET);
				$bytesWritten = fwrite( $file, $text );
				fflush($file);
				$returnValue['bytesWritten'] = $bytesWritten;
	//var_dump(filesize($filename));

				// $d = fread($file, filesize($filename));
				// while( $d != $text ) {
					// $d = fread($file, filesize($filename));
					// print_r( array($d, strlen($d), $text, strlen($text)));
					// ftruncate($file, 0);
					// $bytesWritten = fwrite( $file, $text );
					// fflush($file);
					// if(strlen($d) == 0) {
						// $d = $text;
					// } 
				// }
				
				// print_r($text . "\n");
				// print_r($bytesWritten . "\n");
	//var_dump(filesize($filename));
				
				if ( $bytesWritten != strlen($text) ) {
					$returnValue = createReturnValue(2, "error", "File write did not perform properly!");
					ftruncate($file, 0);
					fseek($file, 0, SEEK_SET);
					fwrite($file, $buffer);
					fflush($file);
				}
				
				//usleep(50000);
			}
		}
	//var_dump(filesize($filename));

		flock( $file, LOCK_UN );
	}
	else {
		$returnValue = createReturnValue(1, "error", "File could not be locked.");
	}

	fclose( $file );

	//var_dump("--- WRITE END ---");

	return $returnValue;
}

// tries to lock the file and append the text to it
// return values
// 0 - ok 		- everything went fine
// 1 - error  	- the file could not be locked
// 2 - error 	- the write seems to have failed
function lockedFileAppend( $filename, $text, $expectedMd5 = 0) {

	$returnValue = createReturnValue(3, "error", "could not open file");
	$waitIfLocked = true;
	
	$file = fopen( $filename, 'a+' );
	
	if ( $file !== false && flock($file, LOCK_EX, $waitIfLocked) ) {
		
		$bytesWritten = 0;
		$buffer = "";
		
		// write ONLY if the file is not yet changed
		if ( $expectedMd5 == 0 || $expectedMd5 == md5chk($filename) ) {
			if(filesize($filename) != 0){
				$buffer = fread($file, filesize($filename));
			}
			
			$bytesWritten = fwrite( $file, $text );
			fflush($file);
			$returnValue['bytesWritten'] = $bytesWritten;
		
			if ( $bytesWritten != strlen($text) ) {
				$returnValue = createReturnValue(2, "error", "File write did not perform properly!");
				ftruncate($file, 0);
				fseek($file, 0, SEEK_SET);
				fwrite($file, $buffer);
				fflush($file);
			}
			else {
				$returnValue = createReturnValue(0, "ok", "");
			}
		}
		
		if ( $bytesWritten != strlen($text) ) {
			$returnValue = createReturnValue(2, "error", "File write did not perform properly!");
		}
		
		//usleep(50000);
		flock( $file, LOCK_UN );
	}
	else {
		$returnValue = createReturnValue(1, "error", "File could not be locked.");
	}

	if($file !== false) {
		fclose( $file );
	}
	
	return $returnValue;
}

// tries to lock the file and read the length of it
// return values
// 0 - ok 		- everything went fine
// 1 - error  	- the file could not be locked
// 2 - error 	- the read seems to have failed
function lockedFileRead( $filename, $length, $mode, $waitIfLocked = true ) {
	
	//var_dump("--- READ ---");

	$returnValue = createReturnValue(0, "ok", "");
	$data = "";
	//var_dump(filesize($filename));
	
	$file = fopen( $filename, $mode );
	
	if ( flock($file, LOCK_SH, $waitIfLocked) ) {

		if(filesize($filename) != $length || filesize($filename) == 0) {
			$returnValue = createReturnValue(3, "error", "Filesize of file " . $filename . " is 0!");
		}
		else {
			$data = fread( $file, $length );
		}
	
		
		if ( $data === false ) {
			$returnValue = createReturnValue(2, "error", "File read did not perform properly!");
		}
	}
	else {
		$returnValue = createReturnValue(1, "error", "File could not be locked.");
	}

	flock( $file, LOCK_UN );
	fclose( $file );
	
	$returnValue["data"] = $data;
	//var_dump(filesize($filename));
	
	//var_dump("--- READ END ---");

	return $returnValue;
}

// tries to lock the file, increment a counter and return its value
// return values
// >= 0 - current index
// -1   - failed (possibly the lock)
function atomicCounterIncrement( $filename ) {
	
	$file = fopen( $filename, "r+" );
	$counter = -1;
	$waitIfLocked = true;
	
	if ( flock( $file, LOCK_EX, $waitIfLocked ) ) {
	
		$counter = fread( $file, filesize( $filename ) );
		$counter = intval( $counter ) + 1;
		
		ftruncate( $file, 0 );
		fseek( $file, 0, SEEK_SET );
		fwrite( $file, $counter );
		fflush($file);
		flock( $file, LOCK_UN );
	}
	
	fclose( $file );
	
	return $counter;
}

function createReturnValue( $id, $status, $comment ) {
	
	return array (
		"id" => $id,
		"status" => $status,
		"comment" => $comment,
	);
	
}

?>
