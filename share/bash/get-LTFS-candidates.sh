#!/bin/bash
#
# New LTFS candidate file report generator.
#
# This script generates a list of new LTFS candidates, which are the set of accepted
# datasets that are >= 25GB in size as archived on disk. The second part of this report
# indicates any datasets that once were, but are no longer LTFS candidates due to a
# smaller filesize, and the third section of the report coveres replaced datasets.
#
# Updated and deletion candidate files will require updating/removing from LTFS,
# offsite LTFS, and AWS.

## New Files ###################################################################

# Find UDI of all .dat files > 25 GB in /san/data/store
/bin/find /san/data/store -type f -size +25G -name "*.dat" | /bin/sed 's/\/san\/data\/store\///g' | /bin/sed 's/\/.*$//g' | /bin/sort > /var/tmp/LTFS-candidates.$$.txt;

# Filter out non-accepted datasets.
/usr/local/bin/udi-accepted-filter.pl < /var/tmp/LTFS-candidates.$$.txt > /var/tmp/LTFS-accepted-candidates.txt.$$;
/bin/rm /var/tmp/LTFS-candidates.$$.txt;

# Find files not already on list
/usr/bin/comm -2 -3 /var/tmp/LTFS-accepted-candidates.txt.$$ /mnt/LTFS-datasets.sorted.csv > /var/tmp/New-LTFS-accepted-candidates.txt.$$;

# Add to report:
/usr/bin/printf "The following list is of new, accepted datasets for consideration to copy to LTFS: \n" > /var/tmp/report.$$.txt;
/bin/cat /var/tmp/New-LTFS-accepted-candidates.txt.$$ >> /var/tmp/report.$$.txt;
/usr/bin/printf "\n\n" >> /var/tmp/report.$$.txt;

## Old Files ###################################################################

# Find files of LTFS files that once were and are no longer LTFS candidates.
/usr/bin/comm -1 -3 /var/tmp/LTFS-accepted-candidates.txt.$$ /mnt/LTFS-datasets.sorted.csv > /var/tmp/LTFS-removal-candidates.txt.$$;

# Add to report:
/usr/bin/printf "The following list is of files that once were and are no longer LTFS candidates.: \n" >> /var/tmp/report.$$.txt;
/bin/cat /var/tmp/LTFS-removal-candidates.txt.$$ >> /var/tmp/report.$$.txt;
/usr/bin/printf "\n\n" >> /var/tmp/report.$$.txt;

# clean up working files
/bin/rm /var/tmp/LTFS-accepted-candidates.txt.$$;
/bin/rm /var/tmp/New-LTFS-accepted-candidates.txt.$$;

## Updated Files ###############################################################

# Find UDI of all recent .dat files > 25 GB in /san/data/store
/bin/find /san/data/store -type f -size +25G -name "*.dat" -mtime -14 | /bin/sed 's/\/san\/data\/store\///g' | /bin/sed 's/\/.*$//g' | /bin/sort > /var/tmp/recent-LTFS-candidates.$$.txt;

# Find possibly updated files
/usr/bin/comm -1 -2 /var/tmp/recent-LTFS-candidates.$$.txt /mnt/LTFS-datasets.sorted.csv > /var/tmp/Updated-accepted-LTFS-datasets.txt.$$;

# Add to report:
/usr/bin/printf "LTFS datasets updated on disk within 2 weeks (stale LTFS copy): \n" >> /var/tmp/report.$$.txt;
/bin/cat /var/tmp/Updated-accepted-LTFS-datasets.txt.$$ >> /var/tmp/report.$$.txt;
/bin/rm /var/tmp/Updated-accepted-LTFS-datasets.txt.$$;
printf "\n\n" >> /var/tmp/report.$$.txt;

## Report ######################################################################
printf "(Any section on the reports left blank means no data fit the criteria.)\n" >> /var/tmp/report.$$.txt;
/bin/cat /var/tmp/report.$$.txt | /bin/mailx -s "LTFS New/Updated Report" -c "william.nichols@tamucc.edu, rosalie.rossi@tamucc.edu" michael.williamson@tamucc.edu
