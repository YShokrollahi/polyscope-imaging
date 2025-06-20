#!/bin/bash

# Author: Sebastian Schmittner
# Date: 2015.07.28 22:08:03 (+02:00)
# LastAuthor: Sebastian Schmittner
# LastDate: 2015.07.28 22:08:03 (+02:00)
# Version: 0.0.3

SRCPATH="/home/polyscope/productionPolyscope/pz_scripts/userpage/"
ROOTPATH="/var/www/"
GLOBALTARGETPATH="${ROOTPATH}pz_scripts/userpage/"
LOCALTARGETBASEPATH="${ROOTPATH}customers/"

echo "Updating global userpage"
sudo cp -R "${SRCPATH}"* "${GLOBALTARGETPATH}"

echo "Updating local userpages"
find ${LOCALTARGETBASEPATH} -maxdepth 1 -mindepth 1 -type d -name '*-*' -exec sh -c "basename {} | ${ROOTPATH}safe_update_userpage.sh" \;

echo "Done"

