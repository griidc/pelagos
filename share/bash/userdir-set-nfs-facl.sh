#!/bin/bash
USER=$1
ID=`id -u $USER`
APACHE_ID=`id -u apache`

# MAIN DIR
# 750 perm
chmod 750 $USER
# pelagos owned
chown pelagos $USER
# apache RX ACL
nfs4_setfacl -a A::$APACHE_ID:RX $USER
# user RX ACL
nfs4_setfacl -a A::$ID:RX $USER

# INCOMING DIR
# 750 perm
chmod 750 $USER/incoming
# pelagos owner
chown pelagos $USER/incoming
# apache RWX
nfs4_setfacl -a A::$APACHE_ID:RWX $USER/incoming
# user RWX
nfs4_setfacl -a A::$ID:RWX $USER/incomin

# DOWNLOAD DIR
# 750 perm
chmod 750 $USER/download
# pelagos owner
chown pelagos $USER/download
# Apache RWX
nfs4_setfacl -a A::$APACHE_ID:RWX $USER/download
# user RX
nfs4_setfacl -a A::$ID:RX $USER/download
