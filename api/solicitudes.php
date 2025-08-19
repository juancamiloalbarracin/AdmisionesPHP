<?php
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

require_once __DIR__ . '/../config/bootstrap.php';

use UDC\SistemaAdmisiones\Controllers\SolicitudController;
use UDC\SistemaAdmisiones\Middleware\AuthMiddleware;

try {
    // Inicializar buffer de salida
    ob_start();
    
    // Configurar headers para respuesta JSON
    header('Content-Type: application/json; charset=utf-8');
    
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
    
    // Obtener la ruta solicitada
    $requestUri = $_SERVER['REQUEST_URI'];
    $basePath = '/api/solicitudes';
    $route = str_replace($basePath, '', parse_url($requestUri, PHP_URL_PATH));
    
    // Crear instancia del controlador
    $controller = new SolicitudController();
    
    switch ($_SERVER['REQUEST_METHOD']) {
        case 'GET':
            if ($route === '/get' || $route === '') {
                $response = $controller->get($userData);
            } else {
                $response = [
                    'success' => false,
                    'error' => 'ROUTE_NOT_FOUND',
                    'message' => 'Ruta no encontrada',
                    'code' => 404
                ];
            }
            break;
            
        case 'POST':
            if ($route === '/save' || $route === '') {
                $input = json_decode(file_get_contents('php://input'), true);
                if (json_last_error() !== JSON_ERROR_NONE) {
                    $response = [
                        'success' => false,
                        'error' => 'INVALID_JSON',
                        'message' => 'JSON inválido en el cuerpo de la petición',
                        'code' => 400
                    ];
                } else {
                    $response = $controller->save((int)$userId, $input);
                }
            } else {
                $response = [
                    'success' => false,
                    'error' => 'ROUTE_NOT_FOUND',
                    'message' => 'Ruta no encontrada',
                    'code' => 404
                ];
            }
            break;
            
        default:
            $response = [
                'success' => false,
                'error' => 'METHOD_NOT_ALLOWED',
                'message' => 'Método HTTP no permitido',
                'code' => 405
            ];
            break;
    }
    
    // Establecer código de respuesta HTTP
    if (isset($response['code'])) {
        http_response_code($response['code']);
    } elseif (!$response['success']) {
        http_response_code(400);
    } else {
        http_response_code(200);
    }
    
    // Limpiar cualquier output previo y enviar respuesta JSON
    ob_end_clean();
    echo json_encode($response, JSON_UNESCAPED_UNICODE);

} catch (Exception $e) {
    // Limpiar cualquier output previo
    ob_end_clean();
    
    // Respuesta de error interno
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'error' => 'INTERNAL_SERVER_ERROR',
        'message' => 'Error interno del servidor',
        'code' => 500
    ], JSON_UNESCAPED_UNICODE);
}
?>
