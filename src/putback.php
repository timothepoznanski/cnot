<?php
	date_default_timezone_set('UTC');
	include 'functions.php';
	require 'config.php';
	include 'db_connect.php';
	
	// Validation et sécurisation de l'ID
	$id = filter_var($_POST['id'], FILTER_VALIDATE_INT);
	if ($id === false || $id <= 0) {
		die('Invalid ID');
	}
	
	// Requête préparée pour éviter l'injection SQL
	$stmt = $con->prepare("UPDATE entries SET trash = 0 WHERE id = ?");
	if (!$stmt) {
		die('Prepare failed');
	}
	
	$stmt->bind_param("i", $id);
	
	if($stmt->execute()) {
		echo 1;
	} else {
		error_log("Database error in putback.php: " . $stmt->error);
		echo 'Database error occurred';
	}
	
	$stmt->close();
?>