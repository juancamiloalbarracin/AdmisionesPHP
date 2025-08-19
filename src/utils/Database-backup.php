<?php
/**
 * CLASE UTILITARIA DE BASE DE DATOS
 * =================================
 * Esta clase maneja todas las conexiones a MySQL utilizando PDO
 * Implementa el patrón Singleton para reutilizar conexiones
 * y proporciona métodos seguros para consultas preparadas
 */

namespace UDC\SistemaAdmisiones\Utils;

use PDO;
use PDOException;
use InvalidArgumentException;

class Database
{
    /**
     * Instancia única de la conexión (Singleton)
     */
    private static ?PDO $connection = null;
    
    /**
     * Configuración de la base de datos
     */
    private static ?array $config = null;
    
    /**
     * Contador de consultas ejecutadas (para debugging)
     */
    private static int $queryCount = 0;
    
    /**
     * Log de consultas lentas
     */
    private static array $slowQueries = [];

    /**
     * Constructor privado para implementar Singleton
     * Previene la creación directa de instancias
     */
    private function __construct() {}
    
    /**
     * Prevenir clonación de la instancia
     */
    private function __clone() {}
    
    /**
     * Prevenir deserialización de la instancia
     */
    public function __wakeup()
    {
        throw new \Exception("No se puede deserializar un Singleton");
    }

    /**
     * Obtener la conexión a la base de datos
     * Crea una nueva conexión si no existe o si se perdió la conexión
     * 
     * @return PDO Conexión activa a la base de datos
     * @throws PDOException Si no se puede conectar a la base de datos
     */
    public static function getConnection(): PDO
    {
        // Si no hay conexión o se perdió, crear una nueva
        if (self::$connection === null || !self::isConnectionAlive()) {
            self::connect();
        }
        
        return self::$connection;
    }

    /**
     * Establecer conexión con la base de datos
     * Carga la configuración y establece la conexión PDO
     * 
     * @throws PDOException Si no se puede establecer la conexión
     */
    private static function connect(): void
    {
        try {
            // Cargar configuración si no está cargada
            if (self::$config === null) {
                $configPath = CONFIG_DIR . '/database.php';
                if (!file_exists($configPath)) {
                    throw new \Exception("Archivo de configuración de BD no encontrado: $configPath");
                }
                self::$config = require $configPath;
            }
            
            $config = self::$config['mysql'];
            
            // Construir DSN (Data Source Name)
            $dsn = sprintf(
                'mysql:host=%s;port=%d;dbname=%s;charset=%s',
                $config['host'],
                $config['port'],
                $config['database'],
                $config['charset']
            );
            
            // Crear conexión PDO con opciones de configuración
            self::$connection = new PDO(
                $dsn,
                $config['username'],
                $config['password'],
                $config['options']
            );
            
            // Log de conexión exitosa en desarrollo
            if ($_ENV['APP_ENV'] === 'development') {
                error_log("[Database] Conexión establecida exitosamente");
            }
            
        } catch (PDOException $e) {
            // Log del error sin exponer información sensible
            error_log("[Database ERROR] " . $e->getMessage());
            
            // En desarrollo mostrar más detalles, en producción ser genérico
            if ($_ENV['APP_ENV'] === 'development') {
                throw new PDOException("Error de conexión a BD: " . $e->getMessage());
            } else {
                throw new PDOException("Error de conexión a la base de datos");
            }
        }
    }

    /**
     * Verificar si la conexión está activa
     * Útil para reconectar en caso de timeouts largos
     * 
     * @return bool true si la conexión está activa
     */
    private static function isConnectionAlive(): bool
    {
        if (self::$connection === null) {
            return false;
        }
        
        try {
            // Hacer una consulta simple para verificar la conexión
            self::$connection->query('SELECT 1');
            return true;
        } catch (PDOException $e) {
            error_log("[Database] Conexión perdida: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Ejecutar una consulta preparada de forma segura
     * 
     * @param string $sql Consulta SQL con placeholders
     * @param array $params Parámetros para la consulta preparada
     * @return \PDOStatement Resultado de la consulta
     * @throws PDOException Si hay error en la consulta
     */
    public static function query(string $sql, array $params = []): \PDOStatement
    {
        $startTime = microtime(true);
        
        try {
            $connection = self::getConnection();
            $statement = $connection->prepare($sql);
            
            // Ejecutar con parámetros si los hay
            if (!empty($params)) {
                $statement->execute($params);
            } else {
                $statement->execute();
            }
            
            self::$queryCount++;
            
            // Log de consultas lentas en desarrollo
            $executionTime = microtime(true) - $startTime;
            if ($_ENV['APP_ENV'] === 'development' && $executionTime > 0.5) {
                $logEntry = [
                    'sql' => $sql,
                    'params' => $params,
                    'execution_time' => $executionTime,
                    'timestamp' => date('Y-m-d H:i:s')
                ];
                self::$slowQueries[] = $logEntry;
                error_log("[Database SLOW QUERY] " . json_encode($logEntry));
            }
            
            return $statement;
            
        } catch (PDOException $e) {
            // Log del error con contexto
            $errorContext = [
                'sql' => $sql,
                'params' => $params,
                'error' => $e->getMessage(),
                'timestamp' => date('Y-m-d H:i:s')
            ];
            error_log("[Database QUERY ERROR] " . json_encode($errorContext));
            
            throw $e;
        }
    }

    /**
     * Obtener una sola fila de resultado
     * 
     * @param string $sql Consulta SQL
     * @param array $params Parámetros de la consulta
     * @return array|null Fila encontrada o null si no hay resultados
     */
    public static function fetchOne(string $sql, array $params = []): ?array
    {
        $statement = self::query($sql, $params);
        $result = $statement->fetch();
        return $result ?: null;
    }

    /**
     * Obtener todas las filas de resultado
     * 
     * @param string $sql Consulta SQL
     * @param array $params Parámetros de la consulta
     * @return array Array de filas encontradas
     */
    public static function fetchAll(string $sql, array $params = []): array
    {
        $statement = self::query($sql, $params);
        return $statement->fetchAll();
    }

    /**
     * Obtener el ID del último registro insertado
     * 
     * @return string ID del último registro insertado
     */
    public static function getLastInsertId(): string
    {
        return self::getConnection()->lastInsertId();
    }

    /**
     * Comenzar una transacción
     * Útil para operaciones que requieren atomicidad
     */
    public static function beginTransaction(): bool
    {
        return self::getConnection()->beginTransaction();
    }

    /**
     * Confirmar una transacción
     */
    public static function commit(): bool
    {
        return self::getConnection()->commit();
    }

    /**
     * Revertir una transacción
     */
    public static function rollback(): bool
    {
        return self::getConnection()->rollback();
    }

    /**
     * Verificar si estamos dentro de una transacción
     */
    public static function inTransaction(): bool
    {
        return self::getConnection()->inTransaction();
    }

    /**
     * Obtener estadísticas de uso de la base de datos
     * Útil para debugging y monitoreo
     * 
     * @return array Estadísticas de uso
     */
    public static function getStats(): array
    {
        return [
            'query_count' => self::$queryCount,
            'slow_queries_count' => count(self::$slowQueries),
            'connection_active' => self::$connection !== null,
            'last_query_time' => self::$slowQueries ? end(self::$slowQueries)['timestamp'] : null
        ];
    }

    /**
     * Limpiar estadísticas (útil para testing)
     */
    public static function resetStats(): void
    {
        self::$queryCount = 0;
        self::$slowQueries = [];
    }

    /**
     * Cerrar la conexión explícitamente
     * Se ejecuta automáticamente al final del script
     */
    public static function closeConnection(): void
    {
        if ($_ENV['APP_ENV'] === 'development' && self::$queryCount > 0) {
            error_log("[Database] Cerrando conexión. Total consultas: " . self::$queryCount);
        }
        
        self::$connection = null;
    }
}
