#!/bin/bash
#
# chkconfig: 35 98 02
# description: Pelagos MessageMQ Consumer Supervisor
#
# This script to be copied to or symlinked at /usr/local/bin and set to executable.
#
# This script is called by the pelagos-messagemq-consumer.service service with either a
# start or stop argument.

# Get function from functions library
. /etc/init.d/functions

prog="pelagos-messagemq-consumer-supervisor"
runuser=pelagos

success=true;

# Start the service
start() {
    echo -n $"Starting $prog: "
	cd /opt/pelagos

    if su - $runuser -c "supervisord --configuration=/opt/pelagos/config/supervisor/supervisord.conf --identifier=pelagos" ; then
        echo "started supervisord"
    else
        echo "failed to start supervisord"
        success=false
    fi

    if su - $runuser -c "supervisorctl --serverurl unix:///opt/pelagos/var/supervisor/supervisor.sock update" ; then
        echo "updated supervisor"
    else
        echo "failed to update supervisor"
        success=false
    fi

    if su - $runuser -c "supervisorctl --serverurl unix:///opt/pelagos/var/supervisor/supervisor.sock reload" ; then
        echo "reloaded supervisor"
    else
        echo "failed to reload supervisord"
        success=false
    fi

    if su - $runuser -c "supervisorctl --serverurl unix:///opt/pelagos/var/supervisor/supervisor.sock start pelagos:*" ; then
        echo "started messenger consumer"
    else
        echo "failed to start the actual messenger consumer"
        success=false
    fi

    if $success ; then
        success
    else
        failure
    fi

    echo
}

# Stop the service
stop() {
	cd /opt/pelagos

    if su - $runuser -c "supervisorctl --serverurl unix:///opt/pelagos/var/supervisor/supervisor.sock stop pelagos:*" ; then
        echo "stopped consumer"
    else
        success=false
    fi

    if su - $runuser -c "killall supervisord" ; then
        echo "stopped supervisord"
    else
        success=false
    fi

    if $success ; then
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
