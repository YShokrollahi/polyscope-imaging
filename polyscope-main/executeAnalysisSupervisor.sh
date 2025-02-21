#!/bin/bash

# Author: Sebastian Schmittner
# Date: 2015.05.30 17:46:10 (+02:00)
# LastAuthor: Sebastian Schmittner
# LastDate: 2015.05.30 17:46:10 (+02:00)
# Version: 0.0.1

LOCKFILE="/tmp/polyzoomerAnalysisSupervisor.lock"

# create lock
lockfile -r 0 $LOCKFILE || exit 1

# execute the supervisor
php ./executeAnalysisSupervisor.php

# remove the lock
rm -f $LOCKFILE
