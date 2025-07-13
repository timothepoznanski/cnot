<?php
	date_default_timezone_set('UTC');
	require 'config.php';
	include 'db_connect.php';
	
	$now = $_POST['now'];
	$created_date = date("Y-m-d H:i:s", (int)$now);
	
// Insert the new note
$query = "INSERT INTO entries (heading, entry, created, updated) VALUES ('Untitled note', '', '$created_date', '$created_date')";
if ($con->query($query)) {
	$id = $con->insert_id;
	// Return both the heading and the id (for future-proofing)
	echo json_encode([
		'status' => 1,
		'heading' => 'Untitled note',
		'id' => $id
	]);
} else {
	echo json_encode([
		'status' => 0,
		'error' => 'Database error occurred'
	]);
}
?>
