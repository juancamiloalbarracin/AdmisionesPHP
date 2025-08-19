<?php
require_once __DIR__ . '/config/bootstrap.php';

use UDC\SistemaAdmisiones\Utils\Database;

$email = $argv[1] ?? 'user1@gmail.com';
$password = $argv[2] ?? null;

try {
    $db = Database::getInstance();
    $row = $db->fetch('SELECT id,email,password_hash,activo FROM usuarios WHERE email = :email', [':email' => strtolower(trim($email))]);
    if (!$row) {
        echo "NOT_FOUND\n";
        exit(0);
    }
    echo "id=" . $row['id'] . " email=" . $row['email'] . " activo=" . $row['activo'] . "\n";
    echo "hash=" . $row['password_hash'] . "\n";
    if ($password !== null) {
        echo 'verify=' . (password_verify($password, $row['password_hash']) ? 'OK' : 'FAIL') . "\n";
    }
} catch (Throwable $e) {
    echo 'ERROR: ' . $e->getMessage() . "\n";
}
