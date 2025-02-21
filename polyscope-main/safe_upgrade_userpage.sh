#!/bin/bash

# Author: Sebastian Schmittner (stp.schmittner@gmail.com)
# Date: 2016.03.31 10:00:00
# LastAuthor: Sebastian Schmittner (stp.schmittner@gmail.com)
# LastDate: 2016.03.31 10:00:00
# Version: 0.0.1

INPUT="$1"

SRCPATH="/var/www/pz_scripts/polyzoomer/"
USERFOLDER="/var/www/customers/${INPUT}/"
EXECUTEABLE="createPolyzoomerSite.sh"

echo "Copying new 'createPolyzoomerSite.sh'"
find ${USERFOLDER} -maxdepth 1 -name "Path*" -exec cp "${SRCPATH}/${EXECUTEABLE}" "{}"/ \;

echo "Upgrading pages..."
find ${USERFOLDER} -maxdepth 1 -name "Path0000*" -exec /bin/su - www-data -c "cd {} && ./${EXECUTEABLE} FORCE" \;

echo "Done."

