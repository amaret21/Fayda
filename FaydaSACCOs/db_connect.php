<?php
// Database configuration
$servername = "localhost"; // or your server IP/name
$username = "faydaSACCO_user"; // your database username
$password = "secure_password_123"; // your database password
$dbname = "faydaSACCO"; // your database name

// Create connection
$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Set charset to utf8mb4 for proper character encoding
$conn->set_charset("utf8mb4");

// Function to safely close the connection when done
function closeDatabaseConnection($conn) {
    $conn->close();
}
?>