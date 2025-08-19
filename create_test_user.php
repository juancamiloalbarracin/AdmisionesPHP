<?php
/**
 * CREAR USUARIO DE PRUEBA PARA LOGIN
 * ==================================
 */

require_once __DIR__ . '/config/bootstrap.php';

use UDC\SistemaAdmisiones\Utils\Database;

try {
    $database = Database::getInstance();
    $conn = $database->getConnection();
    
    // Verificar si ya existe el usuario admin
    $stmt = $conn->prepare("SELECT COUNT(*) FROM usuarios WHERE email = ?");
    $stmt->execute(['admin@uniminuto.edu.co']);
    $userExists = $stmt->fetchColumn() > 0;
    
    if (!$userExists) {
        // Crear usuario admin
        $hashedPassword = password_hash('admin123', PASSWORD_DEFAULT);
        
        $stmt = $conn->prepare("
            INSERT INTO usuarios (
                tipo_documento, numero_documento, nombres, apellidos, 
                email, password, telefono, fecha_nacimiento, 
                genero, tipo_usuario, activo, creado_en
            ) VALUES (
                'CC', '12345678', 'Administrador', 'Sistema',
                'admin@uniminuto.edu.co', ?, '3001234567', '1990-01-01',
                'M', 'admin', 1, NOW()
            )
        ");
        
        $result = $stmt->execute([$hashedPassword]);
        
        if ($result) {
            echo "✅ Usuario admin creado exitosamente\n";
            echo "Email: admin@uniminuto.edu.co\n";
            echo "Password: admin123\n";
        } else {
            echo "❌ Error al crear el usuario admin\n";
        }
    } else {
        echo "ℹ️  Usuario admin ya existe\n";
        echo "Email: admin@uniminuto.edu.co\n";
        echo "Password: admin123\n";
    }
    
} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
}
?>
