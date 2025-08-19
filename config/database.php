<?php
/**
 * CONFIGURACIÓN DE BASE DE DATOS
 * ==============================
 * Este archivo contiene la configuración específica para la
 * conexión con MySQL y parámetros relacionados con la base de datos
 */

declare(strict_types=1);

return [
    // Configuración principal de MySQL
    'mysql' => [
        'host' => $_ENV['DB_HOST'] ?? 'localhost',
        'port' => (int)($_ENV['DB_PORT'] ?? 3306),
        'database' => $_ENV['DB_DATABASE'] ?? 'admisiones_udc',
        'username' => $_ENV['DB_USERNAME'] ?? 'root',
        'password' => $_ENV['DB_PASSWORD'] ?? '',
        'charset' => $_ENV['DB_CHARSET'] ?? 'utf8mb4',
        'collation' => 'utf8mb4_unicode_ci',
        
        // Opciones de conexión PDO
        'options' => [
            // Modo de error: lanzar excepciones
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            // Modo de fetch por defecto: arrays asociativos
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            // Preparar statements emulados: false para mejor rendimiento
            PDO::ATTR_EMULATE_PREPARES => false,
            // Codificación de caracteres
            PDO::MYSQL_ATTR_INIT_COMMAND => 'SET NAMES utf8mb4 COLLATE utf8mb4_unicode_ci',
            // Timeout de conexión (segundos)
            PDO::ATTR_TIMEOUT => 30,
            // Conexiones persistentes para mejor rendimiento
            PDO::ATTR_PERSISTENT => false,
            // SSL Mode (requerido para conexiones seguras)
            PDO::MYSQL_ATTR_SSL_VERIFY_SERVER_CERT => false,
        ]
    ],
    
    // Configuración de pool de conexiones
    'pool' => [
        'max_connections' => 10,
        'min_connections' => 2,
        'connection_timeout' => 30,
        'idle_timeout' => 300
    ],
    
    // Configuración de logging para consultas
    'logging' => [
        'enabled' => $_ENV['APP_DEBUG'] === 'true',
        'log_queries' => $_ENV['APP_ENV'] === 'development',
        'slow_query_threshold' => 1.0, // segundos
        'log_file' => 'logs/database.log'
    ],
    
    // Configuración de caché de consultas
    'cache' => [
        'enabled' => $_ENV['APP_ENV'] === 'production',
        'default_ttl' => 3600, // 1 hora
        'prefix' => 'udc_admisiones_'
    ]
];
