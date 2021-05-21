#!/bin/bash

display_date() {
	date "+%H:%M:%S %d/%m/%Y"
}

log_file="/etc/user-scripts/log/create-user.log"
sql_pass=$(sudo cat /etc/sql_root_pass)

# read entries from users_actions
while read -r line; do
	username=$(echo "$line" | awk '{print $1}')
	password=$(echo "$line" | awk '{print $2}')

	# create user
	echo "[-] $(display_date) ===== CREATION OF USER $username =====" |& tee -a "$log_file"

	if sudo useradd -b /home/"$username" -d /home/"$username" -m -s /bin/bash "$username"; then
		echo "[-] $(display_date) - creating $username ..." |& tee -a "$log_file"

		# sets password
		echo "[-] $(display_date) - changing $username's password ..." |& tee -a "$log_file"
		userch="$username"
		passch="$password"
		echo "$userch:$passch" | sudo chpasswd

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
		RewriteEngine on
		AllowOverride All
		Order Allow,Deny
		Allow from All
		php_admin_flag engine on
		AddType application/x-httpd-php .php
	</Directory>

	ErrorLog /var/log/apache2/error.log
	CustomLog /var/log/apache2/access.log combined
</VirtualHost>
EOF
		sudo a2ensite "$username".conf

		# restart services
		echo "[-] $(display_date) - restarting DNS and Apache2 service ..." |& tee -a "$log_file"
		if sudo systemctl reload bind9.service; then
			echo "[-] $(display_date) - DNS server restarted" |& tee -a "$log_file"
		else
			echo "[-] $(display_date) - ERROR restarting DNS server" |& tee -a "$log_file"
		fi
		if sudo systemctl reload apache2.service; then
			echo "[-] $(display_date) - Apache2 server restarted" |& tee -a "$log_file"
		else
			echo "[-] $(display_date) - ERROR restarting Apache2 server" |& tee -a "$log_file"
		fi

		# changes /home owner and permissions
		echo "[-] $(display_date) - changing /home/$username owner to $username:www-data ..." |& tee -a "$log_file"
		sudo mkdir /home/"$username"/public_html
		sudo chown -Rv "$username":www-data /home/"$username"

		echo "[-] $(display_date) - changing /home/$username permissions to 750 ..." |& tee -a "$log_file"
		sudo chmod u+s -Rv 750 /home/"$username"
		sudo chmod g+s -Rv 750 /home/"$username"

		# delete sql entry once user created
		echo "[!] $(display_date) - deleting user entry from users_actions.user_acts ..." |& tee -a "$log_file"
		echo "DELETE FROM user_acts WHERE username = '$username' AND action = 'add'" | mysql -u root -p"$(cat /etc/sql_pass)" users_actions

		# looks for the user in the /etc/passwd to check if it's created
		if getent passwd | grep "\\<$username\\>"; then
			echo "[+] $(display_date) - == $(getent passwd | grep "\\<$username\\>") ==" |& tee -a "$log_file"
			echo "[+] $(display_date) - user $username was created successfully" |& tee -a "$log_file"
			echo -e "Subject: == USER $username CREATED ==\\n$(tail -13 "$log_file")" | sudo sendmail -f create-user@mercury.cells.es ismael
			# delete sql entry once user is created
			echo "DELETE FROM user_acts WHERE username = '$username';" | mysql -u root -p"$sql_pass" users_actions
		fi

	else
		# user creation fails
		echo "[!] $(display_date) - user creation failed" |& tee -a "$log_file"
		echo -e "Subject: == USER $username CREATION FAILED ==\\n" | sudo sendmail -f create-user@mercury.cells.es ismael
	fi

	echo "" |& tee -a "$log_file"
done < <(echo "SELECT username,password FROM user_acts WHERE action = 'add'" | mysql -u root -p"$sql_pass" users_actions | tail -n +2)
