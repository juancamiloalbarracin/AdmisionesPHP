<?php
/**
 * MIGRACIÓN DE BASE DE DATOS
 * ==========================
 * Este script crea todas las tablas necesarias para el sistema
 * de admisiones en MySQL, equivalentes a las colecciones de MongoDB
 */

// Cargar bootstrap del sistema
require_once __DIR__ . '/../config/bootstrap.php';

use UDC\SistemaAdmisiones\Utils\Database;

class DatabaseMigration
{
    public function run()
    {
        echo " Iniciando migración de base de datos...\n\n";
        
        try {
            // Verificar MySQL está funcionando
            echo " Verificando conexión a MySQL...\n";
            
            // Conectar sin base de datos específica primero
            $dsn = "mysql:host=localhost;port=3306;charset=utf8mb4";
            $pdo = new PDO($dsn, 'root', '', [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
            ]);
            
            echo " Conexión a MySQL exitosa\n\n";
            
            // Crear base de datos
            echo " Creando base de datos 'admisiones_udc'...\n";
            $pdo->exec("CREATE DATABASE IF NOT EXISTS admisiones_udc CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
            $pdo->exec("USE admisiones_udc");
            echo " Base de datos 'admisiones_udc' lista\n\n";
            
            // Crear tablas
            $this->createUsuariosTable($pdo);
            $this->createInfoPersonalTable($pdo);
            $this->createInfoAcademicaTable($pdo);
            $this->createSolicitudesTable($pdo);
            $this->createTokenBlacklistTable($pdo);
            
            // Insertar datos de ejemplo
            $this->insertSampleData($pdo);
            
            echo " Migración completada exitosamente!\n\n";
            $this->showTablesInfo($pdo);
            
        } catch (Exception $e) {
            echo " Error en migración: " . $e->getMessage() . "\n";
            exit(1);
        }
    }
    
    private function createUsuariosTable($pdo)
    {
        echo " Creando tabla 'usuarios'...\n";
        
        $sql = "CREATE TABLE IF NOT EXISTS usuarios (
            id INT AUTO_INCREMENT PRIMARY KEY,
            email VARCHAR(255) UNIQUE NOT NULL COMMENT 'Email único del usuario',
            password_hash VARCHAR(255) NOT NULL COMMENT 'Hash de la contraseña',
            nombres VARCHAR(255) NOT NULL COMMENT 'Nombres del usuario',
            apellidos VARCHAR(255) NOT NULL COMMENT 'Apellidos del usuario',
            tipo_documento VARCHAR(10) COMMENT 'Tipo de documento (CC, TI, CE, etc.)',
            numero_documento VARCHAR(20) COMMENT 'Número de documento',
            telefono VARCHAR(20) COMMENT 'Teléfono de contacto',
            fecha_registro TIMESTAMP DEFAULT CURRENT_TIMESTAMP COMMENT 'Fecha de registro',
            activo BOOLEAN DEFAULT TRUE COMMENT 'Usuario activo/inactivo',
            ultimo_acceso TIMESTAMP NULL COMMENT 'Último acceso al sistema',
            
            INDEX idx_email (email),
            INDEX idx_documento (tipo_documento, numero_documento),
            INDEX idx_activo (activo),
            INDEX idx_fecha_registro (fecha_registro)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci";
        
        $pdo->exec($sql);
        echo " Tabla 'usuarios' creada\n\n";
    }
    
    private function createInfoPersonalTable($pdo)
    {
        echo " Creando tabla 'info_personal'...\n";
        
        $sql = "CREATE TABLE IF NOT EXISTS info_personal (
            id INT AUTO_INCREMENT PRIMARY KEY,
            usuario_id INT NOT NULL COMMENT 'FK a usuarios.id',
            email VARCHAR(255) NOT NULL COMMENT 'Email del usuario',
            nombres VARCHAR(255) COMMENT 'Nombres completos',
            apellidos VARCHAR(255) COMMENT 'Apellidos completos',
            tipo_documento VARCHAR(10) COMMENT 'Tipo de documento',
            numero_documento VARCHAR(20) COMMENT 'Número de documento',
            fecha_nacimiento DATE COMMENT 'Fecha de nacimiento',
            lugar_nacimiento VARCHAR(255) COMMENT 'Lugar de nacimiento',
            genero VARCHAR(20) COMMENT 'Género del usuario',
            estado_civil VARCHAR(20) COMMENT 'Estado civil',
            direccion TEXT COMMENT 'Dirección de residencia',
            telefono VARCHAR(20) COMMENT 'Teléfono de contacto',
            email_alternativo VARCHAR(255) COMMENT 'Email alternativo',
            estrato_socioeconomico VARCHAR(10) COMMENT 'Estrato socioeconómico',
            fecha_creacion TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            fecha_actualizacion TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            
            FOREIGN KEY (usuario_id) REFERENCES usuarios(id) ON DELETE CASCADE,
            INDEX idx_email (email),
            INDEX idx_usuario_id (usuario_id),
            UNIQUE KEY unique_user_info (usuario_id)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci";
        
        $pdo->exec($sql);
        echo " Tabla 'info_personal' creada\n\n";
    }
    
    private function createInfoAcademicaTable($pdo)
    {
        echo " Creando tabla 'info_academica'...\n";
        
        $sql = "CREATE TABLE IF NOT EXISTS info_academica (
            id INT AUTO_INCREMENT PRIMARY KEY,
            usuario_id INT NOT NULL COMMENT 'FK a usuarios.id',
            email VARCHAR(255) NOT NULL COMMENT 'Email del usuario',
            nivel VARCHAR(50) COMMENT 'Nivel académico',
            sede VARCHAR(100) COMMENT 'Sede universitaria',
            grado_academico VARCHAR(100) COMMENT 'Grado académico actual',
            periodo_admision VARCHAR(20) COMMENT 'Período de admisión',
            metodologia VARCHAR(50) COMMENT 'Metodología de estudio',
            jornada VARCHAR(50) COMMENT 'Jornada académica',
            plan_decision VARCHAR(100) COMMENT 'Plan de decisión académica',
            grado_seleccionado VARCHAR(255) COMMENT 'Programa seleccionado',
            pais VARCHAR(100) COMMENT 'País de procedencia',
            grado_obtenido VARCHAR(255) COMMENT 'Grado obtenido previamente',
            fecha_graduacion DATE COMMENT 'Fecha de graduación anterior',
            fecha_creacion TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            fecha_actualizacion TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            
            FOREIGN KEY (usuario_id) REFERENCES usuarios(id) ON DELETE CASCADE,
            INDEX idx_email (email),
            INDEX idx_usuario_id (usuario_id),
            UNIQUE KEY unique_user_academic (usuario_id)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci";
        
        $pdo->exec($sql);
        echo " Tabla 'info_academica' creada\n\n";
    }
    
    private function createSolicitudesTable($pdo)
    {
        echo " Creando tabla 'solicitudes'...\n";
        
        $sql = "CREATE TABLE IF NOT EXISTS solicitudes (
            id INT AUTO_INCREMENT PRIMARY KEY,
            usuario_id INT NOT NULL COMMENT 'FK a usuarios.id',
            email VARCHAR(255) NOT NULL COMMENT 'Email del usuario',
            numero_radicado VARCHAR(50) UNIQUE NOT NULL COMMENT 'Número de radicado único',
            tipo_solicitud VARCHAR(100) COMMENT 'Tipo de solicitud',
            telefono_contacto VARCHAR(20) COMMENT 'Teléfono para contacto',
            email_notificacion VARCHAR(255) COMMENT 'Email para notificaciones',
            estado VARCHAR(50) DEFAULT 'Radicada' COMMENT 'Estado de la solicitud',
            observaciones TEXT COMMENT 'Observaciones adicionales',
            fecha_radicacion TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            fecha_actualizacion TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            
            FOREIGN KEY (usuario_id) REFERENCES usuarios(id) ON DELETE CASCADE,
            INDEX idx_email (email),
            INDEX idx_usuario_id (usuario_id),
            INDEX idx_numero_radicado (numero_radicado),
            INDEX idx_estado (estado)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci";
        
        $pdo->exec($sql);
        echo " Tabla 'solicitudes' creada\n\n";
    }
    
    private function createTokenBlacklistTable($pdo)
    {
        echo " Creando tabla 'token_blacklist'...\n";
        
        $sql = "CREATE TABLE IF NOT EXISTS token_blacklist (
            id INT AUTO_INCREMENT PRIMARY KEY,
            token_jti VARCHAR(255) UNIQUE NOT NULL COMMENT 'JWT ID único',
            usuario_id INT NOT NULL COMMENT 'ID del usuario',
            fecha_revocacion TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            fecha_expiracion TIMESTAMP NOT NULL,
            razon VARCHAR(100) DEFAULT 'logout',
            
            FOREIGN KEY (usuario_id) REFERENCES usuarios(id) ON DELETE CASCADE,
            INDEX idx_token_jti (token_jti),
            INDEX idx_fecha_expiracion (fecha_expiracion)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci";
        
        $pdo->exec($sql);
        echo " Tabla 'token_blacklist' creada\n\n";
    }
    
    private function insertSampleData($pdo)
    {
        echo " Insertando datos de ejemplo...\n";
        
        $passwordHash = password_hash('admin123', PASSWORD_DEFAULT);
        
        $sql = "INSERT IGNORE INTO usuarios (email, password_hash, nombres, apellidos, tipo_documento, numero_documento, telefono)
                VALUES (?, ?, ?, ?, ?, ?, ?)";
        
        $stmt = $pdo->prepare($sql);
        $stmt->execute([
            'admin@unicordoba.edu.co',
            $passwordHash,
            'Administrador',
            'Sistema',
            'CC',
            '12345678',
            '3001234567'
        ]);
        
        echo " Usuario admin creado (email: admin@unicordoba.edu.co, password: admin123)\n\n";
    }
    
    private function showTablesInfo($pdo)
    {
        echo " Información de tablas creadas:\n";
        echo str_repeat("=", 50) . "\n";
        
        $tables = $pdo->query("SHOW TABLES")->fetchAll();
        
        foreach ($tables as $table) {
            $tableName = array_values($table)[0];
            $count = $pdo->query("SELECT COUNT(*) as count FROM `$tableName`")->fetch();
            printf("%-20s: %d registros\n", $tableName, $count['count']);
        }
        
        echo "\n ¡BASE DE DATOS LISTA!\n";
    }
}

// Ejecutar migración
$migration = new DatabaseMigration();
$migration->run();
