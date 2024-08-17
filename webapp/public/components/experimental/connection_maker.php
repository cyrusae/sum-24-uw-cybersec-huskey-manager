<?php

$hostname = 'backend-mysql-database';
$username = $_ENV["MYSQL_USER"];
$password = $_ENV["MYSQL_PASSWORD"];
$database = $_ENV["MYSQL_DATABASE"];

$conn = new mysqli($hostname, $username, $password, $database);

if ($conn->connect_error) {
 $errorMessage = "Connection failed: " . $conn->connect_error;    
 $logger->error($errorMessage); //Log failed connection
 die($errorMessage);
}

?>