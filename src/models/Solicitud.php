<?php
namespace UDC\SistemaAdmisiones\Models;

use UDC\SistemaAdmisiones\Utils\Database;
use DateTime;
use Exception;

/**
 * MODELO: SOLICITUDES DE ADMISIÓN
 * ==============================
 * Este modelo maneja todas las solicitudes de admisión de los aspirantes
 * Equivalente a SolicitudModel.java del sistema original
 * Compatible con Solicitudes-Fixed.jsx
 * 
 * Funcionalidades:
 * - Creación y gestión de solicitudes
 * - Estados del proceso de admisión
 * - Validación de documentos requeridos
 * - Historial de cambios de estado
 * - Cálculo de progreso de solicitud
 * - CRUD completo
 */
class Solicitud
{
    private Database $database;
    private int $userId;
    private array $data;

    /**
     * Estados válidos de solicitud
     */
    private const ESTADOS_SOLICITUD = [
        'BORRADOR' => 'Borrador - En construcción',
        'ENVIADA' => 'Enviada - En revisión',
        'EN_REVISION' => 'En revisión por admisiones',
        'DOCUMENTOS_PENDIENTES' => 'Documentos pendientes',
        'APROBADA' => 'Aprobada - Admitido',
        'RECHAZADA' => 'Rechazada - No admitido',
        'EN_LISTA_ESPERA' => 'En lista de espera',
        'CANCELADA' => 'Cancelada por el aspirante'
    ];

    /**
     * Programas académicos disponibles
     */
    private const PROGRAMAS_ACADEMICOS = [
        'MEDICINA' => 'Medicina',
        'ENFERMERIA' => 'Enfermería',
        'ODONTOLOGIA' => 'Odontología',
        'MEDICINA_VETERINARIA' => 'Medicina Veterinaria y Zootecnia',
        'INGENIERIA_SISTEMAS' => 'Ingeniería de Sistemas',
        'INGENIERIA_CIVIL' => 'Ingeniería Civil',
        'INGENIERIA_INDUSTRIAL' => 'Ingeniería Industrial',
        'INGENIERIA_AMBIENTAL' => 'Ingeniería Ambiental',
        'DERECHO' => 'Derecho',
        'ADMINISTRACION' => 'Administración en Finanzas y Negocios Internacionales',
        'CONTADURIA' => 'Contaduría Pública',
        'ECONOMIA' => 'Economía',
        'LICENCIATURA_MATEMATICAS' => 'Licenciatura en Matemáticas',
        'LICENCIATURA_LENGUAS' => 'Licenciatura en Lenguas Extranjeras',
        'LICENCIATURA_CIENCIAS_NATURALES' => 'Licenciatura en Ciencias Naturales',
        'BIOLOGIA' => 'Biología',
        'QUIMICA' => 'Química',
        'FISICA' => 'Física',
        'GEOGRAFIA' => 'Geografía'
    ];

    /**
     * Períodos académicos
     */
    private const PERIODOS_ACADEMICOS = [
        '2025-1' => 'Primer semestre 2025',
        '2025-2' => 'Segundo semestre 2025',
        '2026-1' => 'Primer semestre 2026',
        '2026-2' => 'Segundo semestre 2026'
    ];

    /**
     * Modalidades de ingreso
     */
    private const MODALIDADES_INGRESO = [
        'REGULAR' => 'Admisión regular',
        'ESPECIAL' => 'Admisión especial',
        'TRANSFERENCIA' => 'Transferencia externa',
        'REINGRESO' => 'Reingreso',
        'SEGUNDA_CARRERA' => 'Segunda carrera'
    ];

    /**
     * Documentos requeridos
     */
    private const DOCUMENTOS_REQUERIDOS = [
        'cedula_ciudadania' => 'Cédula de ciudadanía (escaneada)',
        'diploma_bachiller' => 'Diploma de bachiller',
        'certificado_notas' => 'Certificado de notas de bachillerato',
        'resultado_icfes' => 'Resultado pruebas ICFES/Saber 11',
        'foto_3x4' => 'Fotografía 3x4 fondo blanco',
        'certificado_eps' => 'Certificado de afiliación EPS',
        'recibo_pago' => 'Recibo de pago derechos de inscripción'
    ];

    /**
     * Constructor
     */
    public function __construct(int $userId = 0)
    {
        $this->database = Database::getInstance();
        $this->userId = $userId;
        $this->data = [];
    }

    /**
     * Obtener solicitud por ID de usuario
     */
    public function getByUserId(int $userId): ?array
    {
        try {
            $connection = $this->database->getConnection();
            $sql = "SELECT * FROM solicitudes WHERE user_id = ? ORDER BY created_at DESC LIMIT 1";
            $stmt = $connection->prepare($sql);
            $stmt->execute([$userId]);
            
            $result = $stmt->fetch();
            if ($result) {
                return $this->formatDataFromDB($result);
            }
            
            return null;
            
        } catch (Exception $e) {
            error_log("Error obteniendo solicitud: " . $e->getMessage());
            throw new Exception("Error consultando solicitud de admisión");
        }
    }

    /**
     * Obtener todas las solicitudes (para admin)
     */
    public function getAllSolicitudes(int $limit = 50, int $offset = 0): array
    {
        try {
            $connection = $this->database->getConnection();
            
            $sql = "SELECT s.*, u.email, u.nombres, u.apellidos 
                    FROM solicitudes s 
                    INNER JOIN usuarios u ON s.user_id = u.id 
                    ORDER BY s.created_at DESC 
                    LIMIT ? OFFSET ?";
            
            $stmt = $connection->prepare($sql);
            $stmt->execute([$limit, $offset]);
            
            $results = $stmt->fetchAll();
            
            $solicitudes = [];
            foreach ($results as $row) {
                $solicitudes[] = $this->formatDataFromDB($row);
            }
            
            return $solicitudes;
            
        } catch (Exception $e) {
            error_log("Error obteniendo todas las solicitudes: " . $e->getMessage());
            throw new Exception("Error consultando solicitudes");
        }
    }

    /**
     * Establecer datos para validación y guardado
     * Mapea nombres del frontend (camelCase) a nombres de BD (snake_case)
     */
    public function setData(array $data): void
    {
        // Mapeo de campos frontend -> base de datos
        $fieldMapping = [
            'programa' => 'programa_academico',
            'sede' => 'sede',
            'modalidad' => 'modalidad_ingreso',
            'jornadaSolicitud' => 'jornada',
            'observacionesSolicitud' => 'observaciones',
            'periodoAcademico' => 'periodo_academico'
        ];

        // Mapeo de valores de programa (nombre completo -> código)
        $programaMapping = [
            'Medicina' => 'MEDICINA',
            'Enfermería' => 'ENFERMERIA',
            'Odontología' => 'ODONTOLOGIA',
            'Medicina Veterinaria y Zootecnia' => 'MEDICINA_VETERINARIA',
            'Ingeniería de Sistemas' => 'INGENIERIA_SISTEMAS',
            'Ingeniería Civil' => 'INGENIERIA_CIVIL',
            'Ingeniería Industrial' => 'INGENIERIA_INDUSTRIAL',
            'Ingeniería Ambiental' => 'INGENIERIA_AMBIENTAL',
            'Derecho' => 'DERECHO',
            'Administración en Finanzas y Negocios Internacionales' => 'ADMINISTRACION',
            'Contaduría Pública' => 'CONTADURIA',
            'Economía' => 'ECONOMIA',
            'Licenciatura en Matemáticas' => 'LICENCIATURA_MATEMATICAS',
            'Licenciatura en Lenguas Extranjeras' => 'LICENCIATURA_LENGUAS',
            'Licenciatura en Ciencias Naturales' => 'LICENCIATURA_CIENCIAS_NATURALES',
            'Biología' => 'BIOLOGIA',
            'Química' => 'QUIMICA',
            'Física' => 'FISICA',
            'Geografía' => 'GEOGRAFIA'
        ];

        // Mapeo de modalidades (nombre -> código)
        $modalidadMapping = [
            'Presencial' => 'REGULAR',
            'Virtual' => 'ESPECIAL',
            'Semipresencial' => 'REGULAR',
            'Admisión regular' => 'REGULAR',
            'Admisión especial' => 'ESPECIAL',
            'Transferencia externa' => 'TRANSFERENCIA',
            'Reingreso' => 'REINGRESO',
            'Segunda carrera' => 'SEGUNDA_CARRERA'
        ];

        // Mapear campos
        $mappedData = [];
        foreach ($data as $key => $value) {
            $dbKey = $fieldMapping[$key] ?? $key; // Usar mapeo o mantener key original
            
            // Aplicar mapeos específicos de valores
            if ($key === 'programa' && isset($programaMapping[$value])) {
                $mappedData[$dbKey] = $programaMapping[$value];
            } elseif ($key === 'modalidad' && isset($modalidadMapping[$value])) {
                $mappedData[$dbKey] = $modalidadMapping[$value];
            } else {
                $mappedData[$dbKey] = $value;
            }
        }

        // Agregar valores por defecto si no están presentes
        if (!isset($mappedData['periodo_academico'])) {
            $mappedData['periodo_academico'] = '2025-1'; // Período por defecto
        }

        $this->data = $mappedData;
    }

    /**
     * Validar solicitud de admisión
     */
    public function validate(): array
    {
        $errors = [];

        // Validar programa académico
        if (empty($this->data['programa_academico'])) {
            $errors[] = 'El programa académico es requerido';
        } elseif (!array_key_exists($this->data['programa_academico'], self::PROGRAMAS_ACADEMICOS)) {
            $errors[] = 'Programa académico no válido';
        }

        // Validar período académico
        if (empty($this->data['periodo_academico'])) {
            $errors[] = 'El período académico es requerido';
        } elseif (!array_key_exists($this->data['periodo_academico'], self::PERIODOS_ACADEMICOS)) {
            $errors[] = 'Período académico no válido';
        }

        // Validar modalidad de ingreso
        if (empty($this->data['modalidad_ingreso'])) {
            $errors[] = 'La modalidad de ingreso es requerida';
        } elseif (!array_key_exists($this->data['modalidad_ingreso'], self::MODALIDADES_INGRESO)) {
            $errors[] = 'Modalidad de ingreso no válida';
        }

        // Validar sede (opcional pero si se envía debe ser válida)
        if (!empty($this->data['sede']) && strlen($this->data['sede']) > 100) {
            $errors[] = 'La sede no puede exceder 100 caracteres';
        }

        // Validar estado (si se envía)
        if (!empty($this->data['estado']) && !array_key_exists($this->data['estado'], self::ESTADOS_SOLICITUD)) {
            $errors[] = 'Estado de solicitud no válido';
        }

        // Validar documentos adjuntos (si se envían)
        if (!empty($this->data['documentos_adjuntos'])) {
            if (is_string($this->data['documentos_adjuntos'])) {
                $documentos = json_decode($this->data['documentos_adjuntos'], true);
                if (!$documentos) {
                    $errors[] = 'Formato de documentos adjuntos no válido';
                }
            } elseif (is_array($this->data['documentos_adjuntos'])) {
                // Validar que los documentos existan en la lista
                foreach (array_keys($this->data['documentos_adjuntos']) as $doc) {
                    if (!array_key_exists($doc, self::DOCUMENTOS_REQUERIDOS)) {
                        $errors[] = "Documento '$doc' no es un documento válido";
                    }
                }
            }
        }

        // Validar observaciones (opcional)
        if (!empty($this->data['observaciones']) && strlen($this->data['observaciones']) > 1000) {
            $errors[] = 'Las observaciones no pueden exceder 1000 caracteres';
        }

        // Validar que el usuario tenga información personal y académica completa
        if (!empty($this->data['validar_completitud']) && $this->data['validar_completitud'] === true) {
            $completitudErrors = $this->validateCompletitudRequerida($this->userId);
            $errors = array_merge($errors, $completitudErrors);
        }

        return [
            'valid' => empty($errors),
            'errors' => $errors
        ];
    }

    /**
     * Guardar solicitud de admisión
     */
    public function save(int $userId): array
    {
        try {
            // Validar datos primero
            $validation = $this->validate();
            if (!$validation['valid']) {
                return [
                    'success' => false,
                    'message' => 'Datos no válidos',
                    'errors' => $validation['errors']
                ];
            }

            $connection = $this->database->getConnection();

            // Verificar si ya existe una solicitud para este usuario
            $existing = $this->getByUserId($userId);

            // Preparar datos para guardar
            $dataToSave = $this->prepareDataForDB($this->data, $userId);

            if ($existing) {
                // Actualizar registro existente
                $sql = "UPDATE solicitudes SET 
                        programa_academico = ?,
                        periodo_academico = ?,
                        modalidad_ingreso = ?,
                        sede = ?,
                        estado = ?,
                        documentos_adjuntos = ?,
                        observaciones = ?,
                        updated_at = NOW()
                        WHERE user_id = ?";

                $stmt = $connection->prepare($sql);
                $success = $stmt->execute([
                    $dataToSave['programa_academico'],
                    $dataToSave['periodo_academico'],
                    $dataToSave['modalidad_ingreso'],
                    $dataToSave['sede'],
                    $dataToSave['estado'],
                    $dataToSave['documentos_adjuntos'],
                    $dataToSave['observaciones'],
                    $userId
                ]);

                if ($success) {
                    // Registrar cambio de estado si cambió
                    if ($existing['estado'] !== $dataToSave['estado']) {
                        $this->registrarCambioEstado($userId, $existing['estado'], $dataToSave['estado']);
                    }

                    return [
                        'success' => true,
                        'message' => 'Solicitud actualizada exitosamente',
                        'data' => $this->getByUserId($userId)
                    ];
                }
            } else {
                // Crear nueva solicitud
                $sql = "INSERT INTO solicitudes (
                        user_id, numero_solicitud, programa_academico, periodo_academico,
                        modalidad_ingreso, sede, estado, documentos_adjuntos,
                        observaciones, created_at, updated_at
                    ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, NOW(), NOW())";

                $numeroSolicitud = $this->generateNumeroSolicitud();

                $stmt = $connection->prepare($sql);
                $success = $stmt->execute([
                    $userId,
                    $numeroSolicitud,
                    $dataToSave['programa_academico'],
                    $dataToSave['periodo_academico'],
                    $dataToSave['modalidad_ingreso'],
                    $dataToSave['sede'],
                    $dataToSave['estado'],
                    $dataToSave['documentos_adjuntos'],
                    $dataToSave['observaciones']
                ]);

                if ($success) {
                    // Registrar creación de solicitud
                    $this->registrarCambioEstado($userId, null, $dataToSave['estado'], 'Solicitud creada');

                    return [
                        'success' => true,
                        'message' => 'Solicitud creada exitosamente',
                        'data' => $this->getByUserId($userId)
                    ];
                }
            }

            return [
                'success' => false,
                'message' => 'Error al guardar la solicitud'
            ];

        } catch (Exception $e) {
            error_log("Error guardando solicitud: " . $e->getMessage());
            return [
                'success' => false,
                'message' => 'Error interno al guardar solicitud'
            ];
        }
    }

    /**
     * Cambiar estado de solicitud
     */
    public function cambiarEstado(int $userId, string $nuevoEstado, string $observacion = ''): array
    {
        try {
            if (!array_key_exists($nuevoEstado, self::ESTADOS_SOLICITUD)) {
                return [
                    'success' => false,
                    'message' => 'Estado no válido'
                ];
            }

            $solicitud = $this->getByUserId($userId);
            if (!$solicitud) {
                return [
                    'success' => false,
                    'message' => 'Solicitud no encontrada'
                ];
            }

            $estadoAnterior = $solicitud['estado'];

            $connection = $this->database->getConnection();
            $sql = "UPDATE solicitudes SET estado = ?, updated_at = NOW() WHERE user_id = ?";
            $stmt = $connection->prepare($sql);
            $success = $stmt->execute([$nuevoEstado, $userId]);

            if ($success) {
                // Registrar cambio de estado
                $this->registrarCambioEstado($userId, $estadoAnterior, $nuevoEstado, $observacion);

                return [
                    'success' => true,
                    'message' => 'Estado actualizado exitosamente',
                    'data' => $this->getByUserId($userId)
                ];
            }

            return [
                'success' => false,
                'message' => 'Error al actualizar el estado'
            ];

        } catch (Exception $e) {
            error_log("Error cambiando estado solicitud: " . $e->getMessage());
            return [
                'success' => false,
                'message' => 'Error interno al cambiar estado'
            ];
        }
    }

    /**
     * Calcular progreso de solicitud
     */
    public function calcularProgreso(int $userId): array
    {
        try {
            $solicitud = $this->getByUserId($userId);
            
            if (!$solicitud) {
                return [
                    'porcentaje' => 0,
                    'pasos_completados' => 0,
                    'pasos_totales' => 5,
                    'siguiente_paso' => 'Crear solicitud'
                ];
            }

            $pasos = [
                'solicitud_creada' => !empty($solicitud['id']),
                'programa_seleccionado' => !empty($solicitud['programa_academico']),
                'documentos_adjuntos' => $this->verificarDocumentosCompletos($solicitud),
                'informacion_personal_completa' => $this->verificarInfoPersonalCompleta($userId),
                'informacion_academica_completa' => $this->verificarInfoAcademicaCompleta($userId)
            ];

            $completados = array_sum($pasos);
            $total = count($pasos);
            $porcentaje = ($completados / $total) * 100;

            // Determinar siguiente paso
            $siguientePaso = 'Solicitud completa';
            if (!$pasos['solicitud_creada']) {
                $siguientePaso = 'Crear solicitud';
            } elseif (!$pasos['programa_seleccionado']) {
                $siguientePaso = 'Seleccionar programa académico';
            } elseif (!$pasos['informacion_personal_completa']) {
                $siguientePaso = 'Completar información personal';
            } elseif (!$pasos['informacion_academica_completa']) {
                $siguientePaso = 'Completar información académica';
            } elseif (!$pasos['documentos_adjuntos']) {
                $siguientePaso = 'Adjuntar documentos requeridos';
            }

            return [
                'porcentaje' => round($porcentaje, 2),
                'pasos_completados' => $completados,
                'pasos_totales' => $total,
                'siguiente_paso' => $siguientePaso,
                'detalle_pasos' => $pasos
            ];

        } catch (Exception $e) {
            error_log("Error calculando progreso: " . $e->getMessage());
            return [
                'porcentaje' => 0,
                'pasos_completados' => 0,
                'pasos_totales' => 5,
                'siguiente_paso' => 'Error calculando progreso'
            ];
        }
    }

    /**
     * Eliminar solicitud
     */
    public function delete(int $userId): bool
    {
        try {
            $connection = $this->database->getConnection();
            $sql = "DELETE FROM solicitudes WHERE user_id = ?";
            $stmt = $connection->prepare($sql);
            
            return $stmt->execute([$userId]);
            
        } catch (Exception $e) {
            error_log("Error eliminando solicitud: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Obtener catálogos para formularios
     */
    public static function getCatalogs(): array
    {
        return [
            'programas_academicos' => self::PROGRAMAS_ACADEMICOS,
            'periodos_academicos' => self::PERIODOS_ACADEMICOS,
            'modalidades_ingreso' => self::MODALIDADES_INGRESO,
            'estados_solicitud' => self::ESTADOS_SOLICITUD,
            'documentos_requeridos' => self::DOCUMENTOS_REQUERIDOS
        ];
    }

    /**
     * Obtener estadísticas (para admin)
     */
    public function getStats(): array
    {
        try {
            $connection = $this->database->getConnection();
            
            // Estadísticas generales
            $sql = "SELECT 
                        COUNT(*) as total_solicitudes,
                        COUNT(CASE WHEN estado = 'ENVIADA' THEN 1 END) as enviadas,
                        COUNT(CASE WHEN estado = 'APROBADA' THEN 1 END) as aprobadas,
                        COUNT(CASE WHEN estado = 'RECHAZADA' THEN 1 END) as rechazadas,
                        COUNT(CASE WHEN estado = 'BORRADOR' THEN 1 END) as borradores
                    FROM solicitudes";
            
            $stmt = $connection->prepare($sql);
            $stmt->execute();
            $general = $stmt->fetch();

            // Por programa académico
            $sql = "SELECT 
                        programa_academico,
                        COUNT(*) as cantidad
                    FROM solicitudes 
                    GROUP BY programa_academico 
                    ORDER BY cantidad DESC";
            
            $stmt = $connection->prepare($sql);
            $stmt->execute();
            $porPrograma = $stmt->fetchAll();

            // Por período académico
            $sql = "SELECT 
                        periodo_academico,
                        COUNT(*) as cantidad
                    FROM solicitudes 
                    GROUP BY periodo_academico 
                    ORDER BY cantidad DESC";
            
            $stmt = $connection->prepare($sql);
            $stmt->execute();
            $porPeriodo = $stmt->fetchAll();

            return [
                'general' => $general,
                'por_programa' => $porPrograma,
                'por_periodo' => $porPeriodo
            ];

        } catch (Exception $e) {
            error_log("Error obteniendo estadísticas solicitudes: " . $e->getMessage());
            return [
                'general' => [],
                'por_programa' => [],
                'por_periodo' => []
            ];
        }
    }

    /**
     * Formatear datos desde base de datos
     */
    private function formatDataFromDB(array $data): array
    {
        return [
            'id' => (int) $data['id'],
            'user_id' => (int) $data['user_id'],
            'numero_solicitud' => $data['numero_solicitud'],
            'programa_academico' => $data['programa_academico'],
            'periodo_academico' => $data['periodo_academico'],
            'modalidad_ingreso' => $data['modalidad_ingreso'],
            'sede' => $data['sede'],
            'estado' => $data['estado'],
            'documentos_adjuntos' => $data['documentos_adjuntos'] ? json_decode($data['documentos_adjuntos'], true) : [],
            'observaciones' => $data['observaciones'],
            'created_at' => $data['created_at'],
            'updated_at' => $data['updated_at'],
            // Campos adicionales si están disponibles
            'email' => $data['email'] ?? null,
            'nombres' => $data['nombres'] ?? null,
            'apellidos' => $data['apellidos'] ?? null
        ];
    }

    /**
     * Preparar datos para guardar en base de datos
     */
    private function prepareDataForDB(array $data, int $userId): array
    {
        return [
            'programa_academico' => trim($data['programa_academico'] ?? ''),
            'periodo_academico' => trim($data['periodo_academico'] ?? ''),
            'modalidad_ingreso' => trim($data['modalidad_ingreso'] ?? ''),
            'sede' => trim($data['sede'] ?? 'Montería'),
            'estado' => trim($data['estado'] ?? 'BORRADOR'),
            'documentos_adjuntos' => !empty($data['documentos_adjuntos']) ? 
                (is_array($data['documentos_adjuntos']) ? 
                    json_encode($data['documentos_adjuntos']) : 
                    $data['documentos_adjuntos']) : 
                null,
            'observaciones' => trim($data['observaciones'] ?? '')
        ];
    }

    /**
     * Generar número de solicitud único
     */
    private function generateNumeroSolicitud(): string
    {
        $year = date('Y');
        $timestamp = date('md') . date('His');
        return "UDC-{$year}-{$timestamp}";
    }

    /**
     * Registrar cambio de estado
     */
    private function registrarCambioEstado(int $userId, ?string $estadoAnterior, string $nuevoEstado, string $observacion = ''): void
    {
        try {
            $connection = $this->database->getConnection();
            $sql = "INSERT INTO historial_estados (user_id, estado_anterior, nuevo_estado, observacion, created_at) 
                    VALUES (?, ?, ?, ?, NOW())";
            
            $stmt = $connection->prepare($sql);
            $stmt->execute([$userId, $estadoAnterior, $nuevoEstado, $observacion]);
            
        } catch (Exception $e) {
            error_log("Error registrando cambio de estado: " . $e->getMessage());
        }
    }

    /**
     * Validar completitud requerida
     */
    private function validateCompletitudRequerida(int $userId): array
    {
        $errors = [];
        
        // Verificar información personal
        if (!$this->verificarInfoPersonalCompleta($userId)) {
            $errors[] = 'Debe completar la información personal antes de enviar la solicitud';
        }

        // Verificar información académica
        if (!$this->verificarInfoAcademicaCompleta($userId)) {
            $errors[] = 'Debe completar la información académica antes de enviar la solicitud';
        }

        return $errors;
    }

    /**
     * Verificar si información personal está completa
     */
    private function verificarInfoPersonalCompleta(int $userId): bool
    {
        try {
            $connection = $this->database->getConnection();
            $sql = "SELECT COUNT(*) FROM info_personal 
                    WHERE user_id = ? 
                    AND nombres IS NOT NULL 
                    AND apellidos IS NOT NULL 
                    AND tipo_documento IS NOT NULL 
                    AND numero_documento IS NOT NULL";
            
            $stmt = $connection->prepare($sql);
            $stmt->execute([$userId]);
            
            return $stmt->fetchColumn() > 0;
            
        } catch (Exception $e) {
            return false;
        }
    }

    /**
     * Verificar si información académica está completa
     */
    private function verificarInfoAcademicaCompleta(int $userId): bool
    {
        try {
            $connection = $this->database->getConnection();
            $sql = "SELECT COUNT(*) FROM info_academica 
                    WHERE user_id = ? 
                    AND nombre_institucion IS NOT NULL 
                    AND tipo_bachillerato IS NOT NULL 
                    AND ano_graduacion IS NOT NULL";
            
            $stmt = $connection->prepare($sql);
            $stmt->execute([$userId]);
            
            return $stmt->fetchColumn() > 0;
            
        } catch (Exception $e) {
            return false;
        }
    }

    /**
     * Verificar si documentos están completos
     */
    private function verificarDocumentosCompletos(array $solicitud): bool
    {
        if (empty($solicitud['documentos_adjuntos'])) {
            return false;
        }

        $documentosAdjuntos = is_array($solicitud['documentos_adjuntos']) ? 
            $solicitud['documentos_adjuntos'] : 
            json_decode($solicitud['documentos_adjuntos'], true);

        if (!$documentosAdjuntos) {
            return false;
        }

        // Documentos mínimos requeridos
        $documentosMinimos = ['cedula_ciudadania', 'diploma_bachiller', 'resultado_icfes'];
        
        foreach ($documentosMinimos as $doc) {
            if (!isset($documentosAdjuntos[$doc]) || empty($documentosAdjuntos[$doc])) {
                return false;
            }
        }

        return true;
    }
}
