<?php
/**
 * BOOTSTRAP - CONFIGURACIÓN INICIAL DEL SISTEMA
 * ==============================================
 * Este archivo se encarga de cargar todas las configuraciones
 * necesarias para que el sistema funcione correctamente:
 * - Autoloader de Composer
 * - Variables de entorno
 * - Configuración de zona horaria
 * - Configuración de errores según el entorno
 */

// Verificar versión mínima de PHP requerida
if (version_compare(PHP_VERSION, '8.0.0', '<')) {
    die('Este sistema requiere PHP 8.0 o superior. Versión actual: ' . PHP_VERSION);
}

// Definir constantes del sistema (protegidas contra doble carga)
if (!defined('APP_ROOT')) {
    define('APP_ROOT', dirname(__DIR__));
}
if (!defined('CONFIG_DIR')) {
    define('CONFIG_DIR', APP_ROOT . '/config');
}
if (!defined('SRC_DIR')) {
    define('SRC_DIR', APP_ROOT . '/src');
}
if (!defined('LOGS_DIR')) {
    define('LOGS_DIR', APP_ROOT . '/logs');
}

// Crear directorio de logs si no existe
if (!is_dir(LOGS_DIR)) {
    mkdir(LOGS_DIR, 0755, true);
}

// Cargar el autoloader de Composer
$autoloadPath = APP_ROOT . '/vendor/autoload.php';
if (!file_exists($autoloadPath)) {
    die('Por favor ejecute: composer install');
}
require_once $autoloadPath;

// Cargar variables de entorno
$dotenv = Dotenv\Dotenv::createImmutable(APP_ROOT);
$dotenv->load();

// Configurar zona horaria para Colombia
date_default_timezone_set('America/Bogota');

// Configurar manejo de errores según el entorno
$appEnv = $_ENV['APP_ENV'] ?? 'production';
$appDebug = filter_var($_ENV['APP_DEBUG'] ?? false, FILTER_VALIDATE_BOOLEAN);

if ($appEnv === 'development' && $appDebug) {
    // En desarrollo: mostrar todos los errores
    error_reporting(E_ALL);
    ini_set('display_errors', '1');
    ini_set('display_startup_errors', '1');
} else {
    // En producción: no mostrar errores al usuario
    error_reporting(E_ALL);
    ini_set('display_errors', '0');
    ini_set('display_startup_errors', '0');
    ini_set('log_errors', '1');
    ini_set('error_log', LOGS_DIR . '/php_errors.log');
}

// Configurar headers de seguridad por defecto
if (!headers_sent()) {
    // Prevenir ataques XSS
    header('X-Content-Type-Options: nosniff');
    header('X-Frame-Options: DENY');
    header('X-XSS-Protection: 1; mode=block');
    
    // Content Security Policy: permitir llamadas entre 3000 (front) y 8000 (API) en desarrollo
    $csp = [
        "default-src 'self'",
        "connect-src 'self' http://localhost:8000 http://localhost:3000",
        "img-src 'self' data:",
        "style-src 'self' 'unsafe-inline'",
        "script-src 'self' 'unsafe-inline'"
    ];
    header('Content-Security-Policy: ' . implode('; ', $csp));
    
    // Configurar CORS si estamos en una petición API
    if (isset($_SERVER['REQUEST_URI']) && strpos($_SERVER['REQUEST_URI'], '/api/') === 0) {
        $allowedOrigins = explode(',', $_ENV['CORS_ALLOWED_ORIGINS'] ?? 'http://localhost:3000');
        $origin = $_SERVER['HTTP_ORIGIN'] ?? '';
        
        if (in_array($origin, $allowedOrigins)) {
            header("Access-Control-Allow-Origin: $origin");
        }
        
        header('Access-Control-Allow-Methods: ' . ($_ENV['CORS_ALLOWED_METHODS'] ?? 'GET,POST,PUT,DELETE,OPTIONS'));
        header('Access-Control-Allow-Headers: ' . ($_ENV['CORS_ALLOWED_HEADERS'] ?? 'Content-Type,Authorization'));
        header('Access-Control-Allow-Credentials: true');
        header('Access-Control-Max-Age: 86400');
        
        // Responder a peticiones OPTIONS (preflight)
        if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
            http_response_code(204);
            exit();
        }
    }
}

// Sistema iniciado correctamente
