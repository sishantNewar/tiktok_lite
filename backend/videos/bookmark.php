<?php
require_once __DIR__ . '/../db.php';
require_once __DIR__ . '/../helpers.php';
require_login();


$userId = $_SESSION['user']['id'];
$input = json_input();
$videoId = (int)($input['video_id'] ?? 0);


if ($videoId <= 0) { http_response_code(400); echo json_encode(['success' => false]); exit; }


$check = $mysqli->prepare('SELECT 1 FROM video_bookmarks WHERE user_id=? AND video_id=?');
$check->bind_param('ii', $userId, $videoId);
$check->execute();
$exists = $check->get_result()->fetch_row();


if ($exists) {
$del = $m