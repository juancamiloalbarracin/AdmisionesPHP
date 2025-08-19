<?php
// Limpiar cualquier output previo ANTES de los headers
if (ob_get_level()) ob_end_clean();
ob_start();

// Configurar headers CORS dinámicamente (coincide con bootstrap.php)
$origin = $_SERVER['HTTP_ORIGIN'] ?? '';
$allowedOrigins = explode(',', $_ENV['CORS_ALLOWED_ORIGINS'] ?? 'http://localhost:3000');
if ($origin && in_array($origin, $allowedOrigins)) {
    header("Access-Control-Allow-Origin: $origin");
}
header('Access-Control-Allow-Methods: ' . ($_ENV['CORS_ALLOWED_METHODS'] ?? 'GET,POST,PUT,DELETE,OPTIONS'));
header('Access-Control-Allow-Headers: ' . ($_ENV['CORS_ALLOWED_HEADERS'] ?? 'Content-Type,Authorization'));
header('Access-Control-Allow-Credentials: true');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

require_once __DIR__ . '/../config/bootstrap.php';

use UDC\SistemaAdmisiones\Controllers\InfoAcademicaController;
use UDC\SistemaAdmisiones\Middleware\AuthMiddleware;

// Convert PHP warnings/notices to exceptions so they get handled and return JSON
set_error_handler(function($severity, $message, $file, $line) {
    throw new \ErrorException($message, 0, $severity, $file, $line);
});

// Global exception handler to ensure JSON responses
set_exception_handler(function($e) {
    // Registrar excepción para diagnóstico
    @file_put_contents(LOGS_DIR . '/exceptions.log', date('c') . " UNCAUGHT_EXCEPTION: " . $e->getMessage() . " in " . $e->getFile() . ":" . $e->getLine() . "\n" . $e->getTraceAsString() . "\n---\n", FILE_APPEND | LOCK_EX);
    if (ob_get_level()) ob_end_clean();
    http_response_code(500);
    header('Content-Type: application/json; charset=utf-8');
    $origin = $_SERVER['HTTP_ORIGIN'] ?? '';
    $allowedOrigins = explode(',', $_ENV['CORS_ALLOWED_ORIGINS'] ?? 'http://localhost:3000');
    if ($origin && in_array($origin, $allowedOrigins)) {
        header("Access-Control-Allow-Origin: $origin");
    }
    header('Access-Control-Allow-Credentials: true');
    echo json_encode([
        'success' => false,
        'error' => 'UNCAUGHT_EXCEPTION',
        'message' => $e->getMessage(),
        'file' => $e->getFile(),
        'line' => $e->getLine()
    ], JSON_UNESCAPED_UNICODE);
    exit();
});

try {
    // Asegurar buffer de salida y configurar headers para respuesta JSON
    if (ob_get_level()) ob_end_clean();
    ob_start();
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
    $basePath = '/api/info-academica';
    $route = str_replace($basePath, '', parse_url($requestUri, PHP_URL_PATH));
    
    // Crear instancia del controlador
    $controller = new InfoAcademicaController();
    
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
                $rawInput = file_get_contents('php://input');
                // Logging raw input
                @file_put_contents(LOGS_DIR . '/info-academica-raw.log', date('c') . " RAW_INPUT: " . $rawInput . "\n", FILE_APPEND | LOCK_EX);
                $input = json_decode($rawInput, true);
                // Log JSON error if any
                if (json_last_error() !== JSON_ERROR_NONE) {
                    @file_put_contents(LOGS_DIR . '/info-academica-raw.log', date('c') . " JSON_ERROR: " . json_last_error_msg() . "\n", FILE_APPEND | LOCK_EX);
                    $response = [
                        'success' => false,
                        'error' => 'INVALID_JSON',
                        'message' => 'JSON inválido en el cuerpo de la petición: ' . json_last_error_msg(),
                        'code' => 400
                    ];
                } else {
                    // Log decoded input structure
                    @file_put_contents(LOGS_DIR . '/info-academica-raw.log', date('c') . " DECODED: " . json_encode($input) . "\n", FILE_APPEND | LOCK_EX);
                    $response = $controller->save($input, $userData);
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
    
    // Inspeccionar si hay salida accidental (HTML, warnings, etc.) en el buffer
    $bufferContent = '';
    if (ob_get_level()) {
        $bufferContent = ob_get_contents();
    }

    if (trim($bufferContent) !== '') {
        // Registrar salida no-JSON para análisis (solo un resumen)
        $snippet = substr($bufferContent, 0, 4000);
        @file_put_contents(LOGS_DIR . '/non_json_output.log', date('c') . " BUFFER_SNIPPET: " . $snippet . "\nHEADERS: " . json_encode(getallheaders() ?: []) . "\nRESPONSE_INTENDED: " . json_encode($response) . "\n---\n", FILE_APPEND | LOCK_EX);

        // Limpiar buffer y devolver un error JSON controlado
        if (ob_get_level()) ob_end_clean();
        http_response_code(500);
        header('Content-Type: application/json; charset=utf-8');
        // Enviar una respuesta segura que indique que hubo salida inesperada
        echo json_encode([
            'success' => false,
            'error' => 'NON_JSON_OUTPUT_DETECTED',
            'message' => 'Salida inesperada en el servidor. Revise logs/non_json_output.log para detalles.'
        ], JSON_UNESCAPED_UNICODE);
        exit();
    }

    // Limpiar cualquier output previo y enviar respuesta JSON
    if (ob_get_level()) ob_end_clean();
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

// Registrar shutdown handler para convertir errores fatales en JSON
register_shutdown_function(function() {
    $error = error_get_last();
    if ($error !== null) {
        // Limpiar cualquier buffer y devolver JSON de error
        if (ob_get_level()) ob_end_clean();
        http_response_code(500);
        header('Content-Type: application/json; charset=utf-8');
        $origin = $_SERVER['HTTP_ORIGIN'] ?? '';
        $allowedOrigins = explode(',', $_ENV['CORS_ALLOWED_ORIGINS'] ?? 'http://localhost:3000');
        if ($origin && in_array($origin, $allowedOrigins)) {
            header("Access-Control-Allow-Origin: $origin");
        }
        header('Access-Control-Allow-Credentials: true');
        echo json_encode([
            'success' => false,
            'error' => 'FATAL_ERROR',
            'message' => $error['message'],
            'file' => $error['file'],
            'line' => $error['line']
        ], JSON_UNESCAPED_UNICODE);
        exit();
    }
});

