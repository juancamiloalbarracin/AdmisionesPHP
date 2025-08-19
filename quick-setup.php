<?php
echo " Creando base de datos MySQL...\n";

try {
    $pdo = new PDO('mysql:host=localhost;port=3306;charset=utf8mb4', 'root', '', [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION
    ]);
    
    $pdo->exec('CREATE DATABASE IF NOT EXISTS admisiones_udc CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci');
    $pdo->exec('USE admisiones_udc');
    
    echo " Base de datos 'admisiones_udc' creada/conectada\n\n";
    
    // Crear tabla usuarios
    echo " Creando tabla usuarios...\n";
    $pdo->exec("
        CREATE TABLE IF NOT EXISTS usuarios (
            id INT AUTO_INCREMENT PRIMARY KEY,
            email VARCHAR(255) UNIQUE NOT NULL,
            password_hash VARCHAR(255) NOT NULL,
            nombres VARCHAR(255) NOT NULL,
            apellidos VARCHAR(255) NOT NULL,
            tipo_documento VARCHAR(10),
            numero_documento VARCHAR(20),
            telefono VARCHAR(20),
            fecha_registro TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            activo BOOLEAN DEFAULT TRUE
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4
    ");
    echo " Tabla usuarios creada\n";
    
    // Insertar usuario de prueba
    $passwordHash = password_hash('admin123', PASSWORD_DEFAULT);
    $stmt = $pdo->prepare("INSERT IGNORE INTO usuarios (email, password_hash, nombres, apellidos, tipo_documento, numero_documento) VALUES (?, ?, ?, ?, ?, ?)");
    $stmt->execute(['admin@unicordoba.edu.co', $passwordHash, 'Admin', 'Sistema', 'CC', '12345678']);
    
    echo " Usuario admin creado: admin@unicordoba.edu.co / admin123\n";
    echo " ¡MIGRACIÓN COMPLETADA!\n";
    
} catch (Exception $e) {
    echo " Error: " . $e->getMessage() . "\n";
}
