<?php

include 'sendEmail.php';
include 'serverCredentials.php';

sendMail("polyzoomer@gmail.com", "test", "test" . $externalLink);

?>