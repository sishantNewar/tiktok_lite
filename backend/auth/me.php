<?php
require_once __DIR__ . '/../db.php';
if (isset($_SESSION['user'])) {
echo json_encode(['authenticated' => true, 'user' => $_SESSION['user']]);
} else {
echo json_encode(['authenticated' => false]);
}