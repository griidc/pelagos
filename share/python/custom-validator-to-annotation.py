#!/usr/bin/python3
import re

infile = open("src/Entity/DataCenter.php", "r")
msg = ''

for line in infile:
    if '@CustomAssert\\NoAngleBrackets' in line:
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
