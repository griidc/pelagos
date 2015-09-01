#! /bin/bash

# test_person.sh
#
# This script issues database queries against an optionally supplied host and
# port, and reports the results. It will exit on the first failure and emit
# information about the failure.

#----- Script CONSTANTS: -------------------------------------------------------

declare -r CAT="`which cat`"
declare -r END_COMMENT="-- END"
declare -r FAIL_COMMENT="-- FAIL"
declare -r PASS_COMMENT="-- PASS"
declare -r PASS_PERMS=600
declare -r PGPASS=~/.pgpass
declare -r PSQL="`which psql`"
declare -r PROG_NAME=`basename "$0"`


#----- Script EXIT CONSTANTS: --------------------------------------------------

declare -r SUCCESS=0

declare -r BAD_FILE=65
declare -r BAD_HOST_CMD=66
declare -r FAILURE=1

# declare -r _DEBUG=TRUE


#----- Independent script variables: -------------------------------------------

declare l_count=1

#----- Dependent script variables: ---------------------------------------------

declare db_hostname="proteus.tamucc.edu"
declare db_name=gomri
declare db_port=5432
declare db_user=gomri_user
declare end_seen=false
declare expected_result=""
declare input_file=sql_test.sql
declare line=""
declare line_number=0
declare pg_version
declare sql_stmt=""


#----- Script functions: -------------------------------------------------------

# A function to check a file's existence and attributes:
check_file()
{
   if [ -z "$1" ] || [ -z "$2" ]
   then
      return $BAD_FILE
   fi

   case "$2" in
      "b" | "c" | "d" | "e" | "f" | "g" | "G" | "h" | "k" | "L" | \
      "N" | "O" | "p" | "r" | "s" | "S" | "t" | "u" | "w" | "x" )
         if [ -"$2" "$1" ]
         then
            return $SUCCESS
         fi
         ;;
   esac

   return $BAD_FILE

}

# A function to print usage information. It takes an optional exit code:
usage()
{
   echo -e  "$PROG_NAME"
   echo -en "Usage:\n   $PROG_NAME [-f filename ] [-U username ] "
   echo     "[-d database ] [-h hostname] [-p portnumber]"
   echo -en "\n\nDescription:   \n   "
   echo     "Test all aspects of the person, email, and history entities"
   echo -e  "\nOptions:\n   -f file - filename containing sql statements"
   echo     "   -U username - user name to connect as"
   echo     "   -h database - database to connect to"
   echo     "   -h host     - remote host to connect to"
   echo     "   -p port     - remote port to connect to"
   echo     "   -H help     - print this help message"
   echo -n  "If no options are provided, or if the option's argument is not "
   echo -en "supplied, the\nscript looks for file sql_test.sql in the current "
   echo -en "directory. If is found it is\nrun against the default host "
   echo     "proteus.tamucc.edu on the default port, 5432."
   echo -en "\nThe input file must contain valid SQL statement blocks that "
   echo -en "are preceeded by\n\"$PASS_COMMENT\" or \"$FAIL_COMMENT\" "
   echo -en "(indicating the SQL statement's expected outcome), and\nending "
   echo -en "with \"$END_COMMENT\", all without the quotes. Blank lines "
   echo -en "preceeding the outcome\naction or between the end statement and "
   echo -en "the outcome action are ignored.\nOtherwise everything between "
   echo -en "the outcome action and $END_COMMENT is passed literally\nas the "
   echo -en "SQL statement for the database to execute.\ni.e\n"
   echo -en "   $PASS_COMMENT\n   SELECT COUNT(*)\n   FROM <table name>;\n"
   echo -en "   $END_COMMENT\n"

   [ -n "$1" ] && exit "$1"

   exit $SUCCESS
}


#----- Script body: ------------------------------------------------------------

# Expected options are d, h, p, U, and H.
# The : following some of the options indicate they should have an argument
# passed with them. The colon as the first character of the optstring causes
# no dignostic output on error. See man bash for more information
# Set the options:
if [ -n "$1" ]
then
   while getopts ":f:d:h:p:U:H" Option
   do
      case $Option in
         d) db_name="$OPTARG"
            ;;
         f) input_file="$OPTARG"
            ;;
         h) db_hostname="$OPTARG"
            ;;
         p) db_port="$OPTARG"
            ;;
         U) db_user="$OPTARG"
            ;;
         \?) echo -e "Invalid option: -$OPTARG\n" >&2
            usage $FAILURE
            ;;
         :) echo "Using default value for -$OPTARG argument"
            ;;
         H|*) usage
            ;;
      esac
   done
fi

if [ -n "$_DEBUG" ]
then
   echo -e "\n\n#############################################"
   echo "#             DEBUG MODE ACTIVE             #"
   echo -e "#############################################\n\n"
   echo "PASS_PERMS:  PASS_PERMS"
   echo "PGPASS:      $PGPASS"
   echo "PSQL:        $PSQL"
   echo "PROG_NAME:   $PROG_NAME"
   echo "db_hostname: $db_hostname"
   echo "db_name:     $db_name"
   echo "db_port:     $db_port"
   echo "db_user:     $db_user"
   echo "input_file:  $input_file"
   echo
fi

# Check for necessary files, starting with cat:
[ -z $CAT ] && echo "ERROR: command cat not found" && exit $BAD_FILE

# Does the input file exist?:
check_file "$input_file" "e"
if [ $? -eq $BAD_FILE ]
then
   echo "ERROR: Input file $input_file not found."
   exit $BAD_FILE
fi

# Obviously we can't do much if the psql client was not found:
[ -z $PSQL ] && echo "ERROR: psql not found" && exit $BAD_FILE

check_file "$PGPASS" "e"
if [ ! $? -eq $BAD_FILE ] && [ "`stat -c %a $PGPASS`" = "$PASS_PERMS" ]
then
   export PGPASS
else
   echo "ERROR: $PGPASS not found, or has incorrect permissions"
   exit $BAD_FILE
fi

# Let the user know what we are doing:
echo -e "\nTests will be run as user $db_user using the $db_name database"
echo -e "on host ${db_hostname}, port ${db_port}.\n"
echo -n "Please wait while the connection is verified... "

# I can't find a sure-fire way to verify a good host was passed by either IP
# or by name (hosts will verify name to IP, but not a valid IP address), so
# we'll just make sure we have a valid host / port combination this way:
pg_version="`$PSQL -U $db_user -d $db_name -h $db_hostname -p $db_port \
             -Aqtc \"SELECT VERSION();\" 2>/dev/null`"

[ -z "$pg_version" ] && \
   echo "ERROR: Unable to connect to $db_hostname on port ${db_port}." && \
   exit $FAILURE

echo -e "Done.\nWill now begin testing.\n\n"
sleep 2

# And begin testing:
end_seen=TRUE
$CAT $input_file |
   while read line
   do
      ((line_number++))
      if [ "$line" = "$FAIL_COMMENT" ]
      then
         if [ $end_seen = TRUE ]
         then
            end_seen=FALSE
            expected_result=$FAILURE
            sql_stmt=""
            printf "% 4d: Preparing to execute a failing statement: " $l_count
            ((l_count += 1))
         else
            continue
         fi
      elif [ "$line" = "$PASS_COMMENT" ]
      then
         if [ $end_seen = TRUE ]
         then
            end_seen=FALSE
            expected_result=$SUCCESS
            sql_stmt=""
            printf "% 4d: Preparing to execute a passing statement: " $l_count
            ((l_count += 1))
         else
            continue
         fi
      elif [ "$line" = "$END_COMMENT" ]
      then
         end_seen=TRUE
         $PSQL -U $db_user -d $db_name -h $db_hostname -p $db_port \
               -Aqtc "$sql_stmt;" 1>/dev/null 2>&1
         if [ $? -eq $expected_result ]
         then
            echo "success"
         else
            echo "failed. Failing SQL statement was:"
            echo "$sql_stmt"
            echo "ending at line number $line_number"
            exit 1
         fi
         continue
      elif [ "${line:0:2}" = "--" ]
      then
         continue
      else
         sql_stmt="$sql_stmt $line"
      fi
   done

exit 0
