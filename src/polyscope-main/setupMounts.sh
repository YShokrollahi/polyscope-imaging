#!/bin/bash

# Author: Sebastian Schmittner
# Date: 2015.05.18
# LastAuthor: Sebastian Schmittner
# LastDate: 2015.06.04 09:39:26 (+02:00)
# Version: 0.0.2

# I did not have the sudo pw for yy3 therefore I mount it from PZ
# TODO: change the mount direction

echo "Setting up mounts"
mkdir ./analyses
mkdir ./analyses/analysis_in
mkdir ./analyses/analysis_out
mkdir ./analyses/analysis_jobs

SETTINGS=" -o idmap=user -o uid=33 -o gid=33 -o allow_other -o default_permissions"
REMOTE="polyzoomer@yy3.local:/Users/polyzoomer/polyzoomer/"
LOCAL="./"
PATHIN="analyses/analysis_in"
PATHOUT="analyses/analysis_out"
PATHJOBS="analyses/analysis_jobs"

#sshfs polyzoomer@yy3.local:/Users/polyzoomer/polyzoomer/analyses/analysis_in ./analyses/analysis_in $SETTINGS
#sshfs polyzoomer@yy3.local:/Users/polyzoomer/polyzoomer/analyses/analysis_out ./analyses/analysis_out $SETTINGS
#sshfs polyzoomer@yy3.local:/Users/polyzoomer/polyzoomer/analyses/analysis_jobs ./analyses/analysis_jobs $SETTIGNS

sshfs $REMOTE$PATHIN   $LOCAL$PATHIN   $SETTINGS
sshfs $REMOTE$PATHOUT  $LOCAL$PATHOUT  $SETTINGS
sshfs $REMOTE$PATHJOBS $LOCAL$PATHJOBS $SETTINGS

echo "Done"

