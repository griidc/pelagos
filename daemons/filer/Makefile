all:
	gcc src/trigger-filer.c -o trigger-filer
	chmod 4755 trigger-filer

install:
	ln -sf `pwd`/trigger-filer /usr/local/bin/trigger-filer

init:
	ln -sf `pwd`/filerd.init /etc/init.d/filerd

config:
	@if test -f /etc/griidc/filer.ini; then \
		echo "/etc/griidc/filer.ini already exists. Exiting..."; exit 1; \
	else \
		cp filer.ini.example /etc/griidc/filer.ini; \
	fi
