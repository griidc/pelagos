package Config::PHPINI;

use strict;
use Config::Tiny;

sub new { return Config::Tiny->new(shift); }

sub read {
	shift; # class
    my $conf = Config::Tiny->read(shift);
    return clean_conf($conf);
}

sub read_string {
	shift; # class
    my $conf = Config::Tiny->read_string(shift);
    return clean_conf($conf);
}

sub clean_conf {
    my $conf = shift;
    for my $section (keys(%{$conf})) {
        for my $key (keys(%{$conf->{$section}})) {
            $conf->{$section}->{$key} =~ s/^"//;
            $conf->{$section}->{$key} =~ s/"$//;
        }
    }
    return $conf;
}

1;
