#!/bin/sh
THISHOST=$(hostname)

if [ "$THISHOST" == "proteus.tamucc.edu" ]; then
    # De-activate 172.22.10.212 on proteus
    echo "De-activating Proteus's 172.22.10.212 interface"
    #/sbin/ifdown 'ifcfg-em1:0'

    # SSH into poseidon and activate 172.22.10.212
    echo "Enabling poseidon's 172.22.10.212 interface"
    #/usr/bin/ssh -C /usr/local/bin/Web-IP-Switcher/private/Enable-212.sh ipmanager@poseidon.tamucc.edu
else 
    echo "This utility was meant to be run from proteus.tamucc.edu only."
fi
