<?php
	session_start();
	//var_dump($_SESSION);

	# show php errors
	error_reporting(E_ALL);
	ini_set('display_errors', TRUE);
	ini_set('display_startup_errors', TRUE);
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
			if (!empty($_SESSION["username"])) {
				include("../header_home.html");
			} else {
				include("../header.html");
			}
		?>
	</header>
	<section>
	</section>
	<footer>
		<?php
			include("../footer.php");
		?>
	</footer>
</body>
</html>
