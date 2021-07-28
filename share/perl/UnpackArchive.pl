#!/usr/bin/perl

use strict;
use warnings;

# input will be udi, fileName, fileType

my $udi = $ARGV[0];
my $fileType = $ARGV[1];

my $cmd = '';
my $arg = '';

# CONFIG
my $storage = '/griidc/san-data-store';

if (0 == checkDeps()) {
    die("Please install needed commands.\n");
}

print "Processing: udi: $udi, src: $storage/$udi.$fileType, type: $fileType\n";

if ($fileType eq 'tar.bz2') {
    $cmd = 'tar';
    $arg = 'xfj';
} elsif ($fileType eq 'tar.gz') {
    $cmd = 'tar';
    $arg = 'xfz';
} elsif ($fileType eq 'tar') {
    $cmd = 'tar';
    $arg = 'xf';
} elsif ($fileType eq 'tarz') {
    $cmd = 'tar';
    $arg = 'xf';
} elsif ($fileType eq 'gtar') {
    $cmd = 'tar';
    $arg = 'xf';
} elsif ($fileType eq 'tgz') {
    $cmd = 'tar';
    $arg = 'xfz';
} elsif ($fileType eq '7z') {
    $cmd = '7za';
    $arg = 'x';
} elsif ($fileType eq 'zip') {
    $cmd = 'unzip';
    $arg = '';
} elsif ($fileType eq 'rar') {
    $cmd = 'unar';
    $arg = '';
} else {
    print "Unknown filetype in $fileType. I cannot unpack this.\n";
}

if ($cmd ne '' and $fileType ne '') {
    `rm -rf "$udi"`;
    mkdir($udi);
    chdir($udi);
    `$cmd $arg "$storage/$udi/$udi.dat"`;
} else {
    print "Missing argument. ERROR.\n";
}

sub checkDeps {
    # Ensure these commands exist in the path and are executable.
    my @commands = ('tar', '7za', 'unzip', 'unar');
    foreach my $command (@commands) {
        my $cmd = '';
        $cmd = `which $command`; chomp($cmd);
        if (!$cmd or !-x $cmd) {
            return 0;
        }
    }
    return 1;
}
