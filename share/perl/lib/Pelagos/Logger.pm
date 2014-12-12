package Pelagos::Logger;

use strict;
use warnings;
use IO::Handle;
use POSIX qw(strftime);

my $log;
my $error;

sub new {
    my ($class,$logfile) = @_;

    my $self = {};
    bless $self, $class;

    $self->open($logfile) if defined $logfile;

    return $self;
}

sub open {
    my ($self,$logfile) = @_;
    unless (open($log,'>>',$logfile)) {
        $error = "cannot open log file $logfile: $!";
        return 0;
    }
    $log->autoflush(1);
    return 1;
}

sub write {
    my ($self,$message) = @_;
    my $ts = POSIX::strftime("%Y-%m-%d %H:%M:%S", localtime);
    print $log "[$ts] $message\n";
}

sub close { close $log; }

sub error { return $error; }

1;
