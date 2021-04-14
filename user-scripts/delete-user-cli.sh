#!/bin/bash

display_date() {
	date "+%H:%M:%S %d/%m/%Y"
}

echo "[-] $(display_date) ===== ELIMINATION OF USER $1 =====" |& tee -a /etc/user-scripts/log/delete-user.log
if sudo userdel "$1"; then
	echo "[-] $(display_date): deleting user $1..." |& tee -a /etc/user-scripts/log/delete-user.log
	echo "[-] $(display_date): deleting /home/$1" |& tee -a /etc/user-scripts/log/delete-user.log
	sudo rm -rf /home/"$1"
	if ! getent passwd | grep "\<$1\>"; then
		echo "[+] $(display_date): user $1 was deleted succesfuly" |& tee -a /etc/user-scripts/log/delete-user.log
	fi
else
	echo "[!] $(display_date): failed to delete user $1" |& tee -a /etc/user-scripts/log/delete-user.log
fi
echo "" |& tee -a /etc/user-scripts/log/delete-user.log
