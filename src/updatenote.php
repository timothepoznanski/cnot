<?php

// ini_set('display_errors',1);
// ini_set('display_startup_errors',1);
// error_reporting(-1);
                       
	date_default_timezone_set('UTC');
	include 'functions.php';
	require 'config.php';
        
	include 'db_connect.php';
	$id = $_POST['id'];
	$heading = $_POST['heading'];
	$entry = $_POST['entry']; // Save the HTML content (including images) in an HTML file.
	$entrycontent = $_POST['entrycontent']; // Save the text content (without images) in the database.
	$now = $_POST['now'];
	$seconds = $now;
    $tags = str_replace(' ', ',', $_POST['tags']);	
	
	$query="SELECT * from entries WHERE id=".$id;
	$res = $con->query($query);
	$row = mysqli_fetch_array($res,MYSQLI_ASSOC);
    
    $filename = "entries/".$id.".html";
	    
	$handle = fopen($filename, 'w+'); 

	// ATTENTION !!! $str is always empty so this part never works !!!!" 	
	$str = fread($handle, filesize($filename)); // Read the file in binary mode. Read the existing content from the HTML file saved on disk.
    
	// If there have been no changes to the note, exit the script
	if(htmlspecialchars($heading,ENT_QUOTES)==$row['heading'] && $entry==$str && htmlspecialchars($tags,ENT_QUOTES)==$row['tags'])
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
    
	$query="UPDATE entries SET heading = '".htmlspecialchars($heading,ENT_QUOTES)."', entry = '".htmlspecialchars($entrycontent,ENT_QUOTES)."', created = created, updated = '".date("Y-m-d H:i:s", $seconds)."', tags = '".htmlspecialchars($tags,ENT_QUOTES)."' WHERE id=".$id;
    
	if($con->query($query)) echo die(formatDateTime(strtotime(date("Y-m-d H:i:s", $seconds)))); // If writing the query in base is ok then we exit
	else echo 'Error mysql : '.mysqli_error($con); // Otherwise we display the SQL error
