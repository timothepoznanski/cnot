<?php
	date_default_timezone_set('UTC');
	include 'functions.php';
	require 'config.php';
	$pass=$_POST['pass'];
	if($pass!=APP_PASSWORD)
	{
		die('Incorrect password');
	}
	include 'db_connect.php';
	$id = $_POST['id'];
	$query="UPDATE entries SET trash = 0 WHERE id=".$id;
	
	if($con->query($query)) echo 1;
	else echo mysqli_error($con);
?>