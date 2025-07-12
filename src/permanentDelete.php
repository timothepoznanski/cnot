<?php
	date_default_timezone_set('UTC');
	include 'functions.php';
	require 'config.php';
	include 'db_connect.php';
	
	$id = $_POST['id'];
	
	$filename = "entries/" . $id . ".html";
	if (file_exists($filename)) {
		unlink($filename);
	}
	
	$query = "DELETE FROM entries WHERE id = $id";
	
	if($con->query($query)) {
		echo 1;
	} else {
		echo 'Database error occurred';
	}
?>
