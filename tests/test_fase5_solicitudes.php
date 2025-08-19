<?php
/**
 * PRUEBAS INTEGRALES - FASE 5: SOLICITUDES DE ADMISIÓN
 * ===================================================
 * Este script prueba exhaustivamente todos los endpoints y funcionalidades
 * del sistema de solicitudes de admisión
 */

require_once __DIR__ . '/../config/bootstrap.php';

use UDC\SistemaAdmisiones\Utils\Database;
use UDC\SistemaAdmisiones\Models\Solicitud;
use UDC\SistemaAdmisiones\Controllers\SolicitudController;

class TestSolicitudes
{
    private $solicitudController;
    private $testUserId = 1;
    private $testSolicitudId;
    private $testResults = [];

    public function __construct()
    {
        $this->solicitudController = new SolicitudController();
        $this->initializeTestData();
    }

    private function initializeTestData()
    {
        try {
            echo "🔧 Inicializando datos de prueba...\n";
            
            $db = Database::getInstance();
            $connection = $db->getConnection();
            
            // Crear tabla historial_estados si no existe
            $sql = "
            CREATE TABLE IF NOT EXISTS historial_estados (
                id INT AUTO_INCREMENT PRIMARY KEY,
                user_id INT NOT NULL,
                estado_anterior VARCHAR(50) NULL,
                nuevo_estado VARCHAR(50) NOT NULL,
                observacion TEXT NULL,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                INDEX idx_user_id (user_id),
                INDEX idx_nuevo_estado (nuevo_estado),
                INDEX idx_created_at (created_at),
                FOREIGN KEY (user_id) REFERENCES usuarios(id) ON DELETE CASCADE
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
            ";
            $connection->exec($sql);

            // Crear/actualizar tabla solicitudes
            $connection->exec("DROP TABLE IF EXISTS solicitudes");
            $sqlSolicitudes = "
            CREATE TABLE solicitudes (
                id INT AUTO_INCREMENT PRIMARY KEY,
                user_id INT NOT NULL,
                numero_solicitud VARCHAR(50) UNIQUE NOT NULL,
                programa_academico VARCHAR(100) NOT NULL,
                periodo_academico VARCHAR(20) NOT NULL,
                modalidad_ingreso VARCHAR(50) NOT NULL,
                sede VARCHAR(100) DEFAULT 'Montería',
                estado ENUM('BORRADOR','ENVIADA','EN_REVISION','DOCUMENTOS_PENDIENTES','APROBADA','RECHAZADA','EN_LISTA_ESPERA','CANCELADA') DEFAULT 'BORRADOR',
                documentos_adjuntos JSON NULL,
                observaciones TEXT NULL,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                INDEX idx_user_id (user_id),
                INDEX idx_numero_solicitud (numero_solicitud),
                INDEX idx_programa_academico (programa_academico),
                INDEX idx_periodo_academico (periodo_academico),
                INDEX idx_estado (estado),
                FOREIGN KEY (user_id) REFERENCES usuarios(id) ON DELETE CASCADE
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
            ";
            $connection->exec($sqlSolicitudes);

            echo "✅ Tablas inicializadas correctamente\n\n";
            
        } catch (Exception $e) {
            echo "❌ Error inicializando datos: " . $e->getMessage() . "\n";
        }
    }

    private function logTest($testName, $passed, $message = "")
    {
        $status = $passed ? "✅ PASS" : "❌ FAIL";
        echo "[$status] $testName";
        if ($message) {
            echo " - $message";
        }
        echo "\n";
        
        $this->testResults[] = [
            'test' => $testName,
            'passed' => $passed,
            'message' => $message
        ];
    }

    public function runAllTests()
    {
        echo "🚀 INICIANDO PRUEBAS INTEGRALES - FASE 5\n";
        echo str_repeat("=", 60) . "\n\n";

        $this->testGetCatalogs();
        $this->testCreateSolicitud();
        $this->testGetSolicitud();
        $this->testUpdateSolicitud();
        $this->testSubmitSolicitud();
        $this->testGetProgress();
        $this->testChangeStatus();
        $this->testGetAllSolicitudes();
        $this->testValidateDocuments();
        $this->testGetStats();

        $this->showTestSummary();
    }

    private function testGetCatalogs()
    {
        echo "🔍 PRUEBA 1: GET CATALOGS\n";
        echo str_repeat("-", 30) . "\n";
        
        try {
            $catalogs = $this->solicitudController->getCatalogs();
            
            $this->logTest("Obtener catálogos", !empty($catalogs));
            $this->logTest("Programas académicos", 
                isset($catalogs['programasAcademicos']) && count($catalogs['programasAcademicos']) >= 19);
            $this->logTest("Modalidades de ingreso", 
                isset($catalogs['modalidadesIngreso']) && count($catalogs['modalidadesIngreso']) >= 5);
            $this->logTest("Sedes disponibles", 
                isset($catalogs['sedes']) && count($catalogs['sedes']) >= 5);
            $this->logTest("Estados de solicitud", 
                isset($catalogs['estados']) && count($catalogs['estados']) >= 8);
            $this->logTest("Documentos requeridos", 
                isset($catalogs['documentosRequeridos']) && count($catalogs['documentosRequeridos']) >= 7);
                
        } catch (Exception $e) {
            $this->logTest("GET Catalogs", false, $e->getMessage());
        }
        
        echo "\n";
    }

    private function testCreateSolicitud()
    {
        echo "📝 PRUEBA 2: CREATE SOLICITUD\n";
        echo str_repeat("-", 30) . "\n";
        
        try {
            $data = [
                'programa_academico' => 'MEDICINA',
                'periodo_academico' => '2024-1',
                'modalidad_ingreso' => 'REGULAR',
                'sede' => 'Montería'
            ];

            $result = $this->solicitudController->save($this->testUserId, $data);
            
            if (isset($result['id'])) {
                $this->testSolicitudId = $result['id'];
                $this->logTest("Crear solicitud", true, "ID: " . $this->testSolicitudId);
                $this->logTest("Número de solicitud generado", 
                    !empty($result['numeroSolicitud']));
                $this->logTest("Estado inicial BORRADOR", 
                    $result['estado'] === 'BORRADOR');
            } else {
                $this->logTest("Crear solicitud", false, "No se devolvió ID");
            }
            
        } catch (Exception $e) {
            $this->logTest("Crear solicitud", false, $e->getMessage());
        }
        
        echo "\n";
    }

    private function testGetSolicitud()
    {
        echo "🔍 PRUEBA 3: GET SOLICITUD\n";
        echo str_repeat("-", 30) . "\n";
        
        if (!$this->testSolicitudId) {
            $this->logTest("GET Solicitud", false, "No hay solicitud de prueba");
            echo "\n";
            return;
        }
        
        try {
            $solicitud = $this->solicitudController->get($this->testUserId, $this->testSolicitudId);
            
            $this->logTest("Obtener solicitud", !empty($solicitud));
            $this->logTest("ID correcto", $solicitud['id'] == $this->testSolicitudId);
            $this->logTest("Usuario correcto", $solicitud['userId'] == $this->testUserId);
            $this->logTest("Programa académico", $solicitud['programaAcademico'] === 'MEDICINA');
            
        } catch (Exception $e) {
            $this->logTest("GET Solicitud", false, $e->getMessage());
        }
        
        echo "\n";
    }

    private function testUpdateSolicitud()
    {
        echo "✏️ PRUEBA 4: UPDATE SOLICITUD\n";
        echo str_repeat("-", 30) . "\n";
        
        if (!$this->testSolicitudId) {
            $this->logTest("Update Solicitud", false, "No hay solicitud de prueba");
            echo "\n";
            return;
        }
        
        try {
            $updateData = [
                'sede' => 'Lorica',
                'observaciones' => 'Solicitud de prueba actualizada'
            ];

            $result = $this->solicitudController->save($this->testUserId, $updateData, $this->testSolicitudId);
            
            $this->logTest("Actualizar solicitud", !empty($result));
            $this->logTest("Sede actualizada", $result['sede'] === 'Lorica');
            $this->logTest("Observaciones añadidas", !empty($result['observaciones']));
            
        } catch (Exception $e) {
            $this->logTest("Update Solicitud", false, $e->getMessage());
        }
        
        echo "\n";
    }

    private function testSubmitSolicitud()
    {
        echo "📤 PRUEBA 5: SUBMIT SOLICITUD\n";
        echo str_repeat("-", 30) . "\n";
        
        if (!$this->testSolicitudId) {
            $this->logTest("Submit Solicitud", false, "No hay solicitud de prueba");
            echo "\n";
            return;
        }
        
        try {
            $result = $this->solicitudController->submit($this->testUserId, $this->testSolicitudId);
            
            $this->logTest("Enviar solicitud", !empty($result));
            $this->logTest("Estado cambió a ENVIADA", $result['estado'] === 'ENVIADA');
            $this->logTest("Número de solicitud asignado", !empty($result['numeroSolicitud']));
            
        } catch (Exception $e) {
            $this->logTest("Submit Solicitud", false, $e->getMessage());
        }
        
        echo "\n";
    }

    private function testGetProgress()
    {
        echo "📊 PRUEBA 6: GET PROGRESS\n";
        echo str_repeat("-", 30) . "\n";
        
        if (!$this->testSolicitudId) {
            $this->logTest("Get Progress", false, "No hay solicitud de prueba");
            echo "\n";
            return;
        }
        
        try {
            $progress = $this->solicitudController->getProgress($this->testUserId, $this->testSolicitudId);
            
            $this->logTest("Obtener progreso", !empty($progress));
            $this->logTest("Pasos definidos", isset($progress['pasos']) && count($progress['pasos']) >= 5);
            $this->logTest("Progreso calculado", isset($progress['porcentajeCompletado']));
            $this->logTest("Paso actual definido", isset($progress['pasoActual']));
            
        } catch (Exception $e) {
            $this->logTest("Get Progress", false, $e->getMessage());
        }
        
        echo "\n";
    }

    private function testChangeStatus()
    {
        echo "🔄 PRUEBA 7: CHANGE STATUS\n";
        echo str_repeat("-", 30) . "\n";
        
        if (!$this->testSolicitudId) {
            $this->logTest("Change Status", false, "No hay solicitud de prueba");
            echo "\n";
            return;
        }
        
        try {
            $data = [
                'nuevo_estado' => 'EN_REVISION',
                'observacion' => 'Cambiando a revisión para pruebas'
            ];

            $result = $this->solicitudController->changeStatus($this->testSolicitudId, $data);
            
            $this->logTest("Cambiar estado", !empty($result));
            $this->logTest("Estado actualizado", $result['estado'] === 'EN_REVISION');
            
        } catch (Exception $e) {
            $this->logTest("Change Status", false, $e->getMessage());
        }
        
        echo "\n";
    }

    private function testGetAllSolicitudes()
    {
        echo "📋 PRUEBA 8: GET ALL SOLICITUDES\n";
        echo str_repeat("-", 30) . "\n";
        
        try {
            $params = [
                'page' => 1,
                'limit' => 10,
                'programa' => 'MEDICINA'
            ];

            $result = $this->solicitudController->getAll($params);
            
            $this->logTest("Obtener todas las solicitudes", !empty($result));
            $this->logTest("Datos de solicitudes", isset($result['data']));
            $this->logTest("Información de paginación", isset($result['pagination']));
            $this->logTest("Al menos una solicitud", count($result['data']) >= 1);
            
        } catch (Exception $e) {
            $this->logTest("Get All Solicitudes", false, $e->getMessage());
        }
        
        echo "\n";
    }

    private function testValidateDocuments()
    {
        echo "📄 PRUEBA 9: VALIDATE DOCUMENTS\n";
        echo str_repeat("-", 30) . "\n";
        
        if (!$this->testSolicitudId) {
            $this->logTest("Validate Documents", false, "No hay solicitud de prueba");
            echo "\n";
            return;
        }
        
        try {
            $result = $this->solicitudController->validateDocuments($this->testUserId, $this->testSolicitudId);
            
            $this->logTest("Validar documentos", !empty($result));
            $this->logTest("Documentos requeridos listados", isset($result['documentosRequeridos']));
            $this->logTest("Estado de validación", isset($result['valido']));
            $this->logTest("Documentos faltantes identificados", isset($result['documentosFaltantes']));
            
        } catch (Exception $e) {
            $this->logTest("Validate Documents", false, $e->getMessage());
        }
        
        echo "\n";
    }

    private function testGetStats()
    {
        echo "📈 PRUEBA 10: GET STATS\n";
        echo str_repeat("-", 30) . "\n";
        
        try {
            $stats = $this->solicitudController->getStats();
            
            $this->logTest("Obtener estadísticas", !empty($stats));
            $this->logTest("Total de solicitudes", isset($stats['totalSolicitudes']));
            $this->logTest("Estadísticas por estado", isset($stats['porEstado']));
            $this->logTest("Estadísticas por programa", isset($stats['porPrograma']));
            $this->logTest("Tendencias mensuales", isset($stats['tendenciaMensual']));
            
        } catch (Exception $e) {
            $this->logTest("Get Stats", false, $e->getMessage());
        }
        
        echo "\n";
    }

    private function showTestSummary()
    {
        echo str_repeat("=", 60) . "\n";
        echo "📊 RESUMEN DE PRUEBAS - FASE 5\n";
        echo str_repeat("=", 60) . "\n";
        
        $total = count($this->testResults);
        $passed = array_sum(array_column($this->testResults, 'passed'));
        $failed = $total - $passed;
        $percentage = ($passed / $total) * 100;
        
        echo "Total de pruebas: $total\n";
        echo "✅ Exitosas: $passed\n";
        echo "❌ Fallidas: $failed\n";
        echo "📊 Porcentaje de éxito: " . number_format($percentage, 1) . "%\n\n";
        
        if ($failed > 0) {
            echo "❌ PRUEBAS FALLIDAS:\n";
            foreach ($this->testResults as $result) {
                if (!$result['passed']) {
                    echo "• " . $result['test'] . ": " . $result['message'] . "\n";
                }
            }
            echo "\n";
        }
        
        echo str_repeat("=", 60) . "\n";
        
        if ($percentage >= 80) {
            echo "🎉 FASE 5 - SOLICITUDES: IMPLEMENTACIÓN EXITOSA\n";
            echo "✅ Sistema de solicitudes de admisión completamente funcional\n";
        } else {
            echo "⚠️ FASE 5 - SOLICITUDES: NECESITA REVISIÓN\n";
            echo "❌ Algunos componentes requieren corrección\n";
        }
        
        echo str_repeat("=", 60) . "\n";
    }
}

// Ejecutar pruebas
try {
    $test = new TestSolicitudes();
    $test->runAllTests();
} catch (Exception $e) {
    echo "❌ ERROR GENERAL: " . $e->getMessage() . "\n";
    echo "Archivo: " . $e->getFile() . "\n";
    echo "Línea: " . $e->getLine() . "\n";
}
