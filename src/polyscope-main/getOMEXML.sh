#!/bin/bash

# Author: Sebastian Schmittner
# Date: 2015.04.09 14:21:51 (+02:00)
# LastAuthor: Sebastian Schmittner
# LastDate: 2015.04.09 14:21:51 (+02:00)
# Version: 0.0.4

# Description: tests if the provided filename contains "THUMBNAIL_", in which case
# the file is with high probability an internal file. Otherwise it generates the
# OMEXML xml file.

FILEOFINTEREST=$1

if [[ ! "${FILEOFINTEREST}" =~ "THUMBNAIL_" ]]; then
	sudo /var/www/pz_scripts/bmtools/showinf -nopix -omexml -novalid "${FILEOFINTEREST}" > "${FILEOFINTEREST}.xml"
fi

