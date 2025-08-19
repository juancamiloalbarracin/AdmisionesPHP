<?php
/**
 * PÁGINA PRINCIPAL DEL SISTEMA - OPTIMIZADA
 * Redirige automáticamente al login rápido (ahora principal)
 */

// Verificar si ya hay una sesión activa
session_start();

if (isset($_SESSION['user_id']) && isset($_SESSION['email'])) {
    // Usuario ya logueado, redirigir al menú principal
    header('Location: http://localhost:3000/views/main_modern.php');
    exit;
}

// No hay sesión activa, redirigir al login
header('Location: views/login.php');
exit;
?>
