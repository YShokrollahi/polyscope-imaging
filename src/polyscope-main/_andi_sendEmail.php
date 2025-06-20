<?php 

require_once __DIR__ . "/pear/share/pear/Mail.php";

// POST
/*
  adress: email adress of the recepient
  cleanadress: cleaned email adress
  samples: list of samples to see
	- path (incl index.html)
	- delete code
*/

function sendMail($recepient, $filename, $text){
	
	$from = "<polyzoomer@icr.ac.uk>";
	$subject = "[" . $filename . "] is ready";
	$to = $recepient;
	
	$headers = array(
		'From' => $from,
		'To' => $to,
		'Subject' => $subject
	);
	
	$smtp = Mail::factory('smtp', array(
		'host' => 'outlook.icr.ac.uk',
		'port' => '587',
		'auth' => true,
		'username' => 'polyzoomer',
		'password' => '$$$PzForever$$$'
	));
	
	$mail = $smtp->send($to, $headers, $text);
	
	if(PEAR::isError($mail)) {
		echo('<p>' . $mail->getMessage() . '<p>');
	}
	else {
		echo('<p>Message successfully sent!<p>');
	}
	
}

?>
