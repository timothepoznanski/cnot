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
	
	// Sécurisation du chemin de fichier
	$filename = "entries/" . $id . ".html";
	// Vérification que le fichier est dans le bon répertoire
	$realpath = realpath($filename);
	$basepath = realpath("entries/");
	
	if ($realpath && $basepath && strpos($realpath, $basepath) === 0) {
		unlink($filename);
	}
	
	// Requête préparée pour éviter l'injection SQL
	$stmt = $con->prepare("DELETE FROM entries WHERE id = ?");
	if (!$stmt) {
		die('Prepare failed');
	}
	
	$stmt->bind_param("i", $id);
	
	if($stmt->execute()) {
		echo 1;
	} else {
		error_log("Database error in permanentDelete.php: " . $stmt->error);
		echo 'Database error occurred';
	}
	
	$stmt->close();
?>
