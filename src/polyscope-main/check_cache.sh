#!/bin/bash

# Author: Sebastian Schmittner
# Date: 2015.08.03 21:53:36 (+02:00)
# LastAuthor: Sebastian Schmittner
# LastDate: 2015.08.03 21:53:36 (+02:00)
# Version: 0.0.2

USERNAME="$1"
USERPATH="/var/www/customers/${USERNAME}/"
CACHEFILE="/var/www/customers/${USERNAME}/cache.lst"

ZOOMS=$(find ${USERPATH} -maxdepth 1 -type l)

printf "Checking ${USERNAME}\n"

while read -r line; do
	line=$(basename "$line")
	
	ISNOTCONTAINED=$(grep -q "$line" "$CACHEFILE"; echo $?)
	if [ ${ISNOTCONTAINED} -eq 1 ]; then
		printf "Adding ${line}...\n"
		$(/var/www/addZoomToUser.sh $line $USERNAME)
	fi
done <<< "$ZOOMS"

printf "Done\n"

