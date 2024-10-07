#!/usr/bin/python3
import re
import sys

filename = sys.argv[1]

try:
    infile = open(filename, "r")
except OSError:
    print("Could not open/read file: ", filename)
    sys.exit()

msg = ''
removed = False

for line in infile:
    if '@CustomAssert\\NoAngleBrackets' in line:
        removed = True;
        msgline = next(infile)
        msg = re.sub('^.*message=\"', '', msgline)
        msg = re.sub('\"\n', '', msg)
        next(infile)
    elif '*/' not in line:
        print(line, end="")

    if '*/' in line:
        print(line, end="")
        if msg:
            print('    #[Assert\Regex(\'/<>/\', \''+msg+'\')]')
            msg = ''
