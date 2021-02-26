#!/bin/bash
# This needs to run as an administrative user.

homeDir='/san/home'
maxAgeDays=30

find $homeDir/upload/files -type f -mtime +$maxAgeDays -exec rm -f {} \;
find $homeDir/upload/chunks -type f -mtime +$maxAgeDays -exec rm -f {} \;
