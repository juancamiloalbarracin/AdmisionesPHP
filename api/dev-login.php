<?php
// Dev helper: login simple que setea cookie user_id para pruebas locales
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') { http_response_code(204); exit(); }
require_once __DIR__ . '/../config/bootstrap.php';

$userId = $_GET['user_id'] ?? null;
if (!$userId) {
    http_response_code(400);
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'message' => 'user_id required']);
    exit();
}

// Set cookie on path / for local dev; avoid explicit domain to keep it simple
setcookie('user_id', (int)$userId, time()+3600, '/');
header('Content-Type: application/json');
echo json_encode(['success' => true, 'message' => 'cookie set', 'user_id' => (int)$userId]);
