#!/bin/sh
PelagosDBScript=($(find . -wholename "*[0-9]*/[0-9]*.sql" | sort | sed "s/^\.\///"))

for sql in "${PelagosDBScript[@]}"
do
    echo "${sql}"
    psql -U postgres gomri < "${sql}"
    echo
done
