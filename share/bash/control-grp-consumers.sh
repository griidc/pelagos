#!/bin/bash
#
# chkconfig: 35 98 02
# description: GRP RabbitMQ Consumer Supervisor
#
# This script to be copied to or symlinked at /usr/local/bin and set to executable.
#
# This script is called by the grp-consumer.service service with either a
# start or stop argument.

# Get function from functions library
. /etc/init.d/functions

prog="grp-rabbitmq-consumer-supervisor"
basecmd="bin/console rabbitmq-supervisor:control"
runuser=pelagos
postcmd="--wait-for-supervisord"

# Start the service
start() {
        echo -n $"Starting $prog: "
	cd /opt/grp
        if su - $runuser -c "$basecmd start $postcmd" ; then
            success
        else
            failure
        fi
        echo
}

# Stop the service
stop() {
	cd /opt/grp
	pwd
        echo -n $"Stopping $prog: "
        if su - $runuser -c "$basecmd stop $postcmd" ; then
            success
        else
            failure
        fi
        echo
}

### main logic ###
case "$1" in
  start)
        start
        ;;
  stop)
        stop
        ;;
  restart)
        stop
        start
        ;;
  *)
        echo $"Usage: $0 {start|stop|restart}"
        exit 1
esac

exit 0
