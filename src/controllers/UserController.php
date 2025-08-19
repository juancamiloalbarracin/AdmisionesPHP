<?php
/**
 * CONTROLADOR DE USUARIOS
 * =======================
 * Este controlador maneja las operaciones de usuarios
 * Migración del UserApiServlet.java del sistema Java
 * Incluye registro, gestión de perfil y operaciones de usuario
 */

namespace UDC\SistemaAdmisiones\Controllers;

use UDC\SistemaAdmisiones\Utils\Database;
use UDC\SistemaAdmisiones\Utils\JwtHelper;
use UDC\SistemaAdmisiones\Middleware\AuthMiddleware;
use DateTime;
use Exception;
use PDOException;

class UserController
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
     * ENDPOINT: POST /api/users/register
     * Registrar nuevo usuario - Compatible con frontend React
     * 
     * @param array $requestData Datos del nuevo usuario
     * @return array Respuesta JSON
     */
    public function register(array $requestData): array
    {
        try {
            // Validar datos de entrada
            $validation = $this->validateRegistrationData($requestData);
            if (!$validation['valid']) {
                return $this->errorResponse($validation['message'], 400, $validation['errors']);
            }

            // Preparar datos del usuario
            // Aceptar tanto camelCase como snake_case desde el frontend
            $tipoDocumento = $requestData['tipoDocumento'] ?? $requestData['tipo_documento'] ?? null;
            $numeroDocumento = $requestData['numeroDocumento'] ?? $requestData['numero_documento'] ?? null;

            $userData = [
                'email' => trim(strtolower($requestData['email'])),
                'password_hash' => password_hash($requestData['password'], PASSWORD_DEFAULT),
                'nombres' => trim($requestData['nombres']),
                'apellidos' => trim($requestData['apellidos']),
                'tipo_documento' => $tipoDocumento,
                'numero_documento' => $numeroDocumento,
                // No existe columna tipo_usuario en el esquema actual
            ];

            // Verificar que el email no esté en uso
            if ($this->emailExists($userData['email'])) {
                return $this->errorResponse('El email ya está registrado', 409);
            }

            // Verificar que el documento no esté en uso
            if ($this->documentExists($userData['tipo_documento'], $userData['numero_documento'])) {
                return $this->errorResponse('El documento ya está registrado', 409);
            }

            // Crear usuario en la base de datos
            $userId = $this->createUser($userData);
            
            if (!$userId) {
                return $this->errorResponse('Error creando el usuario', 500);
            }

            // Generar token JWT para login automático
            $token = JwtHelper::generateToken([
                'id' => $userId,
                'email' => $userData['email'],
                'nombres' => $userData['nombres'],
                'apellidos' => $userData['apellidos'],
                'user_type' => 'aspirante',
                'tipo_documento' => $userData['tipo_documento'],
                'numero_documento' => $userData['numero_documento']
            ]);

            // Log de registro exitoso
            error_log("[USER] Nuevo usuario registrado: {$userData['email']} (ID: {$userId})");

            // Respuesta compatible con frontend React
            return $this->successResponse([
                'user' => [
                    'id' => $userId,
                    'email' => $userData['email'],
                    'nombres' => $userData['nombres'],
                    'apellidos' => $userData['apellidos'],
                    'nombreCompleto' => trim($userData['nombres'] . ' ' . $userData['apellidos']),
                    // No hay tipo_usuario en el esquema; devolver valor por defecto
                    'tipoUsuario' => 'aspirante',
                    'tipoDocumento' => $userData['tipo_documento'],
                    'numeroDocumento' => $userData['numero_documento']
                ],
                'token' => $token,
                'message' => 'Usuario registrado exitosamente'
            ]);

        } catch (Exception $e) {
            error_log("[USER ERROR] Error en registro: " . $e->getMessage());
            return $this->errorResponse('Error interno del servidor', 500);
        }
    }

    /**
     * ENDPOINT: GET /api/users/profile
     * Obtener perfil del usuario autenticado
     * 
     * @return array Respuesta JSON
     */
    public function getProfile(): array
    {
        try {
            // Obtener usuario autenticado
            $user = AuthMiddleware::getCurrentUser();
            
            if (!$user) {
                return $this->errorResponse('Usuario no autenticado', 401);
            }

            // Obtener datos completos del usuario
            $userData = $this->getUserById((int)$user['user_id']);
            
            if (!$userData) {
                return $this->errorResponse('Usuario no encontrado', 404);
            }

            // Respuesta compatible con frontend React
            return $this->successResponse([
                'user' => [
                    'id' => (int)$userData['id'],
                    'email' => $userData['email'],
                    'nombres' => $userData['nombres'],
                    'apellidos' => $userData['apellidos'],
                    'nombreCompleto' => trim($userData['nombres'] . ' ' . $userData['apellidos']),
                    'tipoUsuario' => $userData['tipo_usuario'],
                    'tipoDocumento' => $userData['tipo_documento'],
                    'numeroDocumento' => $userData['numero_documento'],
                    'fechaRegistro' => $userData['fecha_creacion'],
                    'ultimoAcceso' => $userData['ultimo_acceso'],
                    'activo' => (bool)$userData['activo']
                ]
            ]);

        } catch (Exception $e) {
            error_log("[USER ERROR] Error obteniendo perfil: " . $e->getMessage());
            return $this->errorResponse('Error interno del servidor', 500);
        }
    }

    /**
     * ENDPOINT: PUT /api/users/profile
     * Actualizar perfil del usuario autenticado
     * 
     * @param array $requestData Datos a actualizar
     * @return array Respuesta JSON
     */
    public function updateProfile(array $requestData): array
    {
        try {
            // Obtener usuario autenticado
            $user = AuthMiddleware::getCurrentUser();
            
            if (!$user) {
                return $this->errorResponse('Usuario no autenticado', 401);
            }

            // Validar datos de actualización
            $validation = $this->validateProfileUpdateData($requestData, (int)$user['user_id']);
            if (!$validation['valid']) {
                return $this->errorResponse($validation['message'], 400, $validation['errors']);
            }

            // Preparar datos para actualización
            $updateData = [];
            $params = [':id' => (int)$user['user_id']];
            
            // Campos actualizables
            $allowedFields = ['nombres', 'apellidos', 'tipo_documento', 'numero_documento'];
            
            foreach ($allowedFields as $field) {
                if (isset($requestData[$field]) && !empty(trim($requestData[$field]))) {
                    $dbField = $field === 'tipoDocumento' ? 'tipo_documento' : 
                              ($field === 'numeroDocumento' ? 'numero_documento' : $field);
                    $updateData[] = "{$dbField} = :{$field}";
                    $params[":{$field}"] = trim($requestData[$field]);
                }
            }

            if (empty($updateData)) {
                return $this->errorResponse('No hay datos para actualizar', 400);
            }

            // Actualizar usuario
            $sql = "UPDATE usuarios SET " . implode(', ', $updateData) . ", fecha_actualizacion = NOW() WHERE id = :id";
            
            $updated = $this->database->execute($sql, $params);
            
            if ($updated) {
                // Obtener datos actualizados
                $userData = $this->getUserById((int)$user['user_id']);
                
                error_log("[USER] Perfil actualizado: {$user['email']} (ID: {$user['user_id']})");
                
                return $this->successResponse([
                    'user' => [
                        'id' => (int)$userData['id'],
                        'email' => $userData['email'],
                        'nombres' => $userData['nombres'],
                        'apellidos' => $userData['apellidos'],
                        'nombreCompleto' => trim($userData['nombres'] . ' ' . $userData['apellidos']),
                        'tipoUsuario' => $userData['tipo_usuario'],
                        'tipoDocumento' => $userData['tipo_documento'],
                        'numeroDocumento' => $userData['numero_documento']
                    ],
                    'message' => 'Perfil actualizado exitosamente'
                ]);
            } else {
                return $this->errorResponse('Error actualizando perfil', 500);
            }

        } catch (Exception $e) {
            error_log("[USER ERROR] Error actualizando perfil: " . $e->getMessage());
            return $this->errorResponse('Error interno del servidor', 500);
        }
    }

    /**
     * ENDPOINT: POST /api/users/change-password
     * Cambiar contraseña del usuario autenticado
     * 
     * @param array $requestData Datos de cambio de contraseña
     * @return array Respuesta JSON
     */
    public function changePassword(array $requestData): array
    {
        try {
            // Obtener usuario autenticado
            $user = AuthMiddleware::getCurrentUser();
            
            if (!$user) {
                return $this->errorResponse('Usuario no autenticado', 401);
            }

            // Validar datos de entrada
            $validation = $this->validatePasswordChangeData($requestData);
            if (!$validation['valid']) {
                return $this->errorResponse($validation['message'], 400, $validation['errors']);
            }

            // Verificar contraseña actual
            $userData = $this->getUserByIdWithPassword((int)$user['user_id']);
            if (!$userData || !password_verify($requestData['currentPassword'], $userData['password_hash'])) {
                return $this->errorResponse('Contraseña actual incorrecta', 400);
            }

            // Actualizar contraseña
            $newPasswordHash = password_hash($requestData['newPassword'], PASSWORD_DEFAULT);
            
            $sql = "UPDATE usuarios SET password_hash = :password, fecha_actualizacion = NOW() WHERE id = :id";
            $updated = $this->database->execute($sql, [
                ':password' => $newPasswordHash,
                ':id' => (int)$user['user_id']
            ]);

            if ($updated) {
                error_log("[USER] Contraseña cambiada: {$user['email']} (ID: {$user['user_id']})");
                
                return $this->successResponse([
                    'message' => 'Contraseña actualizada exitosamente'
                ]);
            } else {
                return $this->errorResponse('Error actualizando contraseña', 500);
            }

        } catch (Exception $e) {
            error_log("[USER ERROR] Error cambiando contraseña: " . $e->getMessage());
            return $this->errorResponse('Error interno del servidor', 500);
        }
    }

    /**
     * Validar datos de registro
     * 
     * @param array $data Datos a validar
     * @return array Resultado de validación
     */
    private function validateRegistrationData(array $data): array
    {
        $errors = [];

        // Validar email
        if (empty($data['email'])) {
            $errors['email'] = 'Email es requerido';
        } elseif (!filter_var($data['email'], FILTER_VALIDATE_EMAIL)) {
            $errors['email'] = 'Email no tiene formato válido';
        }

        // Validar contraseña
        if (empty($data['password'])) {
            $errors['password'] = 'Contraseña es requerida';
        } elseif (strlen($data['password']) < 6) {
            $errors['password'] = 'Contraseña debe tener al menos 6 caracteres';
        }

        // Validar nombres
        if (empty(trim($data['nombres'] ?? ''))) {
            $errors['nombres'] = 'Nombres son requeridos';
        }

        // Validar apellidos
        if (empty(trim($data['apellidos'] ?? ''))) {
            $errors['apellidos'] = 'Apellidos son requeridos';
        }

        // Validar tipo y número de documento (acepta camelCase o snake_case)
        $tipoDoc = $data['tipoDocumento'] ?? $data['tipo_documento'] ?? null;
        $numeroDoc = $data['numeroDocumento'] ?? $data['numero_documento'] ?? null;
        $tiposDocumento = ['CC', 'TI', 'CE', 'PA'];
        if (empty($tipoDoc) || !in_array($tipoDoc, $tiposDocumento)) {
            $errors['tipoDocumento'] = 'Tipo de documento no válido';
        }

        if (empty($numeroDoc)) {
            $errors['numeroDocumento'] = 'Número de documento es requerido';
        } elseif (!preg_match('/^[0-9]+$/', (string)$numeroDoc)) {
            $errors['numeroDocumento'] = 'Número de documento debe contener solo números';
        }

        return [
            'valid' => empty($errors),
            'errors' => $errors,
            'message' => empty($errors) ? 'Datos válidos' : 'Datos de registro inválidos'
        ];
    }

    /**
     * Validar datos de actualización de perfil
     * 
     * @param array $data Datos a validar
     * @param int $userId ID del usuario actual
     * @return array Resultado de validación
     */
    private function validateProfileUpdateData(array $data, int $userId): array
    {
        $errors = [];

        // Validar nombres si se proporcionan
        if (isset($data['nombres']) && empty(trim($data['nombres']))) {
            $errors['nombres'] = 'Nombres no pueden estar vacíos';
        }

        // Validar apellidos si se proporcionan
        if (isset($data['apellidos']) && empty(trim($data['apellidos']))) {
            $errors['apellidos'] = 'Apellidos no pueden estar vacíos';
        }

        // Validar tipo de documento si se proporciona
        if (isset($data['tipoDocumento'])) {
            $tiposDocumento = ['CC', 'TI', 'CE', 'PA'];
            if (!in_array($data['tipoDocumento'], $tiposDocumento)) {
                $errors['tipoDocumento'] = 'Tipo de documento no válido';
            }
        }

        // Validar número de documento si se proporciona
        if (isset($data['numeroDocumento'])) {
            if (empty($data['numeroDocumento'])) {
                $errors['numeroDocumento'] = 'Número de documento no puede estar vacío';
            } elseif (!preg_match('/^[0-9]+$/', $data['numeroDocumento'])) {
                $errors['numeroDocumento'] = 'Número de documento debe contener solo números';
            } else {
                // Verificar que no esté en uso por otro usuario
                $documentInUse = $this->documentExistsExcludingUser(
                    $data['tipoDocumento'], 
                    $data['numeroDocumento'], 
                    $userId
                );
                if ($documentInUse) {
                    $errors['numeroDocumento'] = 'Número de documento ya está en uso';
                }
            }
        }

        return [
            'valid' => empty($errors),
            'errors' => $errors,
            'message' => empty($errors) ? 'Datos válidos' : 'Datos de actualización inválidos'
        ];
    }

    /**
     * Validar datos de cambio de contraseña
     * 
     * @param array $data Datos a validar
     * @return array Resultado de validación
     */
    private function validatePasswordChangeData(array $data): array
    {
        $errors = [];

        // Validar contraseña actual
        if (empty($data['currentPassword'])) {
            $errors['currentPassword'] = 'Contraseña actual es requerida';
        }

        // Validar nueva contraseña
        if (empty($data['newPassword'])) {
            $errors['newPassword'] = 'Nueva contraseña es requerida';
        } elseif (strlen($data['newPassword']) < 6) {
            $errors['newPassword'] = 'Nueva contraseña debe tener al menos 6 caracteres';
        }

        // Validar confirmación de contraseña
        if (empty($data['confirmPassword'])) {
            $errors['confirmPassword'] = 'Confirmación de contraseña es requerida';
        } elseif ($data['newPassword'] !== $data['confirmPassword']) {
            $errors['confirmPassword'] = 'Las contraseñas no coinciden';
        }

        return [
            'valid' => empty($errors),
            'errors' => $errors,
            'message' => empty($errors) ? 'Datos válidos' : 'Datos de cambio de contraseña inválidos'
        ];
    }

    /**
     * Verificar si un email ya existe
     * 
     * @param string $email Email a verificar
     * @return bool true si existe
     */
    private function emailExists(string $email): bool
    {
        $sql = "SELECT COUNT(*) FROM usuarios WHERE email = :email";
        $count = $this->database->fetchColumn($sql, [':email' => $email]);
        return (int)$count > 0;
    }

    /**
     * Verificar si un documento ya existe
     * 
     * @param string $tipoDocumento Tipo de documento
     * @param string $numeroDocumento Número de documento
     * @return bool true si existe
     */
    private function documentExists(string $tipoDocumento, string $numeroDocumento): bool
    {
        $sql = "SELECT COUNT(*) FROM usuarios WHERE tipo_documento = :tipo AND numero_documento = :numero";
        $count = $this->database->fetchColumn($sql, [
            ':tipo' => $tipoDocumento,
            ':numero' => $numeroDocumento
        ]);
        return (int)$count > 0;
    }

    /**
     * Verificar si un documento existe excluyendo un usuario específico
     * 
     * @param string $tipoDocumento Tipo de documento
     * @param string $numeroDocumento Número de documento
     * @param int $excludeUserId ID del usuario a excluir
     * @return bool true si existe
     */
    private function documentExistsExcludingUser(string $tipoDocumento, string $numeroDocumento, int $excludeUserId): bool
    {
        $sql = "SELECT COUNT(*) FROM usuarios 
                WHERE tipo_documento = :tipo AND numero_documento = :numero AND id != :user_id";
        $count = $this->database->fetchColumn($sql, [
            ':tipo' => $tipoDocumento,
            ':numero' => $numeroDocumento,
            ':user_id' => $excludeUserId
        ]);
        return (int)$count > 0;
    }

    /**
     * Crear nuevo usuario en la base de datos
     * 
     * @param array $userData Datos del usuario
     * @return int|null ID del usuario creado o null si hay error
     */
    private function createUser(array $userData): ?int
    {
        try {
            $sql = "INSERT INTO usuarios (
                        email, password_hash, nombres, apellidos, tipo_documento, 
                        numero_documento, activo, fecha_registro
                    ) VALUES (
                        :email, :password_hash, :nombres, :apellidos, :tipo_documento,
                        :numero_documento, 1, NOW()
                    )";

            $params = [
                ':email' => $userData['email'],
                ':password_hash' => $userData['password_hash'],
                ':nombres' => $userData['nombres'],
                ':apellidos' => $userData['apellidos'],
                ':tipo_documento' => $userData['tipo_documento'],
                ':numero_documento' => $userData['numero_documento']
            ];

            if ($this->database->execute($sql, $params)) {
                return (int)$this->database->getLastInsertId();
            }

            return null;

        } catch (Exception $e) {
            error_log("[USER ERROR] Error creando usuario: " . $e->getMessage());
            return null;
        }
    }

    /**
     * Obtener usuario por ID
     * 
     * @param int $userId ID del usuario
     * @return array|null Datos del usuario
     */
    private function getUserById(int $userId): ?array
    {
        try {
            $sql = "SELECT id, email, nombres, apellidos, tipo_usuario, tipo_documento, 
                           numero_documento, activo, fecha_creacion, ultimo_acceso, fecha_actualizacion
                    FROM usuarios WHERE id = :id";

            return $this->database->fetch($sql, [':id' => $userId]) ?: null;

        } catch (Exception $e) {
            error_log("[USER ERROR] Error obteniendo usuario: " . $e->getMessage());
            return null;
        }
    }

    /**
     * Obtener usuario por ID incluyendo contraseña
     * 
     * @param int $userId ID del usuario
     * @return array|null Datos del usuario con contraseña
     */
    private function getUserByIdWithPassword(int $userId): ?array
    {
        try {
            $sql = "SELECT id, email, password_hash, nombres, apellidos 
                    FROM usuarios WHERE id = :id";

            return $this->database->fetch($sql, [':id' => $userId]) ?: null;

        } catch (Exception $e) {
            error_log("[USER ERROR] Error obteniendo usuario con contraseña: " . $e->getMessage());
            return null;
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
     * @param array $errors Errores específicos (opcional)
     * @return array Respuesta formateada
     */
    private function errorResponse(string $message, int $code = 400, array $errors = []): array
    {
        $response = [
            'success' => false,
            'error' => 'USER_ERROR',
            'message' => $message,
            'code' => $code,
            'timestamp' => (new DateTime())->format('Y-m-d H:i:s')
        ];

        if (!empty($errors)) {
            $response['errors'] = $errors;
        }

        return $response;
    }
}
