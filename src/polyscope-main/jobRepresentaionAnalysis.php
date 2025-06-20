<?php
/*
	Desc: Represents an analysis job
	Author:	Sebastian Schmittner
	Date: 2015.05.30 17:47:00 (+02:00)
	Last Author: Sebastian Schmittner
	Last Date: 2015.05.30 17:47:00 (+02:00)
	Version: 0.0.1
*/

class AnalysisJob {
	public $data;
	
	public function __construct() {
		$this->data = array();
	}
	
	public function withText( $text ) {
		$this->disassemble( $text );		
	}
	
	public function withParameters( $guid, $statusId, $status, $filename, $sampleName, 
								$md5, $email, $appName, $parameterFile ) {

		$this->data['guid'] = $guid;
		$this->data['statusId'] = $statusId;
		$this->data['status'] = $status;
		$this->data['sourcefile'] = $filename;
		$this->data['sampleName'] = $sampleName;
		$this->data['md5'] = $md5;
		$this->data['email'] = $email;
		$this->data['appName'] = $appName;
		$this->data['parameterFile'] = $parameterFile;
	}
	
	public function __destruct() {
	}
	
	public function disassemble( $text ) {
	
		$parts = explode(";", $text);
		
		if(count($parts) != 9) {
			doLog(json_encode($parts), logfile());
			throw new Exception('Wrong parameter count!');//WrongArgumentCountException();
		}
		
		$this->data['guid'] = $parts[0];
		$this->data['statusId'] = $parts[1];
		$this->data['status'] = $parts[2];
		$this->data['sourcefile'] = $parts[3];
		$this->data['sampleName'] = $parts[4];
		$this->data['md5'] = $parts[5];
		$this->data['email'] = $parts[6];
		$this->data['appName'] = $parts[7];
		$this->data['parameterFile'] = $parts[8];
	}
	
	public function reassemble() {
		return implode(";", $this->data);
	}
}

?>
