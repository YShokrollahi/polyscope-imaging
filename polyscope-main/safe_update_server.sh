#!/bin/bash

# Author: Sebastian Schmittner
# Date: 2015.07.14 21:34:00 (+02:00)
# LastAuthor: Sebastian Schmittner
# LastDate: 2016.08.14 10:27:00 (+02:00)
# Version: 0.0.2

SRCPATH="/home/polyzoomer/productionPolyscope/"
TARGETPATH="/var/www/"

dos2unix "${TARGETPATH}/"*.sh
dos2unix "${SRCPATH}/"*.sh

# copy the pz sources
cp -R "${SRCPATH}"*.css "${TARGETPATH}"
cp -R "${SRCPATH}"*.html "${TARGETPATH}"
cp -R "${SRCPATH}"*.js "${TARGETPATH}"
cp -R "${SRCPATH}"*.php "${TARGETPATH}"
cp -R "${SRCPATH}"*.sh "${TARGETPATH}"

cp -R "${SRCPATH}pz_scripts" "${TARGETPATH}"
cp -R "${SRCPATH}tests" "${TARGETPATH}"

# update the version key
VERSIONKEY=`git --git-dir ~/productionPolyscope/.git describe --long --dirty --abbrev=10 --tags`
echo ${VERSIONKEY}
echo ${VERSIONKEY} > "/var/www/pz_version"

echo "Process *.html files"
find /var/www/ -maxdepth 1 -name "*.html" -type f -print0 | xargs -0 sed -i "s/VERSIONKEY/${VERSIONKEY}/g"
find /var/www/pz_scripts/ -name "*.html" -type f -print0 | xargs -0 sed -i "s/VERSIONKEY/${VERSIONKEY}/g"

echo "Process *.block files"
find /var/www/ -maxdepth 1 -name "*.block" -type f -print0 | xargs -0 sed -i "s/VERSIONKEY/${VERSIONKEY}/g"
find /var/www/pz_scripts/ -name "*.block" -type f -print0 | xargs -0 sed -i "s/VERSIONKEY/${VERSIONKEY}/g"

echo "Process *.js files"
find /var/www/ -maxdepth 1 -name "*.js" -type f -print0 | xargs -0 sed -i "s/VERSIONKEY/${VERSIONKEY}/g"
find /var/www/pz_scripts/ -name "*.js" -type f -print0 | xargs -0 sed -i "s/VERSIONKEY/${VERSIONKEY}/g"

./safe_update_all_userpages.sh

echo "DeDos files"
find /var/www/pz_scripts -type f -name "*.sh" -exec dos2unix {} \;
find /var/www/ -maxdepth 1 -type f -name "*.sh" -exec dos2unix {} \;
find /var/www/customers -type f -name "*.sh" -exec dos2unix {} \;

