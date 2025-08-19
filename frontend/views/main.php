<?php
/**
 * SISTEMA DE ADMISIONES UDC - PÁGINA PRINCIPAL
 * ============================================
 * Archivo: main.php
 * Descripción: Dashboard principal del sistema replicando la funcionalidad del proyecto original Java
 */

// Configuración de la página
$pageTitle = 'Dashboard';
$additionalCSS = [];
$additionalJS = [];

// Iniciar sesión
session_start();

// Verificar autenticación
if (!isset($_SESSION['user_id']) || !isset($_SESSION['jwt_token'])) {
    header('Location: login.php');
    exit();
}

// Obtener datos del usuario
$currentUser = $_SESSION['user_data'];
$apiUrl = 'http://localhost:8000/api';
$token = $_SESSION['jwt_token'];

// Función para hacer peticiones API
function makeApiRequest($url, $token, $method = 'GET', $data = null) {
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_TIMEOUT, 30);
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        'Authorization: Bearer ' . $token,
        'Content-Type: application/json',
        'Accept: application/json'
    ]);
    
    if ($method === 'POST') {
        curl_setopt($ch, CURLOPT_POST, true);
        if ($data) {
            curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
        }
    }
    
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    
    return ['response' => $response, 'code' => $httpCode];
}

// Obtener estadísticas del dashboard
$stats = [
    'solicitudes_total' => 0,
    'solicitudes_pendientes' => 0,
    'solicitudes_aprobadas' => 0,
    'solicitudes_rechazadas' => 0,
    'info_personal_completada' => false,
    'info_academica_completada' => false
];

// Obtener solicitudes del usuario
$solicitudesResult = makeApiRequest($apiUrl . '/solicitudes', $token);
if ($solicitudesResult['code'] === 200) {
    $solicitudesData = json_decode($solicitudesResult['response'], true);
    if ($solicitudesData['success'] ?? false) {
        $solicitudes = $solicitudesData['solicitudes'] ?? [];
        $stats['solicitudes_total'] = count($solicitudes);
        
        foreach ($solicitudes as $solicitud) {
            switch ($solicitud['estado']) {
                case 'PENDIENTE':
                    $stats['solicitudes_pendientes']++;
                    break;
                case 'APROBADA':
                    $stats['solicitudes_aprobadas']++;
                    break;
                case 'RECHAZADA':
                    $stats['solicitudes_rechazadas']++;
                    break;
            }
        }
    }
}

// Verificar información personal
$infoPersonalResult = makeApiRequest($apiUrl . '/info-personal', $token);
if ($infoPersonalResult['code'] === 200) {
    $infoPersonalData = json_decode($infoPersonalResult['response'], true);
    $stats['info_personal_completada'] = ($infoPersonalData['success'] ?? false) && !empty($infoPersonalData['info']);
}

// Verificar información académica
$infoAcademicaResult = makeApiRequest($apiUrl . '/info-academica', $token);
if ($infoAcademicaResult['code'] === 200) {
    $infoAcademicaData = json_decode($infoAcademicaResult['response'], true);
    $stats['info_academica_completada'] = ($infoAcademicaData['success'] ?? false) && !empty($infoAcademicaData['info']);
}

// Calcular progreso del perfil
$profileProgress = 0;
if ($stats['info_personal_completada']) $profileProgress += 50;
if ($stats['info_academica_completada']) $profileProgress += 50;

include '../includes/header.php';
?>

<div class="dashboard-container">
    <div class="container">
        <!-- Header del Dashboard -->
        <div class="dashboard-header">
            <div class="row align-items-center mb-4">
                <div class="col-md-8">
                    <h1 class="page-title">
                        <i class="fas fa-home"></i>
                        Bienvenido, <?php echo htmlspecialchars(($currentUser['nombres'] ?? $currentUser['nombre'] ?? '') . (isset($currentUser['apellidos']) ? ' ' . $currentUser['apellidos'] : '')); ?>
                    </h1>
                    <p class="page-subtitle">
                        Panel de control del Sistema de Admisiones - Uniminuto
                    </p>
                </div>
                <div class="col-md-4 text-md-end">
                    <div class="quick-actions">
                        <a href="radicar.php" class="btn btn-primary">
                            <i class="fas fa-plus-circle"></i> Nueva Solicitud
                        </a>
                    </div>
                </div>
            </div>
        </div>

        <!-- Tarjetas de Estadísticas -->
        <div class="stats-cards">
            <div class="row">
                <div class="col-lg-3 col-md-6 mb-4">
                    <div class="stat-card">
                        <div class="stat-card-body">
                            <div class="stat-icon bg-primary">
                                <i class="fas fa-file-alt"></i>
                            </div>
                            <div class="stat-content">
                                <h3><?php echo $stats['solicitudes_total']; ?></h3>
                                <p>Total Solicitudes</p>
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="col-lg-3 col-md-6 mb-4">
                    <div class="stat-card">
                        <div class="stat-card-body">
                            <div class="stat-icon bg-warning">
                                <i class="fas fa-clock"></i>
                            </div>
                            <div class="stat-content">
                                <h3><?php echo $stats['solicitudes_pendientes']; ?></h3>
                                <p>Pendientes</p>
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="col-lg-3 col-md-6 mb-4">
                    <div class="stat-card">
                        <div class="stat-card-body">
                            <div class="stat-icon bg-success">
                                <i class="fas fa-check-circle"></i>
                            </div>
                            <div class="stat-content">
                                <h3><?php echo $stats['solicitudes_aprobadas']; ?></h3>
                                <p>Aprobadas</p>
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="col-lg-3 col-md-6 mb-4">
                    <div class="stat-card">
                        <div class="stat-card-body">
                            <div class="stat-icon bg-danger">
                                <i class="fas fa-times-circle"></i>
                            </div>
                            <div class="stat-content">
                                <h3><?php echo $stats['solicitudes_rechazadas']; ?></h3>
                                <p>Rechazadas</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="row">
            <!-- Panel de Progreso del Perfil -->
            <div class="col-lg-6 mb-4">
                <div class="dashboard-card">
                    <div class="card-header">
                        <h5 class="card-title">
                            <i class="fas fa-user-cog"></i>
                            Progreso del Perfil
                        </h5>
                    </div>
                    <div class="card-body">
                        <div class="profile-progress">
                            <div class="progress-circle">
                                <div class="progress-bar-circular" data-percent="<?php echo $profileProgress; ?>">
                                    <div class="progress-text">
                                        <span class="progress-number"><?php echo $profileProgress; ?>%</span>
                                        <span class="progress-label">Completado</span>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="profile-checklist">
                                <div class="checklist-item <?php echo $stats['info_personal_completada'] ? 'completed' : ''; ?>">
                                    <div class="check-icon">
                                        <i class="fas <?php echo $stats['info_personal_completada'] ? 'fa-check-circle' : 'fa-circle'; ?>"></i>
                                    </div>
                                    <div class="check-content">
                                        <h6>Información Personal</h6>
                                        <p><?php echo $stats['info_personal_completada'] ? 'Completada' : 'Pendiente'; ?></p>
                                    </div>
                                    <?php if (!$stats['info_personal_completada']): ?>
                                    <div class="check-action">
                                        <a href="info-personal.php" class="btn btn-sm btn-outline-primary">Completar</a>
                                    </div>
                                    <?php endif; ?>
                                </div>
                                
                                <div class="checklist-item <?php echo $stats['info_academica_completada'] ? 'completed' : ''; ?>">
                                    <div class="check-icon">
                                        <i class="fas <?php echo $stats['info_academica_completada'] ? 'fa-check-circle' : 'fa-circle'; ?>"></i>
                                    </div>
                                    <div class="check-content">
                                        <h6>Información Académica</h6>
                                        <p><?php echo $stats['info_academica_completada'] ? 'Completada' : 'Pendiente'; ?></p>
                                    </div>
                                    <?php if (!$stats['info_academica_completada']): ?>
                                    <div class="check-action">
                                        <a href="info-academica.php" class="btn btn-sm btn-outline-primary">Completar</a>
                                    </div>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Panel de Acciones Rápidas -->
            <div class="col-lg-6 mb-4">
                <div class="dashboard-card">
                    <div class="card-header">
                        <h5 class="card-title">
                            <i class="fas fa-bolt"></i>
                            Acciones Rápidas
                        </h5>
                    </div>
                    <div class="card-body">
                        <div class="quick-actions-grid">
                            <a href="info-personal.php" class="action-item">
                                <div class="action-icon bg-info">
                                    <i class="fas fa-user"></i>
                                </div>
                                <div class="action-content">
                                    <h6>Información Personal</h6>
                                    <p>Actualizar datos personales</p>
                                </div>
                            </a>
                            
                            <a href="info-academica.php" class="action-item">
                                <div class="action-icon bg-success">
                                    <i class="fas fa-graduation-cap"></i>
                                </div>
                                <div class="action-content">
                                    <h6>Información Académica</h6>
                                    <p>Gestionar historial académico</p>
                                </div>
                            </a>
                            
                            <a href="radicar.php" class="action-item">
                                <div class="action-icon bg-primary">
                                    <i class="fas fa-plus-circle"></i>
                                </div>
                                <div class="action-content">
                                    <h6>Radicar Solicitud</h6>
                                    <p>Nueva solicitud de admisión</p>
                                </div>
                            </a>
                            
                            <a href="solicitudes.php" class="action-item">
                                <div class="action-icon bg-warning">
                                    <i class="fas fa-list-alt"></i>
                                </div>
                                <div class="action-content">
                                    <h6>Mis Solicitudes</h6>
                                    <p>Ver estado de solicitudes</p>
                                </div>
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Últimas Solicitudes -->
        <?php if (!empty($solicitudes)): ?>
        <div class="dashboard-card">
            <div class="card-header">
                <h5 class="card-title">
                    <i class="fas fa-history"></i>
                    Últimas Solicitudes
                </h5>
                <div class="card-actions">
                    <a href="solicitudes.php" class="btn btn-sm btn-outline-primary">Ver Todas</a>
                </div>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Programa</th>
                                <th>Fecha</th>
                                <th>Estado</th>
                                <th>Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach (array_slice($solicitudes, 0, 5) as $solicitud): ?>
                            <tr>
                                <td>
                                    <span class="text-muted">#<?php echo $solicitud['id']; ?></span>
                                </td>
                                <td>
                                    <strong><?php echo htmlspecialchars($solicitud['programa_academico']); ?></strong>
                                </td>
                                <td>
                                    <span class="text-muted">
                                        <?php echo date('d/m/Y', strtotime($solicitud['fecha_creacion'])); ?>
                                    </span>
                                </td>
                                <td>
                                    <?php
                                    $badgeClass = 'badge-secondary';
                                    switch ($solicitud['estado']) {
                                        case 'PENDIENTE':
                                            $badgeClass = 'badge-warning';
                                            break;
                                        case 'APROBADA':
                                            $badgeClass = 'badge-success';
                                            break;
                                        case 'RECHAZADA':
                                            $badgeClass = 'badge-danger';
                                            break;
                                    }
                                    ?>
                                    <span class="badge <?php echo $badgeClass; ?>">
                                        <?php echo $solicitud['estado']; ?>
                                    </span>
                                </td>
                                <td>
                                    <div class="btn-group btn-group-sm">
                                        <button class="btn btn-outline-info" 
                                                title="Ver Detalles"
                                                onclick="viewSolicitud(<?php echo $solicitud['id']; ?>)">
                                            <i class="fas fa-eye"></i>
                                        </button>
                                        <?php if ($solicitud['estado'] === 'PENDIENTE'): ?>
                                        <button class="btn btn-outline-primary" 
                                                title="Editar"
                                                onclick="editSolicitud(<?php echo $solicitud['id']; ?>)">
                                            <i class="fas fa-edit"></i>
                                        </button>
                                        <?php endif; ?>
                                    </div>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
        <?php endif; ?>

        <!-- Panel de Ayuda -->
        <div class="help-panel">
            <div class="row">
                <div class="col-lg-12">
                    <div class="help-card">
                        <div class="help-icon">
                            <i class="fas fa-question-circle"></i>
                        </div>
                        <div class="help-content">
                            <h5>¿Necesita ayuda?</h5>
                            <p>Si tiene alguna duda sobre el proceso de admisión, no dude en contactarnos.</p>
                            <div class="help-actions">
                                <button class="btn btn-outline-primary" onclick="openHelpModal()">
                                    <i class="fas fa-headset"></i> Soporte
                                </button>
                                <a href="mailto:admisiones@correo.unicordoba.edu.co" class="btn btn-outline-success">
                                    <i class="fas fa-envelope"></i> Escribenos
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal de Ayuda -->
<div class="modal fade" id="helpModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Centro de Ayuda</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="help-sections">
                    <div class="help-section">
                        <h6><i class="fas fa-user"></i> Perfil de Usuario</h6>
                        <ul>
                            <li>Complete su información personal en la sección correspondiente</li>
                            <li>Asegúrese de proporcionar datos actualizados y verificables</li>
                            <li>Suba los documentos requeridos en formato PDF o imagen</li>
                        </ul>
                    </div>
                    
                    <div class="help-section">
                        <h6><i class="fas fa-graduation-cap"></i> Información Académica</h6>
                        <ul>
                            <li>Registre su historial académico completo</li>
                            <li>Incluya certificados y notas de estudios anteriores</li>
                            <li>Verifique que la información sea precisa</li>
                        </ul>
                    </div>
                    
                    <div class="help-section">
                        <h6><i class="fas fa-file-alt"></i> Solicitudes</h6>
                        <ul>
                            <li>Puede radicar múltiples solicitudes para diferentes programas</li>
                            <li>Revise el estado de sus solicitudes regularmente</li>
                            <li>Tenga en cuenta los plazos de cada convocatoria</li>
                        </ul>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
                <a href="mailto:admisiones@correo.unicordoba.edu.co" class="btn btn-primary">Contactar Soporte</a>
            </div>
        </div>
    </div>
</div>

<style>
    .dashboard-container {
        padding: 2rem 0;
    }
    
    .page-title {
        color: #1e4d72;
        font-weight: 700;
        margin-bottom: 0.5rem;
    }
    
    .page-title i {
        margin-right: 0.5rem;
        color: #2980b9;
    }
    
    .page-subtitle {
        color: #6c757d;
        margin-bottom: 0;
    }
    
    .stat-card {
        background: white;
        border-radius: 15px;
        border: none;
        box-shadow: 0 5px 15px rgba(0,0,0,0.08);
        transition: all 0.3s ease;
        overflow: hidden;
    }
    
    .stat-card:hover {
        transform: translateY(-5px);
        box-shadow: 0 15px 35px rgba(0,0,0,0.15);
    }
    
    .stat-card-body {
        padding: 2rem;
        display: flex;
        align-items: center;
        gap: 1rem;
    }
    
    .stat-icon {
        width: 70px;
        height: 70px;
        border-radius: 15px;
        display: flex;
        align-items: center;
        justify-content: center;
        color: white;
        font-size: 1.5rem;
    }
    
    .stat-icon.bg-primary { background: linear-gradient(135deg, #1e4d72, #2980b9); }
    .stat-icon.bg-warning { background: linear-gradient(135deg, #f39c12, #e67e22); }
    .stat-icon.bg-success { background: linear-gradient(135deg, #27ae60, #2ecc71); }
    .stat-icon.bg-danger { background: linear-gradient(135deg, #e74c3c, #c0392b); }
    .stat-icon.bg-info { background: linear-gradient(135deg, #3498db, #2980b9); }
    
    .stat-content h3 {
        font-size: 2.5rem;
        font-weight: 700;
        margin: 0;
        color: #2c3e50;
    }
    
    .stat-content p {
        margin: 0;
        color: #7f8c8d;
        font-weight: 500;
    }
    
    .dashboard-card {
        background: white;
        border-radius: 15px;
        border: none;
        box-shadow: 0 5px 15px rgba(0,0,0,0.08);
        margin-bottom: 2rem;
        overflow: hidden;
    }
    
    .card-header {
        background: linear-gradient(135deg, #f8f9fa, #e9ecef);
        border-bottom: 1px solid #dee2e6;
        padding: 1.5rem;
        display: flex;
        justify-content: between;
        align-items: center;
    }
    
    .card-title {
        margin: 0;
        color: #1e4d72;
        font-weight: 600;
    }
    
    .card-title i {
        margin-right: 0.5rem;
        color: #2980b9;
    }
    
    .card-body {
        padding: 2rem;
    }
    
    .profile-progress {
        display: flex;
        align-items: center;
        gap: 2rem;
    }
    
    .progress-circle {
        position: relative;
        width: 120px;
        height: 120px;
        flex-shrink: 0;
    }
    
    .progress-bar-circular {
        width: 120px;
        height: 120px;
        border-radius: 50%;
        background: conic-gradient(#2980b9 var(--percent), #e9ecef 0);
        display: flex;
        align-items: center;
        justify-content: center;
        position: relative;
    }
    
    .progress-bar-circular::before {
        content: '';
        width: 90px;
        height: 90px;
        border-radius: 50%;
        background: white;
        position: absolute;
    }
    
    .progress-text {
        position: relative;
        z-index: 1;
        text-align: center;
    }
    
    .progress-number {
        display: block;
        font-size: 1.5rem;
        font-weight: 700;
        color: #1e4d72;
    }
    
    .progress-label {
        font-size: 0.8rem;
        color: #6c757d;
    }
    
    .profile-checklist {
        flex: 1;
    }
    
    .checklist-item {
        display: flex;
        align-items: center;
        gap: 1rem;
        padding: 1rem;
        border-radius: 10px;
        margin-bottom: 1rem;
        background: #f8f9fa;
        transition: all 0.3s ease;
    }
    
    .checklist-item.completed {
        background: linear-gradient(135deg, #d4edda, #c3e6cb);
        border-left: 4px solid #28a745;
    }
    
    .checklist-item.completed .check-icon i {
        color: #28a745;
    }
    
    .check-icon i {
        font-size: 1.2rem;
        color: #6c757d;
    }
    
    .check-content h6 {
        margin: 0;
        color: #2c3e50;
        font-weight: 600;
    }
    
    .check-content p {
        margin: 0;
        font-size: 0.85rem;
        color: #6c757d;
    }
    
    .quick-actions-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
        gap: 1rem;
    }
    
    .action-item {
        display: flex;
        align-items: center;
        gap: 1rem;
        padding: 1.5rem;
        background: #f8f9fa;
        border-radius: 10px;
        text-decoration: none;
        color: inherit;
        transition: all 0.3s ease;
    }
    
    .action-item:hover {
        background: #e9ecef;
        transform: translateY(-2px);
        text-decoration: none;
        color: inherit;
    }
    
    .action-icon {
        width: 50px;
        height: 50px;
        border-radius: 10px;
        display: flex;
        align-items: center;
        justify-content: center;
        color: white;
        flex-shrink: 0;
    }
    
    .action-content h6 {
        margin: 0;
        color: #2c3e50;
        font-weight: 600;
    }
    
    .action-content p {
        margin: 0;
        font-size: 0.85rem;
        color: #6c757d;
    }
    
    .badge {
        padding: 0.5rem 0.75rem;
        font-size: 0.75rem;
        font-weight: 600;
        border-radius: 20px;
    }
    
    .badge-warning {
        background-color: #ffc107;
        color: #212529;
    }
    
    .badge-success {
        background-color: #28a745;
        color: white;
    }
    
    .badge-danger {
        background-color: #dc3545;
        color: white;
    }
    
    .help-panel {
        margin-top: 3rem;
    }
    
    .help-card {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        color: white;
        padding: 2rem;
        border-radius: 15px;
        display: flex;
        align-items: center;
        gap: 2rem;
        text-align: center;
    }
    
    .help-icon {
        width: 80px;
        height: 80px;
        background: rgba(255,255,255,0.2);
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 2rem;
        flex-shrink: 0;
    }
    
    .help-content h5 {
        margin-bottom: 0.5rem;
    }
    
    .help-content p {
        margin-bottom: 1rem;
        opacity: 0.9;
    }
    
    .help-actions {
        display: flex;
        gap: 1rem;
        justify-content: center;
    }
    
    .help-section {
        margin-bottom: 2rem;
        padding-bottom: 1rem;
        border-bottom: 1px solid #e9ecef;
    }
    
    .help-section:last-child {
        border-bottom: none;
    }
    
    .help-section h6 {
        color: #1e4d72;
        margin-bottom: 1rem;
    }
    
    .help-section h6 i {
        margin-right: 0.5rem;
        color: #2980b9;
    }
    
    @media (max-width: 768px) {
        .dashboard-header {
            text-align: center;
        }
        
        .profile-progress {
            flex-direction: column;
            text-align: center;
        }
        
        .help-card {
            flex-direction: column;
            text-align: center;
        }
        
        .help-actions {
            flex-direction: column;
            align-items: center;
        }
    }
    
    /* Animaciones */
    @keyframes fadeInUp {
        from {
            opacity: 0;
            transform: translateY(30px);
        }
        to {
            opacity: 1;
            transform: translateY(0);
        }
    }
    
    .stat-card,
    .dashboard-card {
        animation: fadeInUp 0.6s ease-out;
    }
    
    .stat-card:nth-child(1) { animation-delay: 0.1s; }
    .stat-card:nth-child(2) { animation-delay: 0.2s; }
    .stat-card:nth-child(3) { animation-delay: 0.3s; }
    .stat-card:nth-child(4) { animation-delay: 0.4s; }
</style>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Animar los progress bars circulares
        animateProgressBars();
        
        // Configurar tooltips
        const tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
        tooltipTriggerList.map(function (tooltipTriggerEl) {
            return new bootstrap.Tooltip(tooltipTriggerEl);
        });
    });
    
    function animateProgressBars() {
        const progressBars = document.querySelectorAll('.progress-bar-circular');
        
        progressBars.forEach(bar => {
            const percent = bar.dataset.percent;
            bar.style.setProperty('--percent', percent + '%');
        });
    }
    
    function openHelpModal() {
        const modal = new bootstrap.Modal(document.getElementById('helpModal'));
        modal.show();
    }
    
    function viewSolicitud(id) {
        // Implementar vista de solicitud
        window.location.href = `solicitudes.php?view=${id}`;
    }
    
    function editSolicitud(id) {
        // Implementar edición de solicitud
        window.location.href = `radicar.php?edit=${id}`;
    }
</script>

<?php include '../includes/footer.php'; ?>
