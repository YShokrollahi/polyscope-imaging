#!/bin/bash

# Author: Sebastian Schmittner
# Date: 2015.06.04 09:40:10 (+02:00)
# LastAuthor: Sebastian Schmittner
# LastDate: 2015.08.13 13:37:53 (+02:00)
# Version: 0.0.3

LOCKFILE="/home/molecpath/aheindl/scratch/polyzoomer/polyzoomerSupervisor.lock"

# create lock
# lockfile -r 0 $LOCKFILE || exit 1

(
  flock -xn 200 || exit 1

  # execute the supervisor
  cd /home/molecpath/aheindl/scratch/polyzoomer/php

  php ./executeSupervisor.php
) 200>${LOCKFILE}

# remove the lock
#rm -f $LOCKFILE
