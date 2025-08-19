<?php
/**
 * MIGRACIÓN: ACTUALIZACIÓN TABLA INFO_ACADÉMICA
 * ============================================
 * Esta migración actualiza la tabla info_academica para que sea compatible
 * con el nuevo modelo de información académica de bachillerato
 */

require_once __DIR__ . '/../config/bootstrap.php';

use UDC\SistemaAdmisiones\Utils\Database;

try {
    echo "\n🔄 ACTUALIZANDO TABLA INFO_ACADEMICA...\n";
    echo str_repeat("=", 50) . "\n";

    $db = Database::getInstance();
    $connection = $db->getConnection();

    // Primero, hacer backup de datos existentes (si los hay)
    echo "📋 Haciendo backup de datos existentes...\n";
    $backupQuery = "CREATE TABLE IF NOT EXISTS info_academica_backup AS SELECT * FROM info_academica";
    $connection->exec($backupQuery);

    // Eliminar la tabla actual
    echo "🗑️ Eliminando tabla anterior...\n";
    $connection->exec("DROP TABLE IF EXISTS info_academica");

    // Crear nueva tabla con estructura correcta
    echo "🏗️ Creando nueva tabla info_academica...\n";
    $sql = "
    CREATE TABLE info_academica (
        id INT AUTO_INCREMENT PRIMARY KEY,
        user_id INT NOT NULL,
        nombre_institucion VARCHAR(200) NOT NULL,
        ciudad_institucion VARCHAR(100) NOT NULL,
        departamento_institucion VARCHAR(100) NOT NULL,
        tipo_bachillerato ENUM('ACADEMICO', 'TECNICO', 'COMERCIAL', 'PEDAGOGICO', 'INDUSTRIAL', 'AGROPECUARIO', 'OTRO') NOT NULL,
        modalidad VARCHAR(100) NULL,
        jornada ENUM('MANANA', 'TARDE', 'NOCHE', 'COMPLETA', 'SABATINA', 'DOMINICAL') NOT NULL,
        caracter_institucion ENUM('PUBLICO', 'PRIVADO', 'MIXTO') NOT NULL,
        ano_graduacion INT NOT NULL,
        promedio_academico DECIMAL(3,2) NULL,
        puntaje_icfes INT NULL,
        posicion_curso INT NULL,
        total_estudiantes INT NULL,
        observaciones TEXT NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        INDEX idx_user_id (user_id),
        INDEX idx_ano_graduacion (ano_graduacion),
        INDEX idx_tipo_bachillerato (tipo_bachillerato),
        INDEX idx_promedio_academico (promedio_academico),
        FOREIGN KEY (user_id) REFERENCES usuarios(id) ON DELETE CASCADE
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
    ";

    $connection->exec($sql);

    echo "✅ Tabla info_academica actualizada exitosamente\n";

    // Verificar la nueva estructura
    echo "🔍 Verificando nueva estructura...\n";
    $result = $connection->query("DESCRIBE info_academica");
    $fields = $result->fetchAll();
    
    echo "\nCampos de la nueva tabla:\n";
    echo str_repeat("-", 40) . "\n";
    foreach ($fields as $field) {
        echo "• " . $field['Field'] . " (" . $field['Type'] . ")\n";
    }

    echo "\n" . str_repeat("=", 50) . "\n";
    echo "✅ MIGRACIÓN DE INFO_ACADEMICA COMPLETADA\n";
    echo "🎓 Tabla lista para información académica de bachillerato\n";
    echo str_repeat("=", 50) . "\n\n";

} catch (Exception $e) {
    echo "\n❌ ERROR EN MIGRACIÓN:\n";
    echo "Mensaje: " . $e->getMessage() . "\n";
    echo "Archivo: " . $e->getFile() . "\n";
    echo "Línea: " . $e->getLine() . "\n";
    exit(1);
}
