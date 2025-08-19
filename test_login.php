<?php
/**
 * PRUEBA DE LOGIN API
 * ===================
 * Archivo temporal para probar la funcionalidad del login
 */

// Headers para permitir CORS y JSON
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With');
header('Content-Type: application/json; charset=utf-8');

// Responder a preflight OPTIONS
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

echo json_encode([
    'success' => true,
    'message' => 'API endpoint de login accesible',
    'timestamp' => date('Y-m-d H:i:s'),
    'method' => $_SERVER['REQUEST_METHOD'],
    'path' => $_SERVER['REQUEST_URI'],
    'headers' => getallheaders() ?: [],
    'input' => file_get_contents('php://input')
], JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
?>
