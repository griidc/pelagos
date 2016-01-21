#!/bin/sh
if [[ $1 =~ "-f" ]]
then
    PelagosDBScript=($(find . -wholename "*[0-9]*/[0-9]*.sql" | sort | sed "s/^\.\///"))

    for sql in "${PelagosDBScript[@]}"
    do
        echo "${sql}"
        psql -U postgres gomri < "${sql}"
        echo
    done
else
    echo $1
    echo "This is a destructive database building script."
    echo "Run this script with -f switch to really mean it."
    echo "You WILL lose existing data."
fi
