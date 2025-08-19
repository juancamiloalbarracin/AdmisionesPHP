<?php
// Headers CORS simples
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET, POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type");
header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    exit(0);
}

require_once __DIR__ . '/../config/bootstrap.php';

use UDC\SistemaAdmisiones\Controllers\InfoAcademicaController;

try {
    // Obtener datos del usuario desde la URL
    $userId = $_GET['user_id'] ?? null;
    $email = $_GET['email'] ?? null;
    $nombres = $_GET['nombres'] ?? null;
    $apellidos = $_GET['apellidos'] ?? null;
    
    $userData = [
        'id' => $userId,
        'email' => $email,
        'nombres' => $nombres,
        'apellidos' => $apellidos
    ];
    
    // Crear instancia del controlador
    $controller = new InfoAcademicaController();
    
    if ($_SERVER['REQUEST_METHOD'] === 'GET') {
        $response = $controller->get($userData);
        echo json_encode($response);
    } elseif ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $input = json_decode(file_get_contents('php://input'), true);
        if (json_last_error() !== JSON_ERROR_NONE) {
            echo json_encode([
                'success' => false,
                'error' => 'INVALID_JSON',
                'message' => 'JSON inválido: ' . json_last_error_msg()
            ]);
        } else {
            $response = $controller->save($input, $userData);
            echo json_encode($response);
        }
    } else {
        echo json_encode([
            'success' => false,
            'error' => 'METHOD_NOT_ALLOWED',
            'message' => 'Método HTTP no permitido'
        ]);
    }
    
} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'error' => 'INTERNAL_SERVER_ERROR',
        'message' => 'Error interno: ' . $e->getMessage()
    ]);
}
?>
