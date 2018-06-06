#!/bin/bash
# Script to compare historical and generated XML for all accepted data.
#
# Prerequsites:
# Output directory needs to be on a native drive, and not an NFS share, or this will take hours.
# The dataset:write-metadata-files Symfony Command utitily needs to be available.
# A current database dump or production is needed.
#
# Configuration ########################################################################################################
export outputDirectory=~/output;
export pelagosDirectory=~/pelagos;
export databaseDump=~/db_dumps/newest-pelagos.sql
########################################################################################################################

outdir () {
    cd $outputDirectory
}
pelagosdir () {
    cd $pelagosDirectory
}

refreshdb () {
    cd $pelagosDirectory
    printf "Y\n" | bin/loaddump $databaseDump
}
echo "Job Starting";
echo `date`;

# Clean previous run
outdir
rm *.xml *.diff *.canonical

# Temporarily disable .nulling of email
pelagosdir
sed -i 's/echo "UPDATE person SET email_address/#echo "UPDATE person SET email_address/' bin/loaddump

# Load newest dump from production
refreshdb

# Write generated and historical XML files for All Accepted data.
# This takes about 20 minutes.
pelagosdir
bin/console dataset-submission:back-fill-accepted-metadata-command
bin/console dataset-submission:back-fill-distribution-point-command
bin/console dataset:write-metadata-files >> $outputDirectory/XML-Comparison-Tool.log

# Re-Enable .nulling of email
pelagosdir
sed -i 's/#echo "UPDATE person SET email_address/echo "UPDATE person SET email_address/' bin/loaddump

# Load newest dump from production for email nulling.
refreshdb

# Canonicalize XML for easier comparison
cd ~/output;
for file in `ls *.xml`;
    do newname=`echo "$file"`; newname=`echo $newname | sed "s/.xml/.canonical.xml/"`;
    xmllint --c14n "$file" > "$newname";
done;

# Generate/process comparison file.
for nudi in `ls *.generated.xml *.historical.xml | sed 's/.generated.xml\|.historical.xml//' | sort | uniq`

    # Write comparison files
    do git diff --word-diff --unified=0 --color $nudi.historical.canonical.xml $nudi.generated.canonical.xml > $nudi.diff;

    # Remove acceptable differences
    sed -i "s/@@.*@@//" $nudi.diff;
    sed -i "/\[-<gco:CharacterString>Texas<\/gco:CharacterString>-\].*\+<gco:CharacterString>TX<\/gco:CharacterString>.*$/d" $nudi.diff;
    sed -i "/\[-<gco:CharacterString>78412-5869<\/gco:CharacterString>.*<gco:CharacterString>78412<\/gco:CharacterString>.*$/d" $nudi.diff;
    sed -i "/\[-<gmd:URL>http:\/\/data.gulfresearchinitiative.org<\/gmd:URL>-\].*<gmd:URL>https:\/\/data.gulfresearchinitiative.org<\/gmd:URL>.*$/d" $nudi.diff;
    sed -i "/\[-http:\/\/www.ngdc.noaa.gov\/metadata\/published\/xsd\/schema.xsd\">-\].*https:\/\/www.ngdc.noaa.gov\/metadata\/published\/xsd\/schema.xsd\">.*$/d" $nudi.diff;
    sed -i "/^$/d" $nudi.diff;
    sed -i "/This ISO metadata record was /d" $nudi.diff;
    sed -i "/Created with GRIIDC Metadata Editor/d" $nudi.diff;
    sed -i "/Unit 5869/d" $nudi.diff;
    sed -i "/^<gmd:maintenance/d" $nudi.diff;
    sed -i "/\[-<gco:DateTime>.*<\/gco:DateTime>-\].*<gco:DateTime>.*<\/gco:DateTime>.*$/d" $nudi.diff;
    sed -i -e '1,6d' $nudi.diff;
done;

echo "Job Ended";
echo `date`;
