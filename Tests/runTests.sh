#!/bin/sh
logfile="lastRun.log"
echo -n "Syntax check..."
( cd ..
  find . -name \*.php -print | \
  xargs -n 1 php -l | \
  grep -v 'No syntax errors detected in'
) | tee $logfile
if [ -n "`cat $logfile`" ] ; then
    echo "Errors found during syntax check! Stopping!"
    exit 1
fi
echo " passed"
echo

cp ../config.xml.auth ../config.xml
phpunit IntegrationTests.php $* 2>&1 | tee $logfile
cp ../config.xml.noauth ../config.xml
# clear
# less -niFX $logfile

