<?php
/**
 * SISTEMA DE ADMISIONES UDC - PÁGINA PRINCIPAL
 * ============================================
 * Archivo: index.php
 * Descripción: Punto de entrada principal del sistema
 */

// Iniciar sesión
session_start();

// Verificar si el usuario está autenticado
if (isset($_SESSION['user_id']) && isset($_SESSION['jwt_token'])) {
    // Usuario autenticado, redirigir al dashboard
    header('Location: frontend/views/main.php');
    exit();
} else {
    // Usuario no autenticado, redirigir al login
    header('Location: frontend/views/login.php');
    exit();
}
?>
