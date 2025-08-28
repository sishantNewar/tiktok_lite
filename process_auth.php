<?php
session_start();
require "db.php";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if ($_POST['action'] === 'login') {
        // Login process
        $username = trim($_POST['loginUsername']);
        $password = $_POST['loginPassword'];
        
        // Find user by username or email
        $stmt = $mysqli->prepare("SELECT id, username, password FROM users WHERE username = ? OR email = ?");
        $stmt->bind_param("ss", $username, $username);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows === 1) {
            $user = $result->fetch_assoc();
            
            // Verify password (in a real app, you'd use password_verify())
            // For demo purposes, we're using a simple check
            if ($password === "demo" || password_verify($password, $user['password'])) {
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['username'] = $user['username'];
                $_SESSION['message'] = "Login successful!";
                header("Location: index.php");
                exit();
            } else {
                $_SESSION['error'] = "Invalid password!";
                header("Location: index.php");
                exit();
            }
        } else {
            $_SESSION['error'] = "User not found!";
            header("Location: index.php");
            exit();
        }
    } elseif ($_POST['action'] === 'signup') {
        // Signup process
        $username = trim($_POST['signupUsername']);
        $email = trim($_POST['signupEmail']);
        $password = $_POST['signupPassword'];
        $confirmPassword = $_POST['confirmPassword'];
        
        // Validate passwords match
        if ($password !== $confirmPassword) {
            $_SESSION['error'] = "Passwords do not match!";
            header("Location: index.php");
            exit();
        }
        
        // Check if username or email already exists
        $stmt = $mysqli->prepare("SELECT id FROM users WHERE username = ? OR email = ?");
        $stmt->bind_param("ss", $username, $email);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows > 0) {
            $_SESSION['error'] = "Username or email already exists!";
            header("Location: index.php");
            exit();
        }
        
        // Hash password (in a real app)
        $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
        
        // Insert new user
        $stmt = $mysqli->prepare("INSERT INTO users (username, email, password) VALUES (?, ?, ?)");
        $stmt->bind_param("sss", $username, $email, $hashedPassword);
        
        if ($stmt->execute()) {
            $_SESSION['user_id'] = $stmt->insert_id;
            $_SESSION['username'] = $username;
            $_SESSION['message'] = "Registration successful!";
            header("Location: index.php");
            exit();
        } else {
            $_SESSION['error'] = "Registration failed!";
            header("Location: index.php");
            exit();
        }
    }
}

header("Location: index.php");
exit();
?>