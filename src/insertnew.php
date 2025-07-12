<?php
	date_default_timezone_set('UTC');
	require 'config.php';
	include 'db_connect.php';
	
	$now = $_POST['now'];
	$created_date = date("Y-m-d H:i:s", (int)$now);
	
	$query = "INSERT INTO entries (heading, entry, created, updated) VALUES ('Untitled note', '', '$created_date', '$created_date')";
	
	echo $con->query($query) ? '1' : 'Database error occurred';
?>
