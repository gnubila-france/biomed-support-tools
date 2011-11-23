#!/bin/bash
# email-users.sh, v1.0
# Author: F. Michel, CNRS I3S, biomed VO support

help()
{   
  echo 
  echo "This script treats a file generated by monitor-se-space.sh (<SE_hostname>_users)"
  echo "and generates an email template to help VO support shifters send emails to heavy"
  echo "users of most loaded SEs."
  echo
  echo "Usage:"
  echo "$0 [-h|--help]"
  echo "$0 [--vo <VO>] <input file>"
  echo
  echo "  --vo <VO>: the Virtual Organisation. Defaults to biomed."
  echo
  echo "  <input file>: full name of the input file i.e. <SE_hostname>_users"
  echo
  echo "  -h, --help: display this help"
  echo
  exit 1
}

VO=biomed 

# Check parameters
while [ ! -z "$1" ]
do
  case "$1" in
    --vo ) VO=$2; shift;;
    -h | --help ) help;;
    *) INPUTFILE=$1 ;;
  esac
  shift
done
if test -z "$INPUTFILE" ; then
    help
fi

SEHOSTNAME=`echo $INPUTFILE | sed "s/_users//"i | cut -d"/" -f2`

echo -n "TO: biomed-technical-shifts@healthgrid.org;"
awk --field-separator "|" '{ printf " %s;",$2 }' $INPUTFILE
echo
cat <<EOF

SUBJECT: SE $SEHOSTNAME is full, please clean up or migrate your files

Dear $VO VO user,

You have stored more than 1 GB of files on SE $SEHOSTNAME, which is almost full.
Please take some time to cleanup files you no longer need, or migrate them to some other SE. The list below shows all users with more than 1 GB on that SE.

Please don't hesitate to contact us (biomed-technical-shifts@healthgrid.org) in case you experience difficulties in this process.

Also, note that it is not recommended to try to move many files in parallel: due to scalability issues of the SE, only a limited number of concurrent connections can be initiated.

Thanks in advance,
   The $VO technical support team.

EOF

echo "#----------------------------------------------------------------------------------"
echo "# User's DN                                                         Used space (GB)" 

awk --field-separator "|" '{ printf "%-70s %11s\n",$1,$3; }' $INPUTFILE

