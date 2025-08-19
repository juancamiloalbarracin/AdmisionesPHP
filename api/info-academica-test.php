<?php
// Test simple para info-academica
// Limpiar cualquier output previo ANTES de los headers
if (ob_get_level()) ob_end_clean();
ob_start();

// Configurar headers CORS
header("Access-Control-Allow-Origin: http://localhost:3000");
header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With");
header("Access-Control-Allow-Credentials: true");

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

// Configurar headers para respuesta JSON
header('Content-Type: application/json; charset=utf-8');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $input = file_get_contents('php://input');
    $data = json_decode($input, true);
    
    // Respuesta simple sin controlador
    $response = [
        'success' => true,
        'message' => 'Test simple funcionando',
        'received_data' => $data,
        'method' => 'POST'
    ];
    
    ob_end_clean();
    echo json_encode($response, JSON_UNESCAPED_UNICODE);
} else {
    ob_end_clean();
    echo json_encode(['success' => false, 'message' => 'Solo POST permitido'], JSON_UNESCAPED_UNICODE);
}
?>
