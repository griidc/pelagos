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

echo "Generating temporary directory $udi"
mkdir "$udi"

echo "Generating file: $path/$udi-ReadMe.txt"
python share/python/create-tree.py -d $1 > $udi/$readmefile
unix2dos $udi/$readmefile

echo "Generating file: $path/$udi/$udi-file-manifest.txt"
python share/python/create-tree.py $1 > $udi/$manifestfile
unix2dos $udi/$manifestfile

cp $1 .
hashfile=$(basename $1)
unix2dos $hashfile
mv $hashfile $udi
zip -r $zipfile $udi/
unzip -l $zipfile

rm -rf $udi
