<?php
// db.php
$host = 'localhost';
$username = 'root'; // Change if needed
$password = ''; // Change if needed
$database = 'tiktok_clone';

// Create connection
$mysqli = new mysqli($host, $username, $password, $database);

// Check connection
if ($mysqli->connect_error) {
    // Set a session error message
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }
    $_SESSION['db_error'] = "Database connection failed: " . $mysqli->connect_error;
    
    // For debugging purposes, you might want to see the error directly
    // die("Connection failed: " . $mysqli->connect_error);
}
?>