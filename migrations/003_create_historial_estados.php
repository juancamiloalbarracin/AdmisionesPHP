<?php
/**
 * MIGRACIÓN: TABLA HISTORIAL DE ESTADOS
 * ====================================
 * Esta migración crea la tabla historial_estados para rastrear
 * cambios de estado en las solicitudes de admisión
 */

require_once __DIR__ . '/../config/bootstrap.php';

use UDC\SistemaAdmisiones\Utils\Database;

try {
    echo "\n🏗️ CREANDO TABLA HISTORIAL_ESTADOS...\n";
    echo str_repeat("=", 50) . "\n";

    $db = Database::getInstance();
    $connection = $db->getConnection();

    // Crear tabla historial_estados
    $sql = "
    CREATE TABLE IF NOT EXISTS historial_estados (
        id INT AUTO_INCREMENT PRIMARY KEY,
        user_id INT NOT NULL,
        estado_anterior VARCHAR(50) NULL,
        nuevo_estado VARCHAR(50) NOT NULL,
        observacion TEXT NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        INDEX idx_user_id (user_id),
        INDEX idx_nuevo_estado (nuevo_estado),
        INDEX idx_created_at (created_at),
        FOREIGN KEY (user_id) REFERENCES usuarios(id) ON DELETE CASCADE
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
    ";

    $connection->exec($sql);
    echo "✅ Tabla historial_estados creada exitosamente\n";

    // Verificar la estructura
    echo "🔍 Verificando estructura...\n";
    $result = $connection->query("DESCRIBE historial_estados");
    $fields = $result->fetchAll();
    
    echo "\nCampos de la tabla historial_estados:\n";
    echo str_repeat("-", 40) . "\n";
    foreach ($fields as $field) {
        echo "• " . $field['Field'] . " (" . $field['Type'] . ")\n";
    }

    // Verificar que la tabla solicitudes existe y actualizarla si es necesario
    echo "\n📋 Verificando tabla solicitudes...\n";
    $result = $connection->query("SHOW TABLES LIKE 'solicitudes'");
    if ($result->rowCount() > 0) {
        echo "✅ Tabla solicitudes ya existe\n";
        
        // Verificar estructura de solicitudes
        $result = $connection->query("DESCRIBE solicitudes");
        $solicitudesFields = $result->fetchAll();
        $fieldNames = array_column($solicitudesFields, 'Field');
        
        // Verificar si necesita actualizar campos
        $requiredFields = [
            'numero_solicitud' => "VARCHAR(50) UNIQUE",
            'programa_academico' => "VARCHAR(100) NOT NULL",
            'periodo_academico' => "VARCHAR(20) NOT NULL",
            'modalidad_ingreso' => "VARCHAR(50) NOT NULL",
            'sede' => "VARCHAR(100)",
            'estado' => "ENUM('BORRADOR','ENVIADA','EN_REVISION','DOCUMENTOS_PENDIENTES','APROBADA','RECHAZADA','EN_LISTA_ESPERA','CANCELADA') DEFAULT 'BORRADOR'",
            'documentos_adjuntos' => "JSON",
            'observaciones' => "TEXT"
        ];

        $needsUpdate = false;
        foreach (array_keys($requiredFields) as $field) {
            if (!in_array($field, $fieldNames)) {
                $needsUpdate = true;
                echo "⚠️ Campo faltante: $field\n";
            }
        }

        if ($needsUpdate) {
            echo "🔄 Actualizando tabla solicitudes...\n";
            
            // Hacer backup
            $connection->exec("CREATE TABLE IF NOT EXISTS solicitudes_backup AS SELECT * FROM solicitudes");
            
            // Recrear tabla con estructura correcta
            $connection->exec("DROP TABLE solicitudes");
            
            $sqlSolicitudes = "
            CREATE TABLE solicitudes (
                id INT AUTO_INCREMENT PRIMARY KEY,
                user_id INT NOT NULL,
                numero_solicitud VARCHAR(50) UNIQUE NOT NULL,
                programa_academico VARCHAR(100) NOT NULL,
                periodo_academico VARCHAR(20) NOT NULL,
                modalidad_ingreso VARCHAR(50) NOT NULL,
                sede VARCHAR(100) DEFAULT 'Montería',
                estado ENUM('BORRADOR','ENVIADA','EN_REVISION','DOCUMENTOS_PENDIENTES','APROBADA','RECHAZADA','EN_LISTA_ESPERA','CANCELADA') DEFAULT 'BORRADOR',
                documentos_adjuntos JSON NULL,
                observaciones TEXT NULL,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                INDEX idx_user_id (user_id),
                INDEX idx_numero_solicitud (numero_solicitud),
                INDEX idx_programa_academico (programa_academico),
                INDEX idx_periodo_academico (periodo_academico),
                INDEX idx_estado (estado),
                FOREIGN KEY (user_id) REFERENCES usuarios(id) ON DELETE CASCADE
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
            ";
            
            $connection->exec($sqlSolicitudes);
            echo "✅ Tabla solicitudes actualizada\n";
        } else {
            echo "✅ Tabla solicitudes ya está actualizada\n";
        }
    } else {
        echo "🏗️ Creando tabla solicitudes...\n";
        
        $sqlSolicitudes = "
        CREATE TABLE solicitudes (
            id INT AUTO_INCREMENT PRIMARY KEY,
            user_id INT NOT NULL,
            numero_solicitud VARCHAR(50) UNIQUE NOT NULL,
            programa_academico VARCHAR(100) NOT NULL,
            periodo_academico VARCHAR(20) NOT NULL,
            modalidad_ingreso VARCHAR(50) NOT NULL,
            sede VARCHAR(100) DEFAULT 'Montería',
            estado ENUM('BORRADOR','ENVIADA','EN_REVISION','DOCUMENTOS_PENDIENTES','APROBADA','RECHAZADA','EN_LISTA_ESPERA','CANCELADA') DEFAULT 'BORRADOR',
            documentos_adjuntos JSON NULL,
            observaciones TEXT NULL,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            INDEX idx_user_id (user_id),
            INDEX idx_numero_solicitud (numero_solicitud),
            INDEX idx_programa_academico (programa_academico),
            INDEX idx_periodo_academico (periodo_academico),
            INDEX idx_estado (estado),
            FOREIGN KEY (user_id) REFERENCES usuarios(id) ON DELETE CASCADE
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
        ";
        
        $connection->exec($sqlSolicitudes);
        echo "✅ Tabla solicitudes creada\n";
    }

    echo "\n" . str_repeat("=", 50) . "\n";
    echo "✅ MIGRACIÓN COMPLETADA - FASE 5\n";
    echo "📋 Tabla historial_estados: OK\n";
    echo "📋 Tabla solicitudes: OK\n";
    echo "🎯 Sistema de solicitudes listo\n";
    echo str_repeat("=", 50) . "\n\n";

} catch (Exception $e) {
    echo "\n❌ ERROR EN MIGRACIÓN:\n";
    echo "Mensaje: " . $e->getMessage() . "\n";
    echo "Archivo: " . $e->getFile() . "\n";
    echo "Línea: " . $e->getLine() . "\n";
    exit(1);
}
