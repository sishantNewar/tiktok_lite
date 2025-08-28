<?php
// backend/helpers.php
function json_input(): array {
$raw = file_get_contents('php://input');
$data = json_decode($raw, true);
return is_array($data) ? $data : [];
}


function require_login() {
if (!isset($_SESSION['user'])) {
http_response_code(401);
echo json_encode(['success' => false, 'message' => 'Unauthorized']);
exit;
}
}


function require_role(string $role) {
require_login();
if (($_SESSION['user']['role'] ?? '') !== $role) {
http_response_code(403);
echo json_encode(['success' => false, 'message' => 'Forbidden']);
exit;
}
}