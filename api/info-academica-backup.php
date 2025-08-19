<?php
// Headers CORS simples
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET, POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type");
header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    exit(0);
}

// Endpoint simplificado para prueba directa
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Leer datos del POST
    $input = file_get_contents('php://input');
    $data = json_decode($input, true);
    
    // Respuesta simple de prueba
    echo json_encode([
        'status' => 'success',
        'message' => 'POST recibido correctamente',
        'received_data' => $data,
        'method' => $_SERVER['REQUEST_METHOD']
    ]);
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    echo json_encode([
        'status' => 'success',
        'message' => 'GET funcionando',
        'method' => $_SERVER['REQUEST_METHOD']
    ]);
    exit();
}

echo json_encode(['status' => 'error', 'message' => 'Método no permitido']);
exit();
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
                        'message' => 'JSON inválido en el cuerpo de la petición: ' . json_last_error_msg(),
                        'code' => 400
                    ];
                } else {
                    // Log para debugging
                    error_log("API INFO ACADEMICA - Input data: " . json_encode($input));
                    error_log("API INFO ACADEMICA - User data: " . json_encode($userData));
                    
                    $response = $controller->save($input, $userData);
                    
                    // Log la respuesta
                    error_log("API INFO ACADEMICA - Response: " . json_encode($response));
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
