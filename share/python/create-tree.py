#!/usr/bin/python2
# -*- coding: utf-8 -*-
import csv
import argparse
import operator
import os
import re
import sys
import textwrap
from directory_tree_node import DirectoryTreeNode
from collections import OrderedDict

def check_header(filename):
    # This function checks for a valid hashdeep header
    # in the passed file. If the header is valid, the
    # invocation path is returned, otherwise false
    # is returned in the event of an invalid header.

    # example header:

    # %%%% HASHDEEP-1.0
    # %%%% size,sha256,filename
    # ## Invoked from: /scratch-ssd/tempdir/R4.x261.233.0002
    # ## $ hashdeep -rl -c sha256 .
    # ##

    with open(filename) as f:
        first = f.readline().rstrip() == '%%%% HASHDEEP-1.0'
        second = f.readline().rstrip() == '%%%% size,sha256,filename'
        third_line = f.readline().rstrip()
        third = re.match('^## Invoked from: ', third_line) != None
        fourth_line = f.readline().rstrip()
        # This hardcoded offset is safe because of the previous re.match() check.
        path = third_line[17:] + '/' + udi
        forth = re.match('^## \$ hashdeep ', fourth_line) != None
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
                    object_filename = re.sub(path + '/', '', row[2])
                    object_size = row[0]

                    # Find file's filetype, add to count by filetype.
                    filetype = os.path.splitext(row[2])[1]
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
                help@griidc.org to make arrangements. If you would like a subset of the
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
                        opPath = re.sub('\|EOL:', '', path)
                        DirectoryTreeNode.buildTree(directoryTreeNodeRoot, opPath, size)
                else:
                    opPath = re.sub('\|EOL:', '', path)
                    DirectoryTreeNode.buildTree(directoryTreeNodeRoot, opPath, size)
            # print the tree starting with the node(s) that
            # are children of the root. The root does not contain data.
            rootChildren = directoryTreeNodeRoot.getChildren()
            for child in rootChildren:
                child.printTree(0)
    else:
        print("Error in header. Stopping")


directoryTreeNodeRoot = DirectoryTreeNode('root',0)

def main(argv, script_name):
    parser = argparse.ArgumentParser()
    # Stores args.d boolean, true if -d is set, false otherwise.
    parser.add_argument('-d', action='store_true', help='Print only directories.')
    parser.add_argument('hashfile')
    args = parser.parse_args()
    generate_tree(args.hashfile, args.d)

if __name__ == "__main__":
    main(sys.argv[1:], sys.argv[0])
