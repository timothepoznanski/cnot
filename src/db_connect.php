<?php

// Create connection
$conn = new mysqli(MYSQL_HOST, MYSQL_USER, MYSQL_PASSWORD); 
// Check connection
if ($conn->connect_error) {
	die("Connection failed: " . $conn->connect_error);
} 

// Create database
$sql = "CREATE DATABASE IF NOT EXISTS ".MYSQL_DATABASE; 
if ($conn->query($sql) === TRUE) {
} else {
	echo "Error creating database: " . $conn->error; 
}
$conn->close();
$con = new mysqli(MYSQL_HOST, MYSQL_USER, MYSQL_PASSWORD, MYSQL_DATABASE); 
if (mysqli_connect_errno()){
	echo 'Could not connect: ' . mysqli_connect_error();
}
$con->query("SET NAMES 'utf8';");
// If you are connecting via TCP/IP rather than a UNIX socket remember to add the port number as a parameter.
$query='CREATE TABLE IF NOT EXISTS entries (id int(11) UNSIGNED AUTO_INCREMENT PRIMARY KEY,trash int(11) DEFAULT 0, heading text,entry mediumtext,created TIMESTAMP DEFAULT CURRENT_TIMESTAMP,updated TIMESTAMP, tags text)';
$con->query($query);
$query='SELECT * FROM entries';
$res = $con->query($query);

?>
