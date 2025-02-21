#!/bin/bash

# Author: Sebastian Schmittner
# Date: 2014.07.23
# LastAuthor: Sebastian Schmittner
# LastDate: 2014.11.08 13:15:34 (+01:00)
# Version: 0.0.6

sudo cp _andi_sendEmail.php sendEmail.php 
sudo cp _andi_serverCredentials.php serverCredentials.php
php testEmail.php 
