<?php
require_once __DIR__ . '/../db.php';
require_once __DIR__ . '/../helpers.php';
require_login(); // creator or admin


$user = $_SESSION['user'];
$title = trim($_POST['title'] ?? '');
$description = trim($_POST['description'] ?? '');
$age = isset($_POST['age_restriction']) && $_POST['age_restriction'] == '1' ? 1 : 0;


if ($title === '' || !isset($_FILES['video'])) {
http_response_code(400);
echo json_encode(['success' => false, 'message' => 'Missing title or video']);
exit;
}


$allowed = ['mp4','mov','webm','mkv'];
$maxSize = 200 * 1024 * 1024; // 200MB
$uploadDir = __DIR__ . '/../../uploads/';
if (!is_dir($uploadDir)) { mkdir($uploadDir, 0777, true); }


$orig = $_FILES['video']['name'];
$ext = strtolower(pathinfo($orig, PATHINFO_EXTENSION));
$size = (int)$_FILES['video']['size'];


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


$fname = time() . '_' . bin2hex(random_bytes(4)) . '.' . $ext;
$target = $uploadDir . $fname;
if (!move_uploaded_file($_FILES['video']['tmp_name'], $target)) {
http_response_code(500);
echo json_encode(['success' => false, 'message' => 'Failed to move upload']);
exit;
}


// URL path from web root
$videoUrl = '/uploads/' . $fname;


$stmt = $mysqli->prepare('INSERT INTO videos (title, description, video_url, age_restriction, user_id) VALUES (?,?,?,?,?)');
$stmt->bind_param('sssii', $title, $description, $videoUrl, $age, $user['id']);
$stmt->execute();


echo json_encode(['success' => true, 'message' => 'Video uploaded']);