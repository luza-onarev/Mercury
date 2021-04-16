<?php
	error_reporting(E_ALL);
	ini_set('display_errors', TRUE);
	ini_set('display_startup_errors', TRUE);
	## function to validate the user info before insert it to database ##
	function validate_signup_info() {
		# variables to use in inserts and selects
		$username = $_POST["username"];
		$email = $_POST["email"];
		$password = hash('sha256', $_POST["password1"]);
		$date = strftime("%Y-%m-%d"); //yyyy-mm-dd

		# queries to check if all values are correct or user already exists
		$check_mail = mysqli_query($GLOBALS["db_conn"], "SELECT * FROM user WHERE email = '$email'");
		$check_username = mysqli_query($GLOBALS["db_conn"], "SELECT * FROM user WHERE username = '$username'");

		$valid_signup = 1;

		# avoid sql attacks by removing non alphanumerical characters
		$username = preg_replace("/[^a-z0-9]+/", "", $_POST["username"]);

		if ($username != $_POST["username"]) {
			echo '<div class="error-signup">';
			echo "Invalid characters found in user name!<br>";
			echo "Valid characters: a to z and 0 to 9<br>";
			echo "</div>";
			$valid_signup = 0;
		}

		if (mysqli_num_rows($check_username) > 0) {
			echo '<div class="error-signup">';
			echo "User name already exists!";
			echo "</div>";
			$valid_signup = 0;
		}

		if (mysqli_num_rows($check_mail) > 0) {
			echo '<div class="error-signup">';
			echo "Email account already exists!";
			echo "</div>";
			$valid_signup = 0;
		}

		if (!filter_var($_POST["email"], FILTER_VALIDATE_EMAIL)) {
			echo '<div class="error-signup">';
			echo "Email address seems bad formed!";
			echo "</div>";
			$valid_signup = 0;
		}

		if ($_POST["password1"] != $_POST["password2"]) {
			echo '<div class="error-signup">';
			echo "Passwords don't match!";
			echo "</div>";
			$valid_signup = 0;
		}		

		# users doesn't exist and it's good formatted
		if ($valid_signup) {
			$insert_sentence = "INSERT INTO `user` (`email`, `username`, `password`, `creation_date`) VALUES ('$email', '$username', '$password', '$date');";
			$insert_data = mysqli_query($GLOBALS["db_conn"], $insert_sentence);
			create_user_db();

			if ($insert_data) {
				# create file to create local user
				$raw_password = $_POST["password1"];
				$userfile = "username = $username\n";
				$passfile = "password = $raw_password\n";
				
				$file = fopen($_SERVER['DOCUMENT_ROOT'] . "/users/$username","wb");
				fwrite($file, $userfile);
				fwrite($file, $passfile);
				fclose($file);

				# display successful message 
				echo '<div class="succ-signup">';
				echo "The user was created successfully!<br>";
				echo "Access your control panel <a href='/home'>here</a>!";
				echo "</div>";

				return 1;
			} else {
				echo mysqli_error($GLOBALS["db_conn"]);
			}
		}

		mysqli_close($GLOBALS["db_conn"]);
		$_POST = array();
	}

	## function to validate login ##
	function validate_login_info() {
		$valid_login = 1;

		# avoid sql attacks by removing non alphanumerical characters
		$username = preg_replace("/[^a-z0-9]+/", "", $_POST["username"]);
		$password = hash("sha256", $_POST["password"]);

		if ($username != $_POST["username"]) {
			echo '<div class="error-signup">';
			echo "Invalid characters found in user name!<br>";
			echo "Valid characters: a to z and 0 to 9<br>";
			echo "</div>";
			$valid_login = 0;
		} else {
			$check_username = mysqli_query($GLOBALS["db_conn"], "SELECT username FROM user WHERE username = '$username'");
			$check_password = mysqli_query($GLOBALS["db_conn"], "SELECT password FROM user WHERE username = '$username'");
			$check_status = mysqli_query($GLOBALS["db_conn"], "SELECT is_active FROM user WHERE username = '$username'");

			$check_username_data = mysqli_fetch_array($check_username);
			$check_password_data = mysqli_fetch_array($check_password);
			$check_status_data   = mysqli_fetch_array($check_status);

			if ($check_status_data["is_active"] == 0) {
				if (is_null($check_username_data)) {
					$valid_login = 0;
				} else {
					echo '<div class="error-signup">';
					echo "User is disabled!<br>";
					echo "Contact the System Administrator<br>to get more help!<br>";
					# TODO: word "help" should redirect to help page on how to contact a sys admin
					echo "</div>";
					$valid_login = 0;

					# if user is disabled end function and skip the remaining checks
					return 0;
				}
					
			}

			if (is_null($check_username_data)) {
				echo '<div class="error-signup">';
				echo "User name not found!<br>";
				echo "</div>";
				$valid_login = 0;

				# if user doesn't exists end function and skip the remaining check
				return 0;
			}
			if ($check_password_data["password"] != $password) {
				echo '<div class="error-signup">';
				echo "Wrong password!<br>";
				echo "</div>";
				$valid_login = 0;
			}

			if ($valid_login) {
				return 1;
			}
		}

		mysqli_close($GLOBALS["db_conn"]);
		$_POST = array();
	}

	// function to change user's password //
	function change_password($curr_password, $new_password, $new_password2) {
		$change_password = 1;
		$username = $GLOBALS["curr_username"];
		$curr_hash_password = hash('sha256', $curr_password);

		$check_curr_password = mysqli_query($GLOBALS["db_conn"], "SELECT password FROM user WHERE username = '$username'");

		$check_curr_password_data = mysqli_fetch_array($check_curr_password);

		if ($check_curr_password_data["password"] != "$curr_hash_password") {
			echo '<div class="error-signup">';
			echo "Wrong current password!<br>";
			echo "</div>";
			$change_password = 0;
		}

		if ($new_password != $new_password2) {
			echo '<div class="error-signup">';
			echo "New passwords don't match!<br>";
			echo "</div>";
			$change_password = 0;
		}

		if ($change_password) {
			$new_hash_password = hash('sha256', $new_password);
			
			$change_password_command = mysqli_query($GLOBALS["db_conn"], "UPDATE user SET password = '$new_hash_password' WHERE username = '$username'");

			if ($change_password_command) {
				return 1;
			}
		}

		mysqli_close($GLOBALS["db_conn"]);
		$_POST = array();
	}

	function create_user_db() {
		## DATABASE ##
		define("DB_USERNAME_r", "root"); 
		define("DB_PASSWORD_r", "root");

		$db_conn = mysqli_connect(DB_SERVER, DB_USERNAME_r, DB_PASSWORD_r);

		$db_name = "db_" . $_POST["username"];
		$db_user = $_POST["username"];
		$db_password = $_POST["password1"];

		$queries = array(
			"CREATE DATABASE `$db_name` DEFAULT CHARACTER SET utf8 COLLATE utf8_general_ci;",
			"CREATE USER '$db_user'@'%' IDENTIFIED BY PASSWORD('$db_password');",
			"GRANT USAGE ON $db_name.* TO '$db_user'@'%' IDENTIFIED BY '$db_password' WITH MAX_QUERIES_PER_HOUR 0 MAX_CONNECTIONS_PER_HOUR 0 MAX_UPDATES_PER_HOUR 0 MAX_USER_CONNECTIONS 0;",
			"GRANT SELECT , INSERT , UPDATE, DELETE, CREATE , DROP ON `$db_name`.* TO '$db_user'@'%';",
			"FLUSH PRIVILEGES;"
		);

		foreach($queries as $query) {
			mysqli_query($db_conn, $query);
		}
	}
?>
