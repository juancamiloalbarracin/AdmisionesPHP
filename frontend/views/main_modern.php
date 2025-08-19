<?php
/**
 * ===================================================================
 * DASHBOARD MODERNO UNIMINUTO - SISTEMA DE ADMISIONES COMPLETO
 * ===================================================================
 * Archivo: main_modern.php
 * Descripción: Dashboard principal con pestañas completas del sistema
 * Fecha: 2025 - Versión Optimizada
 */

// Iniciar sesión y verificar autenticación
session_start();
if (!isset($_SESSION['user_id']) || !isset($_SESSION['email'])) {
    header('Location: login.php');
    exit();
}

// Obtener datos del usuario
$userId = $_SESSION['user_id'];
$userEmail = $_SESSION['email'];
$userName = $_SESSION['nombres'] ?? 'Usuario';

// Configuración API
$apiUrl = 'http://localhost:8000/api';

// Funciones auxiliares para API calls directas
function makeDirectQuery($query, $params = []) {
    try {
        $dsn = "mysql:host=localhost;dbname=admisiones_udc;charset=utf8mb4";
        $pdo = new PDO($dsn, 'root', '', [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
        ]);
        
        $stmt = $pdo->prepare($query);
        $stmt->execute($params);
        return $stmt->fetchAll();
    } catch (Exception $e) {
        error_log("Error en consulta directa: " . $e->getMessage());
        return [];
    }
}

// Obtener estadísticas del usuario
$stats = [
    'info_personal_completa' => false,
    'info_academica_completa' => false,
    'total_solicitudes' => 0,
    'solicitudes_pendientes' => 0,
    'solicitudes_aprobadas' => 0,
    'progreso_perfil' => 0
];

// Verificar información personal
$infoPersonal = makeDirectQuery("SELECT * FROM info_personal WHERE usuario_id = ?", [$userId]);
$stats['info_personal_completa'] = !empty($infoPersonal);

// Verificar información académica
$infoAcademica = makeDirectQuery("SELECT * FROM info_academica WHERE user_id = ?", [$userId]);
$stats['info_academica_completa'] = !empty($infoAcademica);

// Obtener solicitudes
$solicitudes = makeDirectQuery("SELECT * FROM solicitudes WHERE user_id = ?", [$userId]);
$stats['total_solicitudes'] = count($solicitudes);

foreach ($solicitudes as $solicitud) {
    if ($solicitud['estado'] === 'PENDIENTE') {
        $stats['solicitudes_pendientes']++;
    } elseif ($solicitud['estado'] === 'APROBADA') {
        $stats['solicitudes_aprobadas']++;
    }
}

// Calcular progreso del perfil
$progreso = 0;
if ($stats['info_personal_completa']) $progreso += 40;
if ($stats['info_academica_completa']) $progreso += 40;
if ($stats['total_solicitudes'] > 0) $progreso += 20;
$stats['progreso_perfil'] = $progreso;
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - Sistema de Admisiones Uniminuto</title>
    
    <!-- CSS Libraries -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    
    <style>
        :root {
            --primary-color: #6f42c1;
            --secondary-color: #e83e8c;
            --success-color: #28a745;
            --info-color: #17a2b8;
            --warning-color: #ffc107;
            --danger-color: #dc3545;
            --dark-color: #343a40;
            --light-color: #f8f9fa;
            --gradient-primary: linear-gradient(135deg, #6f42c1, #e83e8c);
            --gradient-secondary: linear-gradient(135deg, #667eea, #764ba2);
            --gradient-success: linear-gradient(135deg, #28a745, #20c997);
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', system-ui, sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            color: #2d3748;
        }

        /* ===== NAVEGACIÓN PRINCIPAL ===== */
        .navbar-modern {
            background: rgba(255, 255, 255, 0.98);
            backdrop-filter: blur(20px);
            border-bottom: 1px solid rgba(255, 255, 255, 0.2);
            box-shadow: 0 4px 30px rgba(0, 0, 0, 0.1);
            position: sticky;
            top: 0;
            z-index: 1000;
            padding: 1rem 0;
        }

        .navbar-container {
            max-width: 1400px;
            margin: 0 auto;
            padding: 0 2rem;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .navbar-brand {
            display: flex;
            align-items: center;
            gap: 0.75rem;
        }

        .brand-icon {
            width: 45px;
            height: 45px;
            background: var(--gradient-primary);
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-size: 1.2rem;
        }

        .brand-text h4 {
            margin: 0;
            font-weight: 700;
            color: var(--primary-color);
            font-size: 1.3rem;
        }

        .brand-text span {
            font-size: 0.8rem;
            color: #64748b;
        }

        .user-dropdown {
            position: relative;
        }

        .user-btn {
            display: flex;
            align-items: center;
            gap: 0.75rem;
            background: rgba(255, 255, 255, 0.9);
            border: 2px solid transparent;
            border-radius: 15px;
            padding: 0.5rem 1rem;
            cursor: pointer;
            transition: all 0.3s ease;
        }

        .user-btn:hover {
            border-color: var(--primary-color);
            transform: translateY(-2px);
            box-shadow: 0 8px 25px rgba(111, 66, 193, 0.2);
        }

        .user-avatar {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            overflow: hidden;
        }

        .user-avatar img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }

        .user-info {
            text-align: left;
        }

        .user-name {
            font-weight: 600;
            color: var(--dark-color);
            font-size: 0.9rem;
        }

        .user-role {
            color: #64748b;
            font-size: 0.75rem;
        }

        /* ===== CONTENEDOR PRINCIPAL ===== */
        .main-container {
            max-width: 1400px;
            margin: 0 auto;
            padding: 2rem;
        }

        .dashboard-header {
            text-align: center;
            margin-bottom: 2rem;
        }

        .dashboard-title {
            font-size: 2.5rem;
            font-weight: 800;
            background: var(--gradient-primary);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            margin-bottom: 0.5rem;
        }

        .dashboard-subtitle {
            color: rgba(255, 255, 255, 0.8);
            font-size: 1.1rem;
            font-weight: 500;
        }

        /* ===== SISTEMA DE PESTAÑAS ===== */
        .nav-tabs-modern {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(20px);
            border-radius: 20px;
            padding: 0.5rem;
            margin-bottom: 2rem;
            border: none;
            box-shadow: 0 10px 40px rgba(0, 0, 0, 0.1);
        }

        .nav-tabs-modern .nav-link {
            background: transparent;
            border: none;
            border-radius: 15px;
            padding: 1rem 2rem;
            font-weight: 600;
            color: #64748b;
            transition: all 0.3s ease;
            margin: 0 0.25rem;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .nav-tabs-modern .nav-link:hover {
            color: var(--primary-color);
            background: rgba(111, 66, 193, 0.1);
            transform: translateY(-2px);
        }

        .nav-tabs-modern .nav-link.active {
            background: var(--gradient-primary);
            color: white !important;
            box-shadow: 0 8px 25px rgba(111, 66, 193, 0.3);
        }

        .nav-link i {
            font-size: 1.1rem;
        }

        /* ===== CONTENIDO DE PESTAÑAS ===== */
        .tab-content {
            background: rgba(255, 255, 255, 0.98);
            backdrop-filter: blur(20px);
            border-radius: 20px;
            padding: 2.5rem;
            box-shadow: 0 20px 60px rgba(0, 0, 0, 0.1);
            border: 1px solid rgba(255, 255, 255, 0.2);
        }

        /* ===== CARDS ESTADÍSTICAS ===== */
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
            gap: 1.5rem;
            margin-bottom: 2rem;
        }

        .stat-card {
            background: rgba(255, 255, 255, 0.9);
            border-radius: 20px;
            padding: 2rem;
            text-align: center;
            border: 2px solid transparent;
            transition: all 0.3s ease;
            position: relative;
            overflow: hidden;
        }

        .stat-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 4px;
            background: var(--gradient-primary);
        }

        .stat-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 15px 40px rgba(0, 0, 0, 0.15);
            border-color: var(--primary-color);
        }

        .stat-icon {
            width: 70px;
            height: 70px;
            margin: 0 auto 1.5rem;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.8rem;
            color: white;
        }

        .stat-card-primary .stat-icon { background: var(--gradient-primary); }
        .stat-card-success .stat-icon { background: var(--gradient-success); }
        .stat-card-warning .stat-icon { background: linear-gradient(135deg, #ffc107, #ff8c00); }
        .stat-card-info .stat-icon { background: linear-gradient(135deg, #17a2b8, #007bff); }

        .stat-number {
            font-size: 2.5rem;
            font-weight: 800;
            margin-bottom: 0.5rem;
            color: var(--dark-color);
        }

        .stat-label {
            font-size: 1rem;
            font-weight: 600;
            color: #64748b;
            margin-bottom: 0.5rem;
        }

        .stat-description {
            font-size: 0.85rem;
            color: #94a3b8;
        }

        /* ===== BOTONES MODERNOS ===== */
        .btn-modern {
            border: none;
            border-radius: 15px;
            padding: 0.75rem 2rem;
            font-weight: 600;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            transition: all 0.3s ease;
            cursor: pointer;
        }

        .btn-primary-modern {
            background: var(--gradient-primary);
            color: white;
        }

        .btn-primary-modern:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 30px rgba(111, 66, 193, 0.3);
            color: white;
        }

        .btn-success-modern {
            background: var(--gradient-success);
            color: white;
        }

        .btn-success-modern:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 30px rgba(40, 167, 69, 0.3);
            color: white;
        }

        .btn-outline-modern {
            background: transparent;
            color: var(--primary-color);
            border: 2px solid var(--primary-color);
        }

        .btn-outline-modern:hover {
            background: var(--primary-color);
            color: white;
            transform: translateY(-2px);
        }

        /* ===== FORMULARIOS MODERNOS ===== */
        .form-modern {
            background: rgba(255, 255, 255, 0.7);
            border-radius: 20px;
            padding: 2rem;
            border: 1px solid rgba(255, 255, 255, 0.3);
            margin-bottom: 1.5rem;
        }

        .form-group-modern {
            margin-bottom: 1.5rem;
        }

        .form-label-modern {
            font-weight: 600;
            color: var(--dark-color);
            margin-bottom: 0.5rem;
            display: block;
        }

        .form-control-modern {
            width: 100%;
            padding: 0.75rem 1rem;
            border: 2px solid #e2e8f0;
            border-radius: 12px;
            font-size: 0.95rem;
            transition: all 0.3s ease;
            background: rgba(255, 255, 255, 0.9);
        }

        .form-control-modern:focus {
            outline: none;
            border-color: var(--primary-color);
            box-shadow: 0 0 0 0.2rem rgba(111, 66, 193, 0.25);
            transform: translateY(-1px);
        }

        /* ===== TABLAS MODERNAS ===== */
        .table-modern {
            background: rgba(255, 255, 255, 0.9);
            border-radius: 15px;
            overflow: hidden;
            border: none;
            box-shadow: 0 5px 20px rgba(0, 0, 0, 0.1);
        }

        .table-modern th {
            background: var(--gradient-primary);
            color: white;
            font-weight: 600;
            padding: 1.25rem 1rem;
            border: none;
        }

        .table-modern td {
            padding: 1rem;
            border-color: #f1f5f9;
            vertical-align: middle;
        }

        .table-modern tbody tr:hover {
            background: rgba(111, 66, 193, 0.05);
        }

        /* ===== BADGES Y ESTADOS ===== */
        .badge-modern {
            padding: 0.5rem 1rem;
            border-radius: 10px;
            font-weight: 600;
            font-size: 0.8rem;
        }

        .badge-pending { background: linear-gradient(135deg, #ffc107, #ff8c00); color: white; }
        .badge-approved { background: linear-gradient(135deg, #28a745, #20c997); color: white; }
        .badge-rejected { background: linear-gradient(135deg, #dc3545, #e91e63); color: white; }
        .badge-draft { background: linear-gradient(135deg, #6c757d, #adb5bd); color: white; }

        /* ===== PROGRESS BARS ===== */
        .progress-modern {
            height: 12px;
            border-radius: 10px;
            background: #e2e8f0;
            overflow: hidden;
        }

        .progress-bar-modern {
            height: 100%;
            background: var(--gradient-primary);
            border-radius: 10px;
            transition: width 0.6s ease;
        }

        /* ===== ALERTAS MODERNAS ===== */
        .alert-modern {
            border-radius: 15px;
            padding: 1.25rem 1.5rem;
            border: none;
            margin-bottom: 1.5rem;
            display: flex;
            align-items: center;
            gap: 0.75rem;
        }

        .alert-info-modern {
            background: linear-gradient(135deg, rgba(23, 162, 184, 0.1), rgba(0, 123, 255, 0.1));
            color: #0056b3;
        }

        .alert-warning-modern {
            background: linear-gradient(135deg, rgba(255, 193, 7, 0.1), rgba(255, 140, 0, 0.1));
            color: #856404;
        }

        .alert-success-modern {
            background: linear-gradient(135deg, rgba(40, 167, 69, 0.1), rgba(32, 201, 151, 0.1));
            color: #155724;
        }

        /* ===== RESPONSIVE ===== */
        @media (max-width: 768px) {
            .navbar-container {
                padding: 0 1rem;
            }
            
            .main-container {
                padding: 1rem;
            }
            
            .dashboard-title {
                font-size: 2rem;
            }
            
            .stats-grid {
                grid-template-columns: 1fr;
            }
            
            .tab-content {
                padding: 1.5rem;
            }
            
            .nav-tabs-modern .nav-link {
                padding: 0.75rem 1rem;
                font-size: 0.9rem;
            }
        }

        /* ===== ANIMACIONES ===== */
        .fade-in {
            animation: fadeIn 0.6s ease-out;
        }

        @keyframes fadeIn {
            from {
                opacity: 0;
                transform: translateY(20px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .slide-up {
            animation: slideUp 0.6s ease-out;
        }

        @keyframes slideUp {
            from {
                opacity: 0;
                transform: translateY(30px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }
    </style>
</head>
<body>
    <!-- Navegación Principal -->
    <nav class="navbar-modern">
        <div class="navbar-container">
            <!-- Logo y Brand -->
            <div class="navbar-brand">
                <div class="brand-icon">
                    <i class="fas fa-graduation-cap"></i>
                </div>
                <div class="brand-text">
                    <h4>Uniminuto</h4>
                    <span>Sistema de Admisiones</span>
                </div>
            </div>
            
            <!-- Menú de Usuario -->
            <div class="user-dropdown">
                <button class="user-btn" onclick="window.location.href='http://localhost:3000/views/login.php'">
                    <div class="user-avatar">
                        <img src="https://ui-avatars.com/api/?name=<?php echo urlencode($userName); ?>&background=6f42c1&color=fff" alt="Avatar">
                    </div>
                    <div class="user-info">
                        <div class="user-name"><?php echo htmlspecialchars($userName); ?></div>
                        <small class="user-role">Estudiante</small>
                    </div>
                    <i class="fas fa-sign-out-alt ms-2"></i>
                </button>
            </div>
        </div>
    </nav>

    <!-- Contenedor Principal -->
    <div class="main-container">
        <!-- Header del Dashboard -->
        <div class="dashboard-header fade-in">
            <h1 class="dashboard-title">¡Bienvenido, <?php echo htmlspecialchars($userName); ?>!</h1>
            <p class="dashboard-subtitle">Gestiona tu proceso de admisión de forma fácil y eficiente</p>
        </div>

        <!-- Sistema de Pestañas -->
        <ul class="nav nav-tabs nav-tabs-modern fade-in" id="dashboardTabs" role="tablist">
            <li class="nav-item" role="presentation">
                <button class="nav-link active" id="dashboard-tab" data-bs-toggle="tab" data-bs-target="#dashboard" type="button" role="tab">
                    <i class="fas fa-tachometer-alt"></i>
                    Dashboard
                </button>
            </li>
            <li class="nav-item" role="presentation">
                <button class="nav-link" id="info-personal-tab" data-bs-toggle="tab" data-bs-target="#info-personal" type="button" role="tab">
                    <i class="fas fa-user"></i>
                    Información Personal
                </button>
            </li>
            <li class="nav-item" role="presentation">
                <button class="nav-link" id="info-academica-tab" data-bs-toggle="tab" data-bs-target="#info-academica" type="button" role="tab">
                    <i class="fas fa-graduation-cap"></i>
                    Información Académica
                </button>
            </li>
            <li class="nav-item" role="presentation">
                <button class="nav-link" id="solicitudes-tab" data-bs-toggle="tab" data-bs-target="#solicitudes" type="button" role="tab">
                    <i class="fas fa-file-alt"></i>
                    Solicitudes
                </button>
            </li>
            <li class="nav-item" role="presentation">
                <button class="nav-link" id="mi-perfil-tab" data-bs-toggle="tab" data-bs-target="#mi-perfil" type="button" role="tab">
                    <i class="fas fa-chart-line"></i>
                    Mi Perfil
                </button>
            </li>
        </ul>

        <!-- Contenido de las Pestañas -->
        <div class="tab-content slide-up" id="dashboardTabsContent">
            
            <!-- PESTAÑA: DASHBOARD -->
            <div class="tab-pane fade show active" id="dashboard" role="tabpanel">
                <h2 class="mb-4"><i class="fas fa-tachometer-alt text-primary me-2"></i>Resumen General</h2>
                
                <!-- Estadísticas Principales -->
                <div class="stats-grid">
                    <div class="stat-card stat-card-primary">
                        <div class="stat-icon">
                            <i class="fas fa-clipboard-check"></i>
                        </div>
                        <div class="stat-number"><?php echo $stats['progreso_perfil']; ?>%</div>
                        <div class="stat-label">Progreso del Perfil</div>
                        <div class="stat-description">Completitud de tu información</div>
                    </div>

                    <div class="stat-card stat-card-success">
                        <div class="stat-icon">
                            <i class="fas fa-file-alt"></i>
                        </div>
                        <div class="stat-number"><?php echo $stats['total_solicitudes']; ?></div>
                        <div class="stat-label">Total Solicitudes</div>
                        <div class="stat-description">Solicitudes creadas</div>
                    </div>

                    <div class="stat-card stat-card-warning">
                        <div class="stat-icon">
                            <i class="fas fa-clock"></i>
                        </div>
                        <div class="stat-number"><?php echo $stats['solicitudes_pendientes']; ?></div>
                        <div class="stat-label">Pendientes</div>
                        <div class="stat-description">En proceso de revisión</div>
                    </div>

                    <div class="stat-card stat-card-info">
                        <div class="stat-icon">
                            <i class="fas fa-check-circle"></i>
                        </div>
                        <div class="stat-number"><?php echo $stats['solicitudes_aprobadas']; ?></div>
                        <div class="stat-label">Aprobadas</div>
                        <div class="stat-description">Solicitudes exitosas</div>
                    </div>
                </div>

                <!-- Progreso del Perfil -->
                <div class="form-modern">
                    <h4 class="mb-3"><i class="fas fa-tasks text-primary me-2"></i>Estado de tu Perfil</h4>
                    <div class="row">
                        <div class="col-md-8">
                            <div class="progress-modern mb-3">
                                <div class="progress-bar-modern" style="width: <?php echo $stats['progreso_perfil']; ?>%"></div>
                            </div>
                        </div>
                        <div class="col-md-4 text-end">
                            <strong><?php echo $stats['progreso_perfil']; ?>% completado</strong>
                        </div>
                    </div>
                    
                    <div class="row mt-3">
                        <div class="col-md-4">
                            <div class="d-flex align-items-center">
                                <i class="fas fa-<?php echo $stats['info_personal_completa'] ? 'check-circle text-success' : 'circle text-muted'; ?> me-2"></i>
                                Información Personal
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="d-flex align-items-center">
                                <i class="fas fa-<?php echo $stats['info_academica_completa'] ? 'check-circle text-success' : 'circle text-muted'; ?> me-2"></i>
                                Información Académica
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="d-flex align-items-center">
                                <i class="fas fa-<?php echo $stats['total_solicitudes'] > 0 ? 'check-circle text-success' : 'circle text-muted'; ?> me-2"></i>
                                Solicitudes Enviadas
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Acciones Rápidas -->
                <div class="form-modern">
                    <h4 class="mb-3"><i class="fas fa-rocket text-primary me-2"></i>Acciones Rápidas</h4>
                    <div class="row">
                        <div class="col-md-3 mb-3">
                            <a href="#info-personal" class="btn btn-primary-modern w-100" onclick="switchTab('info-personal-tab')">
                                <i class="fas fa-user me-2"></i>
                                Completar Perfil Personal
                            </a>
                        </div>
                        <div class="col-md-3 mb-3">
                            <a href="#info-academica" class="btn btn-success-modern w-100" onclick="switchTab('info-academica-tab')">
                                <i class="fas fa-graduation-cap me-2"></i>
                                Info. Académica
                            </a>
                        </div>
                        <div class="col-md-3 mb-3">
                            <a href="#solicitudes" class="btn btn-outline-modern w-100" onclick="switchTab('solicitudes-tab')">
                                <i class="fas fa-plus me-2"></i>
                                Nueva Solicitud
                            </a>
                        </div>
                        <div class="col-md-3 mb-3">
                            <a href="#mi-perfil" class="btn btn-outline-modern w-100" onclick="switchTab('mi-perfil-tab')">
                                <i class="fas fa-chart-line me-2"></i>
                                Ver Progreso
                            </a>
                        </div>
                    </div>
                </div>
            </div>

            <!-- PESTAÑA: INFORMACIÓN PERSONAL -->
            <div class="tab-pane fade" id="info-personal" role="tabpanel">
                <h2 class="mb-4"><i class="fas fa-user text-primary me-2"></i>Información Personal</h2>
                
                <?php if (!$stats['info_personal_completa']): ?>
                <div class="alert-warning-modern">
                    <i class="fas fa-exclamation-triangle"></i>
                    <div>
                        <strong>Información Incompleta</strong><br>
                        Complete su información personal para continuar con el proceso de admisión.
                    </div>
                </div>
                <?php endif; ?>

                <form id="infoPersonalForm" class="form-modern">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group-modern">
                                <label class="form-label-modern">Nombres *</label>
                                <input type="text" class="form-control-modern" id="nombres" name="nombres" required>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group-modern">
                                <label class="form-label-modern">Apellidos *</label>
                                <input type="text" class="form-control-modern" id="apellidos" name="apellidos" required>
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group-modern">
                                <label class="form-label-modern">Tipo de Documento *</label>
                                <select class="form-control-modern" id="tipoDocumento" name="tipoDocumento" required>
                                    <option value="">Seleccione...</option>
                                    <option value="CC">Cédula de Ciudadanía</option>
                                    <option value="TI">Tarjeta de Identidad</option>
                                    <option value="CE">Cédula de Extranjería</option>
                                    <option value="PP">Pasaporte</option>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group-modern">
                                <label class="form-label-modern">Número de Documento *</label>
                                <input type="text" class="form-control-modern" id="numeroDocumento" name="numeroDocumento" required>
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group-modern">
                                <label class="form-label-modern">Fecha de Nacimiento *</label>
                                <input type="date" class="form-control-modern" id="fechaNacimiento" name="fechaNacimiento" required>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group-modern">
                                <label class="form-label-modern">Género *</label>
                                <select class="form-control-modern" id="genero" name="genero" required>
                                    <option value="">Seleccione...</option>
                                    <option value="M">Masculino</option>
                                    <option value="F">Femenino</option>
                                </select>
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group-modern">
                                <label class="form-label-modern">Teléfono Celular *</label>
                                <input type="tel" class="form-control-modern" id="celular" name="celular" required placeholder="3001234567">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group-modern">
                                <label class="form-label-modern">Tipo de Sangre</label>
                                <select class="form-control-modern" id="tipoSangre" name="tipoSangre">
                                    <option value="">Seleccione...</option>
                                    <option value="A+">A+</option>
                                    <option value="A-">A-</option>
                                    <option value="B+">B+</option>
                                    <option value="B-">B-</option>
                                    <option value="AB+">AB+</option>
                                    <option value="AB-">AB-</option>
                                    <option value="O+">O+</option>
                                    <option value="O-">O-</option>
                                </select>
                            </div>
                        </div>
                    </div>

                    <div class="form-group-modern">
                        <label class="form-label-modern">Dirección de Residencia *</label>
                        <input type="text" class="form-control-modern" id="direccion" name="direccion" required placeholder="Calle, carrera, número, barrio">
                    </div>

                    <div class="row">
                        <div class="col-md-4">
                            <div class="form-group-modern">
                                <label class="form-label-modern">Departamento *</label>
                                <select class="form-control-modern" id="departamento" name="departamento" required>
                                    <option value="">Seleccione...</option>
                                    <option value="Córdoba">Córdoba</option>
                                    <option value="Antioquia">Antioquia</option>
                                    <option value="Atlántico">Atlántico</option>
                                    <option value="Bogotá">Bogotá D.C.</option>
                                    <option value="Bolívar">Bolívar</option>
                                    <option value="Caldas">Caldas</option>
                                    <option value="Sucre">Sucre</option>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group-modern">
                                <label class="form-label-modern">Ciudad *</label>
                                <input type="text" class="form-control-modern" id="ciudad" name="ciudad" required>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group-modern">
                                <label class="form-label-modern">País *</label>
                                <input type="text" class="form-control-modern" id="pais" name="pais" value="Colombia" required>
                            </div>
                        </div>
                    </div>

                    <div class="text-end">
                        <button type="button" class="btn btn-outline-modern me-2" onclick="cargarInfoPersonal()">
                            <i class="fas fa-refresh me-2"></i>Cargar Datos
                        </button>
                        <button type="submit" class="btn btn-primary-modern">
                            <i class="fas fa-save me-2"></i>Guardar Información
                        </button>
                    </div>
                </form>
            </div>

            <!-- PESTAÑA: INFORMACIÓN ACADÉMICA -->
            <div class="tab-pane fade" id="info-academica" role="tabpanel">
                <h2 class="mb-4"><i class="fas fa-graduation-cap text-success me-2"></i>Información Académica</h2>
                
                <?php if (!$stats['info_academica_completa']): ?>
                <div class="alert-warning-modern">
                    <i class="fas fa-exclamation-triangle"></i>
                    <div>
                        <strong>Información Académica Incompleta</strong><br>
                        Complete su información académica para poder aplicar a programas.
                    </div>
                </div>
                <?php endif; ?>

                <form id="infoAcademicaForm" class="form-modern">
                    <h4 class="mb-3"><i class="fas fa-school text-success me-2"></i>Información del Bachillerato</h4>
                    
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group-modern">
                                <label class="form-label-modern">Nombre de la Institución *</label>
                                <input type="text" class="form-control-modern" id="nombreInstitucion" name="nombreInstitucion" required>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group-modern">
                                <label class="form-label-modern">Ciudad de la Institución *</label>
                                <input type="text" class="form-control-modern" id="ciudadInstitucion" name="ciudadInstitucion" required>
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group-modern">
                                <label class="form-label-modern">Departamento de la Institución *</label>
                                <select class="form-control-modern" id="departamentoInstitucion" name="departamentoInstitucion" required>
                                    <option value="">Seleccione...</option>
                                    <option value="Cordoba">Córdoba</option>
                                    <option value="Antioquia">Antioquia</option>
                                    <option value="Atlantico">Atlántico</option>
                                    <option value="Bogota">Bogotá D.C.</option>
                                    <option value="Bolivar">Bolívar</option>
                                    <option value="Sucre">Sucre</option>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group-modern">
                                <label class="form-label-modern">Tipo de Bachillerato *</label>
                                <select class="form-control-modern" id="tipoBachillerato" name="tipoBachillerato" required>
                                    <option value="">Seleccione...</option>
                                    <option value="ACADEMICO">Académico</option>
                                    <option value="TECNICO">Técnico</option>
                                    <option value="COMERCIAL">Comercial</option>
                                    <option value="PEDAGOGICO">Pedagógico</option>
                                    <option value="INDUSTRIAL">Industrial</option>
                                    <option value="AGROPECUARIO">Agropecuario</option>
                                    <option value="OTRO">Otro</option>
                                </select>
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group-modern">
                                <label class="form-label-modern">Jornada *</label>
                                <select class="form-control-modern" id="jornada" name="jornada" required>
                                    <option value="">Seleccione...</option>
                                    <option value="MANANA">Mañana</option>
                                    <option value="TARDE">Tarde</option>
                                    <option value="NOCHE">Noche</option>
                                    <option value="COMPLETA">Completa</option>
                                    <option value="SABATINA">Sabatina</option>
                                    <option value="DOMINICAL">Dominical</option>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group-modern">
                                <label class="form-label-modern">Carácter de la Institución *</label>
                                <select class="form-control-modern" id="caracterInstitucion" name="caracterInstitucion" required>
                                    <option value="">Seleccione...</option>
                                    <option value="PUBLICO">Público</option>
                                    <option value="PRIVADO">Privado</option>
                                    <option value="MIXTO">Mixto</option>
                                </select>
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group-modern">
                                <label class="form-label-modern">Año de Graduación *</label>
                                <input type="number" class="form-control-modern" id="anoGraduacion" name="anoGraduacion" 
                                       min="1990" max="<?php echo date('Y'); ?>" required>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group-modern">
                                <label class="form-label-modern">Promedio Académico</label>
                                <input type="number" class="form-control-modern" id="promedioAcademico" name="promedioAcademico" 
                                       step="0.01" min="0" max="5" placeholder="Ej: 4.5">
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-4">
                            <div class="form-group-modern">
                                <label class="form-label-modern">Puntaje ICFES</label>
                                <input type="number" class="form-control-modern" id="puntajeIcfes" name="puntajeIcfes" 
                                       min="0" max="500" placeholder="Ej: 350">
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group-modern">
                                <label class="form-label-modern">Posición en el Curso</label>
                                <input type="number" class="form-control-modern" id="posicionCurso" name="posicionCurso" 
                                       min="1" placeholder="Ej: 5">
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group-modern">
                                <label class="form-label-modern">Total de Estudiantes</label>
                                <input type="number" class="form-control-modern" id="totalEstudiantes" name="totalEstudiantes" 
                                       min="1" placeholder="Ej: 45">
                            </div>
                        </div>
                    </div>

                    <div class="form-group-modern">
                        <label class="form-label-modern">Observaciones</label>
                        <textarea class="form-control-modern" id="observaciones" name="observaciones" 
                                  rows="3" placeholder="Información adicional relevante..."></textarea>
                    </div>

                    <div class="text-end">
                        <button type="button" class="btn btn-outline-modern me-2" onclick="cargarInfoAcademica()">
                            <i class="fas fa-refresh me-2"></i>Cargar Datos
                        </button>
                        <button type="submit" class="btn btn-success-modern">
                            <i class="fas fa-save me-2"></i>Guardar Información
                        </button>
                    </div>
                </form>
            </div>

            <!-- PESTAÑA: SOLICITUDES -->
            <div class="tab-pane fade" id="solicitudes" role="tabpanel">
                <h2 class="mb-4"><i class="fas fa-file-alt text-warning me-2"></i>Gestión de Solicitudes</h2>
                
                <!-- Botón para Nueva Solicitud -->
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <div>
                        <h4>Mis Solicitudes</h4>
                        <p class="text-muted mb-0">Gestiona y da seguimiento a tus solicitudes de admisión</p>
                    </div>
                    <button class="btn btn-primary-modern" onclick="crearNuevaSolicitud()">
                        <i class="fas fa-plus me-2"></i>Nueva Solicitud
                    </button>
                </div>

                <!-- Lista de Solicitudes -->
                <div id="listaSolicitudes">
                    <?php if (empty($solicitudes)): ?>
                    <div class="alert-info-modern">
                        <i class="fas fa-info-circle"></i>
                        <div>
                            <strong>No tienes solicitudes</strong><br>
                            Crea tu primera solicitud de admisión para comenzar el proceso.
                        </div>
                    </div>
                    <?php else: ?>
                    <div class="table-responsive">
                        <table class="table table-modern">
                            <thead>
                                <tr>
                                    <th>Radicado</th>
                                    <th>Programa</th>
                                    <th>Sede</th>
                                    <th>Estado</th>
                                    <th>Fecha</th>
                                    <th>Acciones</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($solicitudes as $solicitud): ?>
                                <tr>
                                    <td><strong><?php echo htmlspecialchars($solicitud['numero_radicado'] ?? 'N/A'); ?></strong></td>
                                    <td><?php echo htmlspecialchars($solicitud['programa'] ?? 'No especificado'); ?></td>
                                    <td><?php echo htmlspecialchars($solicitud['sede'] ?? 'No especificada'); ?></td>
                                    <td>
                                        <?php 
                                        $estado = $solicitud['estado'] ?? 'BORRADOR';
                                        $badgeClass = match($estado) {
                                            'APROBADA' => 'badge-approved',
                                            'PENDIENTE' => 'badge-pending',
                                            'RECHAZADA' => 'badge-rejected',
                                            default => 'badge-draft'
                                        };
                                        ?>
                                        <span class="badge-modern <?php echo $badgeClass; ?>">
                                            <?php echo htmlspecialchars($estado); ?>
                                        </span>
                                    </td>
                                    <td><?php echo htmlspecialchars($solicitud['fecha_creacion'] ?? 'N/A'); ?></td>
                                    <td>
                                        <button class="btn btn-outline-modern btn-sm" onclick="verSolicitud(<?php echo $solicitud['id']; ?>)">
                                            <i class="fas fa-eye"></i>
                                        </button>
                                        <?php if ($estado === 'BORRADOR'): ?>
                                        <button class="btn btn-primary-modern btn-sm" onclick="editarSolicitud(<?php echo $solicitud['id']; ?>)">
                                            <i class="fas fa-edit"></i>
                                        </button>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                    <?php endif; ?>
                </div>

                <!-- Formulario de Nueva Solicitud (Oculto inicialmente) -->
                <div id="nuevaSolicitudForm" style="display: none;" class="form-modern mt-4">
                    <h4 class="mb-3"><i class="fas fa-plus text-primary me-2"></i>Nueva Solicitud de Admisión</h4>
                    
                    <form id="formSolicitud">
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group-modern">
                                    <label class="form-label-modern">Programa Académico *</label>
                                    <select class="form-control-modern" id="programa" name="programa" required>
                                        <option value="">Seleccione un programa...</option>
                                        <option value="Ingeniería de Sistemas">Ingeniería de Sistemas</option>
                                        <option value="Administración de Empresas">Administración de Empresas</option>
                                        <option value="Contaduría Pública">Contaduría Pública</option>
                                        <option value="Derecho">Derecho</option>
                                        <option value="Psicología">Psicología</option>
                                        <option value="Enfermería">Enfermería</option>
                                        <option value="Trabajo Social">Trabajo Social</option>
                                        <option value="Comunicación Social">Comunicación Social</option>
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group-modern">
                                    <label class="form-label-modern">Sede *</label>
                                    <select class="form-control-modern" id="sede" name="sede" required>
                                        <option value="">Seleccione una sede...</option>
                                        <option value="Montería">Montería</option>
                                        <option value="Lorica">Lorica</option>
                                        <option value="Sahagún">Sahagún</option>
                                        <option value="Planeta Rica">Planeta Rica</option>
                                    </select>
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group-modern">
                                    <label class="form-label-modern">Modalidad *</label>
                                    <select class="form-control-modern" id="modalidad" name="modalidad" required>
                                        <option value="">Seleccione modalidad...</option>
                                        <option value="Presencial">Presencial</option>
                                        <option value="Virtual">Virtual</option>
                                        <option value="Semipresencial">Semipresencial</option>
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group-modern">
                                    <label class="form-label-modern">Jornada *</label>
                                    <select class="form-control-modern" id="jornadaSolicitud" name="jornadaSolicitud" required>
                                        <option value="">Seleccione jornada...</option>
                                        <option value="Mañana">Mañana</option>
                                        <option value="Tarde">Tarde</option>
                                        <option value="Noche">Noche</option>
                                        <option value="Sábados">Sábados</option>
                                    </select>
                                </div>
                            </div>
                        </div>

                        <div class="form-group-modern">
                            <label class="form-label-modern">Observaciones</label>
                            <textarea class="form-control-modern" id="observacionesSolicitud" name="observacionesSolicitud" 
                                      rows="3" placeholder="Información adicional sobre tu solicitud..."></textarea>
                        </div>

                        <div class="text-end">
                            <button type="button" class="btn btn-outline-modern me-2" onclick="cancelarSolicitud()">
                                <i class="fas fa-times me-2"></i>Cancelar
                            </button>
                            <button type="submit" class="btn btn-success-modern">
                                <i class="fas fa-paper-plane me-2"></i>Enviar Solicitud
                            </button>
                        </div>
                    </form>
                </div>
            </div>

            <!-- PESTAÑA: MI PERFIL -->
            <div class="tab-pane fade" id="mi-perfil" role="tabpanel">
                <h2 class="mb-4">
                    <i class="fas fa-user-circle text-info me-2"></i>Mi Perfil Completo
                    <button class="btn btn-outline-info btn-sm float-end" onclick="refreshAllProfileData()">
                        <i class="fas fa-sync-alt me-1"></i> Actualizar
                    </button>
                </h2>
                
                <!-- Header del Usuario con Avatar y Datos Básicos -->
                <div class="form-modern mb-4">
                    <div class="row align-items-center">
                        <div class="col-md-2 text-center">
                            <div class="user-avatar-large mx-auto mb-3" style="width: 100px; height: 100px;">
                                <img src="https://ui-avatars.com/api/?name=<?php echo urlencode($userName); ?>&background=6f42c1&color=fff&size=100" 
                                     alt="Avatar" class="img-fluid rounded-circle shadow-lg">
                            </div>
                        </div>
                        <div class="col-md-10">
                            <div class="row">
                                <div class="col-md-4">
                                    <h4 class="mb-1" id="profile-user-name"><?php echo htmlspecialchars($userName); ?></h4>
                                    <p class="text-muted mb-2" id="profile-user-email"><?php echo htmlspecialchars($userEmail); ?></p>
                                    <span class="badge bg-primary" id="profile-user-id">ID: <?php echo $userId; ?></span>
                                </div>
                                <div class="col-md-4">
                                    <div class="d-flex align-items-center mb-2">
                                        <i class="fas fa-chart-line text-success me-2"></i>
                                        <span class="fw-semibold">Progreso del Perfil</span>
                                    </div>
                                    <div class="progress-modern">
                                        <div class="progress-bar-modern" id="profile-progress-bar" style="width: <?php echo $stats['progreso_perfil']; ?>%"></div>
                                    </div>
                                    <small class="text-muted" id="profile-progress-text"><?php echo $stats['progreso_perfil']; ?>% completado</small>
                                </div>
                                <div class="col-md-4">
                                    <div class="text-end">
                                        <div class="d-inline-block me-3">
                                            <i class="fas fa-calendar-alt text-info me-1"></i>
                                            <small class="text-muted">Última actualización</small>
                                            <div id="profile-last-update" class="fw-semibold small">--</div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Pestañas de Información -->
                <ul class="nav nav-pills nav-fill mb-4" id="profileTabs" role="tablist">
                    <li class="nav-item" role="presentation">
                        <button class="nav-link active" id="profile-personal-tab" data-bs-toggle="pill" data-bs-target="#profile-personal" type="button">
                            <i class="fas fa-user me-1"></i>Personal
                        </button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link" id="profile-academic-tab" data-bs-toggle="pill" data-bs-target="#profile-academic" type="button">
                            <i class="fas fa-graduation-cap me-1"></i>Académica
                        </button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link" id="profile-requests-tab" data-bs-toggle="pill" data-bs-target="#profile-requests" type="button">
                            <i class="fas fa-file-alt me-1"></i>Solicitudes
                        </button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link" id="profile-summary-tab" data-bs-toggle="pill" data-bs-target="#profile-summary" type="button">
                            <i class="fas fa-chart-pie me-1"></i>Resumen
                        </button>
                    </li>
                </ul>

                <!-- Contenido de Pestañas del Perfil -->
                <div class="tab-content" id="profileTabsContent">
                    
                    <!-- INFORMACIÓN PERSONAL DETALLADA -->
                    <div class="tab-pane fade show active" id="profile-personal" role="tabpanel">
                        <div class="form-modern">
                            <div class="d-flex justify-content-between align-items-center mb-3">
                                <h4><i class="fas fa-user text-primary me-2"></i>Información Personal</h4>
                                <div class="badge bg-success" id="personal-status">Cargando...</div>
                            </div>
                            
                            <div class="row" id="personal-info-container">
                                <div class="col-12 text-center py-4">
                                    <div class="spinner-border text-primary" role="status">
                                        <span class="visually-hidden">Cargando...</span>
                                    </div>
                                    <p class="mt-2 text-muted">Cargando información personal...</p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- INFORMACIÓN ACADÉMICA DETALLADA -->
                    <div class="tab-pane fade" id="profile-academic" role="tabpanel">
                        <div class="form-modern">
                            <div class="d-flex justify-content-between align-items-center mb-3">
                                <h4><i class="fas fa-graduation-cap text-success me-2"></i>Información Académica</h4>
                                <div class="badge bg-info" id="academic-status">Cargando...</div>
                            </div>
                            
                            <div class="row" id="academic-info-container">
                                <div class="col-12 text-center py-4">
                                    <div class="spinner-border text-success" role="status">
                                        <span class="visually-hidden">Cargando...</span>
                                    </div>
                                    <p class="mt-2 text-muted">Cargando información académica...</p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- SOLICITUDES DETALLADAS -->
                    <div class="tab-pane fade" id="profile-requests" role="tabpanel">
                        <div class="form-modern">
                            <div class="d-flex justify-content-between align-items-center mb-3">
                                <h4><i class="fas fa-file-alt text-warning me-2"></i>Mis Solicitudes</h4>
                                <div class="badge bg-warning" id="requests-status">Cargando...</div>
                            </div>
                            
                            <div id="requests-info-container">
                                <div class="text-center py-4">
                                    <div class="spinner-border text-warning" role="status">
                                        <span class="visually-hidden">Cargando...</span>
                                    </div>
                                    <p class="mt-2 text-muted">Cargando solicitudes...</p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- RESUMEN COMPLETO -->
                    <div class="tab-pane fade" id="profile-summary" role="tabpanel">
                        <div class="form-modern">
                            <h4><i class="fas fa-chart-pie text-info me-2"></i>Resumen Completo</h4>
                            
                            <div class="row">
                                <div class="col-md-8">
                                    <div class="row" id="summary-stats-container">
                                        <div class="col-12 text-center py-4">
                                            <div class="spinner-border text-info" role="status">
                                                <span class="visually-hidden">Cargando...</span>
                                            </div>
                                            <p class="mt-2 text-muted">Generando resumen...</p>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="card border-0 bg-light">
                                        <div class="card-body">
                                            <h6><i class="fas fa-tasks me-1"></i>Acciones Rápidas</h6>
                                            <div class="d-grid gap-2">
                                                <button class="btn btn-sm btn-outline-primary" onclick="switchTab('info-personal-tab')">
                                                    <i class="fas fa-edit me-1"></i>Editar Personal
                                                </button>
                                                <button class="btn btn-sm btn-outline-success" onclick="switchTab('info-academica-tab')">
                                                    <i class="fas fa-edit me-1"></i>Editar Académica
                                                </button>
                                                <button class="btn btn-sm btn-outline-warning" onclick="switchTab('solicitudes-tab')">
                                                    <i class="fas fa-plus me-1"></i>Nueva Solicitud
                                                </button>
                                                <button class="btn btn-sm btn-outline-info" onclick="exportProfile()">
                                                    <i class="fas fa-download me-1"></i>Exportar Perfil
                                                </button>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- JavaScript Libraries -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    
    <script>
        // ===== VARIABLES GLOBALES =====
    const API_URL = 'http://localhost:8000/api';
    const userId = <?php echo $userId; ?>;
    // In development, use dev_user_id query param so we avoid custom headers and CORS preflight issues
    const DEV_USER_ID = <?php echo (($_ENV['APP_ENV'] ?? 'development') !== 'production') ? (int)$userId : 'null'; ?>;
    // Ensure we always send a dev_user_id param (falls back to session userId). This keeps calls simple and avoids preflight issues.
    const DEV_QUERY = `?dev_user_id=${DEV_USER_ID || userId}`;
        
        // ===== FUNCIONES DE NAVEGACIÓN =====
        function switchTab(tabId) {
            const tab = document.getElementById(tabId);
            if (tab) {
                const bootstrap = window.bootstrap || {};
                const tabInstance = new bootstrap.Tab(tab);
                tabInstance.show();
            }
        }
        
        // ===== FUNCIONES DE INFORMACIÓN PERSONAL =====
        async function cargarInfoPersonal() {
            try {
                const url = `${API_URL}/info-personal-clean/get${DEV_QUERY}`;
                const response = await fetch(url, { method: 'GET' });
                
                if (response.ok) {
                    const data = await response.json();
                    if (data.success && data.infoPersonal) {
                        const info = data.infoPersonal;
                        
                        // Llenar campos del formulario
                        document.getElementById('nombres').value = info.nombres || '';
                        document.getElementById('apellidos').value = info.apellidos || '';
                        document.getElementById('tipoDocumento').value = info.tipoDocumento || '';
                        document.getElementById('numeroDocumento').value = info.numeroDocumento || '';
                        document.getElementById('fechaNacimiento').value = info.fechaNacimiento || '';
                        document.getElementById('genero').value = info.genero || '';
                        document.getElementById('celular').value = info.celular || '';
                        document.getElementById('tipoSangre').value = info.tipoSangre || '';
                        document.getElementById('direccion').value = info.direccion || '';
                        document.getElementById('departamento').value = info.departamento || '';
                        document.getElementById('ciudad').value = info.ciudad || '';
                        document.getElementById('pais').value = info.pais || 'Colombia';
                        
                        showAlert('Información personal cargada correctamente', 'success');
                    } else {
                        showAlert('No se encontró información personal guardada', 'info');
                    }
                } else {
                    throw new Error('Error al cargar la información');
                }
            } catch (error) {
                showAlert('Error al cargar información personal: ' + error.message, 'danger');
            }
        }
        
        // ===== FUNCIONES DE INFORMACIÓN ACADÉMICA =====
        async function cargarInfoAcademica() {
            try {
                const urlAc = `${API_URL}/info-academica-clean/get${DEV_QUERY}`;
                const response = await fetch(urlAc, { method: 'GET' });
                
                if (response.ok) {
                    const data = await response.json();
                    if (data.success && data.infoAcademica) {
                        const info = data.infoAcademica;
                        
                        // Llenar campos del formulario
                        document.getElementById('nombreInstitucion').value = info.nombre_institucion || '';
                        document.getElementById('ciudadInstitucion').value = info.ciudad_institucion || '';
                        document.getElementById('departamentoInstitucion').value = info.departamento_institucion || '';
                        document.getElementById('tipoBachillerato').value = info.tipo_bachillerato || '';
                        document.getElementById('jornada').value = info.jornada || '';
                        document.getElementById('caracterInstitucion').value = info.caracter_institucion || '';
                        document.getElementById('anoGraduacion').value = info.ano_graduacion || '';
                        document.getElementById('promedioAcademico').value = info.promedio_academico || '';
                        document.getElementById('puntajeIcfes').value = info.puntaje_icfes || '';
                        document.getElementById('posicionCurso').value = info.posicion_curso || '';
                        document.getElementById('totalEstudiantes').value = info.total_estudiantes || '';
                        document.getElementById('observaciones').value = info.observaciones || '';
                        
                        showAlert('Información académica cargada correctamente', 'success');
                    } else {
                        showAlert('No se encontró información académica guardada', 'info');
                    }
                } else {
                    throw new Error('Error al cargar la información');
                }
            } catch (error) {
                showAlert('Error al cargar información académica: ' + error.message, 'danger');
            }
        }
        
        // ===== FUNCIONES DE SOLICITUDES =====
        function crearNuevaSolicitud() {
            document.getElementById('nuevaSolicitudForm').style.display = 'block';
            document.getElementById('nuevaSolicitudForm').scrollIntoView({ behavior: 'smooth' });
        }
        
        function cancelarSolicitud() {
            document.getElementById('nuevaSolicitudForm').style.display = 'none';
            document.getElementById('formSolicitud').reset();
        }
        
        function verSolicitud(id) {
            showAlert('Funcionalidad de visualización en desarrollo', 'info');
        }
        
        function editarSolicitud(id) {
            showAlert('Funcionalidad de edición en desarrollo', 'info');
        }
        
        // ===== MANEJO DE FORMULARIOS =====
        document.addEventListener('DOMContentLoaded', function() {
            // Formulario de Información Personal
            const formInfoPersonal = document.getElementById('infoPersonalForm');
            if (formInfoPersonal) {
                formInfoPersonal.addEventListener('submit', async function(e) {
                    e.preventDefault();
                    
                    const formData = new FormData(formInfoPersonal);
                    const data = Object.fromEntries(formData);
                    
                    try {
                        const url = `${API_URL}/info-personal-clean/save${DEV_QUERY}`;
                        const response = await fetch(url, {
                            method: 'POST',
                            headers: { 'Content-Type': 'application/json' },
                            body: JSON.stringify(data)
                        });
                        
                        if (response.ok) {
                            const result = await response.json();
                            if (result.success) {
                                showAlert('Información personal guardada exitosamente', 'success');
                                // Actualizar perfil en tiempo real si la pestaña está abierta
                                const miPerfilTab = document.getElementById('mi-perfil');
                                if (miPerfilTab && miPerfilTab.classList.contains('active')) {
                                    setTimeout(() => {
                                        loadPersonalProfile();
                                        updateProfileSummary();
                                    }, 500);
                                }
                                setTimeout(() => {
                                    location.reload(); // Recargar para actualizar estadísticas
                                }, 1500);
                            } else {
                                showAlert('Error: ' + (result.message || 'No se pudo guardar'), 'danger');
                            }
                        } else {
                            throw new Error('Error en el servidor');
                        }
                    } catch (error) {
                        showAlert('Error al guardar información personal: ' + error.message, 'danger');
                    }
                });
            }
            
            // Formulario de Información Académica
            const formInfoAcademica = document.getElementById('infoAcademicaForm');
            if (formInfoAcademica) {
                formInfoAcademica.addEventListener('submit', async function(e) {
                    e.preventDefault();
                    
                    const formData = new FormData(formInfoAcademica);
                    const data = Object.fromEntries(formData);
                    
                    try {
                        const urlAc = `${API_URL}/info-academica-clean/save${DEV_QUERY}`;
                        const response = await fetch(urlAc, {
                            method: 'POST',
                            headers: { 'Content-Type': 'application/json' },
                            body: JSON.stringify(data)
                        });
                        
                        if (response.ok) {
                            const result = await response.json();
                            if (result.success) {
                                showAlert('Información académica guardada exitosamente', 'success');
                                // Actualizar perfil en tiempo real si la pestaña está abierta
                                const miPerfilTab = document.getElementById('mi-perfil');
                                if (miPerfilTab && miPerfilTab.classList.contains('active')) {
                                    setTimeout(() => {
                                        loadAcademicProfile();
                                        updateProfileSummary();
                                    }, 500);
                                }
                                setTimeout(() => {
                                    location.reload(); // Recargar para actualizar estadísticas
                                }, 1500);
                            } else {
                                showAlert('Error: ' + (result.message || 'No se pudo guardar'), 'danger');
                            }
                        } else {
                            throw new Error('Error en el servidor');
                        }
                    } catch (error) {
                        showAlert('Error al guardar información académica: ' + error.message, 'danger');
                    }
                });
            }
            
            // Formulario de Solicitud
            const formSolicitud = document.getElementById('formSolicitud');
            if (formSolicitud) {
                formSolicitud.addEventListener('submit', async function(e) {
                    e.preventDefault();
                    
                    const formData = new FormData(formSolicitud);
                    const data = Object.fromEntries(formData);
                    
                    try {
                                const url = `${API_URL}/solicitudes-clean/save${DEV_QUERY}`;
                                const response = await fetch(url, {
                                    method: 'POST',
                                    headers: {
                                        'Content-Type': 'application/json'
                                    },
                                    body: JSON.stringify(data)
                                });
                        
                        if (response.ok) {
                            const result = await response.json();
                            if (result.success) {
                                showAlert('Solicitud creada exitosamente', 'success');
                                // Actualizar perfil en tiempo real si la pestaña está abierta
                                const miPerfilTab = document.getElementById('mi-perfil');
                                if (miPerfilTab && miPerfilTab.classList.contains('active')) {
                                    setTimeout(() => {
                                        loadRequestsProfile();
                                        updateProfileSummary();
                                    }, 500);
                                }
                                setTimeout(() => {
                                    location.reload(); // Recargar para mostrar la nueva solicitud
                                }, 1500);
                            } else {
                                showAlert('Error: ' + (result.message || 'No se pudo crear la solicitud'), 'danger');
                            }
                        } else {
                            throw new Error('Error en el servidor');
                        }
                    } catch (error) {
                        showAlert('Error al crear solicitud: ' + error.message, 'danger');
                    }
                });
            }
        });
        
        // ===== FUNCIÓN DE ALERTAS =====
        function showAlert(message, type = 'info') {
            // Crear elemento de alerta
            const alertDiv = document.createElement('div');
            alertDiv.className = `alert alert-${type} alert-dismissible fade show position-fixed`;
            alertDiv.style.top = '20px';
            alertDiv.style.right = '20px';
            alertDiv.style.zIndex = '9999';
            alertDiv.style.maxWidth = '400px';
            alertDiv.innerHTML = `
                ${message}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            `;
            
            // Agregar al DOM
            document.body.appendChild(alertDiv);
            
            // Remover automáticamente después de 5 segundos
            setTimeout(() => {
                if (alertDiv.parentNode) {
                    alertDiv.remove();
                }
            }, 5000);
        }
        
        // ===== CARGAR DATOS AL CAMBIAR DE PESTAÑA =====
        document.addEventListener('shown.bs.tab', function(event) {
            const target = event.target.getAttribute('data-bs-target');
            
            if (target === '#info-personal') {
                cargarInfoPersonal();
            } else if (target === '#info-academica') {
                cargarInfoAcademica();
            } else if (target === '#mi-perfil') {
                loadProfileData();
            }
        });

        // ===== FUNCIONES DE MI PERFIL =====
        
        // Variables globales para el perfil
        let profileData = {
            personal: null,
            academic: null,
            requests: null,
            lastUpdate: new Date().toLocaleString('es-CO')
        };

        // Función para cargar todos los datos del perfil
        async function loadProfileData() {
            try {
                document.getElementById('profile-last-update').textContent = 'Actualizando...';
                
                // Cargar información personal
                await loadPersonalProfile();
                
                // Cargar información académica 
                await loadAcademicProfile();
                
                // Cargar solicitudes
                await loadRequestsProfile();
                
                // Actualizar resumen
                updateProfileSummary();
                
                // Actualizar timestamp
                profileData.lastUpdate = new Date().toLocaleString('es-CO');
                document.getElementById('profile-last-update').textContent = profileData.lastUpdate;
                
            } catch (error) {
                console.error('Error cargando datos del perfil:', error);
                showAlert('Error al cargar datos del perfil', 'danger');
            }
        }

        // Cargar información personal para el perfil (usa endpoint "clean" en dev para evitar auth/CORS)
        async function loadPersonalProfile() {
            try {
                const response = await fetch(`${API_URL}/info-personal-clean/get${DEV_QUERY}`);
                const result = await response.json();

                if (result.success && result.infoPersonal) {
                    profileData.personal = result.infoPersonal;
                    renderPersonalProfile(result.infoPersonal);
                    document.getElementById('personal-status').className = 'badge bg-success';
                    document.getElementById('personal-status').textContent = 'Completa';
                } else {
                    document.getElementById('personal-status').className = 'badge bg-warning';
                    document.getElementById('personal-status').textContent = 'Incompleta';
                    renderEmptyPersonalProfile();
                }
            } catch (error) {
                console.error('Error cargando info personal:', error);
                document.getElementById('personal-status').className = 'badge bg-danger';
                document.getElementById('personal-status').textContent = 'Error';
                renderErrorPersonalProfile();
            }
        }

        // Renderizar información personal
        function renderPersonalProfile(data) {
            const container = document.getElementById('personal-info-container');
            // helper to accept snake_case or camelCase
            const g = (a, b) => (data[a] !== undefined && data[a] !== null && data[a] !== '') ? data[a] : (data[b] !== undefined ? data[b] : null);

            const nombres = g('nombres','nombres') || 'No especificado';
            const apellidos = g('apellidos','apellidos') || 'No especificado';
            const tipoDocumento = g('tipo_documento','tipoDocumento') || 'No especificado';
            const numeroDocumento = g('numero_documento','numeroDocumento') || 'No especificado';
            const email = g('email','email') || '<?php echo htmlspecialchars($userEmail); ?>';
            const telefono = g('telefono','celular') || 'No especificado';
            const fechaNacimiento = g('fecha_nacimiento','fechaNacimiento') || 'No especificado';
            const direccion = g('direccion','direccion') || 'No especificado';

            container.innerHTML = `
                <div class="row">
                    <div class="col-md-6">
                        <p><strong>Nombres:</strong> ${nombres}</p>
                        <p><strong>Apellidos:</strong> ${apellidos}</p>
                        <p><strong>Tipo Doc.:</strong> <span class="badge bg-primary">${tipoDocumento}</span></p>
                        <p><strong>No. Documento:</strong> ${numeroDocumento}</p>
                    </div>
                    <div class="col-md-6">
                        <p><strong>Email:</strong> ${email}</p>
                        <p><strong>Teléfono:</strong> ${telefono}</p>
                        <p><strong>Fecha Nac.:</strong> ${fechaNacimiento}</p>
                        <p><strong>Dirección:</strong> ${direccion}</p>
                    </div>
                </div>
            `;
        }

        // Renderizar información académica (acepta snake_case o camelCase de forma flexible)
        function renderAcademicProfile(data) {
            const container = document.getElementById('academic-info-container');

            const get = (a, b) => (data[a] !== undefined && data[a] !== null && data[a] !== '') ? data[a] : (data[b] !== undefined ? data[b] : null);

            const institucion = get('nombre_institucion', 'nombreInstitucion') || get('institucionEducativa','institucion') || 'No especificado';
            const ano = get('ano_graduacion', 'anoGraduacion') || get('ano','') || 'No especificado';
            const tipo = get('tipo_bachillerato', 'tipoBachillerato') || 'No especificado';
            const puntaje = get('puntaje_icfes', 'puntajeIcfes') || get('puntaje','No registrado');
            const clasificacion = get('clasificacion_icfes', 'clasificacionIcfes') || 'No especificado';
            const titulo = get('titulo_obtenido', 'tituloObtenido') || get('titulo','No especificado');

            container.innerHTML = `
                <div class="row">
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label class="form-label-modern">Institución Educativa</label>
                            <div class="form-control-static">${institucion}</div>
                        </div>
                        <div class="mb-3">
                            <label class="form-label-modern">Año de Graduación</label>
                            <div class="form-control-static">
                                <span class="badge bg-info">${ano}</span>
                            </div>
                        </div>
                        <div class="mb-3">
                            <label class="form-label-modern">Tipo de Bachillerato</label>
                            <div class="form-control-static">${tipo}</div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label class="form-label-modern">Puntaje ICFES</label>
                            <div class="form-control-static">
                                <span class="badge bg-success fs-6">${puntaje}</span>
                            </div>
                        </div>
                        <div class="mb-3">
                            <label class="form-label-modern">Clasificación ICFES</label>
                            <div class="form-control-static">${clasificacion}</div>
                        </div>
                        <div class="mb-3">
                            <label class="form-label-modern">Título Obtenido</label>
                            <div class="form-control-static">${titulo}</div>
                        </div>
                    </div>
                </div>
            `;
        }

        // Cargar información académica para el perfil (usa endpoint clean en dev)
        async function loadAcademicProfile() {
            try {
                const response = await fetch(`${API_URL}/info-academica-clean/get${DEV_QUERY}`);
                const result = await response.json();

                if (result.success && result.infoAcademica) {
                    profileData.academic = result.infoAcademica;
                    renderAcademicProfile(result.infoAcademica);
                    document.getElementById('academic-status').className = 'badge bg-success';
                    document.getElementById('academic-status').textContent = 'Completa';
                } else {
                    document.getElementById('academic-status').className = 'badge bg-warning';
                    document.getElementById('academic-status').textContent = 'Incompleta';
                    renderEmptyAcademicProfile();
                }
            } catch (error) {
                console.error('Error cargando info académica:', error);
                document.getElementById('academic-status').className = 'badge bg-danger';
                document.getElementById('academic-status').textContent = 'Error';
                renderErrorAcademicProfile();
            }
        }

        // Cargar solicitudes para el perfil (usa endpoint clean en dev)
        async function loadRequestsProfile() {
            try {
                const response = await fetch(`${API_URL}/solicitudes-clean/get${DEV_QUERY}`);
                const result = await response.json();

                // Aceptar diferentes shapes (solicitud / solicitudes)
                const solicitudes = result.solicitud || result.solicitudes || result.data || [];

                if (result.success) {
                    profileData.requests = solicitudes;
                    renderRequestsProfile(solicitudes || []);
                    const count = Array.isArray(solicitudes) ? solicitudes.length : (solicitudes ? 1 : 0);
                    document.getElementById('requests-status').className = count > 0 ? 'badge bg-success' : 'badge bg-secondary';
                    document.getElementById('requests-status').textContent = `${count} solicitud(es)`;
                } else {
                    document.getElementById('requests-status').className = 'badge bg-secondary';
                    document.getElementById('requests-status').textContent = '0 solicitudes';
                    renderEmptyRequestsProfile();
                }
            } catch (error) {
                console.error('Error cargando solicitudes:', error);
                document.getElementById('requests-status').className = 'badge bg-danger';
                document.getElementById('requests-status').textContent = 'Error';
                renderErrorRequestsProfile();
            }
        }

        // Renderizar solicitudes
        function renderRequestsProfile(data) {
            const container = document.getElementById('requests-info-container');
            
            if (!data || (Array.isArray(data) && data.length === 0)) {
                renderEmptyRequestsProfile();
                return;
            }
            
            const solicitudes = Array.isArray(data) ? data : [data];
            let html = '';
            
            // Función auxiliar para formatear fechas
            function formatDate(dateString) {
                if (!dateString) return 'No especificado';
                try {
                    const date = new Date(dateString);
                    return date.toLocaleDateString('es-CO', {
                        year: 'numeric',
                        month: 'short',
                        day: 'numeric'
                    });
                } catch {
                    return dateString;
                }
            }
            
            // Función auxiliar para formatear programa académico
            function formatPrograma(programa) {
                if (!programa) return 'No especificado';
                return programa.replace(/_/g, ' ').toLowerCase().replace(/\b\w/g, l => l.toUpperCase());
            }
            
            solicitudes.forEach((solicitud, index) => {
                const statusClass = solicitud.estado === 'APROBADA' ? 'success' : 
                                   solicitud.estado === 'RECHAZADA' ? 'danger' : 'warning';
                                   
                html += `
                    <div class="card mb-3">
                        <div class="card-body">
                            <div class="row align-items-center">
                                <div class="col-md-8">
                                    <h6 class="card-title mb-1">
                                        <i class="fas fa-file-alt me-1"></i>
                                        Solicitud #${solicitud.numero_solicitud || (index + 1)}
                                    </h6>
                                    <p class="card-text mb-2">
                                        <strong>Programa:</strong> ${formatPrograma(solicitud.programa_academico)}<br>
                                        <strong>Período:</strong> ${solicitud.periodo_academico || 'No especificado'}<br>
                                        <strong>Modalidad:</strong> ${formatPrograma(solicitud.modalidad_ingreso)}<br>
                                        <strong>Sede:</strong> ${solicitud.sede || 'No especificado'}
                                    </p>
                                    <small class="text-muted">
                                        <i class="fas fa-calendar me-1"></i>
                                        Fecha: ${formatDate(solicitud.created_at)}
                                    </small>
                                </div>
                                <div class="col-md-4 text-end">
                                    <span class="badge bg-${statusClass} fs-6">
                                        ${solicitud.estado || 'PENDIENTE'}
                                    </span>
                                </div>
                            </div>
                        </div>
                    </div>
                `;
            });
            
            container.innerHTML = html;
        }

        // Funciones para estados vacíos
        function renderEmptyPersonalProfile() {
            const container = document.getElementById('personal-info-container');
            container.innerHTML = `
                <div class="col-12 text-center py-4">
                    <i class="fas fa-user-slash fa-3x text-muted mb-3"></i>
                    <h5 class="text-muted">No hay información personal registrada</h5>
                    <p class="text-muted">Completa tu información personal para mejorar tu perfil</p>
                    <button class="btn btn-primary" onclick="switchTab('info-personal-tab')">
                        <i class="fas fa-plus me-1"></i>Completar Información
                    </button>
                </div>
            `;
        }

        function renderEmptyAcademicProfile() {
            const container = document.getElementById('academic-info-container');
            container.innerHTML = `
                <div class="col-12 text-center py-4">
                    <i class="fas fa-graduation-cap fa-3x text-muted mb-3"></i>
                    <h5 class="text-muted">No hay información académica registrada</h5>
                    <p class="text-muted">Agrega tu información educativa para completar tu perfil</p>
                    <button class="btn btn-success" onclick="switchTab('info-academica-tab')">
                        <i class="fas fa-plus me-1"></i>Completar Información
                    </button>
                </div>
            `;
        }

        function renderEmptyRequestsProfile() {
            const container = document.getElementById('requests-info-container');
            container.innerHTML = `
                <div class="text-center py-4">
                    <i class="fas fa-file-plus fa-3x text-muted mb-3"></i>
                    <h5 class="text-muted">No tienes solicitudes registradas</h5>
                    <p class="text-muted">Crea tu primera solicitud para aplicar a programas académicos</p>
                    <button class="btn btn-warning" onclick="switchTab('solicitudes-tab')">
                        <i class="fas fa-plus me-1"></i>Crear Solicitud
                    </button>
                </div>
            `;
        }

        // Funciones para estados de error
        function renderErrorPersonalProfile() {
            const container = document.getElementById('personal-info-container');
            container.innerHTML = `
                <div class="col-12 text-center py-4">
                    <i class="fas fa-exclamation-triangle fa-3x text-danger mb-3"></i>
                    <h5 class="text-danger">Error al cargar información personal</h5>
                    <button class="btn btn-outline-primary" onclick="loadPersonalProfile()">
                        <i class="fas fa-sync-alt me-1"></i>Reintentar
                    </button>
                </div>
            `;
        }

        function renderErrorAcademicProfile() {
            const container = document.getElementById('academic-info-container');
            container.innerHTML = `
                <div class="col-12 text-center py-4">
                    <i class="fas fa-exclamation-triangle fa-3x text-danger mb-3"></i>
                    <h5 class="text-danger">Error al cargar información académica</h5>
                    <button class="btn btn-outline-success" onclick="loadAcademicProfile()">
                        <i class="fas fa-sync-alt me-1"></i>Reintentar
                    </button>
                </div>
            `;
        }

        function renderErrorRequestsProfile() {
            const container = document.getElementById('requests-info-container');
            container.innerHTML = `
                <div class="text-center py-4">
                    <i class="fas fa-exclamation-triangle fa-3x text-danger mb-3"></i>
                    <h5 class="text-danger">Error al cargar solicitudes</h5>
                    <button class="btn btn-outline-warning" onclick="loadRequestsProfile()">
                        <i class="fas fa-sync-alt me-1"></i>Reintentar
                    </button>
                </div>
            `;
        }

        // Actualizar resumen del perfil
        function updateProfileSummary() {
            const container = document.getElementById('summary-stats-container');
            
            // Calcular estadísticas
            const personalComplete = profileData.personal !== null;
            const academicComplete = profileData.academic !== null;
            const hasRequests = profileData.requests && (Array.isArray(profileData.requests) ? 
                profileData.requests.length > 0 : profileData.requests !== null);
            
            const progress = (personalComplete ? 40 : 0) + (academicComplete ? 40 : 0) + (hasRequests ? 20 : 0);
            
            // Actualizar barra de progreso en el header
            document.getElementById('profile-progress-bar').style.width = progress + '%';
            document.getElementById('profile-progress-text').textContent = progress + '% completado';
            
            container.innerHTML = `
                <div class="col-md-6 mb-4">
                    <div class="card border-0 bg-primary bg-opacity-10">
                        <div class="card-body text-center">
                            <i class="fas fa-user fa-2x text-primary mb-2"></i>
                            <h5>Información Personal</h5>
                            <div class="fs-3 fw-bold ${personalComplete ? 'text-success' : 'text-warning'}">
                                ${personalComplete ? '✓' : '○'}
                            </div>
                            <small class="text-muted">${personalComplete ? 'Completa' : 'Pendiente'}</small>
                        </div>
                    </div>
                </div>
                <div class="col-md-6 mb-4">
                    <div class="card border-0 bg-success bg-opacity-10">
                        <div class="card-body text-center">
                            <i class="fas fa-graduation-cap fa-2x text-success mb-2"></i>
                            <h5>Información Académica</h5>
                            <div class="fs-3 fw-bold ${academicComplete ? 'text-success' : 'text-warning'}">
                                ${academicComplete ? '✓' : '○'}
                            </div>
                            <small class="text-muted">${academicComplete ? 'Completa' : 'Pendiente'}</small>
                        </div>
                    </div>
                </div>
                <div class="col-md-6 mb-4">
                    <div class="card border-0 bg-warning bg-opacity-10">
                        <div class="card-body text-center">
                            <i class="fas fa-file-alt fa-2x text-warning mb-2"></i>
                            <h5>Solicitudes</h5>
                            <div class="fs-3 fw-bold text-info">
                                ${Array.isArray(profileData.requests) ? profileData.requests.length : 
                                  (profileData.requests ? 1 : 0)}
                            </div>
                            <small class="text-muted">Total registradas</small>
                        </div>
                    </div>
                </div>
                <div class="col-md-6 mb-4">
                    <div class="card border-0 bg-info bg-opacity-10">
                        <div class="card-body text-center">
                            <i class="fas fa-chart-line fa-2x text-info mb-2"></i>
                            <h5>Progreso Total</h5>
                            <div class="fs-3 fw-bold text-primary">
                                ${progress}%
                            </div>
                            <div class="progress mt-2" style="height: 8px;">
                                <div class="progress-bar bg-primary" style="width: ${progress}%"></div>
                            </div>
                        </div>
                    </div>
                </div>
            `;
        }

        // Función para actualizar todos los datos del perfil
        function refreshAllProfileData() {
            loadProfileData();
            showAlert('Actualizando información del perfil...', 'info');
        }

        // Función para exportar perfil (placeholder)
        function exportProfile() {
            showAlert('Función de exportación próximamente disponible', 'info');
        }

        // Auto-cargar datos del perfil al abrir la pestaña por primera vez
        document.addEventListener('DOMContentLoaded', function() {
            // Si la pestaña Mi Perfil está activa al cargar la página
            const miPerfilTab = document.getElementById('mi-perfil-tab');
            if (miPerfilTab && miPerfilTab.classList.contains('active')) {
                loadProfileData();
            }
        });
    </script>
</body>
</html>
