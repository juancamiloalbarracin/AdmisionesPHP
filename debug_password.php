<?php
require_once __DIR__ . '/config/bootstrap.php';

use UDC\SistemaAdmisiones\Utils\Database;

$email = $argv[1] ?? 'admin@unicordoba.edu.co';
$action = $argv[2] ?? 'show'; // show|reset|check
$checkPlain = $argv[3] ?? '';

$db = Database::getInstance();
$conn = $db->getConnection();

$stmt = $conn->prepare('SELECT id, email, password_hash, activo FROM usuarios WHERE email = ? LIMIT 1');
$stmt->execute([$email]);
$user = $stmt->fetch();

if (!$user) {
    echo "Usuario no encontrado: $email\n";
    exit(1);
}

echo "Usuario: {$user['email']} (ID: {$user['id']}), activo: {$user['activo']}\n";
echo isset($user['password_hash']) ? "Hash actual: {$user['password_hash']}\n" : "Hash no disponible\n";

if ($action === 'check') {
    if (!$checkPlain) {
        echo "Proporcione una contraseña a verificar como 3er argumento.\n";
        exit(1);
    }
    $ok = password_verify($checkPlain, $user['password_hash'] ?? '');
    echo $ok ? "VERIFICACION OK\n" : "VERIFICACION FAIL\n";
} elseif ($action === 'reset') {
    $new = password_hash('admin123', PASSWORD_DEFAULT);
    $upd = $conn->prepare('UPDATE usuarios SET password_hash = ? WHERE id = ?');
    $ok = $upd->execute([$new, $user['id']]);
    echo $ok ? "Contraseña reseteada a 'admin123'\n" : "Error al resetear contraseña\n";
}
