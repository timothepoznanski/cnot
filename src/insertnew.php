<?php
	date_default_timezone_set('UTC');
	include 'functions.php';
	require 'config.php';
	include 'db_connect.php';
	
	$now = $_POST['now'];
	$seconds = (int)$now;
	
	$created_date = date("Y-m-d H:i:s", $seconds);
	$updated_date = date("Y-m-d H:i:s", $seconds);
	
	$heading = 'Untitled note';
	$entry = '';
	
	$query = "INSERT INTO entries (heading, entry, created, updated) VALUES ('$heading', '$entry', '$created_date', '$updated_date')";
	
	if($con->query($query)) {
		die('1');
	} else {
		die('Database error occurred');
	}
?>
