#!/bin/bash

# make sure folder is mounted
sudo mount -t ext4 /dev/sdb1 /backup 2> /dev/null

# make sure folders exists
sudo mkdir /backup 2> /dev/null
sudo mkdir /backup/gz 2> /dev/null
sudo mkdir /backup/log 2> /dev/null
sudo mkdir /backup/database 2> /dev/null
sudo mkdir /backup/home 2> /dev/null
sudo mkdir /backup/mercury 2> /dev/null
sudo mkdir /backup/config 2> /dev/null
sudo mkdir /backup/config/files 2> /dev/null

# date print before every echo
print_date () {
	date "+%Y/%m/%d %T"
}
bak_mail=()

sql_pass=$(sudo cat /etc/sql_root_pass)

# DATABASE #
database () {
	# define date for sql file
	db_file_name="db-$(date '+%Y-%m-%d--%T').sql"

	echo "[-] $(print_date) - Backuping database ..." | sudo tee /backup/log/database.log
	# delete fist sql from directory
	sudo rm -rfv "$(find /backup/gz -name "db-*.sql.gz" -type f | sort | head -1)"

	if mysqldump -u root -p"$sql_pass" --all-databases | sudo tee /backup/database/"$db_file_name" > /dev/null; then
		echo "[-] $(print_date) - Backup database completed" | sudo tee -a /backup/log/database.log
	fi
	echo "" | sudo tee -a /backup/log/database.log

	echo "[-] $(print_date) - Compressing database ..." | sudo tee -a /backup/log/database.log

	if sudo gzip -k /backup/database/"$db_file_name" && sudo mv /backup/database/"$db_file_name".gz /backup/gz; then
		echo "[-] $(print_date) - Compress database completed" | sudo tee -a /backup/log/database.log
		bak_mail+=("database")
	fi
	echo
}

# HOME #
home () {
	echo "[-] $(print_date) - Copying /home ..." | sudo tee /backup/log/home.log
	if sudo cp -r /home /backup/home/; then
		echo "[-] $(print_date) - Copy /home completed" | sudo tee -a /backup/log/home.log
	fi
	echo "" | sudo tee -a /backup/log/home.log

	echo "[-] $(print_date) - Compressing /backup/home ..." | sudo tee -a /backup/log/home.log
	if sudo tar -zcf /backup/gz/home.tar.gz /backup/home/home 2> /dev/null; then
		echo "[-] $(print_date) - Compress /backup/home completed" | sudo tee -a /backup/log/home.log
		bak_mail+=("home")
	fi
	echo
}

# MERCURY #
mercury () {
	echo "[-] $(print_date) - Copying Mercury ..." | sudo tee /backup/log/mercury.log
	if sudo cp -r /var/www/html /backup/mercury/; then
		echo "[-] $(print_date) - Copy Mercury completed" | sudo tee -a /backup/log/mercury.log
	fi

	echo "" | sudo tee -a /backup/log/mercury.log

	echo "[-] $(print_date) - Compressing Mercury ..." | sudo tee -a /backup/log/mercury.log
	if sudo tar -zcf /backup/gz/mercury.tar.gz /backup/mercury/html 2> /dev/null; then
		echo "[-] $(print_date) - Compress Mercury completed" | sudo tee -a /backup/log/mercury.log
		bak_mail+=("mercury")
	fi
	echo
}

# CONFIG FILES #
config () {
	# APACHE #
	echo "[-] $(print_date) - Copying Config files ..." | sudo tee /backup/log/config.log
	echo "[-] $(print_date) - |_ Apache ..." | sudo tee -a /backup/log/config.log
	if sudo cp -r /etc/apache2 /backup/config; then
		echo "[-] $(print_date) - |  |_ Apache completed" | sudo tee -a /backup/log/config.log
	else
		echo "[-] $(print_date) - |  |_ ERROR: Apache" | sudo tee -a /backup/log/config.log
	fi

	# POSTFIX #
	echo "[-] $(print_date) - |_ Postfix ..." | sudo tee -a /backup/log/config.log
	if sudo cp -r /etc/postfix /backup/config; then
		echo "[-] $(print_date) - |  |_ Postfix completed" | sudo tee -a /backup/log/config.log
	else
		echo "[-] $(print_date) - |  |_ ERROR: Postfix" | sudo tee -a /backup/log/config.log
	fi

	# DOVECOT #
	echo "[-] $(print_date) - |_ Dovecot ..." | sudo tee -a /backup/log/config.log
	if sudo cp -r /etc/dovecot /backup/config; then
		echo "[-] $(print_date) - |  |_ Dovecot completed" | sudo tee -a /backup/log/config.log
	else
		echo "[-] $(print_date) - |  |_ ERROR: Dovecot" | sudo tee -a /backup/log/config.log
	fi

	# DNS #
	echo "[-] $(print_date) - |_ Bind9 ..." | sudo tee -a /backup/log/config.log
	if sudo cp -r /etc/bind /backup/config; then
		echo "[-] $(print_date) - |  |_ Bind9 completed" | sudo tee -a /backup/log/config.log
	else
		echo "[-] $(print_date) - |  |_ ERROR: Bind9" | sudo tee -a /backup/log/config.log
	fi

	# CRON #
	cron_bak () {
		for dir_cron in $(sudo find /etc/cron* -type d)
		do
			sudo cp -r "$dir_cron" /backup/config/cron
		done
		sudo crontab -l | sudo tee /backup/config/cron/crontab_by_root > /dev/null
	}

	echo "[-] $(print_date) - |_ Cron ..." | sudo tee -a /backup/log/config.log
	if cron_bak; then
		echo "[-] $(print_date) - |  |_ Cron completed" | sudo tee -a /backup/log/config.log
	else
		echo "[-] $(print_date) - |  |_ ERROR: Cron" | sudo tee -a /backup/log/config.log
	fi

	# VSFTPD #
	echo "[-] $(print_date) - |_ vsFTPd ..." | sudo tee -a /backup/log/config.log
	if sudo cat /etc/vsftpd.conf | sudo tee /backup/config/files/vsftpd.conf > /dev/null; then
		echo "[-] $(print_date) - |  |_ vsFTPd completed" | sudo tee -a /backup/log/config.log
	else
		echo "[-] $(print_date) - |  |_ ERROR: vsFTPd" | sudo tee -a /backup/log/config.log
	fi

	# FSTAB #
	echo "[-] $(print_date) - |_ fstab ..." | sudo tee -a /backup/log/config.log
	if sudo cat /etc/fstab | sudo tee /backup/config/files/fstab > /dev/null; then
		echo "[-] $(print_date) - |  |_ fstab completed" | sudo tee -a /backup/log/config.log
	else
		echo "[-] $(print_date) - |  |_ ERROR: fstab" | sudo tee -a /backup/log/config.log
	fi


	bak_mail+=("config")
	echo
}

## SCRIPT ##
if [[ "$#" == 0 ]] || [[ "$1" == "all" ]]; then
	database
	home
	mercury
	config
else
	for var in "$@"
	do
		case $var in
			"database")
				database
			;;
			"home")
				home
			;;
			"mercury")
				mercury
			;;
			"config")
				config
			;;
			*)
				echo "[!] $(print_date) -- INVALID ARGUMENT $var --" | sudo tee /backup/log/error.log
				echo "" | sudo tee -a /backup/log/error.log
		esac
	done
fi

mail_log () {
	for var in "${bak_mail[@]}"
	do
		cat /backup/log/"$var".log
		echo
	done
}

echo -e "Subject: == BACKUP ${bak_mail[*]} ==\\n$(mail_log)" | sudo sendmail -f backup@mercury.cells.es ismael
