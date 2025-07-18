<?php
	date_default_timezone_set('UTC');
	require 'config.php';
	include 'db_connect.php';
    
    // Delete all files from trash entries
    $res = $con->query('SELECT id FROM entries WHERE trash = 1');
    while($row = mysqli_fetch_array($res, MYSQLI_ASSOC)) {
        $file_path = "entries/".$row["id"].".html";
        if(file_exists($file_path)) unlink($file_path);
    }
    
    // Delete all trash entries
	echo $con->query("DELETE FROM entries WHERE trash = 1") ? 1 : mysqli_error($con);
?>
