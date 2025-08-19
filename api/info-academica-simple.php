<?php
// Headers CORS simples
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET, POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type");
header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    exit(0);
}

// Endpoint simplificado para prueba directa
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Leer datos del POST
    $input = file_get_contents('php://input');
    $data = json_decode($input, true);
    
    // Respuesta simple de prueba
    echo json_encode([
        'status' => 'success',
        'message' => 'POST recibido correctamente',
        'received_data' => $data,
        'method' => $_SERVER['REQUEST_METHOD']
    ]);
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    echo json_encode([
        'status' => 'success',
        'message' => 'GET funcionando',
        'method' => $_SERVER['REQUEST_METHOD']
    ]);
    exit();
}

echo json_encode(['status' => 'error', 'message' => 'Método no permitido']);
exit();
?>
