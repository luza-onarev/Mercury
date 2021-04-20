#!/bin/bash

display_date() {
	date "+%H:%M:%S %d/%m/%Y"
}

log_file="/etc/user-scripts/log/delete-user.log"

# read files created by PHP
if [[ $(find /var/www/html/users-delete/ -type f | wc -l) != 0 ]]; then
	# if file exists, delete the user
	for file in $(find /var/www/html/users-delete/ -type f)
		do
			username=$(grep username "$file" | awk '{print $3}')
			# delete user
			echo "[-] $(display_date) ===== DELETION OF USER $username =====" |& tee -a "$log_file"
			if sudo userdel "$username"; then
				echo "[-] $(display_date) - deleting $username ..." |& tee -a "$log_file"

				# delete home folder
				echo "[-] $(display_date) - deleting /home/$username ..." |& tee -a "$log_file"
				sudo rm -rf /home/"$username"

				# delete dns record
				echo "[-] $(display_date) - deleting DNS record ..." |& tee -a "$log_file"
				sed /"$username".mercury.cells.es/d /etc/bind/db.mercury | sudo tee /tmp/dns
				sudo cp /tmp/dns /etc/bind/db.mercury
				# delete empty lines
				sudo sed '/^$/d' /etc/bind/db.mercury | sudo tee /tmp/dns
				sudo cp /tmp/dns /etc/bind/db.mercury

				# delete vhost
				echo "[-] $(display_date) - deleting Apache2 virtual host ..." |& tee -a "$log_file"
				sudo a2dissite "$username".conf
				sudo rm -rf /etc/apache2/sites-enabled/"$username".conf
				sudo rm -rf /etc/apache2/sites-available/"$username".conf

				# restart services
				echo "[-] $(display_date) - restarting DNS and Apache2 service ..." |& tee -a "$log_file"
				if sudo systemctl restart bind9.service; then
					echo "[-] $(display_date) - DNS server restarted" |& tee -a "$log_file"
				else
					echo "[-] $(display_date) - ERROR restarting DNS server" |& tee -a "$log_file"
				fi
				if sudo systemctl apache2.service; then
					echo "[-] $(display_date) - Apache2 server restarted" |& tee -a "$log_file"
				else
					echo "[-] $(display_date) - ERROR restarting Apache2 server" |& tee -a "$log_file"
				fi

				echo "[+] $(display_date) - user $username was deleted successfully" |& tee -a "$log_file"

				# send mail
				echo -e "Subject: == USER $username DELETED ==\\n$(tail -10 "$log_file")" | sudo sendmail -f create-user@mercury.cells.es ismael

				# delete file
				sudo rm -rf "$file"
				echo "" |& tee -a "$log_file"
			else
				echo -e "Subject: == USER $username DELETION FAILED ==\\n$(tail -10 $log_file)" | sudo sendmail -f delete-user@mercury.cells.es ismael
			fi
		done
else
	echo "[-] $(display_date) - no users to delete"
fi
