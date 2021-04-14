#!/bin/bash

display_date() {
	date "+%H:%M:%S %d/%m/%Y"
}

# create user
echo "[-] $(display_date) ===== CREATION OF USER $1 =====" |& tee -a /etc/user-scripts/log/create-user.log

if sudo useradd -b /home/"$1" -d /home/"$1" -m -s /bin/false "$1"; then
	echo "[-] $(display_date) - creating user $1 ..." |& tee -a /etc/user-scripts/log/create-user.log

	# sets password
	echo "[-] $(display_date) - changing $1's password ..." |& tee -a /etc/user-scripts/log/create-user.log
	user="$1"
	pass="$2"
	echo "$user:$pass" | sudo chpasswd

	# changes /home owner and permissions
	echo "[-] $(display_date) - changing /home/$1 owner to $1:$1 ..." |& tee -a /etc/user-scripts/log/create-user.log
	sudo chown "$1":"$1" /home/"$1"

	echo "[-] $(display_date) - changing /home/$1 permissions to 700 ..." |& tee -a /etc/user-scripts/log/create-user.log
	sudo chmod 700 /home/"$1"

	# looks for the user in the /etc/passwd to check if it's created
	if getent passwd | grep "\<$1\>"; then
		echo "[+] $(display_date) - user $1 was created successfully" |& tee -a /etc/user-scripts/log/create-user.log
	fi
else
	# user creation fails
	echo "[!] $(display_date) - user creation failed" |& tee -a /etc/user-scripts/log/create-user.log
fi
echo "" |& tee -a /etc/user-scripts/log/create-user.log
