<?php
	# delete session and redirect lo log in
	session_start();

	# show php errors
	error_reporting(E_ALL);
	ini_set('display_errors', TRUE);
	ini_set('display_startup_errors', TRUE);
	
	session_destroy();
	header("Location: /login?redi=logout");
?>
