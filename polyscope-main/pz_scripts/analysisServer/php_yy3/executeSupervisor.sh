#!/bin/bash

# Author: Sebastian Schmittner
# Date: 2015.06.04 09:40:10 (+02:00)
# LastAuthor: Sebastian Schmittner
# LastDate: 2015.07.30 12:57:50 (+02:00)
# Version: 0.0.2

LOCKFILE="/Users/polyzoomer/polyzoomerSupervisor.lock"

# create lock
lockfile -r 0 $LOCKFILE || exit 1

# execute the supervisor
cd /Users/polyzoomer/polyzoomer/php

php ./executeSupervisor.php

# remove the lock
rm -f $LOCKFILE
