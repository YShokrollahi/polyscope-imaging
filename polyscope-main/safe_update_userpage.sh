#!/bin/bash

# Author: Sebastian Schmittner
# Date: 2015.07.14 21:34:00 (+02:00)
# LastAuthor: Sebastian Schmittner
# LastDate: 2015.07.24 20:26:56 (+02:00)
# Version: 0.0.1

[ $# -ge 1 ] && INPUT="$1" || INPUT="-"

FILE=`cat $INPUT`

SOURCE="/var/www/pz_scripts/userpage/"
TARGET="/var/www/customers/${FILE}/"

if [ ! -d "$TARGET" ]; then
	echo "User [${FILE}] at [${TARGET}] does not exist!"
	exit
fi

echo -n "Updating user [${FILE}] "
sudo cp -R "$SOURCE"*.php "$TARGET"
sudo cp -R "$SOURCE"*.sh "$TARGET"
sudo cp -R "$SOURCE"*.html "$TARGET"
sudo cp -R "$SOURCE"*.css "$TARGET"
sudo cp -R "$SOURCE"*.js "$TARGET"
sudo cp -R "$SOURCE"*.less "$TARGET"

sudo cp -R "$SOURCE"css "$TARGET"
sudo cp -R "$SOURCE"external "$TARGET"
sudo cp -R "$SOURCE"fonts "$TARGET"
sudo cp -R "$SOURCE"images "$TARGET"
sudo cp -R "$SOURCE"js "$TARGET"
sudo cp -R "$SOURCE"multizooms "$TARGET"
sudo cp -R "$SOURCE"templates "$TARGET"

echo "- Done"

