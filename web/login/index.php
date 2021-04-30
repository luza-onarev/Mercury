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

	# if a session exists redirects to home, else stays in login
	if (isset($_SESSION) and !empty($_SESSION)) {
		header("Location: /home");
	}
?>
<!DOCTYPE html>
<html>
<head>
	<title>Login | MERCURY HOSTING</title>
	<?php
		include("../head.html");
	?>
</head>
<body>
	<header>
		<?php
			include("../header.html");
		?>
	</header>
	<section>
		<h3>Login to you account</h3>
		<div class="login">
			<!-- login form -->
			<form action="." method="POST">
				<input type="text" name="username" placeholder="Username" id="username" required value="<?php if (isset($_POST['username'])) {echo $_POST['username'];} ?>"><br>
				<input type="password" name="password" placeholder="Password" id="password" required><br>
				<input type="submit" name="submit" value="Log In">
				<?php
					# if post variable exists and it's not empty, runs validate_login_info function to check if credentials are valid and logs in
					if (isset($_POST["username"]) AND !empty($_POST["username"])) {
						if (validate_login_info()) {
							$_SESSION["username"] = $_POST["username"];
							header("Location: /home");
						}
					}

					# if get variable exists and it's not empty, display different boxes with a message with the reason of the login redirection
					if (isset($_GET["redi"]) AND !empty($_GET["redi"])) {
						switch ($_GET["redi"]) {
							# user tries to go to home before logging in
							case 'no_auth':
								echo '<div class="error-signup">';
								echo "You must log in first!<br>";
								echo "</div>";
								break;

							# user logs out
							case 'logout':
								echo '<div class="succ-signup">';
								echo "Successfully logged out!<br>";
								echo "</div>";
								break;

							# user changes password
							case 'pass_change':
								echo '<div class="succ-signup">';
								echo "Log in again with new password!<br>";
								echo "</div>";
								break;

							# user deletes account
							case 'acc_del':
								echo '<div class="succ-signup">';
								echo "Account successfully deleted!<br>";
								echo "</div>";
								break;
						}
					}

					# clear variables
					$_POST = array();
					$_GET = array();
				?>
				<p class="already_acc">
					Don't have an account?<br>Click <a href="/signup">here</a> to create one.
				</p>
			</form>
		</div>
	</section>
	<footer>
		<?php
			include("../footer.php");
		?>
	</footer>
</body>
</html>
