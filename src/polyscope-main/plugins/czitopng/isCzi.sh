#!/bin/bash

FILENAME="${1}"
PATTERN="*.czi"

if [[ $FILENAME == $PATTERN ]]; then
  exit 0
fi

exit 1

