#!/bin/bash

# Author: Sebastian Schmittner
# Date: 2015.01.24 16:16:51 (+01:00)
# LastAuthor: Sebastian Schmittner
# LastDate: 2015.06.16 13:28:53 (+02:00)
# Version: 0.0.4

SETUPFILE=$1
SETUPPATH=${SETUPFILE%/*}
DEFAULTSIDELENGHT=256 # pixels
EXECUTIONDIR=$PWD
BORDER=4 # pixels

echo "Start generating thumbnails ..."

COLS=`sed '1q;d' $1`
ROWS=`sed '2q;d' $1`
EMAIL=`sed '3q;d' $1`
ITEMS=`expr $COLS \* $ROWS`

IMAGELIST=""

for Y in `seq 1 $ROWS`;
do

	for X in `seq 1 $COLS`;
	do

		ITEMSPERROW=2
		X1=`expr $X - 1`
		Y1=`expr $Y - 1`
		XI=`expr $ROWS \* $X1 \* $ITEMSPERROW`
		YI=`expr 4 + $Y1 \* $ITEMSPERROW`
		
		INDEXDZI=`expr $XI + $YI`
		DZI=`sed "${INDEXDZI}q;d" ${SETUPFILE}`

		if [[ -z $DZI ]]
		then
			# generate a filling thumbnail for empty squares
			cd "$SETUPPATH"
			convert -size ${DEFAULTSIDELENGHT}x${DEFAULTSIDELENGHT} xc:black "THUMBNAIL_${X}_${Y}.png"
			cd "$EXECUTIONDIR"
		else
			PATHTODZI="${DZI##*/customers/}"
			PATHTODZI="${PATHTODZI/.dzi/}"
			PATHTODZIFILES="/var/www/customers/${PATHTODZI}_files/"
			
			#              - find all directories                - which contain JPEGs - exact 2 of them - and print            -
			PATHTOOVERVIEW=""
			IMAGECOUNTER=2
			LOOPCOUNTER=0
			export IMAGECOUNTER
			
			DIRECTORYCOUNT=$(find "${PATHTODZIFILES}" -type d | wc | awk '{ print $1 };') 
			DIRECTORYCOUNT=`expr $DIRECTORYCOUNT - 1`
			
			while [[ -z $PATHTOOVERVIEW && $LOOPCOUNTER -lt $DIRECTORYCOUNT ]]; do
				PATHTOOVERVIEW=`find "${PATHTODZIFILES}" -type d -exec sh -c 'set -- "$0"/*.jpeg; [ $# -eq ${IMAGECOUNTER} ]' {} \; -print`
				#PATHTOOVERVIEW=`find ${PATHTODZIFILES} -type d -exec sh -c '${COMMAND}' {} \; -print`
				LOOPCOUNTER=`expr $LOOPCOUNTER + 1`
				IMAGECOUNTER=`expr $IMAGECOUNTER + 1`
				export IMAGECOUNTER
			done
			
			if [[ $LOOPCOUNTER -eq $DIRECTORYCOUNT ]]
			then
				cd "$SETUPPATH"
				convert -size ${DEFAULTSIDELENGHT}x${DEFAULTSIDELENGHT} xc:black "THUMBNAIL_${X}_${Y}.png"
				cd "$EXECUTIONDIR"
			else
				OVERVIEWIMAGE=`echo "${PATHTOOVERVIEW}" | head -1`
				OVERVIEWIMAGE="${OVERVIEWIMAGE}/0_0.jpeg"
				
				cd "$SETUPPATH"
				convert "${OVERVIEWIMAGE}" -resize ${DEFAULTSIDELENGHT}x${DEFAULTSIDELENGHT} -background black -gravity center -extent ${DEFAULTSIDELENGHT}x${DEFAULTSIDELENGHT} "THUMBNAIL_${X}_${Y}.png"
				cd "$EXECUTIONDIR"
			fi
			
		fi

		IMAGELIST="${IMAGELIST} THUMBNAIL_${X}_${Y}.png"
	done

done

cd "$SETUPPATH"
montage ${IMAGELIST} -tile ${COLS}x${ROWS} -geometry ${DEFAULTSIDELENGHT}x${DEFAULTSIDELENGHT}+${BORDER}+${BORDER} -background black -gravity center "THUMBNAIL_OVERVIEW.png"
cd "$EXECUTIONDIR"