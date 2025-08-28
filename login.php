<?php
require 'db.php'; // Make sure this connects to tiktok_clone

session_start();

$username = $_POST['username'] ?? '';
$password = $_POST['password'] ?? '';

if (!$username || !$password) {
    echo "❌ Please enter username and password";
    exit;
}

// Prepare statement to avoid SQL injection
$stmt = $mysqli->prepare("SELECT id, password, role FROM users WHERE username = ?");
$stmt->bind_param("s", $username);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    echo "❌ User not found";
    exit;
}

$user = $result->fetch_assoc();

// Verify hashed password
if (password_verify($password, $user['password'])) {
    $_SESSION['user_id'] = $user['id'];
    $_SESSION['username'] = $username;
    $_SESSION['role'] = $user['role'];

    echo "✅ Login successful! Role: " . $user['role'];
} else {
    echo "❌ Wrong password";
}
?>
