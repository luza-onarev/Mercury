<?php
	# show php errors
	error_reporting(E_ALL);
	ini_set('display_errors', TRUE);
	ini_set('display_startup_errors', TRUE);

	// function to validate the user info before insert it to database //
	function validate_signup_info() {
		# variables to use in inserts and selects
		$username = $_POST["username"];
		$email = $_POST["email"];
		$password = hash('sha256', $_POST["password1"]);
		$date = strftime("%Y-%m-%d"); //yyyy-mm-dd
		$raw_password = $_POST["password1"];

		# queries to check if all values are correct or user already exists
		$check_mail = mysqli_query($GLOBALS["db_conn"], "SELECT * FROM user WHERE email = '$email'");
		$check_username = mysqli_query($GLOBALS["db_conn"], "SELECT * FROM user WHERE username = '$username'");

		$valid_signup = 1;

		# avoid sql injection by removing non alphanumerical characters
		$username = preg_replace("/[^a-z0-9]+/", "", $_POST["username"]);

		# user writes non alphanumerical characters in user box
		if ($username != $_POST["username"]) {
			echo '<div class="error-signup">';
			echo "Invalid characters found in user name!<br>";
			echo "Valid characters: a to z and 0 to 9<br>";
			echo "</div>";
			$valid_signup = 0;
		}

		# user write and existing user name
		if (mysqli_num_rows($check_username) > 0) {
			echo '<div class="error-signup">';
			echo "User name already exists!";
			echo "</div>";
			$valid_signup = 0;
		}

		# user write and existing email account
		if (mysqli_num_rows($check_mail) > 0) {
			echo '<div class="error-signup">';
			echo "Email account already exists!";
			echo "</div>";
			$valid_signup = 0;
		}

		# USELESS!! ALREADY DONE BY HTML FORM. LEAVING IT JUST IN CASE
		# user writes a bad formed email address
		if (!filter_var($_POST["email"], FILTER_VALIDATE_EMAIL)) {
			echo '<div class="error-signup">';
			echo "Email address seems bad formed!";
			echo "</div>";
			$valid_signup = 0;
		}

		# passwords don't match
		if ($_POST["password1"] != $_POST["password2"]) {
			echo '<div class="error-signup">';
			echo "Passwords don't match!";
			echo "</div>";
			$valid_signup = 0;
		}		

		# user doesn't exist and it's good formatted, insert user to db
		if ($valid_signup) {
			# insert user in users database
			$insert_sentence = "INSERT INTO `user` (`email`, `username`, `password`, `creation_date`) VALUES ('$email', '$username', '$password', '$date');";
			$insert_data = mysqli_query($GLOBALS["db_conn"], $insert_sentence);

			# create db for user
			create_user_db();

			# if all user info is good, creates file for cron to create unix user
			if ($insert_data) {
				# insert user in users_actions database
				$insert_sentence_new_user = "INSERT INTO `user_acts` (`username`, `password`, `action`) VALUES ('$username', '$raw_password', 'add');";
				$insert_data_new_user = mysqli_query($GLOBALS["db_conn_new_user"], $insert_sentence_new_user);

				# display successful message 
				echo '<div class="succ-signup">';
				echo "The user was created successfully!<br>";
				echo "Access your control panel <a href='/home'>here</a>!";
				echo "</div>";

				return true;
			} else {
				echo mysqli_error($GLOBALS["db_conn"]);
			}
		}
	}

	// function to validate login //
	function validate_login_info() {
		$valid_login = 1;

		# avoid sql injection by removing non alphanumerical characters
		$username = preg_replace("/[^a-z0-9]+/", "", $_POST["username"]);
		$password = hash("sha256", $_POST["password"]);

		# user writes non alphanumerical characters in user box
		if ($username != $_POST["username"]) {
			echo '<div class="error-signup">';
			echo "Invalid characters found in user name!<br>";
			echo "Valid characters: a to z and 0 to 9<br>";
			echo "</div>";
			$valid_login = 0;
		# users writes valid data in log in form
		} else {
			# sql sentences to check user info
			$check_username = mysqli_query($GLOBALS["db_conn"], "SELECT username FROM user WHERE username = '$username'");
			$check_password = mysqli_query($GLOBALS["db_conn"], "SELECT password FROM user WHERE username = '$username'");
			$check_status   = mysqli_query($GLOBALS["db_conn"], "SELECT is_active FROM user WHERE username = '$username'");

			$check_username_data = mysqli_fetch_array($check_username);
			$check_password_data = mysqli_fetch_array($check_password);
			$check_status_data   = mysqli_fetch_array($check_status);

			# user is disabled
			if ($check_status_data["is_active"] == 0) {
				if (is_null($check_username_data)) {
					$valid_login = 0;
				} else {
					echo '<div class="error-signup">';
					echo "User is disabled!<br>";
					echo "Contact a System Administrator<br>to get more <a href='/help/#contact_sys-admin' target='_blank'>help</a>!<br>";
					echo "</div>";
					$valid_login = 0;
					# if user is disabled ends function and skips the remaining checks
					return 0;
				}
			}

			# user writes an inexistent user
			if (is_null($check_username_data)) {
				echo '<div class="error-signup">';
				echo "User name not found!<br>";
				echo "</div>";
				$valid_login = 0;

				# if user doesn't exists ends function and skips the remaining check
				return 0;
			}
			if ($check_password_data["password"] != $password) {
				echo '<div class="error-signup">';
				echo "Wrong password!<br>";
				echo "</div>";
				$valid_login = 0;
			}

			# all user info submitted is valid
			if ($valid_login) {
				return 1;
			}
		}
	}

	// function to change user's password //
	function change_password($username, $password, $new_password, $new_password2) {
		## DATABASE ##
		define("DB_SERVER_cha", "localhost");
		define("DB_USERNAME_cha", "change_user");
		define("DB_PASSWORD_cha", "usertochange");

		$db_conn = mysqli_connect(DB_SERVER_cha, DB_USERNAME_cha, DB_PASSWORD_cha);

		$change_password = 1;
		# encrypt password to compare it with user's password
		$curr_hash_password = hash('sha256', $password);

		# sql sentences to check current password 
		$check_curr_password = mysqli_query($db_conn, "SELECT password FROM users.user WHERE username = '$username'");
		$check_curr_password_data = mysqli_fetch_array($check_curr_password);

		# user writes an invalid current password
		if ($check_curr_password_data["password"] != "$curr_hash_password") {
			echo '<div class="error-signup">';
			echo "Wrong current password!<br>";
			echo "</div>";
			$change_password = 0;
		}

		# new passwords don't match
		if ($new_password != $new_password2) {
			echo '<div class="error-signup">';
			echo "New passwords don't match!<br>";
			echo "</div>";
			$change_password = 0;
		}

		# all data submitted to change password is valid
		if ($change_password) {
			# encrypt new password
			$new_hash_password = hash('sha256', $new_password);
			
			# sql sentence to change password from db
			$change_password_command = mysqli_query($db_conn, "UPDATE users.user SET password = '$new_hash_password' WHERE username = '$username'");

			# add new password info to a file for cron to change unix user password too
			if ($change_password_command) {
				# change sql user password
				$change_sql_user_pass_sentence = "SET PASSWORD FOR '$username'@'%' = PASSWORD('$new_password'); FLUSH PRIVILEGES;";
				if (mysqli_query($db_conn, $change_sql_user_pass_sentence)) {
					$cha_status = 1;
				} else {
					$cha_status = 0;
				}

				$change_unix_user_pass_sentence = "INSERT INTO users_actions.user_acts (`username`, `password`, `action`) VALUES ('$username', '$new_password', 'cha');";
				if (mysqli_query($db_conn, $change_unix_user_pass_sentence)) {
					$cha_status = 1;
				} else {
					$cha_status = 0;
				}

				if ($cha_status == 1) {
					return true;
				}
			}
		}
	}

	// function to create new db with user's name //
	function create_user_db() {
		## DATABASE ##
		define("DB_SERVER_add", "localhost");
		define("DB_USERNAME_add", "root"); 
		define("DB_PASSWORD_add", "root");

		$db_conn = mysqli_connect(DB_SERVER_add, DB_USERNAME_add, DB_PASSWORD_add);

		$db_name = "db_" . $_POST["username"];
		$db_user = $_POST["username"];
		$db_password = $_POST["password1"];

		# sql sentences to create new user and database
		$queries = array(
			"CREATE DATABASE `$db_name` DEFAULT CHARACTER SET utf8 COLLATE utf8_general_ci;",
			"CREATE USER '$db_user'@'%' IDENTIFIED BY PASSWORD('$db_password');",
			"GRANT USAGE ON $db_name.* TO '$db_user'@'%' IDENTIFIED BY '$db_password' WITH MAX_QUERIES_PER_HOUR 0 MAX_CONNECTIONS_PER_HOUR 0 MAX_UPDATES_PER_HOUR 0 MAX_USER_CONNECTIONS 0;",
			"GRANT SELECT , INSERT , UPDATE , DELETE, CREATE , ALTER , DROP ON `$db_name`.* TO '$db_user'@'%';",
			"FLUSH PRIVILEGES;"
		);

		# run all sql sentences from $queries array
		foreach($queries as $query) {
			if (mysqli_query($db_conn, $query)) {
				$add_status = 1;
			} else {
				echo mysqli_query($db_conn, $query);
				$add_status = 0;
			}
		}

		if ($add_status == 1) {
			return true;
		}
	}

	// function to delete users //
	function delete_user($username) {
		## DATABASE ##
		define("DB_SERVER_del", "localhost");
		# this user only has privileges to select, drop, delete and insert on all databases
		define("DB_USERNAME_del", "action_user"); 
		define("DB_PASSWORD_del", "usertodoactions");

		$db_conn = mysqli_connect(DB_SERVER_del, DB_USERNAME_del, DB_PASSWORD_del);

		# sql sentences to delete db user and db
		$queries = array(
			"DROP DATABASE IF EXISTS db_$username;",
			"DROP USER IF EXISTS '$username'@'%';",
			"DELETE FROM users.user WHERE username = '$username';",
			"INSERT INTO users_actions.user_acts (`username`, `action`) VALUES ('$username', 'del');",
			"FLUSH PRIVILEGES;"
		);

		# run all sql sentences from $queries array
		foreach($queries as $query) {
			if (mysqli_query($db_conn, $query)) {
				$del_status = 1;
			} else {
				echo mysqli_query($db_conn, $query);
				$del_status = 0;
			}
		}

		if ($del_status == 1) {
			return true;
		}
	}
?>
