#!/bin/sh
logfile="lastRun.log"

cp ../config.xml.auth ../config.xml
phpunit IntegrationTests.php 2>&1 | tee $logfile
cp ../config.xml.noauth ../config.xml
clear
less -niFX $logfile

