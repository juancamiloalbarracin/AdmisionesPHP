<?php
/**
 * CONFIGURACIÓN JWT (JSON WEB TOKENS)
 * ===================================
 * Configuración para el manejo de tokens de autenticación
 * Compatible con el sistema Java existente para mantener
 * la compatibilidad con el frontend React
 */



return [
    // Configuración principal del JWT
    'secret_key' => $_ENV['JWT_SECRET_KEY'] ?? 'udc_default_secret_change_in_production',
    'algorithm' => $_ENV['JWT_ALGORITHM'] ?? 'HS256',
    'expiration_time' => (int)($_ENV['JWT_EXPIRATION_TIME'] ?? 86400), // 24 horas
    
    // Configuración del emisor (issuer)
    'issuer' => 'UDC-Admisiones-System',
    'audience' => 'UDC-Students-Portal',
    
    // Configuración de refresh tokens
    'refresh_token' => [
        'enabled' => true,
        'expiration_time' => 2592000, // 30 días
        'storage' => 'database' // database, redis, file
    ],
    
    // Headers requeridos para el JWT
    'headers' => [
        'typ' => 'JWT',
        'alg' => $_ENV['JWT_ALGORITHM'] ?? 'HS256'
    ],
    
    // Claims obligatorios que debe contener el token
    'required_claims' => [
        'user_id',      // ID único del usuario
        'email',        // Email del usuario
        'user_type',    // Tipo de usuario (student, admin, etc.)
        'iat',          // Issued at (tiempo de emisión)
        'exp',          // Expiration time (tiempo de expiración)
        'iss',          // Issuer (emisor)
        'aud'           // Audience (audiencia)
    ],
    
    // Claims opcionales que pueden estar en el token
    'optional_claims' => [
        'nombres',
        'apellidos',
        'tipo_documento',
        'numero_documento',
        'permissions'
    ],
    
    // Configuración de validación
    'validation' => [
        'verify_signature' => true,
        'verify_expiration' => true,
        'verify_issuer' => true,
        'verify_audience' => true,
        'leeway' => 60 // Tolerancia en segundos para diferencias de tiempo
    ],
    
    // Configuración de blacklist para tokens revocados
    'blacklist' => [
        'enabled' => true,
        'cleanup_interval' => 3600, // Limpiar tokens expirados cada hora
        'storage' => 'database' // database, redis, file
    ]
];
