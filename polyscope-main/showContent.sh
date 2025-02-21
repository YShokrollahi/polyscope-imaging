#!/bin/bash

# Author: Sebastian Schmittner
# Date: 2015.07.25 21:00
# LastAuthor: Sebastian Schmittner
# LastDate: 2015.07.25 21:00
# Version: 0.0.1

php -r "print_r( '[' . base64_decode('$1') . ']' . PHP_EOL );"