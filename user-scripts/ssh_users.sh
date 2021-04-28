#!/bin/bash
clear

display_date() {
	date "+%H:%M:%S %d/%m/%Y"
}

log_file="/etc/user-scripts/log/ssh_users.log"
sql_pass=$(sudo cat /etc/sql_pass)

for db_user in $(echo "SELECT username FROM user;" | mysql --user=root -p"$sql_pass" users | tail -n +2); do
	if [[ $(echo "SELECT is_premium FROM user WHERE username='$db_user';" | mysql --user=root -p"$sql_pass" users | tail -n +2) == 1 ]]; then
		echo "user $db_user is premium"
		sudo usermod -G ssh_users "$db_user"
	else
		echo "user $db_user is NOT premium"
		sudo deluser "$db_user" ssh_users > /dev/null 2> /dev/null
	fi
done

echo "ssh_users: $(cat /etc/group | grep ssh_users | awk -F ":" '{print $4}')"
