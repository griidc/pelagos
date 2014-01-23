To set up filer:

1. make sure trigger-filer is owned by root: chown root trigger-filer

2. setuid for trigger-filer: chmod 4755 trigger-filer

3. copy filerd.init to /etc/init.d/filerd: cp filerd.init /etc/init.d/filerd

4. start filer daemon: service filerd start

5. set filerd to start automatically: chkconfig filerd on


Files:

filer - the filer script, which:

        1. scans the db for files that need moved into the data store

        2. copies files into the data store

        3. updates the db with status, file size, and original file name

        4. notifies submitters that their files have been processed

        5. notifies internal users of files that are filed, remote, missing, or specified with an unknwon protocol

        6. for each metadata file, sends an email to all metadata reviewers with metadata file attached

filerd - a daemon that listens for SIGHUP to trigger the filer

filerd.init - a runlevel script for registering filerd as a linux service

trigger-filer - a script to find the pid of filerd and send SIGHUP to trigger running the filer
