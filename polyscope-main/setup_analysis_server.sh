#!/bin/bash

# Author: Sebastian Schmittner
# Date: 2015.08.10 19:38
# LastAuthor: Sebastian Schmittner
# LastDate: 2015.08.10 19:38
# Version: 0.0.1

TARGETDIR=${1}
cd $TARGETDIR

mkdir analyses
mkdir analyses/analysis_in
mkdir analyses/analysis_out
mkdir analyses/analysis_jobs

mkdir bftools
mkdir jobcontainers
mkdir modules
mkdir php

