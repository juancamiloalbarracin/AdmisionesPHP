<?php
/**
 * HELPER PARA MANEJO DE JWT TOKENS
 * =================================
 * Esta clase maneja la creación, validación y manejo de tokens JWT
 * Compatible con el sistema Java existente para mantener compatibilidad
 * con el frontend React
 */

namespace UDC\SistemaAdmisiones\Utils;

use Firebase\JWT\JWT;
use Firebase\JWT\Key;
use Firebase\JWT\ExpiredException;
use Firebase\JWT\SignatureInvalidException;
use Exception;
use DateTime;
use DateTimeImmutable;

class JwtHelper
{
    /**
     * Configuración JWT cargada desde config/jwt.php
     */
    private static ?array $config = null;
    
    /**
     * Instancia de Database para manejo de blacklist
     */
    private static ?Database $database = null;

    /**
     * Inicializar configuración JWT
     */
    private static function initConfig(): void
    {
        if (self::$config === null) {
            self::$config = require __DIR__ . '/../../config/jwt.php';
            // Usar el singleton de Database
            self::$database = Database::getInstance();
        }
    }

    /**
     * Generar un token JWT para un usuario
     * 
     * @param array $userData Datos del usuario (id, email, nombres, apellidos, etc.)
     * @return string Token JWT generado
     * @throws Exception Si hay error en la generación
     */
    public static function generateToken(array $userData): string
    {
        self::initConfig();
        
        $now = new DateTimeImmutable();
        $expire = $now->modify('+' . self::$config['expiration_time'] . ' seconds');
        
        // Payload básico del token (claims obligatorios)
        $payload = [
            'iss' => self::$config['issuer'],                    // Issuer
            'aud' => self::$config['audience'],                  // Audience
            'iat' => $now->getTimestamp(),                       // Issued at
            'exp' => $expire->getTimestamp(),                    // Expiration
            'user_id' => $userData['id'],                        // ID único del usuario
            'email' => $userData['email'],                       // Email del usuario
            'user_type' => $userData['user_type'] ?? 'student',  // Tipo de usuario
        ];
        
        // Agregar claims opcionales si están disponibles
        $optionalClaims = ['nombres', 'apellidos', 'tipo_documento', 'numero_documento'];
        foreach ($optionalClaims as $claim) {
            if (isset($userData[$claim]) && !empty($userData[$claim])) {
                $payload[$claim] = $userData[$claim];
            }
        }
        
        try {
            $token = JWT::encode($payload, self::$config['secret_key'], self::$config['algorithm']);
            
            // Log de generación de token (solo en desarrollo)
            if ($_ENV['APP_ENV'] === 'development') {
                error_log("[JWT] Token generado para usuario: {$userData['email']} (ID: {$userData['id']})");
            }
            
            return $token;
            
        } catch (Exception $e) {
            error_log("[JWT ERROR] Error generando token: " . $e->getMessage());
            throw new Exception("Error interno generando token de autenticación");
        }
    }

    /**
     * Validar y decodificar un token JWT
     * 
     * @param string $token Token JWT a validar
     * @return array|null Datos decodificados del token o null si es inválido
     */
    public static function validateToken(string $token): ?array
    {
        self::initConfig();
        
        try {
            // Verificar si el token está en la blacklist
            if (self::isTokenBlacklisted($token)) {
                error_log("[JWT] Token en blacklist rechazado");
                return null;
            }
            
            // Decodificar y validar el token
            $decoded = JWT::decode($token, new Key(self::$config['secret_key'], self::$config['algorithm']));
            
            // Convertir objeto a array
            $payload = json_decode(json_encode($decoded), true);
            
            // Validar claims obligatorios
            $requiredClaims = self::$config['required_claims'];
            foreach ($requiredClaims as $claim) {
                if (!isset($payload[$claim])) {
                    error_log("[JWT] Token inválido - falta claim requerido: $claim");
                    return null;
                }
            }
            
            // Validar que el usuario aún existe en la base de datos
            if (!self::validateUserExists($payload['user_id'])) {
                error_log("[JWT] Token rechazado - usuario no existe: {$payload['user_id']}");
                return null;
            }
            
            return $payload;
            
        } catch (ExpiredException $e) {
            error_log("[JWT] Token expirado: " . $e->getMessage());
            return null;
        } catch (SignatureInvalidException $e) {
            error_log("[JWT] Token con firma inválida: " . $e->getMessage());
            return null;
        } catch (Exception $e) {
            error_log("[JWT] Error validando token: " . $e->getMessage());
            return null;
        }
    }

    /**
     * Agregar token a la blacklist (para logout seguro)
     * 
     * @param string $token Token a invalidar
     * @return bool true si se agregó exitosamente
     */
    public static function blacklistToken(string $token): bool
    {
        self::initConfig();
        
        try {
            // Decodificar token para obtener su tiempo de expiración
            $decoded = JWT::decode($token, new Key(self::$config['secret_key'], self::$config['algorithm']));
            $payload = json_decode(json_encode($decoded), true);
            
            $sql = "INSERT INTO token_blacklist (token_hash, user_id, expires_at, created_at) 
                    VALUES (:token_hash, :user_id, FROM_UNIXTIME(:expires_at), NOW())";
            
            $params = [
                ':token_hash' => hash('sha256', $token),
                ':user_id' => $payload['user_id'],
                ':expires_at' => $payload['exp']
            ];
            
            $result = self::$database->execute($sql, $params);
            
            if ($result) {
                error_log("[JWT] Token agregado a blacklist para usuario: {$payload['user_id']}");
            }
            
            return $result;
            
        } catch (Exception $e) {
            error_log("[JWT ERROR] Error agregando token a blacklist: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Verificar si un token está en la blacklist
     * 
     * @param string $token Token a verificar
     * @return bool true si está en blacklist
     */
    public static function isTokenBlacklisted(string $token): bool
    {
        self::initConfig();
        
        try {
            $tokenHash = hash('sha256', $token);
            
            $sql = "SELECT COUNT(*) FROM token_blacklist 
                    WHERE token_hash = :token_hash 
                    AND expires_at > NOW()";
            
            $result = self::$database->fetchColumn($sql, [':token_hash' => $tokenHash]);
            
            return (int)$result > 0;
            
        } catch (Exception $e) {
            error_log("[JWT ERROR] Error verificando blacklist: " . $e->getMessage());
            // En caso de error, por seguridad asumir que está blacklisted
            return true;
        }
    }

    /**
     * Validar que el usuario del token aún existe en la base de datos
     * 
     * @param int $userId ID del usuario a validar
     * @return bool true si el usuario existe
     */
    private static function validateUserExists(int $userId): bool
    {
        try {
            $sql = "SELECT COUNT(*) FROM usuarios WHERE id = :user_id AND activo = 1";
            $result = self::$database->fetchColumn($sql, [':user_id' => $userId]);
            
            return (int)$result > 0;
            
        } catch (Exception $e) {
            error_log("[JWT ERROR] Error validando existencia de usuario: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Limpiar tokens expirados de la blacklist (mantenimiento)
     * Debe ejecutarse periódicamente (cron job)
     * 
     * @return int Número de tokens eliminados
     */
    public static function cleanupExpiredTokens(): int
    {
        self::initConfig();
        
        try {
            $sql = "DELETE FROM token_blacklist WHERE expires_at <= NOW()";
            $stmt = self::$database->getConnection()->prepare($sql);
            $stmt->execute();
            
            $deletedCount = $stmt->rowCount();
            
            if ($deletedCount > 0) {
                error_log("[JWT CLEANUP] Eliminados $deletedCount tokens expirados de blacklist");
            }
            
            return $deletedCount;
            
        } catch (Exception $e) {
            error_log("[JWT ERROR] Error limpiando tokens expirados: " . $e->getMessage());
            return 0;
        }
    }

    /**
     * Extraer token del header Authorization
     * 
     * @param string|null $authHeader Header Authorization
     * @return string|null Token extraído o null si no es válido
     */
    public static function extractTokenFromHeader(?string $authHeader): ?string
    {
        if (empty($authHeader)) {
            return null;
        }
        
        // Verificar formato "Bearer TOKEN"
        if (!preg_match('/^Bearer\s+(.+)$/', $authHeader, $matches)) {
            return null;
        }
        
        return $matches[1];
    }

    /**
     * Generar respuesta de error JWT estandarizada
     * 
     * @param string $message Mensaje de error
     * @param int $code Código de error HTTP
     * @return array Respuesta de error formateada
     */
    public static function generateErrorResponse(string $message, int $code = 401): array
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
     * Obtener información del token actual (para debugging)
     * 
     * @param string $token Token a analizar
     * @return array Información del token
     */
    public static function getTokenInfo(string $token): array
    {
        try {
            $payload = self::validateToken($token);
            
            if (!$payload) {
                return ['valid' => false, 'reason' => 'Token inválido'];
            }
            
            return [
                'valid' => true,
                'user_id' => $payload['user_id'],
                'email' => $payload['email'],
                'issued_at' => date('Y-m-d H:i:s', $payload['iat']),
                'expires_at' => date('Y-m-d H:i:s', $payload['exp']),
                'time_remaining' => $payload['exp'] - time(),
                'issuer' => $payload['iss'],
                'audience' => $payload['aud']
            ];
            
        } catch (Exception $e) {
            return ['valid' => false, 'reason' => $e->getMessage()];
        }
    }
}
