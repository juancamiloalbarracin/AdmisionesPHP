<?php
// Test con lógica exacta de InfoPersonalController
if (ob_get_level()) ob_end_clean();
ob_start();

header("Access-Control-Allow-Origin: http://localhost:3000");
header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With");
header("Access-Control-Allow-Credentials: true");

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

require_once __DIR__ . '/../config/bootstrap.php';

use UDC\SistemaAdmisiones\Models\InfoAcademica;

try {
    ob_start();
    header('Content-Type: application/json; charset=utf-8');
    
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
    
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $input = json_decode(file_get_contents('php://input'), true);
        if (json_last_error() !== JSON_ERROR_NONE) {
            $response = [
                'success' => false,
                'error' => 'INVALID_JSON',
                'message' => 'JSON inválido: ' . json_last_error_msg()
            ];
        } else {
            // Usar el modelo directamente como InfoPersonal
            $model = new InfoAcademica((int)$userData['id']);
            
            // Probar si el modelo se puede usar para validar
            $validation = $model->validate($input); // Probar si existe este método
            
            $response = [
                'success' => true,
                'message' => 'Test con modelo directo',
                'validation' => $validation,
                'received_data' => $input
            ];
        }
    } else {
        $response = ['success' => false, 'message' => 'Solo POST permitido'];
    }
    
    ob_end_clean();
    echo json_encode($response, JSON_UNESCAPED_UNICODE);
    
} catch (Exception $e) {
    ob_end_clean();
    echo json_encode([
        'success' => false,
        'error' => 'ERROR',
        'message' => $e->getMessage()
    ], JSON_UNESCAPED_UNICODE);
}
?>
