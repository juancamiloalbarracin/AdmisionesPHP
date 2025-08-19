<?php
/**
 * MIDDLEWARE DE AUTENTICACIÓN
 * ===========================
 * Este middleware valida la autenticación JWT en las rutas protegidas
 * Compatible con el sistema de autenticación del frontend React
 */

namespace UDC\SistemaAdmisiones\Middleware;

use UDC\SistemaAdmisiones\Utils\JwtHelper;
use DateTime;

class AuthMiddleware
{
    /**
     * Rutas que NO requieren autenticación
     */
    private static array $publicRoutes = [
        '/api/auth/login',
        '/api/users/register',
        '/api/health',
        '/api/version',
        '/'
    ];

    /**
     * Rutas que requieren roles específicos
     */
    private static array $roleProtectedRoutes = [
        '/api/admin' => ['admin'],
        '/api/reports' => ['admin', 'coordinator']
    ];

    /**
     * Validar autenticación en la solicitud actual
     * 
     * @param string $requestUri URI de la solicitud
     * @param array $headers Headers de la solicitud
     * @return array Resultado de la validación con datos del usuario o error
     */
    public static function validateAuthentication(string $requestUri, array $headers): array
    {
        // Si es ruta pública, no se necesita autenticación
        if (self::isPublicRoute($requestUri)) {
            return [
                'authenticated' => false,
                'required' => false,
                'user' => null,
                'message' => 'Ruta pública - autenticación no requerida'
            ];
        }

        // Simplified auth: accept X-User-Id header or cookie user_id for quick testing
        $devUserId = $headers['X-User-Id'] ?? $headers['x-user-id'] ?? null;
        if (!$devUserId && isset($_COOKIE['user_id'])) {
            $devUserId = $_COOKIE['user_id'];
        }
        if ($devUserId) {
            $uid = (int)$devUserId;
            return [
                'authenticated' => true,
                'required' => true,
                'user' => ['user_id' => $uid, 'id' => $uid, 'email' => 'dev@local'],
                'message' => 'Dev user authenticated via X-User-Id/cookie'
            ];
        }

        // Extraer token del header Authorization
        $authHeader = $headers['Authorization'] ?? $headers['authorization'] ?? null;
        $token = JwtHelper::extractTokenFromHeader($authHeader);

        // Si no hay token en Authorization, aceptar token desde header X-Auth-Token o desde cookies
        if (!$token) {
            $token = $headers['X-Auth-Token'] ?? $headers['x-auth-token'] ?? null;
        }

        if (!$token && isset($_COOKIE['access_token'])) {
            $token = $_COOKIE['access_token'];
        }
        if (!$token && isset($_COOKIE['token'])) {
            $token = $_COOKIE['token'];
        }

        if (!$token) {
            return [
                'authenticated' => false,
                'required' => true,
                'user' => null,
                'error' => 'AUTH_MISSING_TOKEN',
                'message' => 'Token de autenticación requerido',
                'code' => 401
            ];
        }

        // Validar token JWT
        $payload = JwtHelper::validateToken($token);

        if (!$payload) {
            return [
                'authenticated' => false,
                'required' => true,
                'user' => null,
                'error' => 'AUTH_INVALID_TOKEN',
                'message' => 'Token de autenticación inválido o expirado',
                'code' => 401
            ];
        }

        // Normalize payload to always provide both id and user_id keys
        try {
            $normalizedUserId = $payload['user_id'] ?? $payload['id'] ?? $payload['sub'] ?? null;
            if ($normalizedUserId !== null) {
                $payload['user_id'] = (int)$normalizedUserId;
                $payload['id'] = (int)$normalizedUserId;
            }
        } catch (\Throwable $t) {
            // ignore normalization errors
        }

        // Verificar roles específicos si es necesario
        $roleValidation = self::validateUserRole($requestUri, $payload);
        if (!$roleValidation['valid']) {
            return [
                'authenticated' => true,
                'required' => true,
                'user' => $payload,
                'error' => 'AUTH_INSUFFICIENT_PERMISSIONS',
                'message' => $roleValidation['message'],
                'code' => 403
            ];
        }

        // Usuario autenticado correctamente
        return [
            'authenticated' => true,
            'required' => true,
            'user' => $payload,
            'message' => 'Usuario autenticado correctamente'
        ];
    }

    /**
     * Verificar si una ruta es pública (no requiere autenticación)
     * 
     * @param string $requestUri URI a verificar
     * @return bool true si es ruta pública
     */
    private static function isPublicRoute(string $requestUri): bool
    {
        // Remover query parameters para comparación
        $uri = parse_url($requestUri, PHP_URL_PATH);
        
        foreach (self::$publicRoutes as $publicRoute) {
            if ($uri === $publicRoute || strpos($uri, $publicRoute) === 0) {
                return true;
            }
        }

        return false;
    }

    /**
     * Validar roles de usuario para rutas protegidas
     * 
     * @param string $requestUri URI solicitada
     * @param array $userPayload Datos del usuario del token
     * @return array Resultado de validación de rol
     */
    private static function validateUserRole(string $requestUri, array $userPayload): array
    {
        $uri = parse_url($requestUri, PHP_URL_PATH);
        $userRole = $userPayload['user_type'] ?? 'student';

        foreach (self::$roleProtectedRoutes as $protectedRoute => $allowedRoles) {
            if (strpos($uri, $protectedRoute) === 0) {
                if (!in_array($userRole, $allowedRoles)) {
                    return [
                        'valid' => false,
                        'message' => "Acceso denegado. Rol requerido: " . implode(' o ', $allowedRoles)
                    ];
                }
            }
        }

        return ['valid' => true, 'message' => 'Rol válido'];
    }

    /**
     * Procesar middleware de autenticación y retornar respuesta HTTP si es necesario
     * Esta función debe llamarse al inicio de cada endpoint protegido
     * 
     * @param string $requestUri URI de la solicitud
     * @return array|null Datos del usuario si está autenticado, null si hay error (y respuesta HTTP enviada)
     */
    public static function processAuth(string $requestUri): ?array
    {
        // Obtener headers de la solicitud
        $headers = getallheaders() ?: [];

        // Validar autenticación
        $authResult = self::validateAuthentication($requestUri, $headers);

        // Si no requiere autenticación, continuar
        if (!$authResult['required']) {
            return null; // No hay datos de usuario, pero puede continuar
        }

        // Si hay error de autenticación, enviar respuesta de error
        if (!$authResult['authenticated']) {
            self::sendAuthErrorResponse($authResult);
            return null;
        }

        // Retornar datos del usuario autenticado
        return $authResult['user'];
    }

    /**
     * Enviar respuesta de error de autenticación
     * 
     * @param array $authResult Resultado de autenticación con error
     */
    private static function sendAuthErrorResponse(array $authResult): void
    {
        $response = [
            'success' => false,
            'error' => $authResult['error'],
            'message' => $authResult['message'],
            'code' => $authResult['code'],
            'timestamp' => (new DateTime())->format('Y-m-d H:i:s')
        ];

        // Configurar headers de respuesta
        http_response_code($authResult['code']);
        header('Content-Type: application/json; charset=utf-8');

        // Determinar origen permitido dinamicamente (coincide con bootstrap.php)
        $origin = $_SERVER['HTTP_ORIGIN'] ?? '';
        $allowedOrigins = explode(',', $_ENV['CORS_ALLOWED_ORIGINS'] ?? 'http://localhost:3000');
        if ($origin && in_array($origin, $allowedOrigins)) {
            header("Access-Control-Allow-Origin: $origin");
        }

        header('Access-Control-Allow-Credentials: true');

        // Log de debug para fallos de autenticación (solo en desarrollo)
        try {
            $dbgPath = __DIR__ . '/../../logs/auth_debug.log';
            $headers = getallheaders() ?: [];
            $tokenSnippet = '';
            if (isset($headers['Authorization'])) {
                $tokenSnippet = substr($headers['Authorization'], 0, 80);
            } elseif (isset($_COOKIE['access_token'])) {
                $tokenSnippet = substr($_COOKIE['access_token'], 0, 80);
            }
            $dbg = sprintf("%s AUTH_FAIL: %s - HEADERS: %s - TOKEN_SNIPPET: %s\n", (new DateTime())->format('Y-m-d H:i:s'), json_encode($authResult), json_encode($headers), $tokenSnippet);
            @file_put_contents($dbgPath, $dbg, FILE_APPEND | LOCK_EX);
        } catch (\Throwable $t) {
            // ignore logging errors
        }

        // Enviar respuesta JSON y terminar ejecución
        echo json_encode($response, JSON_UNESCAPED_UNICODE);
        exit;
    }

    /**
     * Obtener usuario actual de la solicitud (helper function)
     * 
     * @return array|null Datos del usuario actual o null si no está autenticado
     */
    public static function getCurrentUser(): ?array
    {
        $headers = getallheaders() ?: [];
        $env = $_ENV['APP_ENV'] ?? 'development';

        // Dev shortcut: accept X-User-Id or cookie user_id
        if ($env !== 'production') {
            $devUserId = $headers['X-User-Id'] ?? $headers['x-user-id'] ?? null;
            if (!$devUserId && isset($_COOKIE['user_id'])) {
                $devUserId = $_COOKIE['user_id'];
            }
                if ($devUserId) {
                    $uid = (int)$devUserId;
                    return ['user_id' => $uid, 'id' => $uid, 'email' => 'dev@local'];
                }
        }

        // Fallback: JWT
        $authHeader = $headers['Authorization'] ?? $headers['authorization'] ?? null;
        $token = JwtHelper::extractTokenFromHeader($authHeader);
        if (!$token) {
            $token = $headers['X-Auth-Token'] ?? $headers['x-auth-token'] ?? null;
        }
        if (!$token && isset($_COOKIE['access_token'])) {
            $token = $_COOKIE['access_token'];
        }
        if (!$token && isset($_COOKIE['token'])) {
            $token = $_COOKIE['token'];
        }
        if (!$token) {
            return null;
        }

        $payload = JwtHelper::validateToken($token);
        if ($payload) {
            try {
                $normalizedUserId = $payload['user_id'] ?? $payload['id'] ?? $payload['sub'] ?? null;
                if ($normalizedUserId !== null) {
                    $payload['user_id'] = (int)$normalizedUserId;
                    $payload['id'] = (int)$normalizedUserId;
                }
            } catch (\Throwable $t) {
                // ignore
            }
        }

        return $payload;
    }

    /**
     * Verificar si el usuario actual tiene un rol específico
     * 
     * @param string $requiredRole Rol requerido
     * @return bool true si el usuario tiene el rol
     */
    public static function hasRole(string $requiredRole): bool
    {
        $user = self::getCurrentUser();
        
        if (!$user) {
            return false;
        }

        $userRole = $user['user_type'] ?? 'student';
        return $userRole === $requiredRole;
    }

    /**
     * Verificar si el usuario actual es el propietario del recurso
     * 
     * @param int $resourceUserId ID del usuario propietario del recurso
     * @return bool true si es el propietario o es admin
     */
    public static function isResourceOwner(int $resourceUserId): bool
    {
        $user = self::getCurrentUser();
        
        if (!$user) {
            return false;
        }

        // Admin puede acceder a cualquier recurso
        if (($user['user_type'] ?? '') === 'admin') {
            return true;
        }

        // Verificar si es el propietario
        return (int)($user['user_id'] ?? 0) === $resourceUserId;
    }

    /**
     * Log de actividad de autenticación
     * 
     * @param string $action Acción realizada
     * @param array $context Contexto adicional
     */
    public static function logAuthActivity(string $action, array $context = []): void
    {
        $user = self::getCurrentUser();
        $userId = $user['user_id'] ?? 'anonymous';
        $email = $user['email'] ?? 'unknown';
        
        $logMessage = "[AUTH] {$action} - Usuario: {$email} (ID: {$userId})";
        
        if (!empty($context)) {
            $logMessage .= " - Contexto: " . json_encode($context);
        }
        
        error_log($logMessage);
    }

    /**
     * Validar token de manera directa (para APIs específicas)
     * 
     * @param string $token Token a validar
     * @return array Resultado de validación
     */
    public static function directTokenValidation(string $token): array
    {
        $payload = JwtHelper::validateToken($token);

        if (!$payload) {
            return [
                'valid' => false,
                'error' => 'TOKEN_INVALID',
                'message' => 'Token inválido o expirado'
            ];
        }

        return [
            'valid' => true,
            'user' => $payload,
            'message' => 'Token válido'
        ];
    }

    /**
     * Generar token de API temporal (para integraciones)
     * 
     * @param array $userData Datos del usuario
     * @param int $duration Duración en segundos (por defecto 1 hora)
     * @return string Token temporal
     */
    public static function generateTemporaryToken(array $userData, int $duration = 3600): string
    {
        // Modificar temporalmente la configuración de expiración
        $originalConfig = require __DIR__ . '/../../config/jwt.php';
        
        // Crear configuración temporal
        $tempUserData = array_merge($userData, ['temp_token' => true]);
        
        return JwtHelper::generateToken($tempUserData);
    }

    /**
     * Revocar todos los tokens de un usuario (logout global)
     * 
     * @param int $userId ID del usuario
     * @return bool true si se revocaron correctamente
     */
    public static function revokeAllUserTokens(int $userId): bool
    {
        try {
            // Esta funcionalidad requeriría almacenar todos los tokens activos
            // Por ahora, registramos la acción para implementación futura
            error_log("[AUTH] Revocación global solicitada para usuario: {$userId}");
            
            // TODO: Implementar revocación global cuando sea necesario
            return true;
            
        } catch (\Exception $e) {
            error_log("[AUTH ERROR] Error revocando tokens: " . $e->getMessage());
            return false;
        }
    }
}
