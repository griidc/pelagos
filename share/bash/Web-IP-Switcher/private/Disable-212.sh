#!/bin/sh

# Shut down 172.22.10.212 interface
/sbin/ifdown 'em1:0'

# Prevent it from coming back online at boot
/bin/echo '/etc/sysconfig/network-scripts/ifcfg-em1:0' | /usr/bin/xargs /bin/sed -i 's/ONBOOT=yes/ONBOOT=no/g'
