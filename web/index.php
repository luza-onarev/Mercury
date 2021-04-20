<?php
	error_reporting(E_ALL);
	ini_set('display_errors', TRUE);
	ini_set('display_startup_errors', TRUE);
?>
<!DOCTYPE html>
<html>
<head>
	<title>MERCURY HOSTING | mercury.com</title>
	<?php
		include("head.html");
	?>
</head>
<body>
	<header>
		<?php
			include("header.html");
		?>
	</header>
	<section>
		<h1>Welcome to Mercury Hosting</h1>
		<h2>Free hosting for everybody!</h2>
		<table class="info">
			<tr class="info-padd">
				<td class="info-padd">
					<h3>Free Web hosting</h3>
					Free sub domain to host your web pages. Up to 10 GB to host all your files.
				</td>
				<td class="info-padd">
					<h3>Custom cPanel</h3>
					Simple, easy and ready-to-use cPanel to manage all your files and remote connections from your home with only what you need.
				</td>
				<td class="info-padd">
					<h3>No need to register a domain</h3>
					In less than one minute you will have a sub domain ready to start uploading files. Click <a href="signup">here</a> to start.
				</td>
			</tr>
		</table>
		<table class="info">
			<tr class="info-padd">
				<td style="background-color: white;">
					<img src="/img/coin.gif" class="gif" align="top">
				</td>
				<td class="info-padd-h">
					<h3>Free account</h3>
					Includes...
					<ul>
						<li>10 GB storage</li>
						<li>1 sub domain</li>
						<li>1 FTP account</li>
						<li>1 database</li>
					</ul>	
				</td>
			</tr>
			<tr class="info-padd">
				<td class="info-padd-h">
					<h3>Different uses for your sub domain</h3>
					With a Mercury sub domain you can upload any web application and make it public to the Internet, use it as a backup server in case you don't want to lose files or use it as a file sharing server to anybody on the Internet.
				</td>
				<td class="info-padd" style="background-color: white" >
					<img src="/img/wordpress.gif" class="gif" align="top">
				</td>
			</tr>
			<tr class="info-padd">
				<td style="background-color: white;">
					<img src="/img/team.png" class="gif" align="top">
				</td>
				<td class="info-padd-h">
					<h3>Support the project</h3>
					Support the project with a minimum donation of 2€/month and 5€/month after the first year for unlimited resources, suggest changes and improvements, direct help from our System Administrators and exclusive access to the private forum to create topics and get in touch with other users.<br>
				</td>
			</tr>
		</table>
	</section>
	<footer>
		<?php
			include("footer.php");
		?>
	</footer>
</body>
</html>
