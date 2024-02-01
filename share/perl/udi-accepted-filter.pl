#!/usr/bin/perl
# Filter for Accepted datasets
#
# This filter only passes UDIs that correspond to datasets that are
# in the accepted state. Typical usage is to pipe a textfile of UDIs
# to it that represent datasets in various states, and the resulting
# output is just those UDIs of accepted datasets. This script is used
# by the Pelagos LTFS tools.

use JSON::Parse 'parse_json';
use Data::Dumper;

while(<>) {
    chomp($_);
    my $udi = $_;
    my $statusJSON = `curl -s "https://data.griidc.org/api/datasets?udi=$udi&_properties=datasetStatus"`;
    my $status= parse_json($statusJSON);
    my $hashref = @$status[0];
    my %hash = %$hashref;
    my $accepted = $hash{'datasetStatus'};
    if ($accepted eq 'Accepted' ) {
        print "$udi\n";
    }
}
