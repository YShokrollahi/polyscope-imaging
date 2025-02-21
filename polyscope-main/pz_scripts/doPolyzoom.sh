#!/bin/bash

# Author: Sebastian Schmittner
# Date: 2014.07.08
# LastAuthor: Sebastian Schmittner
# LastDate: 2016.04.13 11:09
# Version: 0.1.0

# log the file size
ls "$1" -l | awk ' {print $5}' >> polyzoom.pid
# log the filename
echo "$1" >> polyzoom.pid

# log start time
date >> polyzoom.pid

t1=`dirname "${1}"`
t2=`basename "${1}"` 
t3="${t2##*.}" #extension 
WORKINGDIR=`echo "${t1}""/""${t2%%.${t3}}"` 

chmod 777 ./createPolyzoomerSite.sh

mkdir "${WORKINGDIR}"
# cp "$1" "${WORKINGDIR}"
#time /usr/local/bin/vips dzsave "${1}" "${WORKINGDIR}/${1}deepzoom" &> deepzoom.log
time /usr/local/bin/vips dzsave "${1}" "${WORKINGDIR}/" &> deepzoom.log

FILENAME="${t2%%.${t3}}"
DZIIN="${WORKINGDIR}/${FILENAME}.dzi"
FILESIN="${WORKINGDIR}/${FILENAME}_files"
DZIOUT="${WORKINGDIR}/${t2}deepzoom.dzi"
FILESOUT="${WORKINGDIR}/${t2}deepzoom_files"

mv "${DZIIN}" "${DZIOUT}"
mv "${FILESIN}" "${FILESOUT}"

# create the polyzoomer site
date > createSiteTiming.log
time bash +x ./createPolyzoomerSite.sh &> creation.log
date >> createSiteTiming.log

# perform OME XML extraction
/var/www/pz_scripts/bmtools/showinf -nopix -omexml -novalid "${1}" > "${1}.xml"

# log end time
date >> polyzoom.pid

# mark as finished
mv polyzoom.pid polyzoom.pid.done


