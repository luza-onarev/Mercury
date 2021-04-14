<?php
	session_start();

	## DATABASE ##
	define("DB_SERVER", "localhost");
	// this user only has privileges to select data from users database
	define("DB_USERNAME", "login_user");
	define("DB_PASSWORD", "usertoselectdata");
	define("DB_DATABASE", "users");

	$db_conn = mysqli_connect(DB_SERVER, DB_USERNAME, DB_PASSWORD, DB_DATABASE);

	include("../funcs.php");

	if (empty($_SESSION)) {
		header("Location: /login?redi=no_auth");
	} else {
		//var_dump($_SESSION);
		$curr_username = $_SESSION["username"];
	}

	$check_premium = mysqli_query($db_conn, "SELECT is_premium FROM user WHERE username = '$curr_username'");
	$check_premium_data = mysqli_fetch_array($check_premium);
	$premium = $check_premium_data["is_premium"];
?>
<!DOCTYPE html>
<html>
<head>
	<title>Home | MERCURY HOSTING</title>
	<?php
		include("../head.html");
	?>
</head>
<body>
	<header>
		<?php
			include("../header_home.html");
		?>
	</header>
	<section>
		<div class="div-home">
			<h3><?php echo $curr_username . ".mercury.cells.es"; ?></h3>
			<p>
				Access your subdomain <a href="<?php echo "http://" .  $curr_username . ".mercury.cells.es"; ?>" target="_blank">here</a>.
			</p>
			<table class="table-home">
				<th colspan="2">
					User directory
				</th>
				<tr>
					<td>
						Path
					</td>
					<td>
						<?php echo "/home/$curr_username" ?>
					</td>
				</tr>
				<tr>
					<td>
						Current space used
					</td>
					<td>
						<?php
							$home_size = shell_exec("du -sh /home/$curr_username | awk '{print $1}'");
							if (is_null($home_size)) {
								echo "Loading folder size ...";
							} else {
								if ($premium) {
									echo $home_size . " - Maximum: Unlimited";
								} else {
									echo $home_size . " - Maximum: 10GB";
								}
							}
						?>
					</td>
				</tr>
				<th colspan="2">
					Remote access
				</th>
				<tr>
					<td>
						FTP User
					</td>
					<td>
						<?php echo "$curr_username" ?>
					</td>
				</tr>
				<tr>
					<td>
						FTP Password
					</td>
					<td>
						Same as login
					</td>
				</tr>
				<tr>
					<td>
						SSH User
					</td>
					<td>
						<?php echo "$curr_username" ?>
					</td>
				<tr>
					<td>
						SSH Password
					</td>
					<td>
						<?php
							if ($premium) {
								echo "Same as login";
							} else {
								echo "SSH access is not permitted with free account";
							}
						?>
					</td>
				</tr>
				<th colspan="2">
					Databases
				</th>
				<tr>
					<td>
						SQL User
					</td>
					<td>
						<?php echo "$curr_username" ?>
					</td>
				</tr>
				<tr>
					<td>
						SQL Password
					</td>
					<td>
						Same as login
					</td>
				</tr>
				<td>
					<p>
						Access your phpMyAdmin panel <a href="/phpmyadmin" target="_blank">here</a>.<br>
						Access your webmail page <a href="/mail/src/webmail.php" target="_blank">here</a>.
					</p>
				</td>
			</table>
		</div>
	</section>
	<footer>
		<?php
			include("../footer.php");
		?>
	</footer>
</body>
</html>
