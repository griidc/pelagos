#!/bin/sh

# Activate 172.22.10.212 interface
/sbin/ifup 'em1:0'

# Ensure it from comes back online at boot
/bin/echo '/etc/sysconfig/network-scripts/ifcfg-em1:0' | /usr/bin/xargs /bin/sed -i 's/ONBOOT=no/ONBOOT=yes/g'

# Refresh the ARP tables in connected networking hardware
/usr/sbin/arping -c 1 -s 172.22.10.212 -I em1 172.22.255.255
