<?php
// Insert a user directly into DB to validate connection and schema
require_once __DIR__ . '/config/bootstrap.php';

use UDC\SistemaAdmisiones\Utils\Database;

try {
    $db = Database::getInstance();
    $pdo = $db->getConnection();

    $email = 'user1@gmail.com';
    $exists = $pdo->prepare('SELECT COUNT(*) FROM usuarios WHERE email = ?');
    $exists->execute([$email]);
    if ($exists->fetchColumn() > 0) {
        echo "EXISTS\n";
        exit(0);
    }

    $stmt = $pdo->prepare('INSERT INTO usuarios (email, password_hash, nombres, apellidos, tipo_documento, numero_documento, telefono, activo, fecha_registro) VALUES (?,?,?,?,?,?,?,?, NOW())');
    $ok = $stmt->execute([
        $email,
        password_hash('user1234', PASSWORD_DEFAULT),
        'User',
        'One',
        'CC',
        '900001',
        '3001234567',
        1
    ]);

    echo $ok ? "INSERTED\n" : "FAILED\n";
} catch (Throwable $e) {
    echo 'ERROR: ' . $e->getMessage() . "\n";
}
