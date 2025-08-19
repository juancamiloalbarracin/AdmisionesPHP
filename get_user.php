<?php
try {
    $conn = new PDO('mysql:host=localhost;dbname=admisiones_udc;charset=utf8mb4', 'root', '', [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]);
    $stmt = $conn->query('SELECT email, nombres, apellidos FROM usuarios LIMIT 1');
    $user = $stmt->fetch();
    echo 'Email: ' . $user['email'] . PHP_EOL;
    echo 'Nombre: ' . $user['nombres'] . ' ' . $user['apellidos'] . PHP_EOL;
    echo 'Puedes usar contraseña: admin123 (o cualquier contraseña que hayas configurado)' . PHP_EOL;
} catch (Exception $e) {
    echo 'Error: ' . $e->getMessage() . PHP_EOL;
}
?>
