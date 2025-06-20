#!/bin/bash

find /var/www/polyzoomer -mindepth 1 -maxdepth 1 -type d -exec ln -s -t /var/www/customers/polyzoomer-mdanderson-org {} \;
