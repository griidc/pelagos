package Pelagos::DataStore;

use strict;

sub check_data_store_directory {
    my ($log,$config,$udi,$custodian_uid,$custodian_gid) = @_;
    # check if data store directory exists for this udi
    if (! -d "$config->{paths}->{data_store}/$udi") {
        writelog($log,"[$udi] creating data store directory $config->{paths}->{data_store}/$udi");
        # make directory
        mkdir("$config->{paths}->{data_store}/$udi",'0750') or die "Error: could not create directory: $config->{paths}->{data_store}/$udi ($!)\n";
        # make custodian the owner
        chown($custodian_uid,$custodian_gid,"$config->{paths}->{data_store}/$udi");
        # give apache access
        system("setfacl -m u:apache:--x $config->{paths}->{data_store}/$udi");
    }
}

sub check_download_directory {
    my ($log,$config,$udi,$apache_uid,$apache_gid,$r) = @_;
    # check if download directory exists for this udi
    if (! -d "$config->{paths}->{download}/$udi") {
        writelog($log,"[$udi] creating download directory $config->{paths}->{download}/$udi");
        # determine download directory permissions
        my $dl_dir_perms = '0750';
        $dl_dir_perms = '0751' if $r->{'access_status'} eq 'None';
        # make directory
        mkdir("$config->{paths}->{download}/$udi",$dl_dir_perms) or die "Error: could not create directory: $config->{paths}->{download}/$udi ($!)\n";
        # make apache the owner
        chown($apache_uid,$apache_gid,"$config->{paths}->{download}/$udi");
        # give custodian access
        system("setfacl -m u:custodian:rwx $config->{paths}->{download}/$udi");
    }
}

1;
