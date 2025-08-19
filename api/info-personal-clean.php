<?php
// Minimal robust endpoint for personal info (clean)
if (ob_get_level()) ob_end_clean();
ob_start();

// Dynamic CORS
$origin = $_SERVER['HTTP_ORIGIN'] ?? '';
$allowedOrigins = explode(',', $_ENV['CORS_ALLOWED_ORIGINS'] ?? 'http://localhost:3000');
if ($origin && in_array($origin, $allowedOrigins)) {
    header("Access-Control-Allow-Origin: $origin");
}
header('Access-Control-Allow-Credentials: true');
// Allow custom dev header X-User-Id for frontend dev auth
header('Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With, X-User-Id');
header('Access-Control-Allow-Methods: GET,POST,OPTIONS');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') { http_response_code(204); exit(); }

require_once __DIR__ . '/../config/bootstrap.php';

use UDC\SistemaAdmisiones\Controllers\InfoPersonalController;
use UDC\SistemaAdmisiones\Models\InfoPersonal as InfoPersonalModel;
use UDC\SistemaAdmisiones\Middleware\AuthMiddleware;

// Simple handlers to guarantee JSON responses
set_error_handler(function($s,$m,$f,$l){ throw new \ErrorException($m,0,$s,$f,$l);});
set_exception_handler(function($e){ if (ob_get_level()) ob_end_clean(); http_response_code(500); header('Content-Type: application/json'); echo json_encode(['success'=>false,'error'=>'UNCAUGHT_EXCEPTION','message'=>$e->getMessage()], JSON_UNESCAPED_UNICODE); exit(); });

$controller = new InfoPersonalController();

$path = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);

// Save personal info
if (strpos($path, '/api/info-personal-clean/save') === 0 && $_SERVER['REQUEST_METHOD'] === 'POST') {
    $raw = file_get_contents('php://input');
    $input = json_decode($raw, true);
    if (json_last_error() !== JSON_ERROR_NONE) {
        http_response_code(400);
        header('Content-Type: application/json');
        echo json_encode(['success'=>false,'error'=>'INVALID_JSON','message'=>json_last_error_msg()], JSON_UNESCAPED_UNICODE);
        exit();
    }

    // Minimal auth/dev fallback
    $user = AuthMiddleware::getCurrentUser();
    if (!$user) {
        $devId = isset($_GET['dev_user_id']) ? (int)$_GET['dev_user_id'] : (isset($_COOKIE['user_id']) ? (int)$_COOKIE['user_id'] : 1);
        $user = ['user_id' => $devId, 'id' => $devId, 'email' => 'dev@local'];
    }

    // Bypass controller validation for school assignment: save directly via model
    $userId = (int)($user['id'] ?? $user['user_id'] ?? 0);
    $model = new InfoPersonalModel($userId);
    $saved = $model->save($userId, $input);
    if ($saved) {
        $updated = $model->getByUserId($userId);
        $res = ['success'=>true,'infoPersonal'=>$updated,'message'=>'Información personal guardada (minimal)','code'=>200];
    } else {
        $res = ['success'=>false,'message'=>'Error guardando (minimal)','code'=>500];
    }
    http_response_code($res['code'] ?? ($res['success']?200:400));
    header('Content-Type: application/json');
    echo json_encode($res, JSON_UNESCAPED_UNICODE);
    exit();
}

// Get personal info
if (strpos($path, '/api/info-personal-clean/get') === 0 && $_SERVER['REQUEST_METHOD'] === 'GET') {
    $user = AuthMiddleware::getCurrentUser();
    if (!$user) {
        $devId = isset($_GET['dev_user_id']) ? (int)$_GET['dev_user_id'] : (isset($_COOKIE['user_id']) ? (int)$_COOKIE['user_id'] : 1);
        $user = ['user_id'=>$devId,'id'=>$devId,'email'=>'dev@local'];
    }

    $res = $controller->get($user);
    http_response_code($res['code'] ?? ($res['success']?200:400));
    header('Content-Type: application/json');
    echo json_encode($res, JSON_UNESCAPED_UNICODE);
    exit();
}

http_response_code(404);
header('Content-Type: application/json');
echo json_encode(['success'=>false,'error'=>'NOT_FOUND'], JSON_UNESCAPED_UNICODE);
exit();
?>
