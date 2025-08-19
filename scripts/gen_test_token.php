<?php
require_once __DIR__ . '/../config/bootstrap.php';
use UDC\SistemaAdmisiones\Utils\JwtHelper;

// Test user payload - adjust ID/email if needed
$testUser = [
    'id' => 6,
    'email' => 'cnavarroi@unicartagena.edu.co',
    'nombres' => 'Cesar Luis',
    'apellidos' => 'Navarro Ibanez',
    'user_type' => 'student'
];

try {
    $token = JwtHelper::generateToken($testUser);
    echo $token . PHP_EOL;
} catch (Exception $e) {
    echo 'ERROR_GENERATING_TOKEN: ' . $e->getMessage() . PHP_EOL;
    exit(1);
}
