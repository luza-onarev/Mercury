#!/bin/bash

display_date() {
	date "+%H:%M:%S %d/%m/%Y"
}

log_file="/etc/user-scripts/log/create-user.log"
no_run_log_file="/etc/user-scripts/log/cron.log"

if [[ $(find /var/www/html/users/ -type f | wc -l) != 0 ]]; then
	for file in $(find /var/www/html/users/ -type f)
		do
			username=$(grep username "$file" | awk '{print $3}')
			password=$(grep password "$file" | awk '{print $3}')
			# create user
			echo "[-] $(display_date) ===== CREATION OF USER $username =====" |& tee -a "$log_file"

			if sudo useradd -b /home/"$username" -d /home/"$username" -m -s /bin/false "$username"; then
				echo "[-] $(display_date) - Creating $username ..." |& tee -a "$log_file"

				# sets password
				echo "[-] $(display_date) - Changing $username's password ..." |& tee -a "$log_file"
				userch="$username"
				passch="$password"
				echo "$userch:$passch" | sudo chpasswd

				# changes /home owner and permissions
				echo "[-] $(display_date) - Changing /home/$username owner to $username:$username ..." |& tee -a "$log_file"
				sudo chown -R "$username":"$username" /home/"$username"

				echo "[-] $(display_date) - Changing /home/$username permissions to 700 ..." |& tee -a "$log_file"
				sudo chmod -R 700 /home/"$username"

				# looks for the user in the /etc/passwd to check if it's created
				if getent passwd | grep "\\<$username\\>"; then
					echo "[+] $(display_date) - User $username was created successfully" |& tee -a "$log_file"
				fi
			else
				# user creation fails
				echo "[!] $(display_date) - User creation failed" |& tee -a "$log_file"
			fi

			sudo rm -rf "$file"
			echo "" |& tee -a "$log_file"
		done
else
	echo "[-] $(display_date) - no users to create" |& tee -a "$no_run_log_file"
fi
