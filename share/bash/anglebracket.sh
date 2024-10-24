#!/bin/bash
for file in $(grep -ir 'CustomAssert\\NoAngleBrackets' src/ | sed "s/:.*$//" | sort -u)
do
    share/python/custom-validator-to-annotation.py $file > $file.new;
    mv $file.new $file;
done
