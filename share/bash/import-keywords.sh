#!/bin/bash

# pelagos:import-keywords [--type [TYPE]] [--dataURI [DATAURI]] [--keyword [KEYWORD]] [--] <action>
echo "Importing 12 pages of keywords from anzsrc.";
for page in $(seq 0 11); do
    echo "Importing page $page"
    bin/console pelagos:import-keywords --type anzsrc --dataURI "https://vocabs.ardc.edu.au/repository/api/lda/anzsrc-2020-for/concept.json?_page=$page" IMPORT
done
echo "Completed with anzsrc imports. Now sorting."
bin/console pelagos:import-keywords --type anzsrc SORT

# While there is a page 0, it contains the same data as page 1, so skipping.
echo "Importing 2 pages of keywords from NASA."
for page in $(seq 1 2); do
    echo "Importing page $page"
    bin/console pelagos:import-keywords --type gcmd --dataURI "https://gcmd.earthdata.nasa.gov/kms/concepts/concept_scheme/sciencekeywords/?format=rdf&page_num=$page" IMPORT
done
echo "Completed with the NASA import. Now sorting."
bin/console pelagos:import-keywords --type gcmd SORT

echo "Expanding GCMD keyword Earth Science"
bin/console pelagos:import-keywords expand --keyword e9f67a66-e9fc-435c-b720-ae32a2c3d8f5
