#!/bin/bash

# Author: Sebastian Schmittner
# Date: 2015.01.24 16:17:24 (+01:00)
# LastAuthor: Sebastian Schmittner
# LastDate: 2015.01.24 16:17:24 (+01:00)
# Version: 0.0.1

echo "Updating local userpage"
sudo cp -R ~/productionPolyscope/pz_scripts/userpage/* ./

echo "Updating global userpage"
sudo cp -R ~/productionPolyscope/pz_scripts/userpage/* /var/www/pz_scripts/userpage/

echo "Done"

