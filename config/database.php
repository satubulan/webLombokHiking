
<?php
// Database configuration
$servername = "localhost";
$username = "root";
$password = "";
$database = "lombok_hiking";

// Create connection
$conn = new mysqli($servername, $username, $password, $database);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Set character set
mysqli_set_charset($conn, "utf8");
?>
