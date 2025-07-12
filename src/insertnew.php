<?php
	date_default_timezone_set('UTC');
	include 'functions.php';
	require 'config.php';
	include 'db_connect.php';
	
	// Validation du timestamp
	$now = filter_var($_POST['now'], FILTER_VALIDATE_FLOAT);
	if ($now === false || $now <= 0) {
		die('Invalid timestamp');
	}
	$seconds = (int)$now;
	
	$created_date = date("Y-m-d H:i:s", $seconds);
	$updated_date = date("Y-m-d H:i:s", $seconds);
	
	// Requête préparée pour éviter l'injection SQL
	$stmt = $con->prepare("INSERT INTO entries (heading, entry, created, updated) VALUES (?, ?, ?, ?)");
	if (!$stmt) {
		die('Prepare failed');
	}
	
	$heading = 'Untitled note';
	$entry = '';
	
	$stmt->bind_param("ssss", $heading, $entry, $created_date, $updated_date);
	
	if($stmt->execute()) {
		die('1');
	} else {
		error_log("Database error in insertnew.php: " . $stmt->error);
		die('Database error occurred');
	}
	
	$stmt->close();
?>
