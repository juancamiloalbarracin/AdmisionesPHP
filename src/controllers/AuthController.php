<?php
/**
 * CONTROLADOR DE AUTENTICACIÓN
 * ============================
 * Este controlador maneja todas las operaciones de autenticación
 * Migración del AuthApiServlet.java del sistema Java
 * Mantiene compatibilidad total con el frontend React
 */

namespace UDC\SistemaAdmisiones\Controllers;

use UDC\SistemaAdmisiones\Utils\Database;
use UDC\SistemaAdmisiones\Utils\JwtHelper;
use UDC\SistemaAdmisiones\Middleware\AuthMiddleware;
use DateTime;
use Exception;
use PDOException;

class AuthController
{
    /**
     * Instancia de base de datos
     */
    private Database $database;

    /**
     * Constructor
     */
    public function __construct()
    {
    $this->database = Database::getInstance();
    }

    /**
     * ENDPOINT: POST /api/auth/login
     * Autenticación de usuario - Migración del AuthApiServlet.java
     * 
     * @param array $requestData Datos de la solicitud (email, password)
     * @return array Respuesta JSON
     */
    public function login(array $requestData): array
    {
        try {
            // Validar datos requeridos
            $validation = $this->validateLoginData($requestData);
            if (!$validation['valid']) {
                return $this->errorResponse($validation['message'], 400);
            }

            $email = trim(strtolower($requestData['email']));
            $password = $requestData['password'];

            // Buscar usuario en la base de datos
            $user = $this->getUserByEmail($email);
            if (!$user) {
                // Log del intento de acceso fallido
                error_log("[AUTH] Intento de login fallido - Email no encontrado: {$email}");
                return $this->errorResponse('Credenciales inválidas', 401);
            }

            // Verificar contraseña (usar columna password_hash del esquema)
            if (!password_verify($password, $user['password_hash'])) {
                // Log del intento de acceso fallido
                error_log("[AUTH] Intento de login fallido - Contraseña incorrecta: {$email}");
                return $this->errorResponse('Credenciales inválidas', 401);
            }

            // Verificar que el usuario esté activo
            if (!$user['activo']) {
                error_log("[AUTH] Intento de login - Usuario inactivo: {$email}");
                return $this->errorResponse('Cuenta de usuario inactiva', 403);
            }

            // Generar token JWT
            $token = JwtHelper::generateToken([
                'id' => $user['id'],
                'email' => $user['email'],
                'nombres' => $user['nombres'],
                'apellidos' => $user['apellidos'],
                // 'tipo_usuario' no existe en el esquema actual; usar valor por defecto
                'user_type' => $user['tipo_usuario'] ?? 'user',
                'tipo_documento' => $user['tipo_documento'] ?? null,
                'numero_documento' => $user['numero_documento'] ?? null
            ]);

            // Actualizar último acceso
            $this->updateLastLogin($user['id']);

            // Log de login exitoso
            error_log("[AUTH] Login exitoso: {$email} (ID: {$user['id']})");

            // Respuesta compatible con frontend React
            return $this->successResponse([
                'token' => $token,
                'user' => [
                    'id' => (int)$user['id'],
                    'email' => $user['email'],
                    'nombres' => $user['nombres'],
                    'apellidos' => $user['apellidos'],
                    'nombreCompleto' => trim($user['nombres'] . ' ' . $user['apellidos']),
                    'tipoUsuario' => $user['tipo_usuario'] ?? 'user',
                    'tipoDocumento' => $user['tipo_documento'] ?? null,
                    'numeroDocumento' => $user['numero_documento'] ?? null,
                    'ultimoAcceso' => null
                ],
                'message' => 'Autenticación exitosa'
            ]);

        } catch (Exception $e) {
            error_log("[AUTH ERROR] Error en login: " . $e->getMessage());
            return $this->errorResponse('Error interno del servidor', 500);
        }
    }

    /**
     * ENDPOINT: POST /api/auth/logout
     * Cerrar sesión - Invalidar token JWT
     * 
     * @return array Respuesta JSON
     */
    public function logout(): array
    {
        try {
            // Obtener token del header
            $headers = getallheaders() ?: [];
            $authHeader = $headers['Authorization'] ?? $headers['authorization'] ?? null;
            $token = JwtHelper::extractTokenFromHeader($authHeader);

            if (!$token) {
                return $this->errorResponse('Token no proporcionado', 400);
            }

            // Obtener datos del usuario antes de invalidar el token
            $user = AuthMiddleware::getCurrentUser();
            
            // Agregar token a blacklist
            $blacklisted = JwtHelper::blacklistToken($token);
            
            if ($blacklisted) {
                if ($user) {
                    error_log("[AUTH] Logout exitoso: {$user['email']} (ID: {$user['user_id']})");
                }
                
                return $this->successResponse([
                    'message' => 'Sesión cerrada exitosamente'
                ]);
            } else {
                return $this->errorResponse('Error cerrando sesión', 500);
            }

        } catch (Exception $e) {
            error_log("[AUTH ERROR] Error en logout: " . $e->getMessage());
            return $this->errorResponse('Error interno del servidor', 500);
        }
    }

    /**
     * ENDPOINT: POST /api/auth/validate
     * Validar token JWT - Verificar sesión activa
     * 
     * @return array Respuesta JSON
     */
    public function validate(): array
    {
        try {
            // Obtener usuario actual mediante middleware
            $user = AuthMiddleware::getCurrentUser();

            if (!$user) {
                return $this->errorResponse('Token inválido o expirado', 401);
            }

            // Verificar que el usuario aún esté activo en la base de datos
            $userData = $this->getUserById((int)$user['user_id']);
            if (!$userData || !$userData['activo']) {
                return $this->errorResponse('Usuario inactivo', 403);
            }

            // Respuesta compatible con frontend React
            return $this->successResponse([
                'user' => [
                    'id' => (int)$user['user_id'],
                    'email' => $user['email'],
                    'nombres' => $user['nombres'] ?? $userData['nombres'],
                    'apellidos' => $user['apellidos'] ?? $userData['apellidos'],
                    'nombreCompleto' => trim(($user['nombres'] ?? $userData['nombres']) . ' ' . ($user['apellidos'] ?? $userData['apellidos'])),
                    'tipoUsuario' => $user['user_type'] ?? 'user',
                    'tipoDocumento' => $user['tipo_documento'] ?? $userData['tipo_documento'] ?? null,
                    'numeroDocumento' => $user['numero_documento'] ?? $userData['numero_documento'] ?? null
                ],
                'tokenInfo' => [
                    'issuedAt' => date('Y-m-d H:i:s', $user['iat']),
                    'expiresAt' => date('Y-m-d H:i:s', $user['exp']),
                    'timeRemaining' => $user['exp'] - time()
                ],
                'message' => 'Token válido'
            ]);

        } catch (Exception $e) {
            error_log("[AUTH ERROR] Error validando token: " . $e->getMessage());
            return $this->errorResponse('Error interno del servidor', 500);
        }
    }

    /**
     * ENDPOINT: POST /api/auth/refresh
     * Renovar token JWT (funcionalidad futura)
     * 
     * @return array Respuesta JSON
     */
    public function refresh(): array
    {
        // TODO: Implementar renovación de tokens cuando sea necesario
        return $this->errorResponse('Funcionalidad de renovación no implementada', 501);
    }

    /**
     * Validar datos de login
     * 
     * @param array $data Datos a validar
     * @return array Resultado de validación
     */
    private function validateLoginData(array $data): array
    {
        $errors = [];

        // Validar email
        if (empty($data['email'])) {
            $errors[] = 'Email es requerido';
        } elseif (!filter_var($data['email'], FILTER_VALIDATE_EMAIL)) {
            $errors[] = 'Email no tiene formato válido';
        }

        // Validar password
        if (empty($data['password'])) {
            $errors[] = 'Contraseña es requerida';
        }

        return [
            'valid' => empty($errors),
            'errors' => $errors,
            'message' => empty($errors) ? 'Datos válidos' : implode(', ', $errors)
        ];
    }

    /**
     * Obtener usuario por email
     * 
     * @param string $email Email del usuario
     * @return array|null Datos del usuario o null si no existe
     */
    private function getUserByEmail(string $email): ?array
    {
        try {
         $sql = "SELECT id, email, password_hash, nombres, apellidos, 
                  tipo_documento, numero_documento, activo
              FROM usuarios 
              WHERE email = :email";

            $result = $this->database->fetch($sql, [':email' => $email]);
            
            return $result ?: null;

        } catch (Exception $e) {
            error_log("[AUTH ERROR] Error buscando usuario por email: " . $e->getMessage());
            return null;
        }
    }

    /**
     * Obtener usuario por ID
     * 
     * @param int $userId ID del usuario
     * @return array|null Datos del usuario o null si no existe
     */
    private function getUserById(int $userId): ?array
    {
        try {
         $sql = "SELECT id, email, nombres, apellidos, 
                  tipo_documento, numero_documento, activo
              FROM usuarios 
              WHERE id = :id";

            $result = $this->database->fetch($sql, [':id' => $userId]);
            
            return $result ?: null;

        } catch (Exception $e) {
            error_log("[AUTH ERROR] Error buscando usuario por ID: " . $e->getMessage());
            return null;
        }
    }

    /**
     * Actualizar último acceso del usuario
     * 
     * @param int $userId ID del usuario
     * @return bool true si se actualizó correctamente
     */
    private function updateLastLogin(int $userId): bool
    {
        try {
            // Algunas bases no tienen la columna ultimo_acceso aún; intentar actualizar fecha_registro como fallback
            try {
                $sql = "UPDATE usuarios SET ultimo_acceso = NOW() WHERE id = :id";
                return $this->database->execute($sql, [':id' => $userId]);
            } catch (\Throwable $e) {
                // Fallback no fatal
                error_log("[AUTH WARN] ultimo_acceso no existe, omitiendo actualización");
                return true;
            }

        } catch (Exception $e) {
            error_log("[AUTH ERROR] Error actualizando último acceso: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Generar respuesta de éxito
     * 
     * @param array $data Datos de respuesta
     * @return array Respuesta formateada
     */
    private function successResponse(array $data): array
    {
        return array_merge([
            'success' => true,
            'timestamp' => (new DateTime())->format('Y-m-d H:i:s')
        ], $data);
    }

    /**
     * Generar respuesta de error
     * 
     * @param string $message Mensaje de error
     * @param int $code Código de error HTTP
     * @return array Respuesta formateada
     */
    private function errorResponse(string $message, int $code = 400): array
    {
        return [
            'success' => false,
            'error' => 'AUTH_ERROR',
            'message' => $message,
            'code' => $code,
            'timestamp' => (new DateTime())->format('Y-m-d H:i:s')
        ];
    }

    /**
     * Obtener estadísticas de autenticación (para admin)
     * 
     * @return array Estadísticas de login
     */
    public function getAuthStats(): array
    {
        try {
            // Verificar que el usuario sea admin
            if (!AuthMiddleware::hasRole('admin')) {
                return $this->errorResponse('Acceso denegado', 403);
            }

            $stats = [];

            // Usuarios activos
            $sql = "SELECT COUNT(*) as total FROM usuarios WHERE activo = 1";
            $stats['usuarios_activos'] = (int)$this->database->fetchColumn($sql);

            // Logins del día
            $sql = "SELECT COUNT(*) as total FROM usuarios 
                    WHERE DATE(ultimo_acceso) = CURDATE()";
            $stats['logins_hoy'] = (int)$this->database->fetchColumn($sql);

            // Tokens en blacklist activos
            $sql = "SELECT COUNT(*) as total FROM token_blacklist 
                    WHERE expires_at > NOW()";
            $stats['tokens_blacklist'] = (int)$this->database->fetchColumn($sql);

            return $this->successResponse(['stats' => $stats]);

        } catch (Exception $e) {
            error_log("[AUTH ERROR] Error obteniendo estadísticas: " . $e->getMessage());
            return $this->errorResponse('Error obteniendo estadísticas', 500);
        }
    }
}
