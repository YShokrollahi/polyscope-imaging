#!/bin/bash

# insall nodejs and npm for 12.04
sudo apt-get update
sudo apt-get install -y python-software-properties
sudo add-apt-repository ppa:chris-lea/node.js
sudo apt-get update
sudo apt-get install nodejs
sudo apt-get install npm

