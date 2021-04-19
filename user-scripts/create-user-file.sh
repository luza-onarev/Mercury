#!/bin/bash

display_date() {
	date "+%H:%M:%S %d/%m/%Y"
}

log_file="/etc/user-scripts/log/create-user.log"

# read files created by PHP
if [[ $(find /var/www/html/users/ -type f | wc -l) != 0 ]]; then
	# if new file exists, create the user
	for file in $(find /var/www/html/users/ -type f)
		do
			username=$(grep username "$file" | awk '{print $3}')
			password=$(grep password "$file" | awk '{print $3}')
			# create user
			echo "[-] $(display_date) ===== CREATION OF USER $username =====" |& tee -a "$log_file"

			if sudo useradd -b /home/"$username" -d /home/"$username" -m -s /bin/bash "$username"; then
				echo "[-] $(display_date) - creating $username ..." |& tee -a "$log_file"

				# sets password
				echo "[-] $(display_date) - changing $username's password ..." |& tee -a "$log_file"
				userch="$username"
				passch="$password"
				echo "$userch:$passch" | sudo chpasswd

				# changes /home owner and permissions
				echo "[-] $(display_date) - changing /home/$username owner to $username:$username ..." |& tee -a "$log_file"
				sudo mkdir /home/"$username"/public_html
				sudo chown -R "$username":"$username" /home/"$username"

				echo "[-] $(display_date) - changing /home/$username permissions to 705 ..." |& tee -a "$log_file"
				sudo chmod -R 705 /home/"$username"

				# create DNS record
				echo "[-] $(display_date) - creating CNAME type DNS record ..." |& tee -a "$log_file"
				echo -e "$username.mercury.cells.es.\\tIN\\tCNAME\\tserver1.mercury.cells.es." | sudo tee -a /etc/bind/db.mercury

				# create vhost
				echo "[-] $(display_date) - creating Apache2 virtual host ..." |& tee -a "$log_file"
				
				sudo cat <<EOF > /etc/apache2/sites-available/"$username".conf
<VirtualHost *:80>
	ServerName $username.mercury.cells.es
	ServerAdmin ismael@mercury.cells.es	
	DocumentRoot /home/$username/public_html

	<Directory /home/$username/public_html>
			AllowOverride All
			Order Allow,Deny
			Allow from All
	</Directory>

	ErrorLog /var/log/apache2/error.log
	CustomLog /var/log/apache2/access.log combined
</VirtualHost>
EOF
				sudo a2ensite "$username".conf

				# restart services
				echo "[-] $(display_date) - restarting DNS and Apache2 service ..." |& tee -a "$log_file"
				sudo systemctl restart bind9.service apache2.service

				# looks for the user in the /etc/passwd to check if it's created
				if getent passwd | grep "\\<$username\\>"; then
					echo "[+] $(display_date) - == $(getent passwd | grep "\\<$username\\>") ==" |& tee -a "$log_file"
					echo "[+] $(display_date) - user $username was created successfully" |& tee -a "$log_file"
					echo -e "Subject: == USER $username CREATED ==\\n$(tail -11 "$log_file")" | sudo sendmail -f create-user@mercury.cells.es ismael
				fi
			else
				# user creation fails
				echo "[!] $(display_date) - user creation failed" |& tee -a "$log_file"
				echo -e "Subject: == USER $username CREATION FAILED ==\\n" | sudo sendmail -f create-user@mercury.cells.es ismael
			fi

			sudo rm -rf "$file"
			echo "" |& tee -a "$log_file"
		done
else
	echo "[-] $(display_date) - no users to create"
fi
