#!/bin/bash

# Author: Sebastian Schmittner
# Date: 2014.09.13 18:18:30 (+02:00)
# LastAuthor: Sebastian Schmittner
# LastDate: 2014.11.08 13:15:20 (+01:00)
# Version: 0.0.7

echo "Removing 'NULL' from *.csv files"
find ./ -type f -name "*.csv" -exec sudo sed -i 's/\x0//g' {}

echo "Removing 'NULL' from *.txt files"
find ./ -type f -name "*.txt" -exec sudo sed -i 's/\x0//g' {}

echo "Done"

