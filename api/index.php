<?php
// Habilitar buffer de salida global para evitar BOM/espacios antes del JSON
ob_start();
/**
 * API ROUTER - SISTEMA DE ADMISIONES UDC
 * ======================================
 * Archivo: api/index.php
 * Descripción: Router principal para manejar todas las rutas API
 */

// Headers CORS específicos para credenciales
$origin = isset($_SERVER['HTTP_ORIGIN']) ? $_SERVER['HTTP_ORIGIN'] : 'http://localhost:3000';
header('Access-Control-Allow-Origin: ' . $origin);
header('Access-Control-Allow-Credentials: true');
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With, Accept');

// Responder a preflight OPTIONS
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

// Obtener la ruta solicitada
$request_uri = $_SERVER['REQUEST_URI'];
$path = parse_url($request_uri, PHP_URL_PATH);

// Remover el prefijo /api de la ruta
$path = preg_replace('/^\/api/', '', $path);
$path = trim($path, '/');

// Incluir configuración
require_once __DIR__ . '/../config/bootstrap.php';

try {
    // Routear según la ruta solicitada
    switch (true) {
    // Autenticación
    case $path === 'login' || $path === 'auth' || preg_match('/^auth(\/.*)?$/', $path):
            require_once __DIR__ . '/auth.php';
            break;
        // Registro directo (compatibilidad): /api/register
        case $path === 'register':
            require_once __DIR__ . '/users.php';
            break;
            
    // Usuarios
    // Abarca /users, /users/{id}, y subrutas como /users/register, /users/profile, etc.
    case $path === 'users' || preg_match('/^users(\/.*)?$/', $path):
            require_once __DIR__ . '/users.php';
            break;
            
        // Información personal
        case $path === 'info-personal' || preg_match('/^info-personal(\/.*)?$/', $path):
            require_once __DIR__ . '/info-personal.php';
            break;
        // Dev login helper
        case $path === 'dev-login':
            require_once __DIR__ . '/dev-login.php';
            break;
            
        // Información académica (legacy)
        case $path === 'info-academica' || preg_match('/^info-academica(\/.*)?$/', $path):
            require_once __DIR__ . '/info-academica.php';
            break;
        // Información académica (clean isolated flow)
        case $path === 'info-academica-clean' || preg_match('/^info-academica-clean(\/.*)?$/', $path):
            require_once __DIR__ . '/info-academica-clean.php';
            break;
        // Información personal (clean isolated flow)
        case $path === 'info-personal-clean' || preg_match('/^info-personal-clean(\/.*)?$/', $path):
            require_once __DIR__ . '/info-personal-clean.php';
            break;
        // Solicitudes (clean)
        case $path === 'solicitudes-clean' || preg_match('/^solicitudes-clean(\/.*)?$/', $path):
            require_once __DIR__ . '/solicitudes-clean.php';
            break;
            
        // Solicitudes
        case $path === 'solicitudes' || preg_match('/^solicitudes(\/.*)?$/', $path):
            require_once __DIR__ . '/solicitudes.php';
            break;
            
        // Ruta no encontrada
        default:
            http_response_code(404);
        if (ob_get_length()) { ob_clean(); }
        echo json_encode([
                'success' => false,
                'message' => 'Endpoint no encontrado: ' . $path,
                'available_endpoints' => [
                    '/api/login',
                    '/api/register',
                    '/api/users',
                    '/api/info-personal',
                    '/api/info-personal-clean',
                    '/api/info-academica',
                    '/api/info-academica-clean',
                    '/api/solicitudes',
                    '/api/solicitudes-clean'
                ]
            ]);
            break;
    }
} catch (Exception $e) {
    http_response_code(500);
    if (ob_get_length()) { ob_clean(); }
    echo json_encode([
        'success' => false,
        'message' => 'Error interno del servidor',
        'error' => $e->getMessage()
    ]);
}
?>
