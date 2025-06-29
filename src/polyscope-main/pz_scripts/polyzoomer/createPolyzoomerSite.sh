#!/bin/bash

# Author: Andreas Heindl
# Date: -
# LastAuthor: Sebastian Schmittner
# LastDate: 2016.01.21 10:25:29 (+01:00)
# Version: 2.0.1 - Fixed template placeholder processing

# Requirements:  Directories must be labeled according to the following scheme
#		 PATIENTIDPATIENTNUMBER_CHANNELNAME_ARBITRARYSTRING
#	         e.g. P02_Cycline_arbitrarytext

DO_FILES=1 
DO_WEBSITE=1
PATH_TO_INSTALL_PACKAGE="/var/www/pz_scripts/polyzoomer/"
WEBDIRECTORY="page"
EXCLUDEFILES='-and ! -name *blocks* -and ! -name *template* -and ! -name . -and ! -wholename *${WEBDIRECTORY}* -and ! -name css -and ! -name static -and ! -name images -and ! -name blocks -and ! -name js'
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
      # If PAT_ID is empty, extract it from the directory name directly
      if [[ -z "$PAT_ID" ]]; then
          PAT_ID=`echo ${f} | cut -d'_' -f1 | sed 's|^\./||'`
      fi
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
    # If PAT_ID is empty, extract it from the directory name directly
    if [[ -z "$PAT_ID" ]]; then
        PAT_ID=`echo ${f} | cut -d'_' -f1 | sed 's|^\./||'`
    fi
    PATHTOINDEX="./${PAT_ID}/index.html"

    echo "testing permission"
    echo "${PATHTOINDEX}"
	echo "./${PAT_ID}/index.html" >> "./indexes"
	
    cat ./blocks/header.block > ${PATHTOINDEX} #create index file
    
    # Process each channel to get the first one for slide info
    FIRST_CHANNEL=""
    FIRST_DZI=""
    SLIDE_INFO=""
    CHANNEL_NAME=""
    
    for c in $CHANNELS; do
      if [[ -z "$FIRST_CHANNEL" ]]; then
        FIRST_CHANNEL=$c
        #search for DZI files (could be png or jpg)
        TMPDZI=`find $c -name "*.dzi" -type f -print -quit` 
        if [[ ! -z "$TMPDZI" ]]; then
          FIRST_DZI=`basename "$TMPDZI"`
          SLIDE_INFO="${FIRST_DZI%.dzi}"
          CHANNEL_ID=`echo ${c} | egrep -i -o '*_[a-z]+[0-9]*' | head -1`  #e.g. _CyclineA
          # If CHANNEL_ID is empty, extract it from the directory name directly  
          if [[ -z "$CHANNEL_ID" ]]; then
              CHANNEL_ID=`echo ${c} | cut -d'_' -f2`
              CHANNEL_ID="_${CHANNEL_ID}"
          fi
          CHANNEL_NAME=${CHANNEL_ID#_}  # Remove leading underscore
          # Set annotations link for the first channel
          BAREFILE=$(basename "${TMPDZI}")
          ANNOTATIONS_LINK="./${CHANNEL_ID}/${BAREFILE%.dzi}_files/annotations.txt"
        fi
        break
      fi
    done
    
    for c in $CHANNELS; do
      #search for DZI files (could be png or jpg)
	  TMPDZI=`find $c -name "*.dzi" -type f -print -quit` 
	  DZINAME=`basename "$TMPDZI"`   
		
	  echo $TMPDZI
	  echo $DZINAME
	  
	  CHANNEL_ID=`echo ${c} | egrep -i -o '*_[a-z]+[0-9]*' | head -1`  #e.g. _CyclineA
    # If CHANNEL_ID is empty, extract it from the directory name directly  
    if [[ -z "$CHANNEL_ID" ]]; then
        CHANNEL_ID=`echo ${c} | cut -d'_' -f2`
        CHANNEL_ID="_${CHANNEL_ID}"
    fi
      
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
	
	  #write to tmp html file that is concated later to the body of the file
      cat ./blocks/viewer.block >> "_tmpviewer"
      
      PATHTOVIEWERIMAGE="..\/images\/"
      PATHTODZI="/${WEBDIRECTORY}/${PAT_ID}/${CHANNEL_ID}"
      
      # CRITICAL FIX: Replace all placeholders in the correct order and format
      # First, set up the viewer variable name
      VIEWER_VAR_NAME="${PAT_ID}${CHANNEL_ID}"
      
      #replace tags in viewer block - FIXED PLACEHOLDER PATTERNS
      sed "s/_CONTENTID_/${PAT_ID}${CHANNEL_ID}/g" _tmpviewer > tmp; cat tmp > _tmpviewer 
      sed "s/_REL_PATH_TO_VIEWERIMAGES_/${PATHTOVIEWERIMAGE}/g" _tmpviewer > tmp; cat tmp > _tmpviewer
      sed "s+_REL_PATH_TO_DZI_+./${CHANNEL_ID}/${DZINAME}+g" _tmpviewer > tmp; cat tmp > _tmpviewer
      sed "s/_VIEWERNAME_/${VIEWERNAME}/g" _tmpviewer > tmp; cat tmp > _tmpviewer
      
      # CRITICAL FIX: Handle both placeholder formats for viewer variable name
      sed "s/_VIEWER_VARNAME_/${VIEWER_VAR_NAME}/g" _tmpviewer > tmp; cat tmp > _tmpviewer  
      sed "s/\*VIEWER\*VARNAME_/${VIEWER_VAR_NAME}/g" _tmpviewer > tmp; cat tmp > _tmpviewer
      
      let VIEWERCOUNTER=VIEWERCOUNTER+1      
      
	  ##
      # hash tables for linking
      ##
      if [ $HASPROCESSED -eq "1" ]; then      
      	      echo "ViewerHash['_VIEWERID_'] = _VIEWERVARNAME_;" >> _tmpbody2
      	      sed "s/_VIEWERID_/${PAT_ID}${CHANNEL_ID}/g" _tmpbody2 > tmp; cat tmp > _tmpbody2
      	      sed "s/_VIEWERVARNAME_/${PAT_ID}${CHANNEL_ID}${LINKSUFFIX}/g" _tmpbody2 > tmp; cat tmp > _tmpbody2
      else
      	      echo "//ViewerHash['_VIEWERID_'] = _VIEWERVARNAME_;" >> _tmpbody2
              sed "s/_VIEWERID_/${PAT_ID}${CHANNEL_ID}/g" _tmpbody2 > tmp; cat tmp > _tmpbody2    
      	      sed "s/_VIEWERVARNAME_/${PAT_ID}${CHANNEL_ID}${LINKSUFFIX}/g" _tmpbody2 > tmp; cat tmp > _tmpbody2       	      
      fi

    done
    
    #replace tags in header/body1 - ENSURE ALL PLACEHOLDERS ARE REPLACED
    sed "s/_PATH_TO_CSS_/..\/css/g" "${PATHTOINDEX}" > tmp; cat tmp > "${PATHTOINDEX}"
    sed "s/_PATH_TO_POLYZOOMER_/../g" "${PATHTOINDEX}" > tmp; cat tmp > "${PATHTOINDEX}"
    sed "s/_SLIDE_INFO_/${SLIDE_INFO}/g" "${PATHTOINDEX}" > tmp; cat tmp > "${PATHTOINDEX}"
    sed "s/_CHANNEL_NAME_/${CHANNEL_NAME}/g" "${PATHTOINDEX}" > tmp; cat tmp > "${PATHTOINDEX}"
    sed "s/_PATIENT_ID_/${PAT_ID}/g" "${PATHTOINDEX}" > tmp; cat tmp > "${PATHTOINDEX}"
    sed "s+_ANNOTATIONS_LINK_+${ANNOTATIONS_LINK}+g" "${PATHTOINDEX}" > tmp; cat tmp > "${PATHTOINDEX}"
    
    # ADDITIONAL FIX: Replace any remaining placeholder patterns that might exist
    sed "s/_CHANNEL_ID_/${CHANNEL_ID}/g" "${PATHTOINDEX}" > tmp; cat tmp > "${PATHTOINDEX}"
    
    # Add body1 content
    cat ./blocks/body1.block >> "${PATHTOINDEX}"

  #add viewer scripts
  cat ./_tmpviewer >> "${PATHTOINDEX}"
  
  #add hash table
  cat ./_tmpbody2 >> "${PATHTOINDEX}" 
  echo "</script>" >> "${PATHTOINDEX}"

  # Set the JavaScript variable with the annotations link
  echo "<script type=\"text/javascript\">var annotationsPath = '${ANNOTATIONS_LINK}';</script>" >> "${PATHTOINDEX}"
  
  # CRITICAL FIX: Add JavaScript configuration object for main.js
  echo "<script type=\"text/javascript\">" >> "${PATHTOINDEX}"
  echo "// Configuration for Enhanced Annotation Manager and main.js" >> "${PATHTOINDEX}"
  echo "window.polyscopeConfig = {" >> "${PATHTOINDEX}"
  echo "  annotationsPath: '${ANNOTATIONS_LINK}'," >> "${PATHTOINDEX}"
  echo "  patientId: '${PAT_ID}'," >> "${PATHTOINDEX}"
  echo "  channelId: '${CHANNEL_ID}'," >> "${PATHTOINDEX}"
  echo "  contentId: '${PAT_ID}${CHANNEL_ID}'," >> "${PATHTOINDEX}"
  echo "  viewerVarName: '${PAT_ID}${CHANNEL_ID}'" >> "${PATHTOINDEX}"
  echo "};" >> "${PATHTOINDEX}"
  echo "</script>" >> "${PATHTOINDEX}"

  # Link the JavaScript file
  echo "<script type=\"text/javascript\" src=\"../enhancedAnnotationManager.js\"></script>" >> "${PATHTOINDEX}"
  echo "</body>" >> "${PATHTOINDEX}"
  echo "</html>" >> "${PATHTOINDEX}"
  
  # Clean up temp files
  rm -f _tmpviewer _tmpbody2 tmp

  done
fi