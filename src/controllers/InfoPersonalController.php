<?php
/**
 * CONTROLADOR DE INFORMACIÓN PERSONAL
 * ===================================
 * Este controlador maneja todas las operaciones de información personal
 * Migración del InfoPersonalApiServlet.java del sistema Java
 * Compatible con InfoPersonal-Fixed.jsx del frontend React
 */

namespace UDC\SistemaAdmisiones\Controllers;

use UDC\SistemaAdmisiones\Models\InfoPersonal;
use UDC\SistemaAdmisiones\Utils\Database;
use UDC\SistemaAdmisiones\Middleware\AuthMiddleware;
use DateTime;
use Exception;

class InfoPersonalController
{
    /**
     * Instancia del modelo InfoPersonal
     */
    private InfoPersonal $infoPersonalModel;

    /**
     * Instancia de base de datos
     */
    private Database $database;

    /**
     * Constructor
     */
    public function __construct()
    {
        $this->infoPersonalModel = new InfoPersonal();
        $this->database = Database::getInstance();
    }

    /**
     * ENDPOINT: GET /api/info-personal/get
     * Obtener información personal del usuario autenticado
     * Compatible con InfoPersonal-Fixed.jsx
     * 
     * @param array|null $user Datos del usuario autenticado (opcional para compatibilidad con sesiones PHP)
     * @return array Respuesta JSON
     */
    public function get(array $user = null): array
    {
        try {
            // Obtener usuario autenticado (priorizar parámetro, luego middleware)
            $currentUser = $user ?: AuthMiddleware::getCurrentUser();
            
            if (!$currentUser) {
                return $this->errorResponse('Usuario no autenticado', 401);
            }

            $userId = (int)$currentUser['id'];

            // Obtener información personal
            $infoPersonal = $this->infoPersonalModel->getByUserId($userId);
            
            if (!$infoPersonal) {
                // Si no existe información, devolver estructura vacía
                return $this->successResponse([
                    'infoPersonal' => $this->getEmptyPersonalInfo($userId),
                    'exists' => false,
                    'completeness' => $this->infoPersonalModel->checkCompleteness($userId),
                    'message' => 'No existe información personal registrada'
                ]);
            }

            // Obtener completitud de la información
            $completeness = $this->infoPersonalModel->checkCompleteness($userId);

            return $this->successResponse([
                'infoPersonal' => $infoPersonal,
                'exists' => true,
                'completeness' => $completeness,
                'message' => 'Información personal obtenida exitosamente'
            ]);

        } catch (Exception $e) {
            error_log("[INFO_PERSONAL ERROR] Error obteniendo información: " . $e->getMessage());
            return $this->errorResponse('Error interno del servidor', 500);
        }
    }

    /**
     * ENDPOINT: POST /api/info-personal/save
     * Guardar o actualizar información personal del usuario autenticado
     * Compatible con InfoPersonal-Fixed.jsx
     * 
     * @param array $requestData Datos de información personal
     * @param array|null $user Datos del usuario autenticado (opcional para compatibilidad con sesiones PHP)
     * @return array Respuesta JSON
     */
    public function save(array $requestData, array $user = null): array
    {
        try {
            // Obtener usuario autenticado (priorizar parámetro, luego middleware)
            $currentUser = $user ?: AuthMiddleware::getCurrentUser();
            
            if (!$currentUser) {
                return $this->errorResponse('Usuario no autenticado', 401);
            }

            $userId = (int)$currentUser['id'];

            // Debug: Log usuario y datos
            error_log("[INFO_PERSONAL] Usuario recibido: " . print_r($currentUser, true));
            error_log("[INFO_PERSONAL] Datos recibidos para validación: " . print_r($requestData, true));

            // Validar datos de entrada
            $validation = $this->infoPersonalModel->validate($requestData);
            
            // Debug: Log resultado de validación
            error_log("[INFO_PERSONAL] Resultado de validación: " . print_r($validation, true));
            
            if (!$validation['valid']) {
                return $this->errorResponse(
                    'Datos de información personal inválidos', 
                    400, 
                    $validation['errors']
                );
            }

            // Verificar duplicación de documento (excluyendo al usuario actual)
            $documentDuplicated = $this->checkDocumentDuplication($requestData, $userId);
            if ($documentDuplicated) {
                return $this->errorResponse(
                    'El número de documento ya está registrado por otro usuario', 
                    409
                );
            }

            // Agregar email del usuario a los datos
            $requestData['email'] = $currentUser['email'];

            // Guardar información personal
            $saved = $this->infoPersonalModel->save($userId, $requestData);
            
            if (!$saved) {
                return $this->errorResponse('Error guardando información personal', 500);
            }

            // Obtener información actualizada
            $updatedInfo = $this->infoPersonalModel->getByUserId($userId);
            $completeness = $this->infoPersonalModel->checkCompleteness($userId);

            // Log de la operación
            error_log("[INFO_PERSONAL] Información guardada para usuario: {$user['email']} (ID: {$userId})");

            return $this->successResponse([
                'infoPersonal' => $updatedInfo,
                'completeness' => $completeness,
                'message' => 'Información personal guardada exitosamente'
            ]);

        } catch (Exception $e) {
            error_log("[INFO_PERSONAL ERROR] Error guardando información: " . $e->getMessage());
            return $this->errorResponse('Error interno del servidor', 500);
        }
    }

    /**
     * ENDPOINT: GET /api/info-personal/completeness
     * Verificar completitud de información personal
     * 
     * @return array Respuesta JSON
     */
    public function completeness(): array
    {
        try {
            // Obtener usuario autenticado
            $user = AuthMiddleware::getCurrentUser();
            
            if (!$user) {
                return $this->errorResponse('Usuario no autenticado', 401);
            }

            $userId = (int)$user['user_id'];
            $completeness = $this->infoPersonalModel->checkCompleteness($userId);

            return $this->successResponse([
                'completeness' => $completeness,
                'message' => 'Estado de completitud obtenido'
            ]);

        } catch (Exception $e) {
            error_log("[INFO_PERSONAL ERROR] Error verificando completitud: " . $e->getMessage());
            return $this->errorResponse('Error interno del servidor', 500);
        }
    }

    /**
     * ENDPOINT: DELETE /api/info-personal/delete
     * Eliminar información personal (solo para testing/admin)
     * 
     * @return array Respuesta JSON
     */
    public function delete(): array
    {
        try {
            // Obtener usuario autenticado
            $user = AuthMiddleware::getCurrentUser();
            
            if (!$user) {
                return $this->errorResponse('Usuario no autenticado', 401);
            }

            // Verificar que sea admin o el propio usuario
            if ($user['user_type'] !== 'admin') {
                return $this->errorResponse('Acceso denegado', 403);
            }

            $userId = (int)$user['user_id'];

            // Eliminar información personal
            $sql = "DELETE FROM info_personal WHERE usuario_id = :user_id";
            $deleted = $this->database->execute($sql, [':user_id' => $userId]);

            if ($deleted) {
                error_log("[INFO_PERSONAL] Información eliminada para usuario: {$user['email']} (ID: {$userId})");
                
                return $this->successResponse([
                    'message' => 'Información personal eliminada exitosamente'
                ]);
            } else {
                return $this->errorResponse('No se encontró información para eliminar', 404);
            }

        } catch (Exception $e) {
            error_log("[INFO_PERSONAL ERROR] Error eliminando información: " . $e->getMessage());
            return $this->errorResponse('Error interno del servidor', 500);
        }
    }

    /**
     * ENDPOINT: GET /api/info-personal/stats
     * Obtener estadísticas de información personal (solo admin)
     * 
     * @return array Respuesta JSON
     */
    public function stats(): array
    {
        try {
            // Verificar que sea admin
            if (!AuthMiddleware::hasRole('admin')) {
                return $this->errorResponse('Acceso denegado', 403);
            }

            $stats = $this->infoPersonalModel->getStats();

            return $this->successResponse([
                'stats' => $stats,
                'message' => 'Estadísticas obtenidas exitosamente'
            ]);

        } catch (Exception $e) {
            error_log("[INFO_PERSONAL ERROR] Error obteniendo estadísticas: " . $e->getMessage());
            return $this->errorResponse('Error interno del servidor', 500);
        }
    }

    /**
     * Verificar duplicación de documento
     * 
     * @param array $data Datos del usuario
     * @param int $excludeUserId ID del usuario a excluir
     * @return bool true si hay duplicación
     */
    private function checkDocumentDuplication(array $data, int $excludeUserId): bool
    {
        try {
            if (empty($data['tipoDocumento']) || empty($data['numeroDocumento'])) {
                return false;
            }

            $sql = "SELECT COUNT(*) FROM info_personal 
                    WHERE tipo_documento = :tipo_documento 
                    AND numero_documento = :numero_documento 
                    AND usuario_id != :exclude_user_id";

            $params = [
                ':tipo_documento' => $data['tipoDocumento'],
                ':numero_documento' => $data['numeroDocumento'],
                ':exclude_user_id' => $excludeUserId
            ];

            $count = $this->database->fetchColumn($sql, $params);
            
            return (int)$count > 0;

        } catch (Exception $e) {
            error_log("[INFO_PERSONAL ERROR] Error verificando duplicación: " . $e->getMessage());
            return false; // En caso de error, permitir continuar
        }
    }

    /**
     * Obtener estructura vacía de información personal
     * 
     * @param int $userId ID del usuario
     * @return array Estructura vacía
     */
    private function getEmptyPersonalInfo(int $userId): array
    {
        return [
            'id' => 0,
            'usuarioId' => $userId,
            'tipoDocumento' => '',
            'numeroDocumento' => '',
            'nombres' => '',
            'apellidos' => '',
            'nombreCompleto' => '',
            'fechaNacimiento' => '',
            'genero' => '',
            'estadoCivil' => '',
            'telefono' => '',
            'celular' => '',
            'emailAlternativo' => '',
            'direccion' => '',
            'ciudad' => '',
            'departamento' => '',
            'pais' => 'Colombia',
            'estratoSocioeconomico' => 0,
            'eps' => '',
            'tipoSangre' => '',
            'discapacidad' => false,
            'descripcionDiscapacidad' => '',
            'poblacionEspecial' => '',
            'fechaCreacion' => null,
            'fechaActualizacion' => null,
            'email' => ''
        ];
    }

    /**
     * Validar datos específicos de información personal
     * 
     * @param array $data Datos a validar
     * @return array Resultado de validación
     */
    private function validateAdditionalData(array $data): array
    {
        $errors = [];

        // Validaciones adicionales específicas del controlador
        
        // Validar coherencia de datos
        if (!empty($data['discapacidad']) && $data['discapacidad'] && empty($data['descripcionDiscapacidad'])) {
            $errors['descripcionDiscapacidad'] = 'Descripción de discapacidad requerida cuando se indica discapacidad';
        }

        // Validar formato de teléfono si se proporciona
        if (!empty($data['telefono']) && !preg_match('/^[0-9]{7,10}$/', $data['telefono'])) {
            $errors['telefono'] = 'Teléfono debe tener entre 7 y 10 dígitos';
        }

        // Validar longitud de campos de texto
        $textFields = [
            'direccion' => 200,
            'ciudad' => 100,
            'eps' => 100,
            'descripcionDiscapacidad' => 500,
            'poblacionEspecial' => 100
        ];

        foreach ($textFields as $field => $maxLength) {
            if (!empty($data[$field]) && strlen($data[$field]) > $maxLength) {
                $errors[$field] = "Campo {$field} no puede exceder {$maxLength} caracteres";
            }
        }

        return [
            'valid' => empty($errors),
            'errors' => $errors,
            'message' => empty($errors) ? 'Validaciones adicionales exitosas' : 'Errores en validaciones adicionales'
        ];
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
            'error' => 'INFO_PERSONAL_ERROR',
            'message' => $message,
            'code' => $code,
            'timestamp' => (new DateTime())->format('Y-m-d H:i:s')
        ];

        if (!empty($errors)) {
            $response['errors'] = $errors;
        }

        return $response;
    }

    /**
     * Obtener catálogos para formularios (departamentos, EPS, etc.)
     * 
     * @return array Catálogos disponibles
     */
    public function getCatalogs(): array
    {
        return $this->successResponse([
            'catalogs' => [
                'tiposDocumento' => [
                    ['value' => 'CC', 'label' => 'Cédula de Ciudadanía'],
                    ['value' => 'TI', 'label' => 'Tarjeta de Identidad'],
                    ['value' => 'CE', 'label' => 'Cédula de Extranjería'],
                    ['value' => 'PA', 'label' => 'Pasaporte'],
                    ['value' => 'RC', 'label' => 'Registro Civil']
                ],
                'generos' => [
                    ['value' => 'M', 'label' => 'Masculino'],
                    ['value' => 'F', 'label' => 'Femenino'],
                    ['value' => 'Otro', 'label' => 'Otro']
                ],
                'estadosCiviles' => [
                    ['value' => 'Soltero', 'label' => 'Soltero(a)'],
                    ['value' => 'Casado', 'label' => 'Casado(a)'],
                    ['value' => 'Union_Libre', 'label' => 'Unión Libre'],
                    ['value' => 'Divorciado', 'label' => 'Divorciado(a)'],
                    ['value' => 'Viudo', 'label' => 'Viudo(a)'],
                    ['value' => 'Separado', 'label' => 'Separado(a)']
                ],
                'departamentos' => [
                    ['value' => 'Cordoba', 'label' => 'Córdoba'],
                    ['value' => 'Antioquia', 'label' => 'Antioquia'],
                    ['value' => 'Atlantico', 'label' => 'Atlántico'],
                    ['value' => 'Bogota', 'label' => 'Bogotá D.C.'],
                    ['value' => 'Bolivar', 'label' => 'Bolívar'],
                    ['value' => 'Valle', 'label' => 'Valle del Cauca'],
                    ['value' => 'Cundinamarca', 'label' => 'Cundinamarca'],
                    ['value' => 'Santander', 'label' => 'Santander']
                ],
                'tiposSangre' => [
                    ['value' => 'O+', 'label' => 'O+'],
                    ['value' => 'O-', 'label' => 'O-'],
                    ['value' => 'A+', 'label' => 'A+'],
                    ['value' => 'A-', 'label' => 'A-'],
                    ['value' => 'B+', 'label' => 'B+'],
                    ['value' => 'B-', 'label' => 'B-'],
                    ['value' => 'AB+', 'label' => 'AB+'],
                    ['value' => 'AB-', 'label' => 'AB-']
                ],
                'estratos' => [
                    ['value' => 1, 'label' => 'Estrato 1'],
                    ['value' => 2, 'label' => 'Estrato 2'],
                    ['value' => 3, 'label' => 'Estrato 3'],
                    ['value' => 4, 'label' => 'Estrato 4'],
                    ['value' => 5, 'label' => 'Estrato 5'],
                    ['value' => 6, 'label' => 'Estrato 6']
                ]
            ],
            'message' => 'Catálogos obtenidos exitosamente'
        ]);
    }
}
