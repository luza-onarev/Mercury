<?php
	session_start();

	## DATABASE ##
	define("DB_SERVER", "localhost");
	// this user only has privileges to select data from users database
	define("DB_USERNAME", "update_user");
	define("DB_PASSWORD", "usertoupdatedata");
	define("DB_DATABASE", "users");

	$db_conn = mysqli_connect(DB_SERVER, DB_USERNAME, DB_PASSWORD, DB_DATABASE);

	include("../funcs.php");

	if (empty($_SESSION)) {
		header("Location: /login?redi=no_auth");
	} else {
		$curr_username = $_SESSION["username"];
	}
?>
<!DOCTYPE html>
<html>
<head>
	<title>Settings | MERCURY HOSTING</title>
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
		<table>
			<tr>
				<td>
					
				</td>
			</tr>
		</table>
		<div class="login">
			<form action="." method="POST">
				<h3>Change Password</h3>
				<input type="password" name="curr_password" placeholder="Current password" id="curr_password" required><br>
				<input type="password" name="new_password" placeholder="New password" id="new_password" required><br>
				<input type="password" name="new_password2" placeholder="Confirm new password" id="new_password2" required><br>
				<input type="submit" name="change_password" id="change_password" value="Change password">

				<?php
				if (isset($_POST["change_password"])) {
					if (change_password($_POST["curr_password"], $_POST["new_password"], $_POST["new_password2"])) {
						session_destroy();
	    				header("Location: /login?redi=pass_change");
					}
				}
			?>
			</form>
		</div>
		<div class="login">
			<form action="." method="POST">
				<h3>Delete account</h3>
				Delete all your remote users, files and sub domain.<br>
				You can't undo this action.
				<input type="submit" name="delete_account" id="delete_account" value="Delete account">
			</form>	

			<?php
				if (isset($_POST["delete_account"])) {
				}
			?>
		</div>
	</section>
	<footer>
		<?php
			include("../footer.php");
		?>
	</footer>
</body>
</html>
