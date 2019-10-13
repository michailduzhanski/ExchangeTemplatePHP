#!/bin/bash
	ret=$(ps aux | grep [w]atchdog.sh | wc -l)
	echo $ret
	if [ "$ret" -eq 2 ]
then {
	echo "EXIT. wathcdog already running!"
        exit 1
}
else 
{
	echo "Running watchdog" #output text
        kill -9 $(ps -ef | awk '$NF~"watchdog" {print $2}')
	sleep 1  #delay
        #echo $(ps -ef | awk '$NF~"watchdog" {print $2}')
        sh watchdog.sh & #command for run program
        exit 1	
}
fi;
