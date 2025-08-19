<?php
// Habilitar buffer de salida para capturar BOM/espacios accidentales de includes
ob_start();
/**
 * API ENDPOINT: AUTENTICACIÓN
 * ===========================
 * Este archivo maneja todas las rutas de autenticación
 * Compatible con el frontend React - Migración de AuthApiServlet.java
 */

// Configuración inicial y headers CORS
require_once __DIR__ . '/../config/bootstrap.php';

use UDC\SistemaAdmisiones\Controllers\AuthController;
use UDC\SistemaAdmisiones\Middleware\AuthMiddleware;

// Headers CORS y de respuesta específicos para credenciales
$origin = isset($_SERVER['HTTP_ORIGIN']) ? $_SERVER['HTTP_ORIGIN'] : 'http://localhost:3000';
header('Access-Control-Allow-Origin: ' . $origin);
header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With, Accept');
header('Access-Control-Allow-Credentials: true');
header('Content-Type: application/json; charset=utf-8');

// Manejar preflight OPTIONS
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

try {
    // Obtener método HTTP y URI
    $method = $_SERVER['REQUEST_METHOD'];
    $uri = $_SERVER['REQUEST_URI'];
    $path = parse_url($uri, PHP_URL_PATH);

    // Crear instancia del controlador
    $authController = new AuthController();

    // Routing basado en el path y método
    switch ($method) {
        case 'POST':
            // Obtener datos JSON del cuerpo de la solicitud
            $input = file_get_contents('php://input');
            $requestData = json_decode($input, true);
            if ($requestData === null) {
                // Log de depuración cuando falla el parseo JSON
                $headers = function_exists('getallheaders') ? (getallheaders() ?: []) : [];
                $contentType = $headers['Content-Type'] ?? $headers['content-type'] ?? '';
                $logLine = '[' . date('Y-m-d H:i:s') . "] AUTH JSON decode error: " . json_last_error_msg() . "\n" .
                    'CT=' . $contentType . ' len=' . strlen((string)$input) . "\nRaw: " . substr((string)$input, 0, 500) . "\n\n";
                @file_put_contents(LOGS_DIR . '/auth_login_debug.log', $logLine, FILE_APPEND);
                $requestData = [];
            }

            // Fallback: aceptar application/x-www-form-urlencoded
            if (empty($requestData) && !empty($_POST)) {
                $requestData = [
                    'email' => $_POST['email'] ?? '',
                    'password' => $_POST['password'] ?? ''
                ];
            }

            // Determinar endpoint específico
            if ($path === '/api/auth/login' || substr($path, -6) === '/login' || substr($path, -5) === '/auth' || strpos($path, 'auth') !== false) {
                $response = $authController->login($requestData);
                
            } elseif (substr($path, -7) === '/logout') {
                $response = $authController->logout();
                
            } elseif (substr($path, -9) === '/validate') {
                $response = $authController->validate();
                
            } elseif (substr($path, -8) === '/refresh') {
                $response = $authController->refresh();
                
            } else {
                $response = [
                    'success' => false,
                    'error' => 'ENDPOINT_NOT_FOUND',
                    'message' => 'Endpoint de autenticación no encontrado',
                    'code' => 404
                ];
            }
            break;

        case 'GET':
            // Endpoints GET de autenticación
            if (substr($path, -6) === '/stats') {
                $response = $authController->getAuthStats();
                
            } elseif (substr($path, -9) === '/validate') {
                $response = $authController->validate();
                
            } else {
                $response = [
                    'success' => false,
                    'error' => 'METHOD_NOT_ALLOWED',
                    'message' => 'Método GET no permitido para este endpoint',
                    'code' => 405
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
    if (ob_get_length()) { ob_clean(); }
    echo json_encode($response, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);

} catch (Exception $e) {
    // Log del error
    error_log("[AUTH API ERROR] " . $e->getMessage() . " in " . $e->getFile() . " on line " . $e->getLine());

    // Respuesta de error interno
    http_response_code(500);
    if (ob_get_length()) { ob_clean(); }
    echo json_encode([
        'success' => false,
        'error' => 'INTERNAL_SERVER_ERROR',
        'message' => 'Error interno del servidor',
        'code' => 500,
        'timestamp' => (new DateTime())->format('Y-m-d H:i:s')
    ], JSON_UNESCAPED_UNICODE);
}
