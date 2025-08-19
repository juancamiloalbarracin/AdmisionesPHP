<?php
/**
 * MODELO DE INFORMACIÓN PERSONAL
 * ==============================
 * Este modelo maneja toda la información personal de los usuarios
 * Compatible con el sistema Java existente
 * Migración de la lógica de InfoPersonalApiServlet.java
 */

namespace UDC\SistemaAdmisiones\Models;

use UDC\SistemaAdmisiones\Utils\Database;
use DateTime;
use Exception;

class InfoPersonal
{
    /**
     * Instancia de base de datos
     */
    private Database $database;

    /**
     * ID del usuario propietario
     */
    private int $userId;

    /**
     * Datos de información personal
     */
    private array $data = [];

    /**
     * Constructor
     */
    public function __construct(int $userId = 0)
    {
        $this->database = Database::getInstance();
        $this->userId = $userId;
    }

    /**
     * Obtener información personal por ID de usuario
     * 
     * @param int $userId ID del usuario
     * @return array|null Datos de información personal
     */
    public function getByUserId(int $userId): ?array
    {
        try {
            $sql = "SELECT ip.*, u.email, u.nombres, u.apellidos 
                    FROM info_personal ip 
                    INNER JOIN usuarios u ON ip.usuario_id = u.id 
                    WHERE ip.usuario_id = :user_id";

            $result = $this->database->fetch($sql, [':user_id' => $userId]);
            
            if ($result) {
                // Formatear datos para compatibilidad con React
                return $this->formatPersonalData($result);
            }

            return null;

        } catch (Exception $e) {
            error_log("[INFO_PERSONAL ERROR] Error obteniendo datos: " . $e->getMessage());
            return null;
        }
    }

    /**
     * Guardar o actualizar información personal
     * 
     * @param int $userId ID del usuario
     * @param array $data Datos a guardar
     * @return bool true si se guardó exitosamente
     */
    public function save(int $userId, array $data): bool
    {
        try {
            // Verificar si ya existe información para este usuario
            $existing = $this->getByUserId($userId);

            if ($existing) {
                return $this->update($userId, $data);
            } else {
                return $this->create($userId, $data);
            }

        } catch (Exception $e) {
            error_log("[INFO_PERSONAL ERROR] Error guardando datos: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Crear nueva información personal
     * 
     * @param int $userId ID del usuario
     * @param array $data Datos a crear
     * @return bool true si se creó exitosamente
     */
    private function create(int $userId, array $data): bool
    {
        try {
            $sql = "INSERT INTO info_personal (
                        usuario_id, email, nombres, apellidos, tipo_documento, numero_documento,
                        fecha_nacimiento, lugar_nacimiento, genero, estado_civil, direccion,
                        telefono, email_alternativo, estrato_socioeconomico, fecha_creacion
                    ) VALUES (
                        :usuario_id, :email, :nombres, :apellidos, :tipo_documento, :numero_documento,
                        :fecha_nacimiento, :lugar_nacimiento, :genero, :estado_civil, :direccion,
                        :telefono, :email_alternativo, :estrato_socioeconomico, NOW()
                    )";

            $params = $this->prepareParams($userId, $data);
            
            $result = $this->database->execute($sql, $params);
            
            if ($result) {
                error_log("[INFO_PERSONAL] Información personal creada para usuario: {$userId}");
            }
            
            return $result;

        } catch (Exception $e) {
            error_log("[INFO_PERSONAL ERROR] Error creando información: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Actualizar información personal existente
     * 
     * @param int $userId ID del usuario
     * @param array $data Datos a actualizar
     * @return bool true si se actualizó exitosamente
     */
    private function update(int $userId, array $data): bool
    {
        try {
            $sql = "UPDATE info_personal SET
                        email = :email,
                        nombres = :nombres,
                        apellidos = :apellidos,
                        tipo_documento = :tipo_documento,
                        numero_documento = :numero_documento,
                        fecha_nacimiento = :fecha_nacimiento,
                        lugar_nacimiento = :lugar_nacimiento,
                        genero = :genero,
                        estado_civil = :estado_civil,
                        direccion = :direccion,
                        telefono = :telefono,
                        email_alternativo = :email_alternativo,
                        estrato_socioeconomico = :estrato_socioeconomico,
                        fecha_actualizacion = NOW()
                    WHERE usuario_id = :usuario_id";

            $params = $this->prepareParams($userId, $data);
            
            $result = $this->database->execute($sql, $params);
            
            if ($result) {
                error_log("[INFO_PERSONAL] Información personal actualizada para usuario: {$userId}");
            }
            
            return $result;

        } catch (Exception $e) {
            error_log("[INFO_PERSONAL ERROR] Error actualizando información: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Preparar parámetros para consulta SQL
     * 
     * @param int $userId ID del usuario
     * @param array $data Datos del usuario
     * @return array Parámetros preparados
     */
    private function prepareParams(int $userId, array $data): array
    {
        return [
            ':usuario_id' => $userId,
            ':email' => $data['email'] ?? '',
            ':nombres' => trim($data['nombres'] ?? ''),
            ':apellidos' => trim($data['apellidos'] ?? ''),
            ':tipo_documento' => $data['tipoDocumento'] ?? '',
            ':numero_documento' => $data['numeroDocumento'] ?? '',
            ':fecha_nacimiento' => $this->formatDate($data['fechaNacimiento'] ?? ''),
            ':lugar_nacimiento' => trim($data['ciudadNacimiento'] ?? $data['ciudad'] ?? ''), // Mapear ciudad a lugar_nacimiento
            ':genero' => $data['genero'] ?? '',
            ':estado_civil' => $data['estadoCivil'] ?? '',
            ':direccion' => trim($data['direccion'] ?? ''),
            ':telefono' => $data['celular'] ?? $data['telefono'] ?? '', // Mapear celular a telefono
            ':email_alternativo' => $data['emailAlternativo'] ?? '',
            ':estrato_socioeconomico' => $data['estratoSocioeconomico'] ?? ''
        ];
    }

    /**
     * Formatear fecha para MySQL
     * 
     * @param string $date Fecha en formato string
     * @return string|null Fecha formateada para MySQL
     */
    private function formatDate(string $date): ?string
    {
        if (empty($date)) {
            return null;
        }

        try {
            // Intentar varios formatos de fecha
            $formats = ['Y-m-d', 'd/m/Y', 'd-m-Y', 'Y-m-d H:i:s'];
            
            foreach ($formats as $format) {
                $dateTime = DateTime::createFromFormat($format, $date);
                if ($dateTime !== false) {
                    return $dateTime->format('Y-m-d');
                }
            }

            return null;

        } catch (Exception $e) {
            error_log("[INFO_PERSONAL ERROR] Error formateando fecha: " . $e->getMessage());
            return null;
        }
    }

    /**
     * Formatear datos para compatibilidad con React
     * 
     * @param array $data Datos de la base de datos
     * @return array Datos formateados
     */
    private function formatPersonalData(array $data): array
    {
        return [
            'id' => (int)$data['id'],
            'usuarioId' => (int)$data['usuario_id'],
            'email' => $data['email'] ?? '',
            'tipoDocumento' => $data['tipo_documento'] ?? '',
            'numeroDocumento' => $data['numero_documento'] ?? '',
            'nombres' => $data['nombres'] ?? '',
            'apellidos' => $data['apellidos'] ?? '',
            'nombreCompleto' => trim(($data['nombres'] ?? '') . ' ' . ($data['apellidos'] ?? '')),
            'fechaNacimiento' => $data['fecha_nacimiento'] ? date('Y-m-d', strtotime($data['fecha_nacimiento'])) : '',
            'lugarNacimiento' => $data['lugar_nacimiento'] ?? '',
            'genero' => $data['genero'] ?? '',
            'estadoCivil' => $data['estado_civil'] ?? '',
            'direccion' => $data['direccion'] ?? '',
            'telefono' => $data['telefono'] ?? '', // Este campo sí existe en la tabla
            'emailAlternativo' => $data['email_alternativo'] ?? '',
            'estratoSocioeconomico' => $data['estrato_socioeconomico'] ?? '',
            'fechaCreacion' => $data['fecha_creacion'] ?? '',
            'fechaActualizacion' => $data['fecha_actualizacion'] ?? null,
            // Mapeo de campos que no existen en la tabla pero el frontend espera
            'celular' => $data['telefono'] ?? '', // Mapear telefono a celular para frontend
            'ciudad' => $data['lugar_nacimiento'] ?? '', // Mapear lugar_nacimiento a ciudad
            'departamento' => '', // Campo no disponible en la tabla
            'pais' => 'Colombia', // Valor por defecto
            'eps' => '', // Campo no disponible en la tabla
            'tipoSangre' => '', // Campo no disponible en la tabla  
            'discapacidad' => false, // Campo no disponible en la tabla
            'descripcionDiscapacidad' => '', // Campo no disponible en la tabla
            'poblacionEspecial' => '' // Campo no disponible en la tabla
        ];
    }

    /**
     * Validar datos de información personal
     * 
     * @param array $data Datos a validar
     * @return array Resultado de validación
     */
    public function validate(array $data): array
    {
        $errors = [];

        // Validar nombres (requerido)
        if (empty(trim($data['nombres'] ?? ''))) {
            $errors['nombres'] = 'Los nombres son requeridos';
        }

        // Validar apellidos (requerido)
        if (empty(trim($data['apellidos'] ?? ''))) {
            $errors['apellidos'] = 'Los apellidos son requeridos';
        }

        // Validar tipo de documento
        $tiposDocumento = ['CC', 'TI', 'CE', 'PA', 'RC'];
        if (empty($data['tipoDocumento']) || !in_array($data['tipoDocumento'], $tiposDocumento)) {
            $errors['tipoDocumento'] = 'Tipo de documento no válido';
        }

        // Validar número de documento
        if (empty($data['numeroDocumento'])) {
            $errors['numeroDocumento'] = 'Número de documento es requerido';
        } elseif (!preg_match('/^[0-9]+$/', $data['numeroDocumento'])) {
            $errors['numeroDocumento'] = 'Número de documento debe contener solo números';
        }

        // Validar fecha de nacimiento
        if (!empty($data['fechaNacimiento'])) {
            $fechaNacimiento = $this->formatDate($data['fechaNacimiento']);
            if (!$fechaNacimiento) {
                $errors['fechaNacimiento'] = 'Fecha de nacimiento no válida';
            } else {
                // Verificar que no sea una fecha futura
                $fechaActual = new DateTime();
                $fechaNac = new DateTime($fechaNacimiento);
                if ($fechaNac > $fechaActual) {
                    $errors['fechaNacimiento'] = 'Fecha de nacimiento no puede ser futura';
                }
                
                // Verificar edad mínima (ejemplo: 14 años)
                $edad = $fechaActual->diff($fechaNac)->y;
                if ($edad < 14) {
                    $errors['fechaNacimiento'] = 'Edad mínima requerida: 14 años';
                }
            }
        }

        // Validar género
        $generos = ['M', 'F', 'Masculino', 'Femenino', 'Otro'];
        if (!empty($data['genero']) && !in_array($data['genero'], $generos)) {
            $errors['genero'] = 'Género no válido';
        }

        // Validar estado civil
        $estadosCiviles = ['Soltero', 'Casado', 'Union_Libre', 'Divorciado', 'Viudo', 'Separado'];
        if (!empty($data['estadoCivil']) && !in_array($data['estadoCivil'], $estadosCiviles)) {
            $errors['estadoCivil'] = 'Estado civil no válido';
        }

        // Validar email alternativo
        if (!empty($data['emailAlternativo']) && !filter_var($data['emailAlternativo'], FILTER_VALIDATE_EMAIL)) {
            $errors['emailAlternativo'] = 'Email alternativo no válido';
        }

        // Validar celular (formato colombiano)
        if (!empty($data['celular']) && !preg_match('/^[3][0-9]{9}$/', $data['celular'])) {
            $errors['celular'] = 'Celular debe tener formato colombiano (3XXXXXXXXX)';
        }

        // Validar estrato socioeconómico
        if (!empty($data['estratoSocioeconomico'])) {
            $estrato = (int)$data['estratoSocioeconomico'];
            if ($estrato < 1 || $estrato > 6) {
                $errors['estratoSocioeconomico'] = 'Estrato debe estar entre 1 y 6';
            }
        }

        // Validar tipo de sangre
        $tiposSangre = ['O+', 'O-', 'A+', 'A-', 'B+', 'B-', 'AB+', 'AB-'];
        if (!empty($data['tipoSangre']) && !in_array($data['tipoSangre'], $tiposSangre)) {
            $errors['tipoSangre'] = 'Tipo de sangre no válido';
        }

        return [
            'valid' => empty($errors),
            'errors' => $errors,
            'message' => empty($errors) ? 'Datos válidos' : 'Datos de información personal inválidos'
        ];
    }

    /**
     * Verificar completitud de información personal
     * 
     * @param int $userId ID del usuario
     * @return array Estado de completitud
     */
    public function checkCompleteness(int $userId): array
    {
        $data = $this->getByUserId($userId);
        
        if (!$data) {
            return [
                'complete' => false,
                'percentage' => 0,
                'missing_fields' => ['Todos los campos'],
                'total_fields' => 15,
                'completed_fields' => 0
            ];
        }

        // Campos requeridos
        $requiredFields = [
            'nombres', 'apellidos', 'tipoDocumento', 'numeroDocumento',
            'fechaNacimiento', 'genero', 'celular', 'direccion', 
            'ciudad', 'departamento', 'pais'
        ];

        $completedFields = 0;
        $missingFields = [];

        foreach ($requiredFields as $field) {
            if (!empty($data[$field])) {
                $completedFields++;
            } else {
                $missingFields[] = $field;
            }
        }

        $percentage = ($completedFields / count($requiredFields)) * 100;

        return [
            'complete' => $percentage >= 90, // 90% o más se considera completo
            'percentage' => round($percentage, 1),
            'missing_fields' => $missingFields,
            'total_fields' => count($requiredFields),
            'completed_fields' => $completedFields
        ];
    }

    /**
     * Obtener estadísticas de información personal (para admin)
     * 
     * @return array Estadísticas
     */
    public function getStats(): array
    {
        try {
            $stats = [];

            // Total de registros
            $sql = "SELECT COUNT(*) as total FROM info_personal";
            $stats['total_registros'] = (int)$this->database->fetchColumn($sql);

            // Registros por género
            $sql = "SELECT genero, COUNT(*) as total FROM info_personal 
                    WHERE genero IS NOT NULL AND genero != '' 
                    GROUP BY genero";
            $result = $this->database->fetchAll($sql);
            $stats['por_genero'] = [];
            foreach ($result as $row) {
                $stats['por_genero'][$row['genero']] = (int)$row['total'];
            }

            // Registros por departamento
            $sql = "SELECT departamento, COUNT(*) as total FROM info_personal 
                    WHERE departamento IS NOT NULL AND departamento != '' 
                    GROUP BY departamento 
                    ORDER BY total DESC 
                    LIMIT 10";
            $result = $this->database->fetchAll($sql);
            $stats['por_departamento'] = [];
            foreach ($result as $row) {
                $stats['por_departamento'][$row['departamento']] = (int)$row['total'];
            }

            return $stats;

        } catch (Exception $e) {
            error_log("[INFO_PERSONAL ERROR] Error obteniendo estadísticas: " . $e->getMessage());
            return [];
        }
    }
}
