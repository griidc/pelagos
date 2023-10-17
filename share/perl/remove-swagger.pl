#!/usr/bin/perl
for my $file (@ARGV) {
    open my $fh, '<', $file or die;
    open my $of, '>', "$file.new" or die;
    while(my $line = <$fh>) {
        chomp($line);
        if ($line =~ /\* \@Operation/) {
            $line = <$fh>;
            while ($line !~ /\* \)$/) {
                chomp($line);
                $line = <$fh>;
            }
        } else {
            print $of "$line\n";
        }
    }
}
