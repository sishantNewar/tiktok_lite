<?php
require_once __DIR__ . '/../db.php';
require_once __DIR__ . '/../helpers.php';
require_login();


$userId = $_SESSION['user']['id'];
$input = json_input();
$videoId = (int)($input['video_id'] ?? 0);


if ($videoId <= 0) { http_response_code(400); echo json_encode(['success' => false]); exit; }


// toggle like
$check = $mysqli->prepare('SELECT 1 FROM video_likes WHERE user_id=? AND video_id=?');
$check->bind_param('ii', $userId, $videoId);
$check->execute();
$liked = $check->get_result()->fetch_row();


if ($liked) {
// unlike
$del = $mysqli->prepare('DELETE FROM video_likes WHERE user_id=? AND video_id=?');
$del->bind_param('ii', $userId, $videoId);
$del->execute();
$mysqli->query("UPDATE videos SET likes = GREATEST(likes-1,0) WHERE id = $videoId");
$state = false;
} else {
// like
$ins = $mysqli->prepare('INSERT INTO video_likes (user_id, video_id) VALUES (?, ?)');
$ins->bind_param('ii', $userId, $videoId);
$ins->execute();
$mysqli->query("UPDATE videos SET likes = likes+1 WHERE id = $videoId");
$state = true;
}


$countRes = $mysqli->query("SELECT likes FROM videos WHERE id = $videoId");
$count = (int)$countRes->fetch_assoc()['likes'];


echo json_encode(['success' => true, 'liked' => $state, 'likes' => $count]);