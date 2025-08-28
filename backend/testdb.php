<?php
$mysqli = new mysqli('localhost','root','','tiktok_clone');

if ($mysqli->connect_errno) {
    die("❌ DB connection failed: " . $mysqli->connect_error);
}
echo "✅ DB connected successfully";
?>
