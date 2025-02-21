#!/bin/bash

FILENAMEPATH="${1}"
DIRNAME=$(dirname "${FILENAMEPATH}")
FILENAME=$(basename "${FILENAMEPATH}")
EXTENSION="${FILENAME##*.}"
FILEBASE="${FILENAME%%.${EXTENSION}}"

PATTERN="*.czi"
INPATH="/var/www/plugins/czitopng/input/"
OUTPATH="/var/www/plugins/czitopng/output/"

if [[ $FILENAMEPATH == $PATTERN ]]; then

  PNGFILENAME="${FILEBASE}.png"

  cp "${FILENAMEPATH}" "${INPATH}"
  /var/www/plugins/czitopng/czi2png.sh "${FILENAME}"
  mv "${INPATH}/${PNGFILENAME}" "${DIRNAME}"

  echo "${DIRNAME}/${PNGFILENAME}"
  exit 0
else
  exit 1
fi


