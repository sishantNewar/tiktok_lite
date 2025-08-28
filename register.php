<?php
// Start session for messages
session_start();

// Show all errors for debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "<h3>🔍 Debugging Registration</h3>";

// Include database connection
require "db.php";

// Check if form was submitted
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    echo "<p>✅ Form was submitted via POST</p>";
    
    // Get form data
    $username = $_POST['username'] ?? '';
    $email = $_POST['email'] ?? '';
    $password = $_POST['password'] ?? '';
    $confirm_password = $_POST['confirm_password'] ?? '';
    
    echo "<p>Username: $username</p>";
    echo "<p>Email: $email</p>";
    
    // Basic validation
    if (empty($username) || empty($email) || empty($password)) {
        die("❌ Please fill all fields");
    }
    
    if ($password !== $confirm_password) {
        die("❌ Passwords don't match");
    }
    
    // Hash password
    $hashed_password = password_hash($password, PASSWORD_DEFAULT);
    
    // Check if user exists
    $check_sql = "SELECT id FROM users WHERE username = ? OR email = ?";
    $check_stmt = $mysqli->prepare($check_sql);
    $check_stmt->bind_param("ss", $username, $email);
    $check_stmt->execute();
    $check_stmt->store_result();
    
    if ($check_stmt->num_rows > 0) {
        die("❌ Username or email already exists");
    }
    
    // Insert new user
    $sql = "INSERT INTO users (username, email, password, role) VALUES (?, ?, ?, 'creator')";
    $stmt = $mysqli->prepare($sql);
    
    if ($stmt) {
        $stmt->bind_param("sss", $username, $email, $hashed_password);
        
        if ($stmt->execute()) {
            echo "✅ Registration successful!";
            echo "<p><a href='index.php'>Go back to login</a></p>";
        } else {
            echo "❌ Error: " . $stmt->error;
        }
        
        $stmt->close();
    } else {
        echo "❌ Prepare failed: " . $mysqli->error;
    }
    
    $check_stmt->close();
    $mysqli->close();
    
} else {
    echo "❌ No form data received";
    echo "<p><a href='index.php'>Go back to form</a></p>";
}
?>