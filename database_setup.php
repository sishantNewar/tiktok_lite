<?php
// database_setup.php
$host = 'localhost';
$username = 'root'; // Change if needed
$password = ''; // Change if needed
$database = 'tiktok_clone';

// Create connection
$mysqli = new mysqli($host, $username, $password);

// Check connection
if ($mysqli->connect_error) {
    die("Connection failed: " . $mysqli->connect_error);
}

// Create database
$sql = "CREATE DATABASE IF NOT EXISTS $database";
if ($mysqli->query($sql) {
    echo "Database created successfully or already exists<br>";
} else {
    die("Error creating database: " . $mysqli->error);
}

// Select database
$mysqli->select_db($database);

// Create users table
$sql = "CREATE TABLE IF NOT EXISTS users (
    id INT(11) AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) NOT NULL UNIQUE,
    email VARCHAR(100) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
)";

if ($mysqli->query($sql)) {
    echo "Users table created successfully or already exists<br>";
} else {
    die("Error creating users table: " . $mysqli->error);
}

// Create videos table
$sql = "CREATE TABLE IF NOT EXISTS videos (
    id INT(11) AUTO_INCREMENT PRIMARY KEY,
    user_id INT(11) NOT NULL,
    title VARCHAR(255) NOT NULL,
    description TEXT,
    video_path VARCHAR(255) NOT NULL,
    likes INT(11) DEFAULT 0,
    comments INT(11) DEFAULT 0,
    shares INT(11) DEFAULT 0,
    age_restricted BOOLEAN DEFAULT FALSE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
)";

if ($mysqli->query($sql)) {
    echo "Videos table created successfully or already exists<br>";
} else {
    die("Error creating videos table: " . $mysqli->error);
}

echo "Database setup completed successfully!";
$mysqli->close();
?>