#include <stdio.h>
#include <stdlib.h>
#include <unistd.h>
#include <signal.h>
#include <sys/types.h>

const char *pidfile = "/var/run/filerd.pid";

int main() {
    pid_t pid = 0;

    FILE *fp = fopen(pidfile, "r");
    if (!fp) {
        char msg[100];
        sprintf(msg,"Failed to open pid file (%s)",pidfile);
        perror(msg);
        exit(EXIT_FAILURE);
    }
    if (fscanf(fp, "%d", &pid) != 1) {
        fprintf(stderr,"Failed to read pid from pid file (%s)\n",pidfile);
        fclose(fp);
        exit(EXIT_FAILURE);
    }
    fclose(fp);
    if (kill(pid,SIGHUP) != 0) {
        char msg[100];
        sprintf(msg,"Failed to send SIGHUP to pid %d",pid);
        perror(msg);
        exit(EXIT_FAILURE);
    }
    return EXIT_SUCCESS;
}
