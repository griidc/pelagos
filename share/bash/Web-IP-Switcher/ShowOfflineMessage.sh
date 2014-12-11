#!/bin/sh
THISHOST=$(hostname)
USERNAME=$(id -un)

if [ "$USERNAME" != 'root' ]; then
    echo "This script must be run from root."
    exit 1
fi

if [ "$THISHOST" == "proteus.tamucc.edu" ]; then
    # SSH into poseidon and shut down 212 IP address
    echo "Shutting down poseidon's 172.22.10.212 interface"
    /usr/bin/ssh -C /usr/local/bin/Web-IP-Switcher/private/Disable-212.sh ipmanager@poseidon.tamucc.edu

    # Activate 172.22.10.212 on proteus
    echo "Activating Proteus's 172.22.10.212 interface"
    /sbin/ifup 'ifcfg-em1:0'

    # Refresh the ARP tables in connected networking hardware
    echo "Informing Network of IP host change"
    /usr/sbin/arping -c 1 -s 172.22.10.212 -I em1 172.22.255.255
else 
    echo "This utility was meant to be run from proteus.tamucc.edu only."
    exit 1
fi
