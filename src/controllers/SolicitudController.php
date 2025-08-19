<?php
namespace UDC\SistemaAdmisiones\Controllers;

use UDC\SistemaAdmisiones\Models\Solicitud;
use UDC\SistemaAdmisiones\Middleware\AuthMiddleware;
use UDC\SistemaAdmisiones\Utils\Database;
use Exception;

/**
 * CONTROLADOR: SOLICITUDES DE ADMISIÓN
 * ===================================
 * Este controlador maneja todas las operaciones relacionadas con
 * las solicitudes de admisión de los aspirantes
 * Migración de SolicitudApiServlet.java
 * Compatible con Solicitudes-Fixed.jsx
 */
class SolicitudController
{
    private Solicitud $solicitudModel;
    private Database $database;

    /**
     * Constructor
     */
    public function __construct()
    {
        $this->solicitudModel = new Solicitud();
        $this->database = Database::getInstance();
    }

    /**
     * ENDPOINT: GET /api/solicitudes/get
     * =================================
     * Obtiene la solicitud del usuario autenticado
     * 
     * @param array|null $user Datos del usuario autenticado (opcional para compatibilidad con sesiones PHP)
     * @return array Respuesta JSON con la solicitud
     */
    public function get(array $user = null): array
    {
        try {
            // Obtener usuario autenticado (priorizar parámetro, luego middleware)
            $currentUser = $user;
            if (!$currentUser) {
                $currentUser = AuthMiddleware::getCurrentUser();
            }
            
            if (!$currentUser || !isset($currentUser['id'])) {
                return [
                    'success' => false,
                    'error' => 'UNAUTHORIZED',
                    'message' => 'Usuario no autenticado',
                    'code' => 401
                ];
            }

            // Obtener solicitud
            $solicitud = $this->solicitudModel->getByUserId($currentUser['id']);

            if ($solicitud) {
                return [
                    'success' => true,
                    'message' => 'Solicitud obtenida exitosamente',
                    'solicitud' => $solicitud,
                    'code' => 200
                ];
            } else {
                return [
                    'success' => true,
                    'message' => 'No se encontró solicitud',
                    'solicitud' => null,
                    'code' => 200
                ];
            }

        } catch (Exception $e) {
            error_log("[SOLICITUD_CONTROLLER] Error en get(): " . $e->getMessage());
            return [
                'success' => false,
                'error' => 'INTERNAL_ERROR',
                'message' => 'Error interno del servidor',
                'code' => 500
            ];
        }
    }

    /**
     * ENDPOINT: POST /api/solicitudes/save
     * ===================================
     * Guarda o actualiza la solicitud del usuario autenticado
     * 
     * @param int $userId ID del usuario
     * @param array $requestData Datos de solicitud
     * @param int|null $solicitudId ID de solicitud (para actualizar)
     * @return array Respuesta JSON con el resultado
     */
    public function save(int $userId, array $requestData, ?int $solicitudId = null): array
    {
        try {
            // Debug: Log datos recibidos
            error_log("[SOLICITUD_CONTROLLER] UserId: " . $userId);
            error_log("[SOLICITUD_CONTROLLER] Request data: " . print_r($requestData, true));
            
            // Validar usuario
            if (!$userId) {
                error_log("[SOLICITUD_CONTROLLER] Error: Invalid user ID");
                return [
                    'success' => false,
                    'error' => 'INVALID_USER',
                    'message' => 'ID de usuario inválido',
                    'code' => 400
                ];
            }

            // Validar que se enviaron datos
            if (empty($requestData)) {
                error_log("[SOLICITUD_CONTROLLER] Error: Empty request data");
                return [
                    'success' => false,
                    'error' => 'INVALID_DATA',
                    'message' => 'No se enviaron datos para guardar',
                    'code' => 400
                ];
            }

            // Establecer datos en el modelo y guardar
            error_log("[SOLICITUD_CONTROLLER] Setting data in model...");
            $this->solicitudModel->setData($requestData);
            
            error_log("[SOLICITUD_CONTROLLER] Calling model save...");
            $result = $this->solicitudModel->save($userId);
            
            error_log("[SOLICITUD_CONTROLLER] Model save result: " . print_r($result, true));

            if ($result['success']) {
                return [
                    'success' => true,
                    'message' => $result['message'],
                    'data' => $result['data'],
                    'code' => 200
                ];
            } else {
                return [
                    'success' => false,
                    'error' => 'VALIDATION_ERROR',
                    'message' => $result['message'],
                    'errors' => $result['errors'] ?? [],
                    'code' => 400
                ];
            }

        } catch (Exception $e) {
            error_log("[SOLICITUD_CONTROLLER] Error en save(): " . $e->getMessage());
            return [
                'success' => false,
                'error' => 'INTERNAL_ERROR',
                'message' => 'Error interno del servidor',
                'code' => 500
            ];
        }
    }

    /**
     * ENDPOINT: POST /api/solicitudes/submit
     * =====================================
     * Envía la solicitud (cambia estado a ENVIADA)
     * 
     * @param array|null $user Datos del usuario autenticado (opcional para compatibilidad con sesiones PHP)
     * @return array Respuesta JSON con el resultado
     */
    public function submit(array $user = null): array
    {
        try {
            // Obtener usuario actual (priorizar parámetro, luego middleware)
            $currentUser = $user ?: AuthMiddleware::getCurrentUser();
            if (!$currentUser) {
                return [
                    'success' => false,
                    'error' => 'UNAUTHORIZED',
                    'message' => 'Usuario no autenticado',
                    'code' => 401
                ];
            }

            // Verificar que existe una solicitud
            $solicitud = $this->solicitudModel->getByUserId($currentUser['id']);
            if (!$solicitud) {
                return [
                    'success' => false,
                    'error' => 'NOT_FOUND',
                    'message' => 'No se encontró solicitud para enviar',
                    'code' => 404
                ];
            }

            // Verificar que la solicitud esté completa
            $progreso = $this->solicitudModel->calcularProgreso($currentUser['id']);
            if ($progreso['porcentaje'] < 100) {
                return [
                    'success' => false,
                    'error' => 'INCOMPLETE_APPLICATION',
                    'message' => 'La solicitud debe estar completa al 100% para enviar',
                    'data' => $progreso,
                    'code' => 400
                ];
            }

            // Cambiar estado a ENVIADA
            $result = $this->solicitudModel->cambiarEstado($currentUser['id'], 'ENVIADA', 'Solicitud enviada por el aspirante');

            if ($result['success']) {
                return [
                    'success' => true,
                    'message' => 'Solicitud enviada exitosamente',
                    'data' => $result['data'],
                    'code' => 200
                ];
            } else {
                return [
                    'success' => false,
                    'error' => 'SUBMIT_ERROR',
                    'message' => $result['message'],
                    'code' => 400
                ];
            }

        } catch (Exception $e) {
            error_log("[SOLICITUD_CONTROLLER] Error en submit(): " . $e->getMessage());
            return [
                'success' => false,
                'error' => 'INTERNAL_ERROR',
                'message' => 'Error interno del servidor',
                'code' => 500
            ];
        }
    }

    /**
     * ENDPOINT: GET /api/solicitudes/progress
     * ======================================
     * Obtiene el progreso de la solicitud del usuario
     * 
     * @return array Respuesta JSON con el progreso
     */
    public function progress(): array
    {
        try {
            // Obtener usuario actual
            $currentUser = AuthMiddleware::getCurrentUser();
            if (!$currentUser) {
                return [
                    'success' => false,
                    'error' => 'UNAUTHORIZED',
                    'message' => 'Usuario no autenticado',
                    'code' => 401
                ];
            }

            // Calcular progreso
            $progreso = $this->solicitudModel->calcularProgreso($currentUser['id']);

            return [
                'success' => true,
                'message' => 'Progreso calculado exitosamente',
                'data' => $progreso,
                'code' => 200
            ];

        } catch (Exception $e) {
            error_log("[SOLICITUD_CONTROLLER] Error en progress(): " . $e->getMessage());
            return [
                'success' => false,
                'error' => 'INTERNAL_ERROR',
                'message' => 'Error interno del servidor',
                'code' => 500
            ];
        }
    }

    /**
     * ENDPOINT: POST /api/solicitudes/change-status
     * ============================================
     * Cambia el estado de una solicitud (solo admin)
     * 
     * @param array $requestData Datos con nuevo estado
     * @return array Respuesta JSON con el resultado
     */
    public function changeStatus(array $requestData): array
    {
        try {
            // Obtener usuario actual
            $currentUser = AuthMiddleware::getCurrentUser();
            if (!$currentUser) {
                return [
                    'success' => false,
                    'error' => 'UNAUTHORIZED',
                    'message' => 'Usuario no autenticado',
                    'code' => 401
                ];
            }

            // Verificar que sea administrador
            if ($currentUser['role'] !== 'admin') {
                return [
                    'success' => false,
                    'error' => 'FORBIDDEN',
                    'message' => 'Solo administradores pueden cambiar estados de solicitudes',
                    'code' => 403
                ];
            }

            // Validar datos requeridos
            if (empty($requestData['user_id']) || empty($requestData['nuevo_estado'])) {
                return [
                    'success' => false,
                    'error' => 'INVALID_DATA',
                    'message' => 'user_id y nuevo_estado son requeridos',
                    'code' => 400
                ];
            }

            $userId = (int) $requestData['user_id'];
            $nuevoEstado = $requestData['nuevo_estado'];
            $observacion = $requestData['observacion'] ?? '';

            // Cambiar estado
            $result = $this->solicitudModel->cambiarEstado($userId, $nuevoEstado, $observacion);

            if ($result['success']) {
                return [
                    'success' => true,
                    'message' => $result['message'],
                    'data' => $result['data'],
                    'code' => 200
                ];
            } else {
                return [
                    'success' => false,
                    'error' => 'CHANGE_STATUS_ERROR',
                    'message' => $result['message'],
                    'code' => 400
                ];
            }

        } catch (Exception $e) {
            error_log("[SOLICITUD_CONTROLLER] Error en changeStatus(): " . $e->getMessage());
            return [
                'success' => false,
                'error' => 'INTERNAL_ERROR',
                'message' => 'Error interno del servidor',
                'code' => 500
            ];
        }
    }

    /**
     * ENDPOINT: GET /api/solicitudes/all
     * =================================
     * Obtiene todas las solicitudes (solo admin)
     * 
     * @return array Respuesta JSON con las solicitudes
     */
    public function getAll(): array
    {
        try {
            // Obtener usuario actual
            $currentUser = AuthMiddleware::getCurrentUser();
            if (!$currentUser) {
                return [
                    'success' => false,
                    'error' => 'UNAUTHORIZED',
                    'message' => 'Usuario no autenticado',
                    'code' => 401
                ];
            }

            // Verificar que sea administrador
            if ($currentUser['role'] !== 'admin') {
                return [
                    'success' => false,
                    'error' => 'FORBIDDEN',
                    'message' => 'Solo administradores pueden ver todas las solicitudes',
                    'code' => 403
                ];
            }

            // Obtener parámetros de paginación
            $limit = isset($_GET['limit']) ? (int) $_GET['limit'] : 50;
            $offset = isset($_GET['offset']) ? (int) $_GET['offset'] : 0;

            // Obtener todas las solicitudes
            $solicitudes = $this->solicitudModel->getAllSolicitudes($limit, $offset);

            return [
                'success' => true,
                'message' => 'Solicitudes obtenidas exitosamente',
                'data' => $solicitudes,
                'pagination' => [
                    'limit' => $limit,
                    'offset' => $offset,
                    'count' => count($solicitudes)
                ],
                'code' => 200
            ];

        } catch (Exception $e) {
            error_log("[SOLICITUD_CONTROLLER] Error en getAll(): " . $e->getMessage());
            return [
                'success' => false,
                'error' => 'INTERNAL_ERROR',
                'message' => 'Error interno del servidor',
                'code' => 500
            ];
        }
    }

    /**
     * ENDPOINT: DELETE /api/solicitudes/delete
     * =======================================
     * Elimina la solicitud del usuario autenticado
     * 
     * @return array Respuesta JSON con el resultado
     */
    public function delete(): array
    {
        try {
            // Obtener usuario actual
            $currentUser = AuthMiddleware::getCurrentUser();
            if (!$currentUser) {
                return [
                    'success' => false,
                    'error' => 'UNAUTHORIZED',
                    'message' => 'Usuario no autenticado',
                    'code' => 401
                ];
            }

            // Eliminar solicitud
            $deleted = $this->solicitudModel->delete($currentUser['id']);

            if ($deleted) {
                return [
                    'success' => true,
                    'message' => 'Solicitud eliminada exitosamente',
                    'code' => 200
                ];
            } else {
                return [
                    'success' => false,
                    'error' => 'DELETE_ERROR',
                    'message' => 'No se pudo eliminar la solicitud',
                    'code' => 400
                ];
            }

        } catch (Exception $e) {
            error_log("[SOLICITUD_CONTROLLER] Error en delete(): " . $e->getMessage());
            return [
                'success' => false,
                'error' => 'INTERNAL_ERROR',
                'message' => 'Error interno del servidor',
                'code' => 500
            ];
        }
    }

    /**
     * ENDPOINT: GET /api/solicitudes/catalogs
     * ======================================
     * Obtiene los catálogos estáticos para formularios
     * 
     * @return array Respuesta JSON con los catálogos
     */
    public function getCatalogs(): array
    {
        try {
            $catalogs = Solicitud::getCatalogs();

            return [
                'success' => true,
                'message' => 'Catálogos obtenidos exitosamente',
                'data' => $catalogs,
                'code' => 200
            ];

        } catch (Exception $e) {
            error_log("[SOLICITUD_CONTROLLER] Error en getCatalogs(): " . $e->getMessage());
            return [
                'success' => false,
                'error' => 'INTERNAL_ERROR',
                'message' => 'Error interno del servidor',
                'code' => 500
            ];
        }
    }

    /**
     * ENDPOINT: GET /api/solicitudes/stats
     * ===================================
     * Obtiene estadísticas de solicitudes (solo admin)
     * 
     * @return array Respuesta JSON con las estadísticas
     */
    public function stats(): array
    {
        try {
            // Obtener usuario actual
            $currentUser = AuthMiddleware::getCurrentUser();
            if (!$currentUser) {
                return [
                    'success' => false,
                    'error' => 'UNAUTHORIZED',
                    'message' => 'Usuario no autenticado',
                    'code' => 401
                ];
            }

            // Verificar que sea administrador
            if ($currentUser['role'] !== 'admin') {
                return [
                    'success' => false,
                    'error' => 'FORBIDDEN',
                    'message' => 'Solo administradores pueden ver estadísticas',
                    'code' => 403
                ];
            }

            // Obtener estadísticas
            $stats = $this->solicitudModel->getStats();

            return [
                'success' => true,
                'message' => 'Estadísticas obtenidas exitosamente',
                'data' => $stats,
                'code' => 200
            ];

        } catch (Exception $e) {
            error_log("[SOLICITUD_CONTROLLER] Error en stats(): " . $e->getMessage());
            return [
                'success' => false,
                'error' => 'INTERNAL_ERROR',
                'message' => 'Error interno del servidor',
                'code' => 500
            ];
        }
    }

    /**
     * ENDPOINT: POST /api/solicitudes/validate
     * =======================================
     * Valida una solicitud sin guardarla
     * 
     * @param array $requestData Datos de solicitud a validar
     * @return array Respuesta JSON con el resultado de validación
     */
    public function validate(array $requestData): array
    {
        try {
            // Obtener usuario actual
            $currentUser = AuthMiddleware::getCurrentUser();
            if (!$currentUser) {
                return [
                    'success' => false,
                    'error' => 'UNAUTHORIZED',
                    'message' => 'Usuario no autenticado',
                    'code' => 401
                ];
            }

            // Validar datos
            $this->solicitudModel->setData($requestData);
            $validation = $this->solicitudModel->validate();

            return [
                'success' => true,
                'message' => 'Validación completada',
                'data' => [
                    'valid' => $validation['valid'],
                    'errors' => $validation['errors']
                ],
                'code' => 200
            ];

        } catch (Exception $e) {
            error_log("[SOLICITUD_CONTROLLER] Error en validate(): " . $e->getMessage());
            return [
                'success' => false,
                'error' => 'INTERNAL_ERROR',
                'message' => 'Error interno del servidor',
                'code' => 500
            ];
        }
    }
}
