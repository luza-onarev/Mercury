<?php
	function create_user_db() {
		## DATABASE ##
		//define("DB_SERVER_r", "localhost");
		define("DB_USERNAME_r", "root"); 
		define("DB_PASSWORD_r", "root");

		$db_conn = mysqli_connect(DB_SERVER, DB_USERNAME_r, DB_PASSWORD_r);

		$db_name = $_POST["username"] . "_db";
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
