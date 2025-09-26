<?php
// config.php - Database connection
$servername = "localhost";
$username = "root"; // Ganti dengan username DB Anda
$password = ""; // Ganti dengan password DB Anda
$dbname = "essay_app";

// Create connection
$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
?>