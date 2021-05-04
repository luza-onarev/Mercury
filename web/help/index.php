<?php
	session_start();

	# show php errors
	error_reporting(E_ALL);
	ini_set('display_errors', TRUE);
	ini_set('display_startup_errors', TRUE);
?>
<!DOCTYPE html>
<html>
<head>
	<title>Help | MERCURY HOSTING</title>
	<?php
		include("../head.html");
	?>
</head>
<body>
	<header>
		<?php
			if (!empty($_SESSION["username"])) {
				include("../header_home.html");
			} else {
				include("../header.html");
			}
		?>
	</header>
	<section>
		<h1>Mercury's help page</h1>
		<h2 id="contact_sys-admin">Contact a sysadmin</h2>
		To contact a System Administrator send a mail to <a href="mailto:ismael@mercury.cells.es">ismael</a>
	</section>
	<footer>
		<?php
			include("../footer.php");
		?>
	</footer>
</body>
</html>
