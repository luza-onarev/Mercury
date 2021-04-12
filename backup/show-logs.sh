#!/bin/bash
while true; do
	clear

	for file in $(find /backup/log/ -type f | sort)
	do
		echo "=== $file ==="
		cat $file
		echo
	done

	sleep 1
done

