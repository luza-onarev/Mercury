#!/bin/bash 

display_date() {
	date "+%H:%M:%S %d/%m/%Y"
}

log_file="/etc/user-scripts/log/create-user.log"

for file in $(find /var/www/html/users/ -type f)
do
	username=$(grep username "$file" | awk '{print $3}')
	password=$(grep password "$file" | awk '{print $3}')
	# create user
	echo "[-] $(display_date) ===== CREATION OF USER $username =====" |& tee -a "$log_file"

	if sudo useradd -b /home/"$username" -d /home/"$username" -m -s /bin/false "$username"; then
		echo "[-] $(display_date) - creating $username ..." |& tee -a "$log_file"

		# sets password
		echo "[-] $(display_date) - changing $username's password ..." |& tee -a "$log_file"
		userch="$username"
		passch="$password"
		echo "$userch:$passch" | sudo chpasswd

		# changes /home owner and permissions
		echo "[-] $(display_date) - changing /home/$username owner to $username:$username ..." |& tee -a "$log_file"
		sudo chown "$username":"$username" /home/"$username"

		echo "[-] $(display_date) - changing /home/$username permissions to 700 ..." |& tee -a "$log_file"
		sudo chmod 700 /home/"$1"

		# looks for the user in the /etc/passwd to check if it's created
		if getent passwd | grep "\\<$username\\>"; then
			echo "[+] $(display_date) - user $username was created successfully" |& tee -a "$log_file"
		fi
	else
		# user creation fails
		echo "[!] $(display_date) - user creation failed" |& tee -a "$log_file"
	fi
	echo "" |& tee -a "$log_file"
done
