#!/bin/bash
clear

display_date() {
	date "+%H:%M:%S %d/%m/%Y"
}

log_file="/etc/user-scripts/log/ssh_users.log"
sql_pass=$(sudo cat /etc/sql_root_pass)

for db_user in $(echo "SELECT username FROM user;" | mysql -u root -p"$sql_pass" users | tail -n +2); do
	if [[ $(echo "SELECT is_premium FROM user WHERE username='$db_user';" | mysql -u root -p"$sql_pass" users | tail -n +2) == 1 ]]; then
		echo "user $db_user is premium"
		if sudo usermod -G ssh_users "$db_user"; then
			echo "[-] $(display_date) - user $db_user added to ssh_users group" |& tee -a "$log_file"
		fi
	else
		echo "user $db_user is NOT premium"
		if sudo deluser "$db_user" ssh_users 2> /dev/null; then

			for pid in $(sudo ps aux | grep sshd | grep "$db_user"@pts | awk '{print $2}'); do	
				echo "[-] killing connection user: $db_user -- pid: $pid"
				sudo kill -9 "$pid"
			done

			echo "[-] $(display_date) - user $db_user removed from ssh_users group" |& tee -a "$log_file"
		fi
	fi
done

echo "ssh_users: $(sudo cat /etc/group | grep ssh_users | awk -F ":" '{print $4}')"
