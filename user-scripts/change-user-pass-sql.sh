#!/bin/bash

display_date() {
	date "+%H:%M:%S %d/%m/%Y"
}

sql_pass=$(sudo cat /etc/sql_login_user_pass)

while read -r line
do
	username=$(echo "$line" | awk '{print $1}')
	password=$(echo "$line" | awk '{print $2}')

	if echo "$username:$password" | sudo chpasswd; then
		echo -e "Subject: == USER $username CHANGED PASSWORD ==" | sudo sendmail -f users-pass@mercury.cells.es ismael
	else
		echo -e "Subject: == USER $username CHANGED PASSWORD FAILED ==" | sudo sendmail -f users-pass@mercury.cells.es ismael
	fi
done < <(echo "select username,password from user_acts where action = 'cha'" | mysql -u login_user -p"$sql_pass" users_actions | tail -n +2)
