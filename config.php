<?php
$servername = "sql209.infinityfree.com";
$username = "if0_36560377";
$password = "ffCQQYATaOv";
$dbname = "if0_36560377_mydatabase";

// Create connection
$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
?>
