#!/bin/bash

# make sure folder is mounted
sudo mount -t ext4 /dev/sdb1 /media/backup 2> /dev/null

# make sure folders exists
sudo mkdir -p /backup/gz 2> /dev/null
sudo mkdir -p /backup/log 2> /dev/null

sudo mkdir -p /backup/database 2> /dev/null
sudo mkdir -p /backup/home 2> /dev/null
sudo mkdir -p /backup/mercury 2> /dev/null
sudo mkdir -p /backup/config 2> /dev/null

# date print before every echo
print_date () {
	date "+%Y/%m/%d %T"
}

##### DATABASE #####
database () {
	# define date for sql file
	db_file_name="db-$(date '+%Y-%m-%d--%T').sql"

	echo "[-] $(print_date) - Backuping database ..." | sudo tee /backup/log/database.log
	# delete fist sql from directory
	sudo rm -rf $(find /backup/gz -name "db-*.sql.gz" -type f | sort | head -1)

	sudo rm -rf /backup/database/*
	if mysqldump --user=root -p"root" -x -A | sudo tee /backup/database/"$db_file_name" > /dev/null; then
		echo "[-] $(print_date) - Backup database completed" | sudo tee -a /backup/log/database.log
	fi

	echo "" | sudo tee -a /backup/log/database.log

	echo "[-] $(print_date) - Compressing database ..." | sudo tee -a /backup/log/database.log

	if sudo gzip -k /backup/database/"$db_file_name" && sudo mv /backup/database/"$db_file_name".gz /backup/gz; then
		echo "[-] $(print_date) - Compress database completed" | sudo tee -a /backup/log/database.log
	fi
	echo
}

##### HOME #####
home () {
	echo "[-] $(print_date) - Copying /home ..." | sudo tee /backup/log/home.log
	if sudo cp -r /home /backup/home/; then
		echo "[-] $(print_date) - Copy /home completed" | sudo tee -a /backup/log/home.log
	fi

	echo "" | sudo tee -a /backup/log/home.log


	echo "[-] $(print_date) - Compressing /backup/home ..." | sudo tee -a /backup/log/home.log
	if sudo tar -zcf /backup/gz/home.tar.gz /backup/home/home 2> /dev/null; then
		echo "[-] $(print_date) - Compress /backup/home completed" | sudo tee -a /backup/log/home.log
	fi
	echo
}

##### MERCURY #####
mercury () {
	echo "[-] $(print_date) - Copying Mercury ..." | sudo tee /backup/log/mercury.log
	if sudo cp -r /var/www/html /backup/mercury/; then
		echo "[-] $(print_date) - Copy Mercury completed" | sudo tee -a /backup/log/mercury.log
	fi

	echo "" | sudo tee -a /backup/log/mercury.log

	echo "[-] $(print_date) - Compressing Mercury ..." | sudo tee -a /backup/log/mercury.log
	if sudo tar -zcf /backup/gz/mercury.tar.gz /backup/mercury/html 2> /dev/null; then
		echo "[-] $(print_date) - Compress Mercury completed" | sudo tee -a /backup/log/mercury.log
	fi
	echo
}

##### CONFIG FILES #####
config () {
	sudo rm -rf /backup/config/*

	# APACHE
	echo "[-] $(print_date) - Copying Config files ..." | sudo tee /backup/log/config.log
	echo "[-] $(print_date) - |_ Apache ..." | sudo tee -a /backup/log/config.log
	if sudo cp -r /etc/apache2 /backup/config; then
		echo "[-] $(print_date) - |  |_ Apache completed" | sudo tee -a /backup/log/config.log
	else
		echo "[-] $(print_date) - |  |_ ERROR: Apache" | sudo tee -a /backup/log/config.log
	fi

	# POSTFIX
	echo "[-] $(print_date) - |_ Postfix ..." | sudo tee -a /backup/log/config.log
	if sudo cp -r /etc/postfix /backup/config; then
		echo "[-] $(print_date) - |  |_ Postfix completed" | sudo tee -a /backup/log/config.log
	else
		echo "[-] $(print_date) - |  |_ ERROR: Postfix" | sudo tee -a /backup/log/config.log
	fi

	# DOVECOT
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
	}

	echo "[-] $(print_date) - |_ Cron ..." | sudo tee -a /backup/log/config.log
	if cron_bak; then
		echo "[-] $(print_date) - |  |_ Cron completed" | sudo tee -a /backup/log/config.log
	else
		echo "[-] $(print_date) - |  |_ ERROR: Cron" | sudo tee -a /backup/log/config.log
	fi

	# FILES #
	files_bak () {
		files=("/etc/fstab")

		for file_bak in "${files[@]}"
		do
			sudo cp -r "$file_bak" /backup/config/files
		done
	}

	echo "[-] $(print_date) - |_ Files ..." | sudo tee -a /backup/log/config.log
	if files_bak; then
		echo "[-] $(print_date) - |  |_ Files completed" | sudo tee -a /backup/log/config.log
	else
		echo "[-] $(print_date) - |  |_ ERROR: Files" | sudo tee -a /backup/log/config.log
	fi
	echo
}

##### SCRIPT #####
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
