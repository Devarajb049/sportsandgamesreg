<?php
$host = "localhost";
$user = "root";  
$pass = "";      
$dbname = "sports_registration";

$conn = new mysqli($host, $user, $pass, $dbname);
if ($conn->connect_error) {
    die("Database connection failed: " . $conn->connect_error);
}
?>
