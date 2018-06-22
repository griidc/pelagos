#!/bin/bash
# Script to compare historical and generated XML for all accepted data.
#
# Prerequsites:
# Output directory needs to be on a native drive, and not an NFS share, or this will take hours.
# The dataset:write-metadata-files Symfony Command utitily needs to be available.
#
# The Pelagos enviroment should contain a fully-refreshed database, including backfilling of
# accepted metadata and the backfilling of distribution points as this is needed for correct
# comparision of XML. Also, I recommend not .nulling the email addresses before running this
# as this may introduce additional artifacts into the historical XML.
#
# Configuration ########################################################################################################
export outputDirectory=~/output;
export pelagosDirectory=~/pelagos;
########################################################################################################################

outdir () {
    cd $outputDirectory
}
pelagosdir () {
    cd $pelagosDirectory
}

echo "Job Starting";
echo `date`;

# Clean previous run
outdir
rm -f *.xml *.html *.txt

# Write generated and historical XML files for All Accepted data.
# This takes about 20 minutes.
pelagosdir
bin/console dataset:write-metadata-files >> $outputDirectory/XML-Comparison-Tool.log

# Canonicalize XML for easier comparison
cd ~/output;
for file in `ls *.xml`;
    do newname=`echo "$file"`; newname=`echo $newname | sed "s/.xml/.canonical.xml/"`;
    xmllint --c14n "$file" > "$newname";
done;

# Generate/process comparison file.
for nudi in `ls *.generated.xml *.historical.xml | sed 's/.generated.xml\|.historical.xml//' | sort | uniq`

    # Write comparison files
    do git diff --word-diff --unified=0 --color $nudi.historical.canonical.xml $nudi.generated.canonical.xml > "$nudi-diff.txt";

    # Remove acceptable differences
    sed -i "s/@@.*@@//" $nudi-diff.txt;
    sed -i "/\[-<gco:CharacterString>Texas<\/gco:CharacterString>-\].*\+<gco:CharacterString>TX<\/gco:CharacterString>.*$/d" $nudi-diff.txt;
    sed -i "/\[-<gco:CharacterString>78412-5869<\/gco:CharacterString>.*<gco:CharacterString>78412<\/gco:CharacterString>.*$/d" $nudi-diff.txt;
    sed -i "/\[-<gmd:URL>http:\/\/data.gulfresearchinitiative.org<\/gmd:URL>-\].*<gmd:URL>https:\/\/data.gulfresearchinitiative.org<\/gmd:URL>.*$/d" $nudi-diff.txt;
    sed -i "/\[-http:\/\/www.ngdc.noaa.gov\/metadata\/published\/xsd\/schema.xsd\">-\].*https:\/\/www.ngdc.noaa.gov\/metadata\/published\/xsd\/schema.xsd\">.*$/d" $nudi-diff.txt;
    sed -i "/^$/d" $nudi-diff.txt;
    sed -i "/This ISO metadata record was /d" $nudi-diff.txt;
    sed -i "/Created with GRIIDC Metadata Editor/d" $nudi-diff.txt;
    sed -i "/Unit 5869/d" $nudi-diff.txt;
    sed -i "/^<gmd:maintenance/d" $nudi-diff.txt;
    sed -i "/\[-<gco:DateTime>.*<\/gco:DateTime>-\].*<gco:DateTime>.*<\/gco:DateTime>.*$/d" $nudi-diff.txt;
    sed -i -e '1,6d' $nudi-diff.txt;

    # Generate HTML version of this differences file.
    cat $nudi-diff.txt | ansi2html > $nudi-diff.html

    # Write comparison files again, but with no color.
    git diff --word-diff --unified=0 $nudi.historical.canonical.xml $nudi.generated.canonical.xml > "$nudi-diff.txt";

    # Remove acceptable differences
    sed -i "s/@@.*@@//" $nudi-diff.txt;
    sed -i "/\[-<gco:CharacterString>Texas<\/gco:CharacterString>-\].*\+<gco:CharacterString>TX<\/gco:CharacterString>.*$/d" $nudi-diff.txt;
    sed -i "/\[-<gco:CharacterString>78412-5869<\/gco:CharacterString>.*<gco:CharacterString>78412<\/gco:CharacterString>.*$/d" $nudi-diff.txt;
    sed -i "/\[-<gmd:URL>http:\/\/data.gulfresearchinitiative.org<\/gmd:URL>-\].*<gmd:URL>https:\/\/data.gulfresearchinitiative.org<\/gmd:URL>.*$/d" $nudi-diff.txt;
    sed -i "/\[-http:\/\/www.ngdc.noaa.gov\/metadata\/published\/xsd\/schema.xsd\">-\].*https:\/\/www.ngdc.noaa.gov\/metadata\/published\/xsd\/schema.xsd\">.*$/d" $nudi-diff.txt;
    sed -i "/^$/d" $nudi-diff.txt;
    sed -i "/This ISO metadata record was /d" $nudi-diff.txt;
    sed -i "/Created with GRIIDC Metadata Editor/d" $nudi-diff.txt;
    sed -i "/Unit 5869/d" $nudi-diff.txt;
    sed -i "/^<gmd:maintenance/d" $nudi-diff.txt;
    sed -i "/\[-<gco:DateTime>.*<\/gco:DateTime>-\].*<gco:DateTime>.*<\/gco:DateTime>.*$/d" $nudi-diff.txt;
    sed -i -e '1,6d' $nudi-diff.txt;

done;

echo "Job Ended";
echo `date`;
