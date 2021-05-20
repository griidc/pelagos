#!/bin/bash

quiet=$1
status=$(bin/console messenger:failed:show 2>&1)
errormsg=$(echo $status | grep 'There are 0 messages pending in the failure transport.')

if [ "$quiet" == "quiet" ]; then
    if [ -z "$errormsg" ]; then
        echo $status | /bin/mailx -s "Pelagos Messenger queue status" email.one@institution.tld email.two@institution.tld email.three@institution.tld...
    fi
else
    echo $status | /bin/mailx -s "Pelagos Messenger queue status" email.one@institution.tld email.two@institution.tld email.three@institution.tld...
fi
