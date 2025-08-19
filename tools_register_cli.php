<?php
// CLI helper to register a user directly via the controller
require_once __DIR__ . '/config/bootstrap.php';

use UDC\SistemaAdmisiones\Controllers\UserController;

$payload = [
    'nombres' => 'User',
    'apellidos' => 'One',
    'email' => 'user1@gmail.com',
    'telefono' => '3001234567',
    'tipo_documento' => 'CC',
    'numero_documento' => '900001',
    'password' => 'user1234'
];

$controller = new UserController();
$result = $controller->register($payload);

header('Content-Type: application/json; charset=utf-8');
echo json_encode($result, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) . "\n";
