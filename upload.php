<?php
session_start(); // MUST be at the very top

// Simulate logged-in user (adjust as needed)
$_SESSION['user'] = [
    'id' => 5,
    'username' => 'test_creator',
    'role' => 'creator'
];

// Include DB and helpers
require_once __DIR__ . '/db.php';
require_once __DIR__ . '/helpers.php';

require_login(); // make sure user is logged in

$user = $_SESSION['user'];

// Get POST data
$title = trim($_POST['title'] ?? '');
$description = trim($_POST['description'] ?? '');
$age = isset($_POST['age_restriction']) && $_POST['age_restriction'] == '1' ? 1 : 0;

// Validate
if ($title === '' || !isset($_FILES['video'])) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Missing title or video']);
    exit;
}

// Allowed video types and max size
$allowed = ['mp4','mov','webm','mkv'];
$maxSize = 200 * 1024 * 1024; // 200MB

// Upload folder
$uploadDir = __DIR__ . '/uploads/';
if (!is_dir($uploadDir)) {
    mkdir($uploadDir, 0777, true);
}

// File info
$orig = $_FILES['video']['name'];
$ext = strtolower(pathinfo($orig, PATHINFO_EXTENSION));
$size = (int)$_FILES['video']['size'];

// Validate type & size
if (!in_array($ext, $allowed)) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Unsupported file type']);
    exit;
}
if ($size > $maxSize) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'File too large']);
    exit;
}

// Generate filename like old videos
$fname = time() . '_' . preg_replace('/[^A-Za-z0-9_\-\.]/', '_', $orig); // replace spaces/special chars
$target = $uploadDir . $fname;

// Move uploaded file
if (!move_uploaded_file($_FILES['video']['tmp_name'], $target)) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Failed to move uploaded file. Check folder permissions: ' . $uploadDir
    ]);
    exit;
}

// Store URL exactly like old videos
$videoUrl = '/uploads/' . $fname;

// Insert into DB
$stmt = $mysqli->prepare('INSERT INTO videos (title, description, age_restriction, video_url, user_id) VALUES (?,?,?,?,?)');
$stmt->bind_param('ssisi', $title, $description, $age, $videoUrl, $user['id']);
$stmt->execute();

// Return success
echo json_encode([
    'success' => true,
    'message' => 'Video uploaded',
    'video_url' => $videoUrl
]);
