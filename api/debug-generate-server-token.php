<?php
// Dev-only helper: generar un token usando la configuración del servidor
if (php_sapi_name() === 'cli') {
    echo "Run via HTTP" . PHP_EOL;
    exit;
}

require_once __DIR__ . '/../config/bootstrap.php';
use UDC\SistemaAdmisiones\Utils\JwtHelper;

// Sólo en entorno de desarrollo
if (($_ENV['APP_ENV'] ?? 'development') !== 'development') {
    http_response_code(403);
    header('Content-Type: application/json');
    echo json_encode(['error' => 'FORBIDDEN']);
    exit();
}

$testUser = [
    'id' => 6,
    'email' => 'cnavarroi@unicartagena.edu.co',
    'nombres' => 'Cesar Luis',
    'apellidos' => 'Navarro Ibanez',
    'user_type' => 'student'
];

try {
    $token = JwtHelper::generateToken($testUser);
    header('Content-Type: application/json');
    echo json_encode(['token' => $token]);
} catch (Exception $e) {
    http_response_code(500);
    header('Content-Type: application/json');
    echo json_encode(['error' => 'TOKEN_GENERATION_FAILED', 'message' => $e->getMessage()]);
}
