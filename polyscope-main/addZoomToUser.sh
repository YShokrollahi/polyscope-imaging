#!/bin/bash

# Author: Sebastian Schmittner (stp.schmittner@gmail.com)
# Date: 2015.06.23
# LastAuthor: Sebastian Schmittner
# LastDate: 2015.06.23
# Version: 0.0.1

# $1 path 
# $2 clean email (e.g. test-mdanderson-org)

# execute the supervisor
sudo php ./addZoomToUser.php "$1" "$2"
