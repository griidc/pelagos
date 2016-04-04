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
    my ($log,$data_store_path,$udi) = @_;
    my (undef,undef,$custodian_uid,$custodian_gid) = getpwnam('custodian');
    # check if data store directory exists for this udi
    if (! -d "$data_store_path/$udi") {
        $log->write("[$udi] creating data store directory $data_store_path/$udi");
        # make directory
        mkdir("$data_store_path/$udi",0750) or die "Error: could not create directory: $data_store_path/$udi ($!)\n";
        # make custodian the owner
        chown($custodian_uid,$custodian_gid,"$data_store_path/$udi");
        # give apache access
        system("setfacl -m u:apache:--x $data_store_path/$udi");
    }
}

sub check_download_directory {
    my ($log,$download_directory_path,$udi,$access_status) = @_;
    my (undef,undef,$apache_uid,$apache_gid) = getpwnam('apache');
    # check if download directory exists for this udi
    if (! -d "$download_directory_path/$udi") {
        $log->write("[$udi] creating download directory $download_directory_path/$udi");
        # determine download directory permissions
        my $dl_dir_perms = 0750;
        $dl_dir_perms = 0751 if $access_status eq 'None';
        # make directory
        mkdir("$download_directory_path/$udi",$dl_dir_perms) or die "Error: could not create directory: $download_directory_path/$udi ($!)\n";
        # make apache the owner
        chown($apache_uid,$apache_gid,"$download_directory_path/$udi");
        # give GRIIDC group access
        system("setfacl -m g:GRIIDC:r-x $download_directory_path/$udi");
    }
}

1;
