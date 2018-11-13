#!/usr/bin/python

import re
import csv
import sys

filename = '/home/users/mwilliamson/R1.x137.108.0001.hashes.txt'

def check_header(filename):
    # This function checks for a valid hashdeep header
    # in the passed file. If the header is valid, the
    # invocation path is returned, otherwise false
    # is returned in the event of an invalid header.

    # example header:

    # %%%% HASHDEEP-1.0
    # %%%% size,md5,sha256,filename
    # ## Invoked from: /mnt/LTFS/R1.x137.108.0001
    # ## $ hashdeep -r .
    # ##

    with open(filename) as f:
        first = f.readline().rstrip() == '%%%% HASHDEEP-1.0'
        second = f.readline().rstrip() == '%%%% size,md5,sha256,filename'
        third_line = f.readline().rstrip()
        third = re.match('^## Invoked from: ', third_line) != None
        fourth_line = f.readline().rstrip()
        # This hardcoded offset is safe because of the previous re.match() check.
        path = third_line[17:]
        forth = re.match('^## \$ hashdeep -r ', fourth_line) != None
        fifth = f.readline().rstrip() == '##'
    if (first and second and third and forth and fifth):
        return path
    else:
        return None


path = check_header(filename)
if (path is not None):
    with open(filename, 'rb') as f:
        reader = csv.reader(f)
        rownum = 1
        for row in reader:
            if (rownum > 5):
                object_filename = re.sub(path + '/', '', row[3])
                print object_filename

            rownum = rownum+1
else:
    print("Error in header. Stopping")



