<?php
require_once __DIR__ . '/config/bootstrap.php';

use UDC\SistemaAdmisiones\Controllers\AuthController;

$email = $argv[1] ?? 'user1@gmail.com';
$pass = $argv[2] ?? 'user1234';

$controller = new AuthController();
$res = $controller->login(['email' => $email, 'password' => $pass]);
echo json_encode($res, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE), "\n";
