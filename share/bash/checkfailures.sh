#!/bin/bash

quiet=$1
status=$(bin/console messenger:failed:show 2>&1)
errormsg=$(echo $status | grep 'There are 0 messages pending in the failure transport.')

if [ "$quiet" == "quiet" ]; then
    if [ -z "$errormsg" ]; then
        echo $status | /bin/mailx -s "Failed message transport status" michael.williamson@tamucc.edu praneeth.pondicherry@tamucc.edu michael.vandeneijnden@tamucc.edu william.nichols@tamucc.edu rosalie.rossi@tamucc.edu
    fi
else
    echo $status | /bin/mailx -s "Failed message transport status" michael.williamson@tamucc.edu praneeth.pondicherry@tamucc.edu michael.vandeneijnden@tamucc.edu william.nichols@tamucc.edu rosalie.rossi@tamucc.edu
fi
