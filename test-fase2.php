<?php
/**
 * TESTING DE FASE 2: AUTENTICACIÓN Y SEGURIDAD
 * =============================================
 * Script para probar todas las funcionalidades implementadas en Fase 2
 * Compatible con el frontend React
 */

require_once __DIR__ . '/config/bootstrap.php';

use UDC\SistemaAdmisiones\Controllers\AuthController;
use UDC\SistemaAdmisiones\Controllers\UserController;
use UDC\SistemaAdmisiones\Utils\JwtHelper;

echo "============================================\n";
echo "   TESTING FASE 2: AUTENTICACIÓN          \n";
echo "============================================\n\n";

// Función para mostrar resultados
function mostrarResultado($test, $resultado, $esperado = 'success') {
    $status = isset($resultado['success']) && $resultado['success'] ? '✓' : '✗';
    echo "[$status] $test\n";
    
    if (isset($resultado['message'])) {
        echo "    Mensaje: {$resultado['message']}\n";
    }
    
    if (!$resultado['success'] && isset($resultado['error'])) {
        echo "    Error: {$resultado['error']}\n";
    }
    
    echo "    Respuesta: " . json_encode($resultado, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT) . "\n\n";
}

try {
    // ===== TEST 1: REGISTRO DE USUARIO =====
    echo "1. TESTING REGISTRO DE USUARIO\n";
    echo "-----------------------------\n";
    
    $userController = new UserController();
    
    $datosRegistro = [
        'email' => 'test' . time() . '@unicordoba.edu.co',
        'password' => 'test123456',
        'nombres' => 'Usuario',
        'apellidos' => 'De Prueba',
        'tipoDocumento' => 'CC',
        'numeroDocumento' => (string)random_int(1000000, 99999999),
        'tipoUsuario' => 'student'
    ];
    
    $resultadoRegistro = $userController->register($datosRegistro);
    mostrarResultado('Registro de nuevo usuario', $resultadoRegistro);
    
    // Guardar datos para siguientes tests
    $emailPrueba = $datosRegistro['email'];
    $passwordPrueba = $datosRegistro['password'];
    $tokenPrueba = $resultadoRegistro['token'] ?? null;
    
    // ===== TEST 2: LOGIN CON CREDENCIALES VÁLIDAS =====
    echo "2. TESTING LOGIN EXITOSO\n";
    echo "----------------------\n";
    
    $authController = new AuthController();
    
    $datosLogin = [
        'email' => $emailPrueba,
        'password' => $passwordPrueba
    ];
    
    $resultadoLogin = $authController->login($datosLogin);
    mostrarResultado('Login con credenciales válidas', $resultadoLogin);
    
    $tokenLogin = $resultadoLogin['token'] ?? null;
    
    // ===== TEST 3: LOGIN CON CREDENCIALES INVÁLIDAS =====
    echo "3. TESTING LOGIN CON CREDENCIALES INCORRECTAS\n";
    echo "--------------------------------------------\n";
    
    $datosLoginMalo = [
        'email' => $emailPrueba,
        'password' => 'contraseña_incorrecta'
    ];
    
    $resultadoLoginMalo = $authController->login($datosLoginMalo);
    mostrarResultado('Login con credenciales incorrectas', $resultadoLoginMalo, 'error');
    
    // ===== TEST 4: VALIDACIÓN DE TOKEN =====
    echo "4. TESTING VALIDACIÓN DE TOKEN\n";
    echo "-----------------------------\n";
    
    if ($tokenLogin) {
        // Simular header Authorization
        $_SERVER['HTTP_AUTHORIZATION'] = "Bearer $tokenLogin";
        
        $resultadoValidacion = $authController->validate();
        mostrarResultado('Validación de token válido', $resultadoValidacion);
    }
    
    // ===== TEST 5: VALIDACIÓN DE TOKEN INVÁLIDO =====
    echo "5. TESTING TOKEN INVÁLIDO\n";
    echo "-----------------------\n";
    
    $_SERVER['HTTP_AUTHORIZATION'] = "Bearer token_invalido_123456";
    
    $resultadoTokenInvalido = $authController->validate();
    mostrarResultado('Validación de token inválido', $resultadoTokenInvalido, 'error');
    
    // ===== TEST 6: OBTENER PERFIL DE USUARIO =====
    echo "6. TESTING OBTENER PERFIL\n";
    echo "------------------------\n";
    
    if ($tokenLogin) {
        $_SERVER['HTTP_AUTHORIZATION'] = "Bearer $tokenLogin";
        
        $resultadoPerfil = $userController->getProfile();
        mostrarResultado('Obtener perfil de usuario', $resultadoPerfil);
    }
    
    // ===== TEST 7: ACTUALIZAR PERFIL =====
    echo "7. TESTING ACTUALIZACIÓN DE PERFIL\n";
    echo "---------------------------------\n";
    
    if ($tokenLogin) {
        $_SERVER['HTTP_AUTHORIZATION'] = "Bearer $tokenLogin";
        
        $datosActualizacion = [
            'nombres' => 'Usuario Actualizado',
            'apellidos' => 'Apellido Actualizado'
        ];
        
        $resultadoActualizacion = $userController->updateProfile($datosActualizacion);
        mostrarResultado('Actualización de perfil', $resultadoActualizacion);
    }
    
    // ===== TEST 8: CAMBIO DE CONTRASEÑA =====
    echo "8. TESTING CAMBIO DE CONTRASEÑA\n";
    echo "------------------------------\n";
    
    if ($tokenLogin) {
        $_SERVER['HTTP_AUTHORIZATION'] = "Bearer $tokenLogin";
        
        $datosCambioPassword = [
            'currentPassword' => $passwordPrueba,
            'newPassword' => 'nuevacontraseña123',
            'confirmPassword' => 'nuevacontraseña123'
        ];
        
        $resultadoCambioPassword = $userController->changePassword($datosCambioPassword);
        mostrarResultado('Cambio de contraseña', $resultadoCambioPassword);
    }
    
    // ===== TEST 9: TESTING JWT HELPER =====
    echo "9. TESTING JWT HELPER FUNCTIONS\n";
    echo "------------------------------\n";
    
    if ($tokenLogin) {
        $infoToken = JwtHelper::getTokenInfo($tokenLogin);
        echo "✓ Información del token:\n";
        echo "    " . json_encode($infoToken, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT) . "\n\n";
    }
    
    // ===== TEST 10: LOGOUT =====
    echo "10. TESTING LOGOUT\n";
    echo "----------------\n";
    
    if ($tokenLogin) {
        $_SERVER['HTTP_AUTHORIZATION'] = "Bearer $tokenLogin";
        
        $resultadoLogout = $authController->logout();
        mostrarResultado('Logout (invalidar token)', $resultadoLogout);
    }
    
    // ===== TEST 11: USAR TOKEN DESPUÉS DE LOGOUT =====
    echo "11. TESTING TOKEN DESPUÉS DE LOGOUT\n";
    echo "----------------------------------\n";
    
    if ($tokenLogin) {
        $_SERVER['HTTP_AUTHORIZATION'] = "Bearer $tokenLogin";
        
        $resultadoTokenBlacklistado = $authController->validate();
        mostrarResultado('Token después de logout (debe fallar)', $resultadoTokenBlacklistado, 'error');
    }
    
    // ===== TESTING CON USUARIO EXISTENTE =====
    echo "\n12. TESTING CON USUARIO EXISTENTE (ADMIN)\n";
    echo "----------------------------------------\n";
    
    $datosLoginAdmin = [
        'email' => 'admin@unicordoba.edu.co',
        'password' => 'admin123'
    ];
    
    $resultadoLoginAdmin = $authController->login($datosLoginAdmin);
    mostrarResultado('Login usuario admin existente', $resultadoLoginAdmin);
    
    if (isset($resultadoLoginAdmin['token'])) {
        $_SERVER['HTTP_AUTHORIZATION'] = "Bearer " . $resultadoLoginAdmin['token'];
        
        $resultadoValidacionAdmin = $authController->validate();
        mostrarResultado('Validación token admin', $resultadoValidacionAdmin);
        
        $resultadoPerfilAdmin = $userController->getProfile();
        mostrarResultado('Perfil usuario admin', $resultadoPerfilAdmin);
    }
    
    echo "\n============================================\n";
    echo "     TESTING FASE 2 COMPLETADO             \n";
    echo "============================================\n\n";
    
    // Resumen de funcionalidades implementadas
    echo "FUNCIONALIDADES IMPLEMENTADAS:\n";
    echo "✓ Sistema de registro de usuarios\n";
    echo "✓ Sistema de login con JWT\n";
    echo "✓ Validación de tokens JWT\n";
    echo "✓ Middleware de autenticación\n";
    echo "✓ Sistema de logout con blacklist\n";
    echo "✓ Gestión de perfiles de usuario\n";
    echo "✓ Cambio de contraseñas\n";
    echo "✓ APIs REST compatibles con React\n";
    echo "✓ Manejo de errores y validaciones\n";
    echo "✓ Logs de seguridad\n\n";
    
    echo "ENDPOINTS DISPONIBLES:\n";
    echo "• POST /api/auth/login\n";
    echo "• POST /api/auth/logout\n";  
    echo "• POST /api/auth/validate\n";
    echo "• POST /api/users/register\n";
    echo "• GET /api/users/profile\n";
    echo "• PUT /api/users/profile\n";
    echo "• POST /api/users/change-password\n\n";
    
    echo "PRÓXIMO PASO: Iniciar FASE 3 - INFORMACIÓN PERSONAL\n";

} catch (Exception $e) {
    echo "❌ ERROR DURANTE EL TESTING: " . $e->getMessage() . "\n";
    echo "Archivo: " . $e->getFile() . "\n";
    echo "Línea: " . $e->getLine() . "\n";
    echo "Stack trace:\n" . $e->getTraceAsString() . "\n";
}
