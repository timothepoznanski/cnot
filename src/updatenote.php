<?php

// ini_set('display_errors',1);
// ini_set('display_startup_errors',1);
// error_reporting(-1);
                       
	date_default_timezone_set('UTC');
	include 'functions.php';
	require 'config.php';
        
	include 'db_connect.php';
	
	if (!isset($_POST['id'])) {
		die("No ID provided");
	}
	
	$id = $_POST['id'];
	$heading = trim($_POST['heading'] ?? '');
	$entry = $_POST['entry'] ?? ''; // Save the HTML content (including images) in an HTML file.
	$entrycontent = $_POST['entrycontent'] ?? ''; // Save the text content (without images) in the database.
	
	$now = $_POST['now'];
	$seconds = (int)$now;
	
    $tags = str_replace(' ', ',', $_POST['tags'] ?? '');	
	
	$query = "SELECT * FROM entries WHERE id = $id";
	$res = $con->query($query);
	$row = mysqli_fetch_array($res, MYSQLI_ASSOC);
	
	if (!$row) {
		die('Note not found');
	}
	
    $filename = "entries/" . $id . ".html";
	
	// Read existing content first before opening in write mode
	$str = '';
	if (file_exists($filename)) {
		$str = file_get_contents($filename); // Read the existing content from the HTML file saved on disk.
	}
	
	$handle = fopen($filename, 'w+');
    
	// Temporary fix: always save (remove change detection for now)
	// if($heading==$row['heading'] && $entry==$str && $tags==$row['tags'])
	// {
	//	fclose($handle);
	//	die('No changes to the note.');
	// }
            
    if ($entry != '') // If the note is empty and we only changed the title, then do not try to write an empty entry in the html file
 	{
		if (!fwrite($handle, $entry)) { // Writes a file in binary mode.
			fclose($handle);
			die("Error writing html file");
		}  
	} else {
		// If entry is empty, still need to clear the file
		ftruncate($handle, 0);
	}
  
	fclose($handle);
    
	$updated_date = date("Y-m-d H:i:s", $seconds);
	
	$query = "UPDATE entries SET heading = '" . mysqli_real_escape_string($con, $heading) . "', entry = '" . mysqli_real_escape_string($con, $entrycontent) . "', created = created, updated = '$updated_date', tags = '" . mysqli_real_escape_string($con, $tags) . "' WHERE id = $id";
    
	if($con->query($query)) {
		die(formatDateTime(strtotime($updated_date))); // If writing the query in base is ok then we exit
	} else {
		die('Database error occurred');
	}
?>
