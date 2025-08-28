<?php
require_once __DIR__ . '/../db.php';


$sql = 'SELECT v.id, v.title, v.description, v.video_url, v.age_restriction, v.likes, v.bookmarks, v.created_at,
u.username
FROM videos v JOIN users u ON v.creator_id = u.id
WHERE v.approved = 1
ORDER BY v.id DESC';
$res = $mysqli->query($sql);
$rows = [];
while ($row = $res->fetch_assoc()) {
$row['id'] = (int)$row['id'];
$row['age_restriction'] = (int)$row['age_restriction'];
$row['likes'] = (int)$row['likes'];
$row['bookmarks'] = (int)$row['bookmarks'];
$rows[] = $row;
}
echo json_encode($rows);