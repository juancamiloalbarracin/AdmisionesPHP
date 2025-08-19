<?php
/**
 * CREAR TABLA TOKEN_BLACKLIST
 * ============================
 * Script para crear la tabla token_blacklist que faltaba
 */

try {
    $dsn = "mysql:host=localhost;dbname=admisiones_udc;charset=utf8mb4";
    $pdo = new PDO($dsn, 'root', '');
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    echo "Creando tabla 'token_blacklist'...\n";
    
    $sql = "CREATE TABLE IF NOT EXISTS token_blacklist (
        id INT AUTO_INCREMENT PRIMARY KEY,
        token_hash VARCHAR(64) NOT NULL,
        user_id INT NOT NULL,
        expires_at DATETIME NOT NULL,
        created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
        INDEX idx_token_hash (token_hash),
        INDEX idx_user_id (user_id),
        INDEX idx_expires_at (expires_at),
        FOREIGN KEY (user_id) REFERENCES usuarios(id) ON DELETE CASCADE
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci";
    
    $pdo->exec($sql);
    echo "✓ Tabla 'token_blacklist' creada exitosamente\n";
    
    // Verificar que la tabla se creó
    $stmt = $pdo->query("DESCRIBE token_blacklist");
    echo "✓ Estructura de token_blacklist verificada\n";
    
} catch (PDOException $e) {
    echo "✗ Error creando tabla: " . $e->getMessage() . "\n";
}
