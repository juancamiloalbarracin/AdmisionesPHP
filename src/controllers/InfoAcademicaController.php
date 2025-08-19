<?php
namespace UDC\SistemaAdmisiones\Controllers;

use UDC\SistemaAdmisiones\Models\InfoAcademica;
use UDC\SistemaAdmisiones\Middleware\AuthMiddleware;
use UDC\SistemaAdmisiones\Utils\Database;
use Exception;

/**
 * CONTROLADOR: INFORMACIÓN ACADÉMICA
 * =================================
 * Este controlador maneja todas las operaciones relacionadas con
 * la información académica de los aspirantes
 * Migración de InfoAcademicaApiServlet.java
 * Compatible con InfoAcademica-Fixed.jsx
 */
class InfoAcademicaController
{
    private InfoAcademica $infoAcademicaModel;
    private Database $database;

    /**
     * Constructor
     */
    public function __construct()
    {
        $this->infoAcademicaModel = new InfoAcademica();
        $this->database = Database::getInstance();
    }

    /**
     * ENDPOINT: GET /api/info-academica/get
     * ====================================
     * Obtiene la información académica del usuario autenticado
     * 
     * @param array|null $user Datos del usuario autenticado (opcional para compatibilidad con sesiones PHP)
     * @return array Respuesta JSON con los datos académicos
     */
    public function get(array $user = null): array
    {
        try {
            // Obtener usuario actual (priorizar parámetro con id, luego middleware)
            $currentUser = null;
            if (is_array($user) && (!empty($user['id']) || !empty($user['user_id']))) {
                $currentUser = $user;
            } else {
                $currentUser = AuthMiddleware::getCurrentUser();
            }
            if (!$currentUser || (empty($currentUser['id']) && empty($currentUser['user_id']))) {
                return [
                    'success' => false,
                    'error' => 'UNAUTHORIZED',
                    'message' => 'Usuario no autenticado',
                    'code' => 401
                ];
            }

            // Obtener información académica
            $userId = (int)($currentUser['id'] ?? $currentUser['user_id']);
            $infoAcademica = $this->infoAcademicaModel->getByUserId($userId);

            if ($infoAcademica) {
                return [
                    'success' => true,
                    'message' => 'Información académica obtenida exitosamente',
                    'infoAcademica' => $infoAcademica,
                    'code' => 200
                ];
            } else {
                return [
                    'success' => true,
                    'message' => 'No se encontró información académica',
                    'infoAcademica' => null,
                    'code' => 200
                ];
            }

        } catch (Exception $e) {
            error_log("[INFO_ACADEMICA_CONTROLLER] Error en get(): " . $e->getMessage());
            return [
                'success' => false,
                'error' => 'INTERNAL_ERROR',
                'message' => 'Error interno del servidor',
                'code' => 500
            ];
        }
    }

    /**
     * ENDPOINT: POST /api/info-academica/save
     * ======================================
     * Guarda o actualiza la información académica del usuario autenticado
     * 
     * @param array $requestData Datos de información académica
     * @param array|null $user Datos del usuario autenticado (opcional para compatibilidad con sesiones PHP)
     * @return array Respuesta JSON con el resultado
     */
    public function save(array $requestData, array $user = null): array
    {
        try {
            // Obtener usuario actual (priorizar parámetro con id, luego middleware)
            $currentUser = null;
            if (is_array($user) && (!empty($user['id']) || !empty($user['user_id']))) {
                $currentUser = $user;
            } else {
                $currentUser = AuthMiddleware::getCurrentUser();
            }
            if (!$currentUser || (empty($currentUser['id']) && empty($currentUser['user_id']))) {
                return [
                    'success' => false,
                    'error' => 'UNAUTHORIZED',
                    'message' => 'Usuario no autenticado',
                    'code' => 401
                ];
            }

            // Validar que se enviaron datos
            if (empty($requestData)) {
                return [
                    'success' => false,
                    'error' => 'INVALID_DATA',
                    'message' => 'No se enviaron datos para guardar',
                    'code' => 400
                ];
            }

            // Establecer datos en el modelo ANTES de validar
            $this->infoAcademicaModel->setData($requestData);
            
            // Validar datos de entrada
            $validation = $this->infoAcademicaModel->validate();
            
            if (!$validation['valid']) {
                return [
                    'success' => false,
                    'error' => 'VALIDATION_ERROR',
                    'message' => 'Datos no válidos',
                    'errors' => $validation['errors'],
                    'code' => 400
                ];
            }

            // Guardar información académica
            $userId = (int)($currentUser['id'] ?? $currentUser['user_id']);
            $result = $this->infoAcademicaModel->save($userId);

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
            error_log("[INFO_ACADEMICA_CONTROLLER] Error en save(): " . $e->getMessage());
            return [
                'success' => false,
                'error' => 'INTERNAL_ERROR',
                'message' => 'Error interno del servidor',
                'code' => 500
            ];
        }
    }

    /**
     * ENDPOINT: GET /api/info-academica/completeness
     * =============================================
     * Verifica el porcentaje de completitud de información académica
     * 
     * @return array Respuesta JSON con estadísticas de completitud
     */
    public function completeness(): array
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

            // Obtener completitud
            $completeness = $this->infoAcademicaModel->checkCompleteness($currentUser['id']);

            return [
                'success' => true,
                'message' => 'Completitud verificada exitosamente',
                'data' => $completeness,
                'code' => 200
            ];

        } catch (Exception $e) {
            error_log("[INFO_ACADEMICA_CONTROLLER] Error en completeness(): " . $e->getMessage());
            return [
                'success' => false,
                'error' => 'INTERNAL_ERROR',
                'message' => 'Error interno del servidor',
                'code' => 500
            ];
        }
    }

    /**
     * ENDPOINT: DELETE /api/info-academica/delete
     * ==========================================
     * Elimina la información académica del usuario autenticado
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

            // Eliminar información académica
            $deleted = $this->infoAcademicaModel->delete($currentUser['id']);

            if ($deleted) {
                return [
                    'success' => true,
                    'message' => 'Información académica eliminada exitosamente',
                    'code' => 200
                ];
            } else {
                return [
                    'success' => false,
                    'error' => 'DELETE_ERROR',
                    'message' => 'No se pudo eliminar la información académica',
                    'code' => 400
                ];
            }

        } catch (Exception $e) {
            error_log("[INFO_ACADEMICA_CONTROLLER] Error en delete(): " . $e->getMessage());
            return [
                'success' => false,
                'error' => 'INTERNAL_ERROR',
                'message' => 'Error interno del servidor',
                'code' => 500
            ];
        }
    }

    /**
     * ENDPOINT: GET /api/info-academica/catalogs
     * =========================================
     * Obtiene los catálogos estáticos para formularios
     * 
     * @return array Respuesta JSON con los catálogos
     */
    public function getCatalogs(): array
    {
        try {
            $catalogs = InfoAcademica::getCatalogs();

            return [
                'success' => true,
                'message' => 'Catálogos obtenidos exitosamente',
                'data' => $catalogs,
                'code' => 200
            ];

        } catch (Exception $e) {
            error_log("[INFO_ACADEMICA_CONTROLLER] Error en getCatalogs(): " . $e->getMessage());
            return [
                'success' => false,
                'error' => 'INTERNAL_ERROR',
                'message' => 'Error interno del servidor',
                'code' => 500
            ];
        }
    }

    /**
     * ENDPOINT: GET /api/info-academica/stats
     * ======================================
     * Obtiene estadísticas de información académica (solo para administradores)
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
                    'message' => 'Acceso denegado. Solo administradores pueden ver estadísticas.',
                    'code' => 403
                ];
            }

            // Obtener estadísticas
            $stats = $this->infoAcademicaModel->getStats();

            return [
                'success' => true,
                'message' => 'Estadísticas obtenidas exitosamente',
                'data' => $stats,
                'code' => 200
            ];

        } catch (Exception $e) {
            error_log("[INFO_ACADEMICA_CONTROLLER] Error en stats(): " . $e->getMessage());
            return [
                'success' => false,
                'error' => 'INTERNAL_ERROR',
                'message' => 'Error interno del servidor',
                'code' => 500
            ];
        }
    }

    /**
     * ENDPOINT: GET /api/info-academica/validate-year
     * ==============================================
     * Valida un año de graduación específico
     * 
     * @param array $requestData Datos con el año a validar
     * @return array Respuesta JSON con el resultado de validación
     */
    public function validateYear(array $requestData): array
    {
        try {
            if (empty($requestData['year'])) {
                return [
                    'success' => false,
                    'error' => 'INVALID_DATA',
                    'message' => 'Año requerido para validación',
                    'code' => 400
                ];
            }

            $year = (int) $requestData['year'];
            $currentYear = (int) date('Y');

            $valid = true;
            $message = 'Año válido';

            if ($year < 1950) {
                $valid = false;
                $message = 'El año no puede ser anterior a 1950';
            } elseif ($year > ($currentYear + 2)) {
                $valid = false;
                $message = 'El año no puede ser más de 2 años en el futuro';
            } elseif ($year > $currentYear) {
                $message = 'Año futuro - Verifique que sea correcto';
            }

            return [
                'success' => true,
                'message' => 'Validación completada',
                'data' => [
                    'year' => $year,
                    'valid' => $valid,
                    'message' => $message
                ],
                'code' => 200
            ];

        } catch (Exception $e) {
            error_log("[INFO_ACADEMICA_CONTROLLER] Error en validateYear(): " . $e->getMessage());
            return [
                'success' => false,
                'error' => 'INTERNAL_ERROR',
                'message' => 'Error interno del servidor',
                'code' => 500
            ];
        }
    }

    /**
     * ENDPOINT: GET /api/info-academica/ranking
     * ========================================
     * Calcula el ranking relativo basado en promedio académico
     * 
     * @return array Respuesta JSON con información de ranking
     */
    public function ranking(): array
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

            // Obtener información académica del usuario
            $infoAcademica = $this->infoAcademicaModel->getByUserId($currentUser['id']);

            if (!$infoAcademica || empty($infoAcademica['promedio_academico'])) {
                return [
                    'success' => false,
                    'error' => 'NO_DATA',
                    'message' => 'No se encontró promedio académico para calcular ranking',
                    'code' => 400
                ];
            }

            $connection = $this->database->getConnection();

            // Calcular posición en ranking general
            $sql = "SELECT COUNT(*) + 1 as posicion 
                    FROM info_academica 
                    WHERE promedio_academico > ? 
                    AND promedio_academico IS NOT NULL";
            
            $stmt = $connection->prepare($sql);
            $stmt->execute([$infoAcademica['promedio_academico']]);
            $posicion = $stmt->fetchColumn();

            // Obtener total de registros con promedio
            $sql = "SELECT COUNT(*) as total 
                    FROM info_academica 
                    WHERE promedio_academico IS NOT NULL";
            
            $stmt = $connection->prepare($sql);
            $stmt->execute();
            $total = $stmt->fetchColumn();

            // Calcular percentil
            $percentil = $total > 0 ? (($total - $posicion + 1) / $total) * 100 : 0;

            return [
                'success' => true,
                'message' => 'Ranking calculado exitosamente',
                'data' => [
                    'promedio_usuario' => (float) $infoAcademica['promedio_academico'],
                    'posicion' => (int) $posicion,
                    'total_registros' => (int) $total,
                    'percentil' => round($percentil, 2),
                    'mejor_que_porcentaje' => round(($total - $posicion) / $total * 100, 2)
                ],
                'code' => 200
            ];

        } catch (Exception $e) {
            error_log("[INFO_ACADEMICA_CONTROLLER] Error en ranking(): " . $e->getMessage());
            return [
                'success' => false,
                'error' => 'INTERNAL_ERROR',
                'message' => 'Error interno del servidor',
                'code' => 500
            ];
        }
    }
}
