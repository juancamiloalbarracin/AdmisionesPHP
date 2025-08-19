<?php
// Habilitar buffer de salida para capturar BOM/espacios accidentales de includes
ob_start();
/**
 * API ENDPOINT: USUARIOS
 * ======================
 * Este archivo maneja todas las rutas de usuarios
 * Compatible con el frontend React - Migración de UserApiServlet.java
 */

// Headers CORS PRIMERO - Antes de cualquier output
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, PUT, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With');
header('Access-Control-Allow-Credentials: true');
header('Content-Type: application/json; charset=utf-8');

// Manejar preflight OPTIONS ANTES de incluir archivos
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

// Configuración inicial después de CORS
require_once __DIR__ . '/../config/bootstrap.php';

use UDC\SistemaAdmisiones\Controllers\UserController;
use UDC\SistemaAdmisiones\Middleware\AuthMiddleware;

try {
    // Obtener método HTTP y URI
    $method = $_SERVER['REQUEST_METHOD'];
    $uri = $_SERVER['REQUEST_URI'];
    $path = parse_url($uri, PHP_URL_PATH);

    // Crear instancia del controlador
    $userController = new UserController();

    // Routing basado en el path y método
    switch ($method) {
        case 'POST':
            // Obtener datos JSON del cuerpo de la solicitud
            $input = file_get_contents('php://input');
            $requestData = json_decode($input, true);
            if ($requestData === null) {
                // Log de depuración si falla el parseo JSON
                $log = __DIR__ . '/../logs/users_register_debug.log';
                @file_put_contents($log, "[" . date('Y-m-d H:i:s') . "] JSON decode error: " . json_last_error_msg() . "\nRaw: " . $input . "\n\n", FILE_APPEND);
                $requestData = [];
            }

            // Determinar endpoint específico
            if (substr($path, -9) === '/register') {
                // Registro de usuario - No requiere autenticación
                $response = $userController->register($requestData);
                
            } elseif (substr($path, -9) === '/users.php' && isset($requestData['password']) && isset($requestData['nombres'])) {
                // Registro directo cuando se accede a users.php con datos de registro
                $response = $userController->register($requestData);
                
            } elseif (substr($path, -16) === '/change-password') {
                // Cambio de contraseña - Requiere autenticación
                $user = AuthMiddleware::processAuth($uri);
                if ($user) {
                    $response = $userController->changePassword($requestData);
                } else {
                    // Error ya enviado por el middleware
                    exit;
                }
                
            } else {
                $response = [
                    'success' => false,
                    'error' => 'ENDPOINT_NOT_FOUND',
                    'message' => 'Endpoint POST de usuario no encontrado',
                    'code' => 404
                ];
            }
            break;

        case 'GET':
            // Endpoints GET - Requieren autenticación
            if (substr($path, -8) === '/profile') {
                // Obtener perfil - Requiere autenticación
                $user = AuthMiddleware::processAuth($uri);
                if ($user) {
                    $response = $userController->getProfile();
                } else {
                    // Error ya enviado por el middleware
                    exit;
                }
                
            } else {
                $response = [
                    'success' => false,
                    'error' => 'ENDPOINT_NOT_FOUND',
                    'message' => 'Endpoint GET de usuario no encontrado',
                    'code' => 404
                ];
            }
            break;

        case 'PUT':
            // Obtener datos JSON del cuerpo de la solicitud
            $input = file_get_contents('php://input');
            $requestData = json_decode($input, true) ?: [];

            // Endpoints PUT - Requieren autenticación
            if (substr($path, -8) === '/profile') {
                // Actualizar perfil - Requiere autenticación
                $user = AuthMiddleware::processAuth($uri);
                if ($user) {
                    $response = $userController->updateProfile($requestData);
                } else {
                    // Error ya enviado por el middleware
                    exit;
                }
                
            } else {
                $response = [
                    'success' => false,
                    'error' => 'ENDPOINT_NOT_FOUND',
                    'message' => 'Endpoint PUT de usuario no encontrado',
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
    }

    // Limpiar cualquier salida previa (BOM, espacios) antes de enviar JSON
    if (ob_get_length()) {
        ob_clean();
    }

    // Establecer código de respuesta HTTP
    if (isset($response['code'])) {
        http_response_code($response['code']);
    } elseif (!$response['success']) {
        http_response_code(400);
    } else {
        http_response_code(200);
    }

    // Enviar respuesta JSON
    echo json_encode($response, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);

} catch (Exception $e) {
    // Log del error
    error_log("[USER API ERROR] " . $e->getMessage() . " in " . $e->getFile() . " on line " . $e->getLine());

    // Respuesta de error interno
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'error' => 'INTERNAL_SERVER_ERROR',
        'message' => 'Error interno del servidor',
        'code' => 500,
        'timestamp' => (new DateTime())->format('Y-m-d H:i:s')
    ], JSON_UNESCAPED_UNICODE);
}
