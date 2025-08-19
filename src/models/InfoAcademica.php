<?php
namespace UDC\SistemaAdmisiones\Models;

use UDC\SistemaAdmisiones\Utils\Database;
use DateTime;
use Exception;

/**
 * MODELO: INFORMACIÓN ACADÉMICA
 * ============================
 * Este modelo maneja toda la información académica de los aspirantes
 * Equivalente a InfoAcademicaModel.java del sistema original
 * Compatible con InfoAcademica-Fixed.jsx
 * 
 * Funcionalidades:
 * - Gestión de información de bachillerato
 * - Validación de años de graduación
 * - Cálculo de promedios académicos
 * - Verificación de completitud
 * - CRUD completo
 */
class InfoAcademica
{
    private Database $database;
    private int $userId;
    private array $data;

    /**
     * Tipos de bachillerato válidos
     */
    private const TIPOS_BACHILLERATO = [
        'ACADEMICO' => 'Académico',
        'TECNICO' => 'Técnico',
        'COMERCIAL' => 'Comercial',
        'PEDAGOGICO' => 'Pedagógico',
        'INDUSTRIAL' => 'Industrial',
        'AGROPECUARIO' => 'Agropecuario',
        'OTRO' => 'Otro'
    ];

    /**
     * Jornadas académicas válidas
     */
    private const JORNADAS = [
        'MANANA' => 'Mañana',
        'TARDE' => 'Tarde',
        'NOCHE' => 'Noche',
        'COMPLETA' => 'Completa',
        'SABATINA' => 'Sabatina',
        'DOMINICAL' => 'Dominical'
    ];

    /**
     * Carácter de la institución
     */
    private const CARACTER_INSTITUCION = [
        'PUBLICO' => 'Público',
        'PRIVADO' => 'Privado',
        'MIXTO' => 'Mixto'
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
     * Obtener información académica por ID de usuario
     */
    public function getByUserId(int $userId): ?array
    {
        try {
            $connection = $this->database->getConnection();
            $sql = "SELECT * FROM info_academica WHERE user_id = ? LIMIT 1";
            $stmt = $connection->prepare($sql);
            $stmt->execute([$userId]);
            
            $result = $stmt->fetch();
            if ($result) {
                // Convertir tipos de datos y formatear
                return $this->formatDataFromDB($result);
            }
            
            return null;
            
        } catch (Exception $e) {
            error_log("Error obteniendo info académica: " . $e->getMessage());
            throw new Exception("Error consultando información académica");
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
            'nombreInstitucion' => 'nombre_institucion',
            'ciudadInstitucion' => 'ciudad_institucion',
            'departamentoInstitucion' => 'departamento_institucion',
            'tipoBachillerato' => 'tipo_bachillerato',
            'caracterInstitucion' => 'caracter_institucion',
            'anoGraduacion' => 'ano_graduacion',
            'promedioAcademico' => 'promedio_academico',
            'puntajeIcfes' => 'puntaje_icfes',
            'posicionCurso' => 'posicion_curso',
            'totalEstudiantes' => 'total_estudiantes'
        ];

        // Mapear campos
        $mappedData = [];
        foreach ($data as $key => $value) {
            $dbKey = $fieldMapping[$key] ?? $key; // Usar mapeo o mantener key original
            $mappedData[$dbKey] = $value;
        }

        $this->data = $mappedData;
    }

    /**
     * Validar información académica
     */
    public function validate(): array
    {
        $errors = [];

        // Validar institución
        if (empty($this->data['nombre_institucion'])) {
            $errors[] = 'El nombre de la institución es requerido';
        } elseif (strlen($this->data['nombre_institucion']) < 3) {
            $errors[] = 'El nombre de la institución debe tener al menos 3 caracteres';
        } elseif (strlen($this->data['nombre_institucion']) > 200) {
            $errors[] = 'El nombre de la institución no puede exceder 200 caracteres';
        }

        // Validar ciudad de la institución
        if (empty($this->data['ciudad_institucion'])) {
            $errors[] = 'La ciudad de la institución es requerida';
        } elseif (strlen($this->data['ciudad_institucion']) < 2) {
            $errors[] = 'La ciudad debe tener al menos 2 caracteres';
        }

        // Validar departamento de la institución
        if (empty($this->data['departamento_institucion'])) {
            $errors[] = 'El departamento de la institución es requerido';
        }

        // Validar tipo de bachillerato
        if (empty($this->data['tipo_bachillerato'])) {
            $errors[] = 'El tipo de bachillerato es requerido';
        } elseif (!array_key_exists($this->data['tipo_bachillerato'], self::TIPOS_BACHILLERATO)) {
            $errors[] = 'Tipo de bachillerato no válido';
        }

        // Validar modalidad (si es diferente de OTRO)
        if (!empty($this->data['modalidad']) && strlen($this->data['modalidad']) > 100) {
            $errors[] = 'La modalidad no puede exceder 100 caracteres';
        }

        // Validar jornada
        if (empty($this->data['jornada'])) {
            $errors[] = 'La jornada académica es requerida';
        } elseif (!array_key_exists($this->data['jornada'], self::JORNADAS)) {
            $errors[] = 'Jornada académica no válida';
        }

        // Validar carácter de la institución
        if (empty($this->data['caracter_institucion'])) {
            $errors[] = 'El carácter de la institución es requerido';
        } elseif (!array_key_exists($this->data['caracter_institucion'], self::CARACTER_INSTITUCION)) {
            $errors[] = 'Carácter de institución no válido';
        }

        // Validar año de graduación
        if (empty($this->data['ano_graduacion'])) {
            $errors[] = 'El año de graduación es requerido';
        } else {
            $year = (int) $this->data['ano_graduacion'];
            $currentYear = (int) date('Y');
            
            if ($year < 1950) {
                $errors[] = 'El año de graduación no puede ser anterior a 1950';
            } elseif ($year > ($currentYear + 2)) {
                $errors[] = 'El año de graduación no puede ser más de 2 años en el futuro';
            }
        }

        // Validar promedio académico (opcional)
        if (!empty($this->data['promedio_academico'])) {
            $promedio = floatval($this->data['promedio_academico']);
            if ($promedio < 0.0 || $promedio > 5.0) {
                $errors[] = 'El promedio académico debe estar entre 0.0 y 5.0';
            }
        }

        // Validar puntaje ICFES/Saber 11 (opcional)
        if (!empty($this->data['puntaje_icfes'])) {
            $puntaje = intval($this->data['puntaje_icfes']);
            if ($puntaje < 0 || $puntaje > 500) {
                $errors[] = 'El puntaje ICFES debe estar entre 0 y 500';
            }
        }

        // Validar posición en el curso (opcional)
        if (!empty($this->data['posicion_curso'])) {
            $posicion = intval($this->data['posicion_curso']);
            if ($posicion < 1) {
                $errors[] = 'La posición en el curso debe ser mayor a 0';
            }
        }

        // Validar total de estudiantes en el curso (opcional)
        if (!empty($this->data['total_estudiantes'])) {
            $total = intval($this->data['total_estudiantes']);
            if ($total < 1) {
                $errors[] = 'El total de estudiantes debe ser mayor a 0';
            }
            
            // Validar que posición no sea mayor que total
            if (!empty($this->data['posicion_curso'])) {
                $posicion = intval($this->data['posicion_curso']);
                if ($posicion > $total) {
                    $errors[] = 'La posición en el curso no puede ser mayor al total de estudiantes';
                }
            }
        }

        return [
            'valid' => empty($errors),
            'errors' => $errors
        ];
    }

    /**
     * Guardar información académica
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

            // Verificar si ya existe información académica para este usuario
            $existing = $this->getByUserId($userId);

            // Preparar datos para guardar
            $dataToSave = $this->prepareDataForDB($this->data, $userId);

            if ($existing) {
                // Actualizar registro existente
                $sql = "UPDATE info_academica SET 
                        nombre_institucion = ?,
                        ciudad_institucion = ?,
                        departamento_institucion = ?,
                        tipo_bachillerato = ?,
                        modalidad = ?,
                        jornada = ?,
                        caracter_institucion = ?,
                        ano_graduacion = ?,
                        promedio_academico = ?,
                        puntaje_icfes = ?,
                        posicion_curso = ?,
                        total_estudiantes = ?,
                        observaciones = ?,
                        updated_at = NOW()
                        WHERE user_id = ?";

                $stmt = $connection->prepare($sql);
                $success = $stmt->execute([
                    $dataToSave['nombre_institucion'],
                    $dataToSave['ciudad_institucion'],
                    $dataToSave['departamento_institucion'],
                    $dataToSave['tipo_bachillerato'],
                    $dataToSave['modalidad'],
                    $dataToSave['jornada'],
                    $dataToSave['caracter_institucion'],
                    $dataToSave['ano_graduacion'],
                    $dataToSave['promedio_academico'],
                    $dataToSave['puntaje_icfes'],
                    $dataToSave['posicion_curso'],
                    $dataToSave['total_estudiantes'],
                    $dataToSave['observaciones'],
                    $userId
                ]);

                if ($success) {
                    return [
                        'success' => true,
                        'message' => 'Información académica actualizada exitosamente',
                        'data' => $this->getByUserId($userId)
                    ];
                }
            } else {
                // Crear nuevo registro
                $sql = "INSERT INTO info_academica (
                        user_id, nombre_institucion, ciudad_institucion, 
                        departamento_institucion, tipo_bachillerato, modalidad,
                        jornada, caracter_institucion, ano_graduacion,
                        promedio_academico, puntaje_icfes, posicion_curso,
                        total_estudiantes, observaciones, created_at, updated_at
                    ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW(), NOW())";

                $stmt = $connection->prepare($sql);
                $success = $stmt->execute([
                    $userId,
                    $dataToSave['nombre_institucion'],
                    $dataToSave['ciudad_institucion'],
                    $dataToSave['departamento_institucion'],
                    $dataToSave['tipo_bachillerato'],
                    $dataToSave['modalidad'],
                    $dataToSave['jornada'],
                    $dataToSave['caracter_institucion'],
                    $dataToSave['ano_graduacion'],
                    $dataToSave['promedio_academico'],
                    $dataToSave['puntaje_icfes'],
                    $dataToSave['posicion_curso'],
                    $dataToSave['total_estudiantes'],
                    $dataToSave['observaciones']
                ]);

                if ($success) {
                    return [
                        'success' => true,
                        'message' => 'Información académica guardada exitosamente',
                        'data' => $this->getByUserId($userId)
                    ];
                }
            }

            return [
                'success' => false,
                'message' => 'Error al guardar la información académica'
            ];

        } catch (Exception $e) {
            error_log("Error guardando info académica: " . $e->getMessage());
            return [
                'success' => false,
                'message' => 'Error interno al guardar información académica'
            ];
        }
    }

    /**
     * Calcular completitud de información académica
     */
    public function checkCompleteness(int $userId): array
    {
        try {
            $info = $this->getByUserId($userId);
            
            if (!$info) {
                return [
                    'percentage' => 0,
                    'completed_fields' => 0,
                    'total_fields' => 8,
                    'missing_fields' => [
                        'nombre_institucion', 'ciudad_institucion', 
                        'departamento_institucion', 'tipo_bachillerato',
                        'jornada', 'caracter_institucion', 'ano_graduacion',
                        'promedio_academico'
                    ]
                ];
            }

            // Campos requeridos para completitud
            $requiredFields = [
                'nombre_institucion' => 'Nombre de la institución',
                'ciudad_institucion' => 'Ciudad de la institución',
                'departamento_institucion' => 'Departamento de la institución',
                'tipo_bachillerato' => 'Tipo de bachillerato',
                'jornada' => 'Jornada académica',
                'caracter_institucion' => 'Carácter de la institución',
                'ano_graduacion' => 'Año de graduación',
                'promedio_academico' => 'Promedio académico'
            ];

            $completed = 0;
            $missing = [];

            foreach ($requiredFields as $field => $label) {
                if (!empty($info[$field])) {
                    $completed++;
                } else {
                    $missing[] = $field;
                }
            }

            $total = count($requiredFields);
            $percentage = ($completed / $total) * 100;

            return [
                'percentage' => round($percentage, 2),
                'completed_fields' => $completed,
                'total_fields' => $total,
                'missing_fields' => $missing
            ];

        } catch (Exception $e) {
            error_log("Error calculando completitud académica: " . $e->getMessage());
            return [
                'percentage' => 0,
                'completed_fields' => 0,
                'total_fields' => 8,
                'missing_fields' => ['error']
            ];
        }
    }

    /**
     * Eliminar información académica
     */
    public function delete(int $userId): bool
    {
        try {
            $connection = $this->database->getConnection();
            $sql = "DELETE FROM info_academica WHERE user_id = ?";
            $stmt = $connection->prepare($sql);
            
            return $stmt->execute([$userId]);
            
        } catch (Exception $e) {
            error_log("Error eliminando info académica: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Obtener catálogos para formularios
     */
    public static function getCatalogs(): array
    {
        return [
            'tipos_bachillerato' => self::TIPOS_BACHILLERATO,
            'jornadas' => self::JORNADAS,
            'caracter_institucion' => self::CARACTER_INSTITUCION,
            'departamentos' => self::getDepartamentos()
        ];
    }

    /**
     * Obtener estadísticas de información académica (para admin)
     */
    public function getStats(): array
    {
        try {
            $connection = $this->database->getConnection();
            
            // Estadísticas generales
            $sql = "SELECT 
                        COUNT(*) as total_registros,
                        AVG(promedio_academico) as promedio_general,
                        AVG(puntaje_icfes) as puntaje_icfes_promedio,
                        MIN(ano_graduacion) as ano_graduacion_min,
                        MAX(ano_graduacion) as ano_graduacion_max
                    FROM info_academica 
                    WHERE promedio_academico IS NOT NULL";
            
            $stmt = $connection->prepare($sql);
            $stmt->execute();
            $general = $stmt->fetch();

            // Estadísticas por tipo de bachillerato
            $sql = "SELECT 
                        tipo_bachillerato,
                        COUNT(*) as cantidad,
                        AVG(promedio_academico) as promedio_avg
                    FROM info_academica 
                    GROUP BY tipo_bachillerato 
                    ORDER BY cantidad DESC";
            
            $stmt = $connection->prepare($sql);
            $stmt->execute();
            $porTipo = $stmt->fetchAll();

            // Estadísticas por carácter de institución
            $sql = "SELECT 
                        caracter_institucion,
                        COUNT(*) as cantidad,
                        AVG(promedio_academico) as promedio_avg
                    FROM info_academica 
                    GROUP BY caracter_institucion 
                    ORDER BY cantidad DESC";
            
            $stmt = $connection->prepare($sql);
            $stmt->execute();
            $porCaracter = $stmt->fetchAll();

            return [
                'general' => $general,
                'por_tipo_bachillerato' => $porTipo,
                'por_caracter_institucion' => $porCaracter
            ];

        } catch (Exception $e) {
            error_log("Error obteniendo estadísticas académicas: " . $e->getMessage());
            return [
                'general' => [],
                'por_tipo_bachillerato' => [],
                'por_caracter_institucion' => []
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
            'nombre_institucion' => $data['nombre_institucion'],
            'ciudad_institucion' => $data['ciudad_institucion'],
            'departamento_institucion' => $data['departamento_institucion'],
            'tipo_bachillerato' => $data['tipo_bachillerato'],
            'modalidad' => $data['modalidad'],
            'jornada' => $data['jornada'],
            'caracter_institucion' => $data['caracter_institucion'],
            'ano_graduacion' => (int) $data['ano_graduacion'],
            'promedio_academico' => $data['promedio_academico'] ? (float) $data['promedio_academico'] : null,
            'puntaje_icfes' => $data['puntaje_icfes'] ? (int) $data['puntaje_icfes'] : null,
            'posicion_curso' => $data['posicion_curso'] ? (int) $data['posicion_curso'] : null,
            'total_estudiantes' => $data['total_estudiantes'] ? (int) $data['total_estudiantes'] : null,
            'observaciones' => $data['observaciones'],
            'created_at' => $data['created_at'],
            'updated_at' => $data['updated_at']
        ];
    }

    /**
     * Preparar datos para guardar en base de datos
     */
    private function prepareDataForDB(array $data, int $userId): array
    {
        return [
            'nombre_institucion' => trim($data['nombre_institucion'] ?? ''),
            'ciudad_institucion' => trim($data['ciudad_institucion'] ?? ''),
            'departamento_institucion' => trim($data['departamento_institucion'] ?? ''),
            'tipo_bachillerato' => trim($data['tipo_bachillerato'] ?? ''),
            'modalidad' => trim($data['modalidad'] ?? ''),
            'jornada' => trim($data['jornada'] ?? ''),
            'caracter_institucion' => trim($data['caracter_institucion'] ?? ''),
            'ano_graduacion' => (int) ($data['ano_graduacion'] ?? 0),
            'promedio_academico' => !empty($data['promedio_academico']) ? (float) $data['promedio_academico'] : null,
            'puntaje_icfes' => !empty($data['puntaje_icfes']) ? (int) $data['puntaje_icfes'] : null,
            'posicion_curso' => !empty($data['posicion_curso']) ? (int) $data['posicion_curso'] : null,
            'total_estudiantes' => !empty($data['total_estudiantes']) ? (int) $data['total_estudiantes'] : null,
            'observaciones' => trim($data['observaciones'] ?? '')
        ];
    }

    /**
     * Obtener lista de departamentos colombianos
     */
    private static function getDepartamentos(): array
    {
        return [
            'AMAZONAS' => 'Amazonas',
            'ANTIOQUIA' => 'Antioquia',
            'ARAUCA' => 'Arauca',
            'ATLANTICO' => 'Atlántico',
            'BOLIVAR' => 'Bolívar',
            'BOYACA' => 'Boyacá',
            'CALDAS' => 'Caldas',
            'CAQUETA' => 'Caquetá',
            'CASANARE' => 'Casanare',
            'CAUCA' => 'Cauca',
            'CESAR' => 'Cesar',
            'CHOCO' => 'Chocó',
            'CORDOBA' => 'Córdoba',
            'CUNDINAMARCA' => 'Cundinamarca',
            'GUAINIA' => 'Guainía',
            'GUAVIARE' => 'Guaviare',
            'HUILA' => 'Huila',
            'LA_GUAJIRA' => 'La Guajira',
            'MAGDALENA' => 'Magdalena',
            'META' => 'Meta',
            'NARINO' => 'Nariño',
            'NORTE_DE_SANTANDER' => 'Norte de Santander',
            'PUTUMAYO' => 'Putumayo',
            'QUINDIO' => 'Quindío',
            'RISARALDA' => 'Risaralda',
            'SAN_ANDRES_Y_PROVIDENCIA' => 'San Andrés y Providencia',
            'SANTANDER' => 'Santander',
            'SUCRE' => 'Sucre',
            'TOLIMA' => 'Tolima',
            'VALLE_DEL_CAUCA' => 'Valle del Cauca',
            'VAUPES' => 'Vaupés',
            'VICHADA' => 'Vichada'
        ];
    }
}
