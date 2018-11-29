#!/usr/bin/python
# -*- coding: utf-8 -*-
import csv
import getopt
import operator
import os
import re
import sys
import textwrap
from collections import OrderedDict

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

# https://www.oreilly.com/library/view/python-cookbook/0596001673/ch04s16.html
def splitall(path):
    allparts = []
    while 1:
        parts = os.path.split(path)
        if parts[0] == path:  # sentinel for absolute paths
            allparts.insert(0, parts[0])
            break
        elif parts[1] == path: # sentinel for relative paths
            allparts.insert(0, parts[1])
            break
        else:
            path = parts[0]
            allparts.insert(0, parts[1])
    return allparts

# Converts bytes into human-readable form, ex TB/GB/MB/KB/Bytes based on size.
def intToSize(size):
    if (size >= 10**12):
        return(str(size/10**12) + " TB")
    elif (size >= 10**9):
        return(str(size/10**9) + " GB")
    elif (size >= 10**6):
        return(str(size/10**6) + " MB")
    elif (size >= 10**3):
        return(str(size/10**3) + " KB")
    else:
        return(str(size) + " Bytes")


def generate_tree(filename, short):
    path = check_header(filename)
    filetypes = {}
    # Extract the UDI from the passed starting path.
    udi_pattern = re.compile('([A-Za-z0-9]{2}.x[0-9]{3}.[0-9]{3})[.:]([0-9]{4})')
    udi_parts = udi_pattern.findall(path)
    udi = udi_parts[0][0] + ':' + udi_parts[0][1]

    if (path is not None):
        sizes = OrderedDict()
        with open(filename, 'rb') as f:
            reader = csv.reader(f)
            rownum = 1
            last = None
            for row in reader:
                # skip header
                if (rownum > 5):
                    object_filename = re.sub(path + '/', '', row[3])
                    object_size = row[0]

                    # Find file's filetype, add to count by filetype.
                    filetype = os.path.splitext(row[3])[1]
                    try:
                        filetypes[filetype] += 1
                    except KeyError:
                        filetypes[filetype] = 1

                    # Split out paths to keep track, by dir, of totals.
                    parts = splitall(object_filename)
                    for i in range (0, len(parts), 1):
                        if (i == 0):
                            my_str = parts[i]
                        elif (i < len(parts)-1):
                            my_str = my_str + '/' + parts[i]
                        else:
                            # Appending '|EOL:' to ends of non-file paths, so this indicates directories.
                            # elegance-- but works.
                            my_str = my_str + '/' + parts[i] + '|EOL:'
                        try:
                            sizes[my_str] += int(object_size)
                        except KeyError:
                            sizes[my_str] = int(object_size)
                rownum += 1
            # Output Section
            if (short):
                print "Dataset Directory Summary for " + udi
            else:
                print "Dataset File Manifest for " + udi
            print textwrap.dedent("""\

                This dataset is greater than 25 GB and therefore too large to be downloaded
                through direct download. In order to obtain this dataset, please email
                griidc@gomri.org to make arrangements. If you would like a subset of the
                dataset files, please indicate which directories and/or files.

                """)
            # Display filetype summary in short mode.
            if (short):
                extensions = []
                for file_type, type_count in filetypes.iteritems():
                    if (file_type == ''):
                        file_type = 'no extension'
                    extensions.append(file_type)
                print("File Extensions:")
                extensions.sort()
                print(','.join(extensions))
                print

                # Sort by count in each type, descending.
                for file_type, type_count in sorted(filetypes.iteritems(), reverse=True, key=lambda (k,v): (v,k)):
                    if(file_type == ''):
                        file_type = '<no extension>'
                    formatted_line = '%10s  %15s' % (str(type_count), file_type)
                    print formatted_line
                print
                print("Total Files - " + str(rownum-5-1))
            print
            if (short):
                print('Directories Structure:')
            else:
                print('File Listing:')
            print
            for path, size in sizes.iteritems():
                if (short):

                    # Display directories only in short mode.
                    if(re.search("\|EOL:$", path)):
                        pass
                    else:
                        print(path + " [" + intToSize(size) + "]")
                else:
                    print(re.sub('\|EOL:', '', path) +  " (" + intToSize(size) + ")")
    else:
        print("Error in header. Stopping")

def main(argv, script_name):
    hashfile = ''
    short_report = False;
    try:
        opts, args = getopt.getopt(argv,"hdi:",["ifile=",])
    except getopt.GetoptError:
        print script_name + ' -i <hashfile>'
        sys.exit(2)
    for opt, arg in opts:
        if opt == '-h':
            print script_name + ' -i <hashfile> [-d <show directories only>]'
            sys.exit()
        elif opt in ("-i", "--ifile"):
            hashfile = arg
        elif opt in ("-d"):
            short_report = True
    generate_tree(hashfile, short_report)

if __name__ == "__main__":
    main(sys.argv[1:], sys.argv[0])

