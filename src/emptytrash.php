<?php
	date_default_timezone_set('UTC');
	include 'functions.php';
    require 'config.php';
	$pass=$_POST['pass'];
	if($pass!=APP_PASSWORD)
	{
		die('Password Incorrect');
	}
	include 'db_connect.php';
    
    $query1 = 'SELECT id FROM entries WHERE trash = 1';
    $res = $con->query($query1);
    while($row = mysqli_fetch_array($res, MYSQLI_ASSOC))
    {
        $file_path = "entries/".$row["id"].".html";
        if(file_exists($file_path))
        {
            unlink($file_path);
        }
        else
        {
            echo 'file not found';
        }
    }
    
	$query2 = "DELETE from entries WHERE trash = 1";
	if($con->query($query2)) echo 1;
	else echo mysqli_error($con);
?>
