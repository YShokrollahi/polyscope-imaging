#!/bin/bash

# default start script for applications
#
# usage: startApp.sh %PARAMETERFILE%
# The parameter handling has to happen in here

# Executes the CRImage App

CURRENTDIR="$(pwd)"

#Rscript ${CURRENTDIR}/CRImage_cli.R "${CURRENTDIR}/" &> ./analysis.log & echo $! > ./pid
Rscript ${CURRENTDIR}/CRImage_cli.R "${CURRENTDIR}/" 2>&1 & echo $! > ./pid
