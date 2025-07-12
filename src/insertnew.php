<?php
	date_default_timezone_set('UTC');
	include 'functions.php';
	require 'config.php';
	include 'db_connect.php';
	$now = $_POST['now'];
    $seconds = $now;

	$query="INSERT into entries (heading,entry,created,updated) values ('Untitled note','','".date("Y-m-d H:i:s", $seconds)."','".date("Y-m-d H:i:s", $seconds)."')";
	if($con->query($query)) die('1');
	else die(mysqli_error($con));
?>
