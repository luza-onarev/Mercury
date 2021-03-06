<?php
	session_start();

	# show php errors
	error_reporting(E_ALL);
	ini_set('display_errors', TRUE);
	ini_set('display_startup_errors', TRUE);

	## DATABASE ##
	define("DB_SERVER", "localhost");
	// this user only has privileges to select data from users database
	define("DB_USERNAME", "login_user");
	define("DB_PASSWORD", "usertoselectdata");
	define("DB_DATABASE", "users");

	$db_conn = mysqli_connect(DB_SERVER, DB_USERNAME, DB_PASSWORD, DB_DATABASE);

	include("../funcs.php");

	# if session doesn't exists redirects to login, else if session exists, stays in home
	if (empty($_SESSION)) {
		header("Location: /login?redi=no_auth");
	} else {
		//var_dump($_SESSION);
		$curr_username = $_SESSION["username"];
		$url = $curr_username . ".mercury.cells.es";
	}

	# check if user is premium to change displayed info
	$check_premium = mysqli_query($db_conn, "SELECT is_premium FROM user WHERE username = '$curr_username'");
	$check_premium_data = mysqli_fetch_array($check_premium);
	$premium = $check_premium_data["is_premium"];

	# show home size
	$home_size_num = shell_exec("du -sh /home/$curr_username | awk '{print $1}'");
	if (is_null($home_size_num)) {
		# if create-user script didn't created the user yet, displays a loading message
		$home_path = "Loading home path ...";
		$home_size = "Loading folder size ...";
	} else {
		$home_path = "/home/$curr_username";
		if ($premium) {
			$home_size = $home_size_num . " - Maximum: Unlimited";
		} else {
			$home_size = $home_size_num . " - Maximum: 10 GB";
		}
	}

	# check if sub domain is created
	function get_HTTP_code_subdomain($url) {
		return shell_exec("curl -I http://$url -s | grep HTTP | awk '{print $2}'");
	}
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
			<h3><?php echo $url; ?></h3>
			<p>
				<?php
					# if sub domain is not created, display loading message, else display link to go there
					if (get_HTTP_code_subdomain($url) != 200) {
						echo "Creating subdomain ...";
					} else {
						echo "Access your subdomain <a href=http://$url target='_blank'>here</a>.";
					}
				?>
			</p>
			<!-- table to diaplay user info -->
			<table class="table-home">
				<th colspan="2">
					User directory
				</th>
				<tr>
					<td>
						Path
					</td>
					<td>
						<?php echo $home_path; ?>
					</td>
				</tr>
				<tr>
					<td>
						Current space used
					</td>
					<td>
						<?php echo $home_size; ?>
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
						<?php echo $curr_username; ?>
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
								echo "SSH access is not permitted<br>with free account";
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
						<!--links to access mail and phpmyadmin -->
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
