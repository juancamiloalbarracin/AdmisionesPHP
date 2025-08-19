<?php
/**
 * TEST LOGIN REDIRECT
 * Página de prueba para verificar que el login funciona correctamente
 */

session_start();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Simular login exitoso para prueba
    $_SESSION['user_id'] = 6;
    $_SESSION['jwt_token'] = 'test-token';
    $_SESSION['user_data'] = [
        'id' => 6,
        'email' => 'cnavarroi@unicartagena.edu.co',
        'nombres' => 'Cesar Luis',
        'apellidos' => 'Navarro Ibañez'
    ];
    
    // Limpiar buffer y redirigir
    if (ob_get_length()) { ob_clean(); }
    header('Location: main_modern.php', true, 303);
    exit();
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Test Login</title>
</head>
<body>
    <h1>Test Login</h1>
    <form method="POST">
        <button type="submit">Login Test</button>
    </form>
</body>
</html>
