<?php
// Minimal clean endpoint for solicitudes (requests related to user's application)
if (ob_get_level()) ob_end_clean();
ob_start();

// Dynamic CORS
$origin = $_SERVER['HTTP_ORIGIN'] ?? '';
$allowedOrigins = explode(',', $_ENV['CORS_ALLOWED_ORIGINS'] ?? 'http://localhost:3000');
if ($origin && in_array($origin, $allowedOrigins)) {
    header("Access-Control-Allow-Origin: $origin");
}
header('Access-Control-Allow-Credentials: true');
header('Access-Control-Allow-Headers: Content-Type, Authorization, X-User-Id');
header('Access-Control-Allow-Methods: GET,POST,OPTIONS');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') { http_response_code(204); exit(); }

require_once __DIR__ . '/../config/bootstrap.php';

use UDC\SistemaAdmisiones\Controllers\SolicitudController;
use UDC\SistemaAdmisiones\Middleware\AuthMiddleware;

set_error_handler(function($s,$m,$f,$l){ throw new \ErrorException($m,0,$s,$f,$l);});
set_exception_handler(function($e){ if (ob_get_level()) ob_end_clean(); http_response_code(500); header('Content-Type: application/json'); echo json_encode(['success'=>false,'error'=>'UNCAUGHT_EXCEPTION','message'=>$e->getMessage()], JSON_UNESCAPED_UNICODE); exit(); });

$controller = new SolicitudController();
$path = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);

// GET user's solicitud
if (strpos($path, '/api/solicitudes-clean/get') === 0 && $_SERVER['REQUEST_METHOD'] === 'GET') {
    $user = AuthMiddleware::getCurrentUser();
    if (!$user) {
        $devId = isset($_GET['dev_user_id']) ? (int)$_GET['dev_user_id'] : (isset($_COOKIE['user_id']) ? (int)$_COOKIE['user_id'] : 1);
        $user = ['user_id'=>$devId,'id'=>$devId,'email'=>'dev@local'];
    }
    $res = $controller->get($user);
    http_response_code($res['code'] ?? ($res['success']?200:400)); header('Content-Type: application/json'); echo json_encode($res, JSON_UNESCAPED_UNICODE); exit();
}

// POST save/update solicitud (accepts full solicitud body)
if (strpos($path, '/api/solicitudes-clean/save') === 0 && $_SERVER['REQUEST_METHOD'] === 'POST') {
    $raw = file_get_contents('php://input');
    $input = json_decode($raw, true);
    if (json_last_error() !== JSON_ERROR_NONE) { http_response_code(400); header('Content-Type: application/json'); echo json_encode(['success'=>false,'error'=>'INVALID_JSON','message'=>json_last_error_msg()], JSON_UNESCAPED_UNICODE); exit(); }

    $user = AuthMiddleware::getCurrentUser();
    if (!$user) {
        $devId = isset($_GET['dev_user_id']) ? (int)$_GET['dev_user_id'] : (isset($_COOKIE['user_id']) ? (int)$_COOKIE['user_id'] : 1);
        $user = ['user_id'=>$devId,'id'=>$devId,'email'=>'dev@local'];
    }

    // Controller expects (int $userId, array $requestData, ?int $solicitudId = null)
    $userId = (int)($user['id'] ?? $user['user_id'] ?? 0);
    $res = $controller->save($userId, $input);
    http_response_code($res['code'] ?? ($res['success']?200:400)); header('Content-Type: application/json'); echo json_encode($res, JSON_UNESCAPED_UNICODE); exit();
}

// POST submit
if (strpos($path, '/api/solicitudes-clean/submit') === 0 && $_SERVER['REQUEST_METHOD'] === 'POST') {
    $user = AuthMiddleware::getCurrentUser();
    if (!$user) { $devId = isset($_GET['dev_user_id']) ? (int)$_GET['dev_user_id'] : (isset($_COOKIE['user_id']) ? (int)$_COOKIE['user_id'] : 1); $user = ['user_id'=>$devId,'id'=>$devId,'email'=>'dev@local']; }
    $res = $controller->submit($user);
    http_response_code($res['code'] ?? ($res['success']?200:400)); header('Content-Type: application/json'); echo json_encode($res, JSON_UNESCAPED_UNICODE); exit();
}

// GET catalogs
if (strpos($path, '/api/solicitudes-clean/catalogs') === 0 && $_SERVER['REQUEST_METHOD'] === 'GET') {
    $res = $controller->getCatalogs();
    http_response_code($res['code'] ?? ($res['success']?200:400)); header('Content-Type: application/json'); echo json_encode($res, JSON_UNESCAPED_UNICODE); exit();
}

http_response_code(404); header('Content-Type: application/json'); echo json_encode(['success'=>false,'error'=>'NOT_FOUND'], JSON_UNESCAPED_UNICODE); exit();
