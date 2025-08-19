<?php
/**
 * SCRIPT DE VERIFICACIÓN DE SISTEMA
 * =================================
 * Este script verifica que el sistema tenga todos los
 * requisitos necesarios para ejecutar la aplicación PHP
 */

echo " VERIFICACIÓN DE REQUISITOS DEL SISTEMA\n";
echo "=========================================\n\n";

// Verificar versión de PHP (ajustado para PHP 8.0)
$phpVersion = PHP_VERSION;
$minPhpVersion = '8.0.0';

echo " PHP Version: $phpVersion\n";

if (version_compare($phpVersion, $minPhpVersion, '<')) {
    echo " ERROR: Se requiere PHP $minPhpVersion o superior\n";
    echo "   Tu versión actual: $phpVersion\n";
    exit(1);
} else {
    echo " Versión de PHP correcta\n\n";
}

// Verificar extensiones PHP requeridas
$requiredExtensions = ['pdo', 'pdo_mysql', 'json', 'openssl', 'mbstring'];
$missingExtensions = [];

echo " Verificando extensiones PHP:\n";
foreach ($requiredExtensions as $extension) {
    if (extension_loaded($extension)) {
        echo " $extension\n";
    } else {
        echo " $extension (FALTANTE)\n";
        $missingExtensions[] = $extension;
    }
}

if (!empty($missingExtensions)) {
    echo "\n ERROR: Faltan extensiones PHP requeridas:\n";
    foreach ($missingExtensions as $ext) {
        echo "   - $ext\n";
    }
    echo "\nPor favor instala estas extensiones antes de continuar.\n";
    exit(1);
}

echo "\n Todas las extensiones PHP están disponibles\n\n";

// Verificar permisos de escritura
$directories = ['logs', 'config'];
echo " Verificando permisos de directorios:\n";

foreach ($directories as $dir) {
    if (!is_dir($dir)) {
        if (mkdir($dir, 0755, true)) {
            echo " Directorio '$dir' creado\n";
        } else {
            echo " No se pudo crear directorio '$dir'\n";
            exit(1);
        }
    }
    
    if (is_writable($dir)) {
        echo " '$dir' tiene permisos de escritura\n";
    } else {
        echo " '$dir' NO tiene permisos de escritura\n";
        exit(1);
    }
}

echo "\n Todos los permisos están correctos\n\n";

// Verificar archivo .env
if (!file_exists('.env')) {
    if (file_exists('.env.example')) {
        copy('.env.example', '.env');
        echo " Archivo .env creado desde .env.example\n";
    } else {
        echo " ERROR: No se encontró .env.example\n";
        exit(1);
    }
} else {
    echo " Archivo .env encontrado\n";
}

echo "\n SISTEMA LISTO PARA USAR\n";
echo "==========================\n";
echo " PHP $phpVersion configurado\n";
echo " Extensiones necesarias disponibles\n";
echo " Permisos correctos\n";
echo " Archivo .env configurado\n\n";

echo " PRÓXIMOS PASOS:\n";
echo "1. Configurar variables de BD en .env\n";
echo "2. Ejecutar: D:\\XAMP\\php\\php.exe migrations/001_create_tables.php\n";
echo "3. Iniciar servidor: D:\\XAMP\\php\\php.exe -S localhost:8080\n\n";
