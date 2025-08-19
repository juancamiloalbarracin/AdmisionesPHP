<?php
/**
 * SISTEMA DE ADMISIONES UDC - LOGOUT
 * ==================================
 * Archivo: logout.php
 * Descripción: Cierra la sesión del usuario
 */

// Iniciar sesión si no está iniciada
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Destruir todas las variables de sesión
$_SESSION = array();

// Si se desea destruir la sesión completamente, también eliminar la cookie de sesión.
if (ini_get("session.use_cookies")) {
    $params = session_get_cookie_params();
    setcookie(session_name(), '', time() - 42000,
        $params["path"], $params["domain"],
        $params["secure"], $params["httponly"]
    );
}

// Eliminar cookie de remember me si existe
if (isset($_COOKIE['remember_token'])) {
    setcookie('remember_token', '', time() - 3600, '/');
}

// Finalmente, destruir la sesión
session_destroy();

// Configurar mensaje de despedida
session_start();
$_SESSION['success_message'] = 'Ha cerrado sesión correctamente. ¡Hasta pronto!';

// Redirigir al login
header('Location: frontend/views/login.php');
exit();
?>
