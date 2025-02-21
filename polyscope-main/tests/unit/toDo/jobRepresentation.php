<?php
/*
	Desc: Represents a job
	Author:	Sebastian Schmittner
	Date: 2014.08.18
	Last Author: Sebastian Schmittner
	Last Date: 2014.09.06 15:20:25 (+02:00)
	Version: 0.0.5
*/

class Job {
	public $data;
	
	public function __construct( $text ) {
		$this->data = array();
		
		$this->disassemble( $text );		
	}
	
	public function __destruct() {
	}
	
	public function disassemble( $text ) {
	
		$parts = explode(";", $text);
		
		if(count($parts) != 10) {
			throw new WrongArgumentCountException();
		}
		
		$this->data['id'] = $parts[0];
		$this->data['guid'] = $parts[1];
		$this->data['statusId'] = $parts[2];
		$this->data['status'] = $parts[3];
		$this->data['origFilename'] = $parts[4];
		$this->data['targetFilename'] = $parts[5];
		$this->data['md5'] = $parts[6];
		$this->data['email'] = $parts[7];
		$this->data['finalFilename'] = $parts[8];
		$this->data['finalPath'] = $parts[9];
	}
	
	public function reassemble() {
		return implode(";", $this->data);
	}
}

?>
