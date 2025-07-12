<?php

// ini_set('display_errors',1);
// ini_set('display_startup_errors',1);
// error_reporting(-1);
                       
	date_default_timezone_set('UTC');
	include 'functions.php';
	require 'config.php';
        
	include 'db_connect.php';
	// Validation et sécurisation des données d'entrée
	$id = filter_var($_POST['id'], FILTER_VALIDATE_INT);
	if ($id === false || $id <= 0) {
		die('Invalid ID');
	}
	
	$heading = trim($_POST['heading'] ?? '');
	$entry = $_POST['entry'] ?? ''; // Save the HTML content (including images) in an HTML file.
	$entrycontent = $_POST['entrycontent'] ?? ''; // Save the text content (without images) in the database.
	
	$now = filter_var($_POST['now'], FILTER_VALIDATE_FLOAT);
	if ($now === false || $now <= 0) {
		die('Invalid timestamp');
	}
	$seconds = (int)$now;
	
    $tags = str_replace(' ', ',', $_POST['tags'] ?? '');	
	
	// Requête préparée pour récupérer la note existante
	$stmt = $con->prepare("SELECT * FROM entries WHERE id = ?");
	if (!$stmt) {
		die('Prepare failed');
	}
	
	$stmt->bind_param("i", $id);
	$stmt->execute();
	$res = $stmt->get_result();
	$row = mysqli_fetch_array($res, MYSQLI_ASSOC);
	$stmt->close();
	
	if (!$row) {
		die('Note not found');
	}
	
	// Sécurisation du chemin de fichier
    $filename = "entries/" . $id . ".html";
	// Vérification que le fichier est dans le bon répertoire
	$basepath = realpath("entries/");
	if (!$basepath) {
		die('Invalid entries directory');
	}
	    
	$handle = fopen($filename, 'w+'); 

	// ATTENTION !!! $str is always empty so this part never works !!!!" 	
	$filesize = filesize($filename);
	if ($filesize > 0) {
	    $str = fread($handle, $filesize); // Read the file in binary mode. Read the existing content from the HTML file saved on disk.
	} else {
	    $str = '';
	}
    
	// If there have been no changes to the note, exit the script
	if(htmlspecialchars($heading)==$row['heading'] && $entry==$str && htmlspecialchars($tags)==$row['tags'])
	{
		die('No changes to the note.');  // Stop the execution of the script and display the message provided.
	}
            
    if ($entry != '') // If the note is empty and we only changed the title, then do not try to write an empty entry in the html file
 	{
		if (!fwrite($handle, $entry)){//;  // Writes a file in binary mode.
			die("Error writing html file");
		}  
	}  
  
	fclose($handle);
    
	$updated_date = date("Y-m-d H:i:s", $seconds);
	
	// Requête préparée pour la mise à jour
	$stmt = $con->prepare("UPDATE entries SET heading = ?, entry = ?, created = created, updated = ?, tags = ? WHERE id = ?");
	if (!$stmt) {
		die('Prepare failed');
	}
	
	$stmt->bind_param("ssssi", 
		htmlspecialchars($heading),
		htmlspecialchars($entrycontent),
		$updated_date,
		htmlspecialchars($tags),
		$id
	);
    
	if($stmt->execute()) {
		echo die(formatDateTime(strtotime($updated_date))); // If writing the query in base is ok then we exit
	} else {
		error_log("Database error in updatenote.php: " . $stmt->error);
		echo 'Database error occurred';
	}
	
	$stmt->close();
