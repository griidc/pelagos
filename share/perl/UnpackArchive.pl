#!/usr/bin/perl

use strict;
use warnings;

# input will be udi, fileName, fileType

my $udi = $ARGV[0];
my $fileName = $ARGV[1];
my $fileType = $ARGV[2];

my $cmd = '';
my $arg = '';

# CONFIG
my $storage = '/san_mwilliamson/data/store';

if (0 == checkDeps()) {
    die("Please install needed commands.\n");
}
die('test');

print "Processing: udi: $udi, src: $storage/$fileName, type: $fileType\n";

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
    $cmd = 'gtar';
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

if ($cmd ne '' and $fileName ne '' and $fileType ne '') {
    mkdir($udi);
    mkdir("$udi-orig");
    symlink("$storage/$udi/$udi.dat", "$udi-orig/$fileName");
    chdir($udi);
    `$cmd $arg ../$udi-orig/$fileName`;
    unlink("../$udi-orig/$fileName");
    rmdir("../$udi-orig");
} else {
    print "Missing argument. ERROR.\n";
}

sub checkDeps {
    # Ensure these commands exist in the path and are executable.
    my @commands = ('tar', 'gtar', '7za', 'unzip', 'unar');
    foreach my $command (@commands) {
        my $cmd = '';
        $cmd = `which $command`; chomp($cmd);
        if (!$cmd or !-x $cmd) {
            return 0;
        }
    }
    return 1;
}
