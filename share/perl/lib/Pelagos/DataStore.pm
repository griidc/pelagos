package Pelagos::DataStore;

use strict;
use warnings;

our(@ISA, @EXPORT);

sub check_data_store_directory;
sub check_download_directory;

require Exporter;
@ISA = qw(Exporter);
@EXPORT = qw(check_data_store_directory check_download_directory);

sub check_data_store_directory {
    my ($log,$config,$udi) = @_;
    my (undef,undef,$custodian_uid,$custodian_gid) = getpwnam('custodian');
    # check if data store directory exists for this udi
    if (! -d "$config->{paths}->{data_store}/$udi") {
        $log->write("[$udi] creating data store directory $config->{paths}->{data_store}/$udi");
        # make directory
        mkdir("$config->{paths}->{data_store}/$udi",'0750') or die "Error: could not create directory: $config->{paths}->{data_store}/$udi ($!)\n";
        # make custodian the owner
        chown($custodian_uid,$custodian_gid,"$config->{paths}->{data_store}/$udi");
        # give apache access
        system("setfacl -m u:apache:--x $config->{paths}->{data_store}/$udi");
    }
}

sub check_download_directory {
    my ($log,$config,$udi,$access_status) = @_;
    my (undef,undef,$apache_uid,$apache_gid) = getpwnam('apache');
    # check if download directory exists for this udi
    if (! -d "$config->{paths}->{download}/$udi") {
        $log->write($log,"[$udi] creating download directory $config->{paths}->{download}/$udi");
        # determine download directory permissions
        my $dl_dir_perms = '0750';
        $dl_dir_perms = '0751' if $access_status eq 'None';
        # make directory
        mkdir("$config->{paths}->{download}/$udi",$dl_dir_perms) or die "Error: could not create directory: $config->{paths}->{download}/$udi ($!)\n";
        # make apache the owner
        chown($apache_uid,$apache_gid,"$config->{paths}->{download}/$udi");
        # give custodian access
        system("setfacl -m u:custodian:rwx $config->{paths}->{download}/$udi");
    }
}

1;
