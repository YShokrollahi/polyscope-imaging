#!/bin/bash

# Author: Sebastian Schmittner
# Date: 2015.04.09 14:21:45 (+02:00)
# LastAuthor: Sebastian Schmittner
# LastDate: 2015.04.09 14:21:45 (+02:00)
# Version: 0.0.2

echo "Generating OMEXML for all NDPI files"
find ./polyzoomer -type f -maxdepth 2 -mindepth 2 -name '*.ndpi' -exec bash -c "./getOMEXML.sh {}" \;
# find all ndpi's, start for each a bash and pipe the showinf stuff into the same named xml

echo "Generating XML for all JPEG files"
find ./polyzoomer -type f -maxdepth 2 -mindepth 2 -name '*.jpg' -exec bash -c "./getOMEXML.sh {}" \;
find ./polyzoomer -type f -maxdepth 2 -mindepth 2 -name '*.jpeg' -exec bash -c "./getOMEXML.sh {}" \;

echo "Generating XML for all PNG files"
# 1.7]
find ./polyzoomer -type f -maxdepth 2 -mindepth 2 \( -iname '*.png' ! -iname '*THUMBNAIL*' ! -iname '*0_thumb*' \) -exec bash -c "./getOMEXML.sh {}" \;
# V [1.8+ find ./polyzoomer -type f \( -iname "*.png" ! -iname "*THUMBNAIL*" \) -exec bash -c "getOMEXML.sh {}" \;

#find ./polyzoomer -mindepth 1 -maxdepth 1 -type d '!' -exec test -e "{}/0_thumb.png" ';' -exec bash -c "find {} -maxdepth 1 -name '*.png'" \;

echo "Done"

