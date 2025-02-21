#!/bin/bash

# Author: Sebastian Schmittner
# Date: 2015.05.13 09:23:36 (+02:00)
# LastAuthor: Sebastian Schmittner
# LastDate: 2015.05.13 09:23:36 (+02:00)
# Version: 0.0.1

VERSIONKEY=`git --git-dir .git describe --long --dirty --abbrev=10 --tags`
echo ${VERSIONKEY}

