<?php
	require 'config.php';
	include 'db_connect.php';
	
	echo $con->query("UPDATE entries SET trash = 0 WHERE id = " . $_POST['id']) ? 1 : 'Database error occurred';
?>