<?php
// endpoint minimal y robusto para información académica (clean)
if (ob_get_level()) ob_end_clean();
ob_start();

// CORS dinámico
$origin = $_SERVER['HTTP_ORIGIN'] ?? '';
$allowedOrigins = explode(',', $_ENV['CORS_ALLOWED_ORIGINS'] ?? 'http://localhost:3000');
if ($origin && in_array($origin, $allowedOrigins)) {
    header("Access-Control-Allow-Origin: $origin");
}
header('Access-Control-Allow-Credentials: true');
// Allow custom dev header X-User-Id for frontend dev auth
header('Access-Control-Allow-Headers: Content-Type, Authorization, X-User-Id');
header('Access-Control-Allow-Methods: GET,POST,OPTIONS');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') { http_response_code(204); exit(); }

require_once __DIR__ . '/../config/bootstrap.php';

use UDC\SistemaAdmisiones\Controllers\InfoAcademicaCleanController;
use UDC\SistemaAdmisiones\Models\InfoAcademicaClean as InfoAcademicaModel;
use UDC\SistemaAdmisiones\Middleware\AuthMiddleware;

// handlers simples
set_error_handler(function($s,$m,$f,$l){ throw new \ErrorException($m,0,$s,$f,$l);});
set_exception_handler(function($e){ if (ob_get_level()) ob_end_clean(); http_response_code(500); header('Content-Type: application/json'); echo json_encode(['success'=>false,'error'=>'UNCAUGHT_EXCEPTION','message'=>$e->getMessage()]); exit(); });

$controller = new InfoAcademicaCleanController();

$path = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
if (strpos($path, '/api/info-academica-clean/save') === 0 && $_SERVER['REQUEST_METHOD'] === 'POST') {
    $raw = file_get_contents('php://input');
    $input = json_decode($raw, true);
    if (json_last_error() !== JSON_ERROR_NONE) {
        http_response_code(400);
        header('Content-Type: application/json');
        echo json_encode(['success'=>false,'error'=>'INVALID_JSON','message'=>json_last_error_msg()]);
        exit();
    }
    // Minimal dev auth fallback
    $user = AuthMiddleware::getCurrentUser();
    if (!$user) {
        $devId = isset($_GET['dev_user_id']) ? (int)$_GET['dev_user_id'] : (isset($_COOKIE['user_id']) ? (int)$_COOKIE['user_id'] : 1);
        $user = ['user_id' => $devId, 'id' => $devId, 'email' => 'dev@local'];
    }

    // Minimal save: map incoming fields permissively and write directly via model
    $userId = (int)($user['id'] ?? $user['user_id'] ?? 0);
    $model = new InfoAcademicaModel();

    // Accept both camelCase and snake_case inputs; normalize keys
    $map = [
        'nombreInstitucion' => 'nombre_institucion', 'nombre_institucion' => 'nombre_institucion',
        'ciudadInstitucion' => 'ciudad_institucion', 'ciudad_institucion' => 'ciudad_institucion',
        'departamentoInstitucion' => 'departamento_institucion', 'departamento_institucion' => 'departamento_institucion',
        'tipoBachillerato' => 'tipo_bachillerato', 'tipo_bachillerato' => 'tipo_bachillerato',
        'jornada' => 'jornada', 'caracterInstitucion' => 'caracter_institucion', 'caracter_institucion' => 'caracter_institucion',
        'anoGraduacion' => 'ano_graduacion', 'ano_graduacion' => 'ano_graduacion',
        'promedioAcademico' => 'promedio_academico', 'promedio_academico' => 'promedio_academico',
        'puntajeIcfes' => 'puntaje_icfes', 'puntaje_icfes' => 'puntaje_icfes',
        'posicionCurso' => 'posicion_curso', 'posicion_curso' => 'posicion_curso',
        'totalEstudiantes' => 'total_estudiantes', 'total_estudiantes' => 'total_estudiantes',
        'observaciones' => 'observaciones'
    ];

    $normalized = [];
    foreach ($map as $src => $dst) {
        if (isset($input[$src]) && $input[$src] !== '') {
            $normalized[$dst] = $input[$src];
        }
    }

    $saved = $model->saveForUser($userId, $normalized);
    if ($saved) {
        $res = ['success'=>true,'message'=>'Información académica guardada (minimal)','data'=>$model->getByUserId($userId),'code'=>200];
    } else {
        $res = ['success'=>false,'message'=>'Error guardando (minimal)','code'=>500];
    }
    http_response_code($res['code'] ?? ($res['success']?200:400));
    header('Content-Type: application/json');
    echo json_encode($res);
    exit();
}

// GET current user's academic info
if (strpos($path, '/api/info-academica-clean/get') === 0 && $_SERVER['REQUEST_METHOD'] === 'GET') {
    $user = AuthMiddleware::getCurrentUser();
    if (!$user) {
        $devId = isset($_GET['dev_user_id']) ? (int)$_GET['dev_user_id'] : (isset($_COOKIE['user_id']) ? (int)$_COOKIE['user_id'] : 1);
        $user = ['user_id'=>$devId,'id'=>$devId,'email'=>'dev@local'];
    }
    $res = $controller->get($user);
    http_response_code($res['code'] ?? ($res['success']?200:400));
    header('Content-Type: application/json');
    echo json_encode($res);
    exit();
}

http_response_code(404);
header('Content-Type: application/json');
echo json_encode(['success'=>false,'error'=>'NOT_FOUND']);
exit();

