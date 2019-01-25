#!/bin/bash

#
#  Creates zip file with manifest files.
#

if [ "$1" == "" ]
    then
        echo "No argument provided!"
    fi

for arg in "$@"
do
    if [ "$arg" == "--help" ] || [ "$arg" == "-h" ]
    then
        echo "Help argument detected."
    fi
done
path=`pwd`
udi=`echo $1 | grep -oP "([A-Za-z0-9]{2}.x[0-9]{3}.[0-9]{3})[.:]([0-9]{4})"`

echo "Processing UDI: $udi"

readmefile="$udi-ReadMe.txt"
manifestfile="$udi-file-manifest.txt"
zipfile="$udi-manifest.zip"

echo "Generating file: $path/$udi-ReadMe.txt"
python share/python/create-tree.py -d $path/$1 > $readmefile
unix2dos $readmefile

echo "Generating file: $path/$udi-file-manifest.txt"
python share/python/create-tree.py $path/$1 > $manifestfile
unix2dos $manifestfile

zip $zipfile $1
zip $zipfile -m $manifestfile $readmefile
unzip -l $zipfile
