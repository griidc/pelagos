#!/bin/bash

###############################################################################
#                                                                             #
#  get_db_params() - retrieve database parameters from central ini file and   #
#                    set environemnet variables with parameter values         #
#                                                                             #
#  Usage: get_db_params database_ini_file ini_file_section [variable_prefix]  #
#                                                                             #
###############################################################################
get_db_params() {
    if [ -z "$1" ]; then
        echo "Must specify database ini file"
        return 1
    fi
    DB_INI_FILE="$1"
    if [ -z "$2" ]; then
        echo "Must specify ini file section"
        return 2
    fi
    SECTION="$2"
    if [ -z $3 ]; then
        PREFIX="DB_"
    else
        PREFIX="$3"
    fi
    eval `sed -e 's/[[:space:]]*\=[[:space:]]*/=/g' -e 's/;.*$//' -e 's/[[:space:]]*$//' -e 's/^[[:space:]]*//' -e "s/^\(.*\)=\([^\"']*\)$/$PREFIX\1='\2'/" $DB_INI_FILE | sed -n -e "/^\[$SECTION\]/,/^\s*\[/{/^[^;].*\=.*/p;}"`
}
