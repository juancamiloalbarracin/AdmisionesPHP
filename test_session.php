<?php
// Simular una sesión PHP como la que crea el login
session_start();

// Establecer datos de sesión de prueba
$_SESSION['user_id'] = 1;
$_SESSION['email'] = 'test@example.com';
$_SESSION['nombres'] = 'Usuario';
$_SESSION['apellidos'] = 'Test';

// Realizar prueba con curl interno
$url = 'http://localhost:8000/api/info-personal/get';
$context = stream_context_create([
    'http' => [
        'method' => 'GET',
        'header' => [
            'Content-Type: application/json',
            'Origin: http://localhost:3000',
            'Cookie: ' . session_name() . '=' . session_id()
        ]
    ]
]);

echo "Testing endpoint: $url\n";
echo "Session ID: " . session_id() . "\n";
echo "User ID in session: " . ($_SESSION['user_id'] ?? 'NOT SET') . "\n\n";

$response = @file_get_contents($url, false, $context);

if ($response === false) {
    echo "❌ FAILED - Could not connect\n";
    print_r(error_get_last());
} else {
    echo "✅ SUCCESS - Response received:\n";
    echo $response . "\n";
}
?>
