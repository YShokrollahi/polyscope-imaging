#!/bin/bash

# Author: Andreas Heindl
# Date: -
# LastAuthor: Sebastian Schmittner
# LastDate: 2016.01.21 10:25:29 (+01:00)
# Version: 0.0.4

# Requirements:  Directories must be labeled according to the following scheme
#		 PATIENTIDPATIENTNUMBER_CHANNELNAME_ARBITRARYSTRING
#	         e.g. P02_Cycline_arbitrarytext
#
# Change log
# 0.1 Initial version
# 0.2 Visualization works (except sync)
# 0.3 Check hashtable and livesync for presence of *processed* 
# 0.6 Move files instead of copying (DO_FILES switch)
# 0.7 -maxdepth 2 added to ZOOMFILES - speed increase * 100
# 0.8 Adaption of paths to fit to the polyzoomer server
# 0.9 Add the copying of the dependencies from /templates/*
# 1.0 Remove tiling
# 1.1 Remove deepzoom    
# 1.2 Added a forced overwrite (FORCE switch)

DO_FILES=1 
DO_WEBSITE=1
PATH_TO_INSTALL_PACKAGE="/var/www/pz_scripts/polyzoomer/"
WEBDIRECTORY="page"
EXCLUDEFILES='-and ! -name *blocks* -and ! -name *template* -and ! -name . -and ! -wholename *${WEBDIRECTORY}* -and ! -name css -and ! -name static -and ! -name images -and ! -name blocks'
LINKSUFFIX="processed" # e.g. P02_HEprocessed  will be sync'ed with P02_HE

ANNOTATIONS_LINK=""


FORCEREPLACE=0
if [[ ! -z "${1// }" ]]; then
	if [[ "$1" == "FORCE" ]]; then
		FORCEREPLACE=1
		echo "[WARN]: Forced update!"
	fi
fi
##################################################

function checkIfProcessedFileAvailable {
  # $1 ... Patient ID and Channel name .. e.g. P02_CyclinA
  # returns 0 if not found else 1
  PROCESSED=`find ../ -maxdepth 1 -type d -name  "*${1}processed*"`    
  if [[ -z "$PROCESSED" ]] ; then # not found 
    echo 0	  
  else
    echo 1	  
  fi
}

# create polyzoomer directory structure
if [ $DO_FILES -eq "1" ]; then
  if [[ ! -d "$WEBDIRECTORY" || ${FORCEREPLACE} -eq 1 ]]; then  #don't overwrite already existing website
    echo "Start creating filestructure for website ..."
    mkdir "$WEBDIRECTORY"
    cp -r "$PATH_TO_INSTALL_PACKAGE"/templates/* "$WEBDIRECTORY"
    FILES=`find . -maxdepth 1 -type d ${EXCLUDEFILES}`
    echo $FILES
	for f in $FILES
    do  
      PAT_ID=`echo ${f} | egrep -i -o '[a-z]+[0-9]+' | head -1` # e.g. P10_CyclineA_sadasdasd  #added 
      echo $PAT_ID
	  #Get all image of the current patient
      ZOOMFILES=`find . -maxdepth 2 -type f -wholename "*${PAT_ID}*dzi" ${EXCLUDEFILES}`
      echo $ZOOMFILES
	  for i in $ZOOMFILES
      do
        PAT_ID=`echo ${i} | egrep -i -o '[a-z]+[0-9]+' | head -1` # e.g. P10_CyclineA_sadasdasd
        CHANNEL_ID=`echo ${i} | egrep -i -o '*_[a-z]+[0-9]*' | head -1`  #e.g. _CyclineA
        echo $PAT_ID
		echo $CHANNEL_ID
		mkdir -p "${WEBDIRECTORY}/${PAT_ID}/${CHANNEL_ID}" #get first N characters and 
	    echo "Copying ${i}..."   				   #create directory (will be grouped on a webpage)
        mv -n "${i}" "${WEBDIRECTORY}/${PAT_ID}/${CHANNEL_ID}/"
        #copy also directories
        mv -n "${i%.dzi}_files" "${WEBDIRECTORY}/${PAT_ID}/${CHANNEL_ID}/"
		BAREFILE=$(basename "${i}")
		touch "./${WEBDIRECTORY}/${PAT_ID}/${CHANNEL_ID}/${BAREFILE%.dzi}_files/annotations.txt"
		chmod -R 777  "./${WEBDIRECTORY}/${PAT_ID}/${CHANNEL_ID}/${BAREFILE%.dzi}_files/annotations.txt"
		
    # Construct the path to annotations.txt
    ANNOTATIONS_PATH="./${WEBDIRECTORY}/${PAT_ID}/${CHANNEL_ID}/${BAREFILE%.dzi}_files/annotations.txt"
    ANNOTATIONS_LINK="./${CHANNEL_ID}/${BAREFILE%.dzi}_files/annotations.txt"



      done
    done
  fi
else
  echo "[ERROR]: Website directory already exists!"
fi

# create website
VIEWERCOUNTER=0
if [ $DO_WEBSITE -eq "1" ]; then
  echo "Start generating website ..."
  cd "$WEBDIRECTORY"
  FILES=`find . -maxdepth 1 -type d ${EXCLUDEFILES}`  
  for f in $FILES
  do
    echo "Processing ${f}"
    echo "" > _tmpviewer #create tmp viewer html file
    echo "" > _tmpbody2  #create tmp hashtable html file
	
    CHANNELS=`find ${f} -maxdepth 1 -type d -and -name "_*" ${EXCLUDEFILES}`    
    PAT_ID=`echo ${f} | egrep -i -o '[a-z]+[0-9]+' | head -1` # e.g. P10
    PATHTOINDEX="./${PAT_ID}/index.html"

echo "testing permision"
echo "${PATHTOINDEX}"
	echo "./${PAT_ID}/index.html" >> "./indexes"
	
    cat ./blocks/header.block > ${PATHTOINDEX} #create index file
    #replace tags
    #sed -i "s/_PATH_TO_CSS_/..\/css/g" "${PATHTOINDEX}" 
    #sed -i "s/_PATH_TO_POLYZOOMER_/../g" "${PATHTOINDEX}" 
    sed "s/_PATH_TO_CSS_/..\/css/g" "${PATHTOINDEX}" > tmp; cat tmp > "${PATHTOINDEX}"
    sed "s/_PATH_TO_POLYZOOMER_/../g" "${PATHTOINDEX}" > tmp; cat tmp > "${PATHTOINDEX}"
    for c in $CHANNELS; do

      #search for DZI files (could be png or jpg)
	  TMPDZI=`find . -name "*.dzi" -type f -print -quit` 
	  DZINAME=`basename "$TMPDZI"`   
		
	  echo $TMPDZI
	  echo $DZINAME
	  
	  CHANNEL_ID=`echo ${c} | egrep -i -o '*_[a-z]+[0-9]*' | head -1`  #e.g. _CyclineA
      
      #check if current image has a corresponding processed one
      HASPROCESSED=$(checkIfProcessedFileAvailable ${PAT_ID}${CHANNEL_ID})
      HASPROCESSED=`echo $HASPROCESSED | sed 's/[^0-9]//g'` #remove spaces      
      if [ $HASPROCESSED -eq "1" ]; then
        echo "${PAT_ID}${CHANNEL_ID} has processed image ${PAT_ID}${CHANNEL_ID}processed"      	      
        echo "LiveSync(${PAT_ID}${CHANNEL_ID})" >> "${PATHTOINDEX}"   # corresponding PROCESSED file found	      
      else
        echo "//LiveSync(${PAT_ID}${CHANNEL_ID})" >> "${PATHTOINDEX}"      	      
      fi            
      
		VIEWERNAME=${DZINAME}
		NDPIKEY=".ndpideepzoom.dzi"
		KEYUNKNOWN="UNKNOWNPAT0001_UNKNOWNCHANNEL0001_"
		VIEWERNAME=${VIEWERNAME/$NDPIKEY}
		VIEWERNAME=${VIEWERNAME/$KEYUNKNOWN}
		
		# read: get the _ positions, get the numbers infront of the : and get the second in the list
		#SECONDUNDERSCORE=`echo $VIEWERNAME | grep -b -o '_' | cut -d: -f1 | sed '2!d;q'`

		#if [[ $VIEWERNAME == *"UNKNOWN"* ]]
		#then
			# get the file name part
		#	VIEWERNAME=${VIEWERNAME:$SECONDUNDERSCORE + 1}
		#else
			# get the detected patient and channel id
		#	VIEWERNAME=${VIEWERNAME:0:$SECONDUNDERSCORE}
		#fi
	
	  #write to tmp html file that is concated later to the body of the file
      cat ./blocks/viewer.block >> "_tmpviewer"
      ##
      #not stored in header but pre-created to concat to body afterwards
      ##
      PATHTOVIEWERIMAGE="..\/images\/"
      PATHTODZI="/${WEBDIRECTORY}/${PAT_ID}/${CHANNEL_ID}"
      #replace tags
      #sed -i "s/_CONTENTID_/${PAT_ID}${CHANNEL_ID}/g" "_tmpviewer"     
      #sed -i "s/_REL_PATH_TO_VIEWERIMAGES_/${PATHTOVIEWERIMAGE}/g" "_tmpviewer"
      #sed -i "s|_REL_PATH_TO_DZI_|./${CHANNEL_ID}/${DZINAME}|g" "_tmpviewer"
      #sed -i "s/_VIEWERNAME_/${VIEWERNAME}/g" "_tmpviewer"
      #sed -i "s/_VIEWER_VARNAME_/${PAT_ID}${CHANNEL_ID}/g" "_tmpviewer"  #important for hash table later    

      sed "s/_CONTENTID_/${PAT_ID}${CHANNEL_ID}/g" _tmpviewer > tmp; cat tmp > _tmpviewer 
      sed "s/_REL_PATH_TO_VIEWERIMAGES_/${PATHTOVIEWERIMAGE}/g" _tmpviewer > tmp; cat tmp > _tmpviewer
      sed "s+_REL_PATH_TO_DZI_+./${CHANNEL_ID}/${DZINAME}+g" _tmpviewer > tmp; cat tmp > _tmpviewer
      sed "s/_VIEWERNAME_/${VIEWERNAME}/g" _tmpviewer > tmp; cat tmp > _tmpviewer
      sed "s/_VIEWER_VARNAME_/${PAT_ID}${CHANNEL_ID}/g" _tmpviewer > tmp; cat tmp > _tmpviewer  
      let VIEWERCOUNTER=VIEWERCOUNTER+1      
      
	  ##
      # hash tables for linking
      ##
      if [ $HASPROCESSED -eq "1" ]; then      
      	      echo "ViewerHash['_VIEWERID_'] = _VIEWERVARNAME_;" >> _tmpbody2
      	      #replace tags
      	      #sed -i "s/_VIEWERID_/${PAT_ID}${CHANNEL_ID}/g" "_tmpbody2"     
      	      #sed -i "s/_VIEWERVARNAME_/${PAT_ID}${CHANNEL_ID}${LINKSUFFIX}/g" "_tmpbody2"
              sed "s/_VIEWERID_/${PAT_ID}${CHANNEL_ID}/g" _tmpbody2 > tmp; cat tmp > _tmpbody2
      	      sed "s/_VIEWERVARNAME_/${PAT_ID}${CHANNEL_ID}${LINKSUFFIX}/g" _tmpbody2 > tmp; cat tmp > _tmpbody2
      else
      	      echo "//ViewerHash['_VIEWERID_'] = _VIEWERVARNAME_;" >> _tmpbody2
      	      #replace tags
      	      #sed -i "s/_VIEWERID_/${PAT_ID}${CHANNEL_ID}/g" "_tmpbody2"     
      	      #sed -i "s/_VIEWERVARNAME_/${PAT_ID}${CHANNEL_ID}${LINKSUFFIX}/g" "_tmpbody2"
              sed "s/_VIEWERID_/${PAT_ID}${CHANNEL_ID}/g" _tmpbody2 > tmp; cat tmp > _tmpbody2    
      	      sed "s/_VIEWERVARNAME_/${PAT_ID}${CHANNEL_ID}${LINKSUFFIX}/g" _tmpbody2 > tmp; cat tmp > _tmpbody2       	      
      fi
      	      

    done
  #close header

  cat ./blocks/body1.block >> "${PATHTOINDEX}"

  # Process the body1.block file to insert the annotations link
  # sed "s|<!--ANNOTATIONS_LINK-->|<a id='downloadAnnotationsBtn' href='${ANNOTATIONS_LINK}' download>Download Annotations</a>|g" ./blocks/body1.block > tmp_body1.block

  # Concatenate the modified body1.block to the HTML file
  cat tmp_body1.block >> "${PATHTOINDEX}"

  # Optionally, remove the temporary file after use
  rm tmp_body1.block



  #add viewer scripts
  cat ./_tmpviewer >> "${PATHTOINDEX}"

  #add last part (hash)
  echo "</tr>" >> "${PATHTOINDEX}"
  echo "</table>" >> "${PATHTOINDEX}"
  echo '<div id="annotationsSection" style="border: 2px solid;padding: 10px;margin: 1px 10px 3px 10px;">' >> "${PATHTOINDEX}"
  echo '<h2 class="buttonStyle"> <i class="fas fa-sticky-note"></i>   Annotations</h2>' >> "${PATHTOINDEX}"
  echo '<div id="annotationSummaryDisplay" style="margin: 20px; font-size: 16px"></div>' >> "${PATHTOINDEX}"
  echo "<a id='downloadAnnotationsBtn' href='${ANNOTATIONS_LINK}' download='Annotations_${PAT_ID}${CHANNEL_ID}.txt'><i class='fas fa-download'></i>   Download</a>" >> "${PATHTOINDEX}"
  echo "</div>" >> "${PATHTOINDEX}"  # This closes the annotationsSection div
  echo "</div>" >> "${PATHTOINDEX}"  # This adds the closing div tag
  echo "<script type="text/javascript">" >> "${PATHTOINDEX}"
  echo "var ViewerHash = new Object();"  >> "${PATHTOINDEX}"
  cat ./_tmpbody2 >> "${PATHTOINDEX}" 
  echo "</script>" >> "${PATHTOINDEX}"

# Set the JavaScript variable with the annotations link
  echo "<script type=\"text/javascript\">var annotationsPath = '${ANNOTATIONS_LINK}';</script>" >> "${PATHTOINDEX}"

  # Link the JavaScript file
  echo "<script type=\"text/javascript\" src=\"../enhancedAnnotationManager.js\"></script>" >> "${PATHTOINDEX}"
  echo "</body>" >> "${PATHTOINDEX}"
  done

fi

# test