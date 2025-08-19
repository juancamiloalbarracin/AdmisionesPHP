<?php
/**
 * VERIFICACIÓN DE BASE DE DATOS
 * =============================
 * Script para verificar el estado de la base de datos
 */

header('Content-Type: text/plain; charset=utf-8');
header('Access-Control-Allow-Origin: *');

echo "🗄️ VERIFICACIÓN DE BASE DE DATOS\n";
echo str_repeat("=", 50) . "\n\n";

try {
    // Conexión a la base de datos
    $pdo = new PDO(
        "mysql:host=localhost;dbname=admisiones_udc;charset=utf8mb4",
        "root",
        "",
        [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
        ]
    );
    
    echo "✅ CONEXIÓN EXITOSA\n";
    echo "Base de datos: admisiones_udc\n";
    echo "Servidor: localhost\n\n";
    
    // Verificar tablas principales
    echo "📋 TABLAS DEL SISTEMA:\n";
    echo str_repeat("-", 30) . "\n";
    
    $tables = [
        'usuarios' => 'Usuarios del sistema',
        'token_blacklist' => 'Tokens JWT inválidos',
        'info_personal' => 'Información personal',
        'info_academica' => 'Información académica',
        'solicitudes' => 'Solicitudes de admisión',
        'historial_estados' => 'Historial de estados'
    ];
    
    foreach ($tables as $table => $description) {
        try {
            $stmt = $pdo->query("SELECT COUNT(*) as count FROM $table");
            $result = $stmt->fetch();
            $count = $result['count'];
            
            echo "✅ $table: $count registros ($description)\n";
        } catch (Exception $e) {
            echo "❌ $table: ERROR - " . $e->getMessage() . "\n";
        }
    }
    
    echo "\n📊 ESTADO DETALLADO:\n";
    echo str_repeat("-", 30) . "\n";
    
    // Usuarios activos
    $stmt = $pdo->query("SELECT COUNT(*) as count FROM usuarios WHERE created_at >= DATE_SUB(NOW(), INTERVAL 7 DAY)");
    $recentUsers = $stmt->fetch()['count'];
    echo "👤 Usuarios recientes (7 días): $recentUsers\n";
    
    // Solicitudes por estado
    try {
        $stmt = $pdo->query("SELECT estado, COUNT(*) as count FROM solicitudes GROUP BY estado");
        $solicitudesPorEstado = $stmt->fetchAll();
        
        if (!empty($solicitudesPorEstado)) {
            echo "📝 Solicitudes por estado:\n";
            foreach ($solicitudesPorEstado as $row) {
                echo "   - " . ($row['estado'] ?: 'SIN ESTADO') . ": " . $row['count'] . "\n";
            }
        } else {
            echo "📝 Solicitudes por estado: Sin datos\n";
        }
    } catch (Exception $e) {
        echo "📝 Solicitudes por estado: ERROR - " . $e->getMessage() . "\n";
    }
    
    // Verificar índices
    echo "\n🔍 ÍNDICES DE TABLAS:\n";
    echo str_repeat("-", 30) . "\n";
    
    foreach (array_keys($tables) as $table) {
        try {
            $stmt = $pdo->query("SHOW INDEX FROM $table");
            $indexes = $stmt->fetchAll();
            $indexCount = count($indexes);
            echo "📊 $table: $indexCount índices\n";
        } catch (Exception $e) {
            echo "📊 $table: ERROR obteniendo índices\n";
        }
    }
    
    echo "\n" . str_repeat("=", 50) . "\n";
    echo "✅ BASE DE DATOS OPERATIVA\n";
    echo "🎯 Sistema listo para recibir peticiones\n";
    echo str_repeat("=", 50) . "\n";
    
} catch (Exception $e) {
    echo "❌ ERROR DE CONEXIÓN:\n";
    echo "Mensaje: " . $e->getMessage() . "\n";
    echo "Código: " . $e->getCode() . "\n";
    echo "\nVerifica que:\n";
    echo "1. MySQL esté corriendo\n";
    echo "2. La base de datos 'admisiones_udc' exista\n";
    echo "3. Las credenciales sean correctas\n";
}
