#!/usr/bin/perl

use strict;
use POSIX qw(strftime);

my $date = uc(strftime("%H%M-%d%b%Y", localtime));
my $filename = "pelagos-$date.sql";
my $outputDir = '/home/users/db_dumps';

`/usr/bin/pg_dump -c -U postgres -h localhost pelagos > "$outputDir/$filename"`;
unlink "$outputDir/newest-pelagos.sql";
`ln -s "$outputDir/$filename" "$outputDir/newest-pelagos.sql"`;

# Compress dump files older than 2 days.
qx /find $outputDir -name "pelagos-*.sql" -mtime +2 -exec pbzip2 {} \\;/;
