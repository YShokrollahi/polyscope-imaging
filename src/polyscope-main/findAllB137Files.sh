#!/bin/bash

# Author: Sebastian Schmittner
# Date: 2015.04.29 13:31:53 (+02:00)
# LastAuthor: Sebastian Schmittner
# LastDate: 2015.04.30 08:44:20 (+02:00)
# Version: 0.0.2

#sudo grep --include=index.html -rlw '/var/www/polyzoomer/' -e "tileSources:     \"http" > ~/B137AffectedFiles.log

# echo '' > ~/B137DZIs.log

# while read in; do
	# DIRNAME=`dirname "$in"`
	# RELSETUP="/../../setup.cfg"
	# SETUP=$DIRNAME$RELSETUP
	# grep 'http' $SETUP >> ~/B137DZIs.log
# done < ~/B137AffectedFiles.log

# echo '' > ~/B137Folder.log

# EMAIL="http://polyzoomer.icr.ac.uk"
# TOPATH="/var/www"

# while read in; do
	# INPATH=`dirname "$in"`
	
	# DZIPATH="${INPATH/$EMAIL/$TOPATH}"
	# ABSOLUTEPATH=`dirname "$DZIPATH"`
	# RELPATH="/../"
	# ABSOLUTEPATH=$ABSOLUTEPATH$RELPATH
	# echo $ABSOLUTEPATH >> ~/B137Folder.log
# done < ~/B137DZIs.log

# SOURCE="/var/www/pz_scripts/userpage/templates/updateAnnotationFile.php"

# echo '' > ~/B137Copies.log

# while read in; do
	# cp "$SOURCE" "$in"
	# echo cp "$SOURCE" "$in" >> ~/B137Copies.log
# done < ~/B137Folder.log


 SOURCE="/var/www/pz_scripts/userpage/templates/updateAnnotationFile.php"

while read in; do
	DIRNAME=`dirname "$in"`
	RPATH='/..'
	REALDIR=$DIRNAME$RPATH
	cp "$SOURCE" "$REALDIR"
done < ~/B137AffectedFiles.log
