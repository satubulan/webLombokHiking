<?php
$servername = "localhost";
$username = "root";
$password = "";
$database = "lohi";

// Koneksi database
$conn = new mysqli($servername, $username, $password, $database);
if ($conn->connect_error) {
    die("Koneksi gagal: " . $conn->connect_error);
}
mysqli_set_charset($conn, "utf8");
?>
