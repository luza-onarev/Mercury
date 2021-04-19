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
			<form action="." method="POST">
				<input type="text" name="username" placeholder="Username" id="username" required value="<?php if (isset($_POST['username'])) {echo $_POST['username'];} ?>"><br>
				<input type="password" name="password" placeholder="Password" id="password" required><br>
				<input type="submit" name="submit" value="Log In">
				<?php
					if (isset($_POST["username"]) AND !empty($_POST["username"])) {
						if (validate_login_info()) {
							$_SESSION["username"] = $_POST["username"];
							header("Location: /home");
						}
					}

					if (isset($_GET["redi"]) AND !empty($_GET["redi"])) {
						switch ($_GET["redi"]) {
							case 'no_auth':
								echo '<div class="error-signup">';
								echo "You must log in first!<br>";
								echo "</div>";
								break;

							case 'logout':
								echo '<div class="succ-signup">';
								echo "Successfully logged out!<br>";
								echo "</div>";
								break;

							case 'pass_change':
								echo '<div class="succ-signup">';
								echo "Log in again with new password!<br>";
								echo "</div>";
								break;

							case 'acc_del':
								echo '<div class="succ-signup">';
								echo "Account successfully deleted!<br>";
								echo "</div>";
								break;
						}
					}

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
