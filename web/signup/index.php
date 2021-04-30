<?php
	session_start();

	# show php errors
	error_reporting(E_ALL);
	ini_set('display_errors', TRUE);
	ini_set('display_startup_errors', TRUE);

	## DATABASE ##
	define("DB_SERVER", "localhost");
	// this user only has privileges to insert and select data from users database
	define("DB_USERNAME", "signup_user"); 
	define("DB_PASSWORD", "usertoinsertdata");
	define("DB_DATABASE", "users");

	$db_conn = mysqli_connect(DB_SERVER, DB_USERNAME, DB_PASSWORD, DB_DATABASE);

	## INCLUDES ##
	include("../funcs.php");
?>
<!DOCTYPE html>
<html>
<head>
	<title>Sign Up | MERCURY HOSTING</title>
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
		<h3>Create account</h3>
		<div class="login">
			<form action="." method="POST">
				<input type="text" name="username" placeholder="Username" id="username" required value="<?php if (isset($_POST['username'])) {echo $_POST['username'];} ?>"><br>
				<input type="email" name="email" placeholder="Email" id="email" required value="<?php if (isset($_POST['email'])) {echo $_POST['email'];} ?>"><br>
				<input type="password" name="password1" placeholder="Password" id="password1" required><br>
				<input type="password" name="password2" placeholder="Confirm password" id="password2" required><br>
				<input type="submit" name="submit" value="Sign Up"><br>
				<?php
					if (isset($_POST["email"]) AND !empty($_POST["email"])) {
						if (validate_signup_info()) {
							$_SESSION["username"] = $_POST["username"];
						}
					}
					$_POST = array();
				?>
				<p class="already_acc">
					Already have an account?<br>Click <a href="/login">here</a> to log in.
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
