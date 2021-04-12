<?php
	session_start();
	//var_dump($_SESSION);
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
