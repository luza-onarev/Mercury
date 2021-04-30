#!/bin/bash

display_date() {
	date "+%H:%M:%S %d/%m/%Y"
}

#log_file="/etc/user-scripts/log/users-pass.log"

# count files created by PHP
if [[ $(find /var/www/html/users-pass/ -type f | wc -l) != 0 ]]; then
	# if new file exists, change the password
	for file in $(find /var/www/html/users-pass/ -type f)
		do
			username=$(grep username "$file" | awk '{print $3}')
			password=$(grep password "$file" | awk '{print $3}')

			if echo "$username:$password" | sudo chpasswd; then
				echo -e "Subject: == USER $username CHANGED PASSWORD ==" | sudo sendmail -f users-pass@mercury.cells.es ismael
			else
				echo -e "Subject: == USER $username CHANGED PASSWORD FAILED ==" | sudo sendmail -f users-pass@mercury.cells.es ismael
			fi

			sudo rm -rf "$file"
		done
fi
