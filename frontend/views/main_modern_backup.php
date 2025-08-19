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
                <button class="user-btn" onclick="window.location.href='logout.php'">
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
                <div class="form-modern mb-4">
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
                    <i class="fas fa-graduation-cap"></i>
                </div>
                <div class="brand-text">
                    <h4>Uniminuto</h4>
                    <span>Admissions Portal</span>
                </div>
            </div>
            
            <!-- Search Bar -->
            <div class="navbar-search">
                <div class="search-container">
                    <i class="fas fa-search search-icon"></i>
                    <input type="text" class="search-input" placeholder="Buscar solicitudes, estudiantes...">
                    <div class="search-suggestions"></div>
                </div>
            </div>
            
            <!-- Right Section -->
            <div class="navbar-actions">
                <!-- Notifications -->
                <div class="notification-dropdown">
                    <button class="notification-btn" data-count="3">
                        <i class="fas fa-bell"></i>
                    </button>
                    <div class="notification-panel">
                        <div class="notification-header">
                            <h6>Notificaciones</h6>
                            <span class="notification-count">3 nuevas</span>
                        </div>
                        <div class="notification-list">
                            <div class="notification-item">
                                <div class="notification-icon bg-success">
                                    <i class="fas fa-check"></i>
                                </div>
                                <div class="notification-content">
                                    <p>Nueva solicitud aprobada</p>
                                    <small>hace 5 minutos</small>
                                </div>
                            </div>
                            <div class="notification-item">
                                <div class="notification-icon bg-warning">
                                    <i class="fas fa-clock"></i>
                                </div>
                                <div class="notification-content">
                                    <p>Solicitud pendiente de revisión</p>
                                    <small>hace 15 minutos</small>
                                </div>
                            </div>
                            <div class="notification-item">
                                <div class="notification-icon bg-info">
                                    <i class="fas fa-user"></i>
                                </div>
                                <div class="notification-content">
                                    <p>Nuevo estudiante registrado</p>
                                    <small>hace 1 hora</small>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- User Menu -->
                <div class="user-dropdown">
                    <button class="user-btn">
                        <div class="user-avatar">
                            <img src="https://ui-avatars.com/api/?name=<?php echo urlencode($currentUser['nombres'] ?? 'Usuario'); ?>&background=FF8C00&color=fff" alt="Avatar">
                        </div>
                        <div class="user-info">
                            <span class="user-name"><?php echo htmlspecialchars($currentUser['nombres'] ?? 'Usuario'); ?></span>
                            <small class="user-role">Estudiante</small>
                        </div>
                        <i class="fas fa-chevron-down"></i>
                    </button>
                    <div class="user-panel">
                        <div class="user-panel-header">
                            <div class="user-avatar-large">
                                <img src="https://ui-avatars.com/api/?name=<?php echo urlencode($currentUser['nombres'] ?? 'Usuario'); ?>&background=FF8C00&color=fff" alt="Avatar">
                            </div>
                            <div class="user-details">
                                <h6><?php echo htmlspecialchars($currentUser['nombres'] ?? 'Usuario'); ?></h6>
                                <p><?php echo htmlspecialchars($currentUser['email'] ?? ''); ?></p>
                            </div>
                        </div>
                        <div class="user-panel-menu">
                            <a href="#" class="user-menu-item">
                                <i class="fas fa-user"></i> Mi Perfil
                            </a>
                            <a href="#" class="user-menu-item">
                                <i class="fas fa-cog"></i> Configuración
                            </a>
                            <a href="#" class="user-menu-item">
                                <i class="fas fa-question-circle"></i> Ayuda
                            </a>
                            <div class="menu-divider"></div>
                            <a href="logout.php" class="user-menu-item text-danger">
                                <i class="fas fa-sign-out-alt"></i> Cerrar Sesión
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </nav>

    <!-- Main Content -->
    <main class="main-content">
        <!-- Welcome Hero Section -->
        <section class="hero-section">
            <div class="hero-content">
                <div class="welcome-card">
                    <div class="welcome-content">
                        <h1 class="welcome-title">
                            ¡Bienvenido de vuelta, <span class="gradient-text"><?php echo htmlspecialchars($currentUser['nombres'] ?? 'Usuario'); ?>!</span>
                        </h1>
                        <p class="welcome-subtitle">
                            Aquí tienes un resumen de tu progreso académico y las últimas actualizaciones
                        </p>
                        <div class="welcome-actions">
                            <button class="btn-modern btn-primary">
                                <i class="fas fa-plus me-2"></i>Nueva Solicitud
                            </button>
                            <button class="btn-modern btn-outline">
                                <i class="fas fa-chart-line me-2"></i>Ver Reportes
                            </button>
                        </div>
                    </div>
                    <div class="welcome-visual">
                        <div class="floating-elements">
                            <div class="floating-icon" style="--delay: 0s;">
                                <i class="fas fa-graduation-cap"></i>
                            </div>
                            <div class="floating-icon" style="--delay: 0.5s;">
                                <i class="fas fa-book"></i>
                            </div>
                            <div class="floating-icon" style="--delay: 1s;">
                                <i class="fas fa-trophy"></i>
                            </div>
                            <div class="floating-icon" style="--delay: 1.5s;">
                                <i class="fas fa-star"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <!-- Stats Grid -->
        <section class="stats-section">
            <div class="container-fluid">
                <div class="row g-4">
                    <!-- Total Solicitudes -->
                    <div class="col-xl-3 col-md-6">
                        <div class="stat-card stat-card-primary" data-aos="fade-up" data-aos-delay="100">
                            <div class="stat-card-content">
                                <div class="stat-icon">
                                    <i class="fas fa-file-alt"></i>
                                    <div class="stat-icon-bg"></div>
                                </div>
                                <div class="stat-details">
                                    <div class="stat-number" data-count="<?php echo $stats['total_solicitudes']; ?>">0</div>
                                    <div class="stat-label">Total Solicitudes</div>
                                    <div class="stat-trend positive">
                                        <i class="fas fa-arrow-up"></i>
                                        <span>+12.5%</span>
                                    </div>
                                </div>
                            </div>
                            <div class="stat-chart">
                                <canvas id="chart-total"></canvas>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Pendientes -->
                    <div class="col-xl-3 col-md-6">
                        <div class="stat-card stat-card-warning" data-aos="fade-up" data-aos-delay="200">
                            <div class="stat-card-content">
                                <div class="stat-icon">
                                    <i class="fas fa-clock"></i>
                                    <div class="stat-icon-bg"></div>
                                </div>
                                <div class="stat-details">
                                    <div class="stat-number" data-count="<?php echo $stats['solicitudes_pendientes']; ?>">0</div>
                                    <div class="stat-label">Pendientes</div>
                                    <div class="stat-trend neutral">
                                        <i class="fas fa-minus"></i>
                                        <span>0%</span>
                                    </div>
                                </div>
                            </div>
                            <div class="stat-chart">
                                <canvas id="chart-pending"></canvas>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Aprobadas -->
                    <div class="col-xl-3 col-md-6">
                        <div class="stat-card stat-card-success" data-aos="fade-up" data-aos-delay="300">
                            <div class="stat-card-content">
                                <div class="stat-icon">
                                    <i class="fas fa-check-circle"></i>
                                    <div class="stat-icon-bg"></div>
                                </div>
                                <div class="stat-details">
                                    <div class="stat-number" data-count="<?php echo $stats['solicitudes_aprobadas']; ?>">0</div>
                                    <div class="stat-label">Aprobadas</div>
                                    <div class="stat-trend positive">
                                        <i class="fas fa-arrow-up"></i>
                                        <span>+8.3%</span>
                                    </div>
                                </div>
                            </div>
                            <div class="stat-chart">
                                <canvas id="chart-approved"></canvas>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Rechazadas -->
                    <div class="col-xl-3 col-md-6">
                        <div class="stat-card stat-card-danger" data-aos="fade-up" data-aos-delay="400">
                            <div class="stat-card-content">
                                <div class="stat-icon">
                                    <i class="fas fa-times-circle"></i>
                                    <div class="stat-icon-bg"></div>
                                </div>
                                <div class="stat-details">
                                    <div class="stat-number" data-count="<?php echo $stats['solicitudes_rechazadas']; ?>">0</div>
                                    <div class="stat-label">Rechazadas</div>
                                    <div class="stat-trend negative">
                                        <i class="fas fa-arrow-down"></i>
                                        <span>-3.1%</span>
                                    </div>
                                </div>
                            </div>
                            <div class="stat-chart">
                                <canvas id="chart-rejected"></canvas>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <!-- Dashboard Content Grid -->
        <section class="content-section">
            <div class="container-fluid">
                <div class="row g-4">
                    <!-- Analytics Chart -->
                    <div class="col-xl-8 col-lg-7">
                        <div class="dashboard-card analytics-card" data-aos="fade-up" data-aos-delay="100">
                            <div class="card-header">
                                <div class="card-title">
                                    <h5><i class="fas fa-chart-area me-2"></i>Tendencia de Solicitudes</h5>
                                    <p>Resumen mensual del último año</p>
                                </div>
                                <div class="card-actions">
                                    <div class="chart-tabs">
                                        <button class="chart-tab active" data-period="monthly">Mensual</button>
                                        <button class="chart-tab" data-period="weekly">Semanal</button>
                                        <button class="chart-tab" data-period="daily">Diario</button>
                                    </div>
                                </div>
                            </div>
                            <div class="card-content">
                                <canvas id="main-analytics-chart"></canvas>
                            </div>
                        </div>
                    </div>

                    <!-- Progress & KPIs -->
                    <div class="col-xl-4 col-lg-5">
                        <div class="row g-4">
                            <!-- Progress Card -->
                            <div class="col-12">
                                <div class="dashboard-card progress-card" data-aos="fade-up" data-aos-delay="200">
                                    <div class="card-header">
                                        <h6><i class="fas fa-target me-2"></i>Métricas de Rendimiento</h6>
                                    </div>
                                    <div class="card-content">
                                        <!-- Completion Rate -->
                                        <div class="progress-item">
                                            <div class="progress-header">
                                                <span>Tasa de Finalización</span>
                                                <strong><?php echo $chartData['performance_metrics']['completion_rate']; ?>%</strong>
                                            </div>
                                            <div class="progress-bar-container">
                                                <div class="progress-bar" data-percentage="<?php echo $chartData['performance_metrics']['completion_rate']; ?>">
                                                    <div class="progress-fill"></div>
                                                </div>
                                            </div>
                                        </div>
                                        
                                        <!-- Satisfaction Rate -->
                                        <div class="progress-item">
                                            <div class="progress-header">
                                                <span>Satisfacción</span>
                                                <strong><?php echo $chartData['performance_metrics']['satisfaction_rate']; ?>%</strong>
                                            </div>
                                            <div class="progress-bar-container">
                                                <div class="progress-bar" data-percentage="<?php echo $chartData['performance_metrics']['satisfaction_rate']; ?>">
                                                    <div class="progress-fill"></div>
                                                </div>
                                            </div>
                                        </div>
                                        
                                        <!-- Response Time -->
                                        <div class="progress-item">
                                            <div class="progress-header">
                                                <span>Tiempo de Respuesta</span>
                                                <strong><?php echo $chartData['performance_metrics']['response_time']; ?>h</strong>
                                            </div>
                                            <div class="metric-indicator excellent">
                                                <div class="indicator-dot"></div>
                                                <span>Excelente</span>
                                            </div>
                                        </div>
                                        
                                        <!-- Success Rate -->
                                        <div class="progress-item">
                                            <div class="progress-header">
                                                <span>Tasa de Éxito</span>
                                                <strong><?php echo $chartData['performance_metrics']['success_rate']; ?>%</strong>
                                            </div>
                                            <div class="progress-bar-container">
                                                <div class="progress-bar" data-percentage="<?php echo $chartData['performance_metrics']['success_rate']; ?>">
                                                    <div class="progress-fill"></div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Quick Actions -->
                            <div class="col-12">
                                <div class="dashboard-card actions-card" data-aos="fade-up" data-aos-delay="300">
                                    <div class="card-header">
                                        <h6><i class="fas fa-bolt me-2"></i>Acciones Rápidas</h6>
                                    </div>
                                    <div class="card-content">
                                        <div class="action-grid">
                                            <button class="action-btn" data-action="new-application">
                                                <div class="action-icon">
                                                    <i class="fas fa-plus"></i>
                                                </div>
                                                <span>Nueva Solicitud</span>
                                            </button>
                                            <button class="action-btn" data-action="view-documents">
                                                <div class="action-icon">
                                                    <i class="fas fa-folder-open"></i>
                                                </div>
                                                <span>Documentos</span>
                                            </button>
                                            <button class="action-btn" data-action="schedule-meeting">
                                                <div class="action-icon">
                                                    <i class="fas fa-calendar-plus"></i>
                                                </div>
                                                <span>Agendar Cita</span>
                                            </button>
                                            <button class="action-btn" data-action="contact-support">
                                                <div class="action-icon">
                                                    <i class="fas fa-headset"></i>
                                                </div>
                                                <span>Soporte</span>
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Recent Activities -->
                    <div class="col-xl-8 col-lg-7">
                        <div class="dashboard-card activities-card" data-aos="fade-up" data-aos-delay="100">
                            <div class="card-header">
                                <div class="card-title">
                                    <h5><i class="fas fa-history me-2"></i>Actividad Reciente</h5>
                                    <p>Últimas actualizaciones en tu perfil</p>
                                </div>
                                <button class="btn-modern btn-outline btn-sm">Ver Todo</button>
                            </div>
                            <div class="card-content">
                                <div class="activities-timeline">
                                    <div class="timeline-item">
                                        <div class="timeline-marker success">
                                            <i class="fas fa-check"></i>
                                        </div>
                                        <div class="timeline-content">
                                            <div class="timeline-header">
                                                <h6>Solicitud Aprobada</h6>
                                                <span class="timeline-time">hace 2 horas</span>
                                            </div>
                                            <p>Tu solicitud para Ingeniería de Sistemas ha sido aprobada exitosamente.</p>
                                            <div class="timeline-tags">
                                                <span class="tag success">Aprobado</span>
                                                <span class="tag">Ingeniería</span>
                                            </div>
                                        </div>
                                    </div>
                                    
                                    <div class="timeline-item">
                                        <div class="timeline-marker info">
                                            <i class="fas fa-upload"></i>
                                        </div>
                                        <div class="timeline-content">
                                            <div class="timeline-header">
                                                <h6>Documentos Actualizados</h6>
                                                <span class="timeline-time">hace 1 día</span>
                                            </div>
                                            <p>Se han actualizado tus documentos de identificación y certificados académicos.</p>
                                            <div class="timeline-tags">
                                                <span class="tag info">Documentos</span>
                                            </div>
                                        </div>
                                    </div>
                                    
                                    <div class="timeline-item">
                                        <div class="timeline-marker warning">
                                            <i class="fas fa-exclamation"></i>
                                        </div>
                                        <div class="timeline-content">
                                            <div class="timeline-header">
                                                <h6>Acción Requerida</h6>
                                                <span class="timeline-time">hace 2 días</span>
                                            </div>
                                            <p>Se requiere completar el formulario de información adicional para procesar tu solicitud.</p>
                                            <div class="timeline-tags">
                                                <span class="tag warning">Pendiente</span>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Programs Distribution -->
                    <div class="col-xl-4 col-lg-5">
                        <div class="dashboard-card distribution-card" data-aos="fade-up" data-aos-delay="200">
                            <div class="card-header">
                                <h6><i class="fas fa-chart-pie me-2"></i>Distribución por Programas</h6>
                            </div>
                            <div class="card-content">
                                <div class="chart-container">
                                    <canvas id="programs-chart"></canvas>
                                </div>
                                <div class="programs-legend">
                                    <?php foreach ($chartData['program_distribution'] as $program): ?>
                                    <div class="legend-item">
                                        <div class="legend-color" style="background: <?php echo $program['color']; ?>"></div>
                                        <div class="legend-info">
                                            <span class="legend-name"><?php echo $program['name']; ?></span>
                                            <span class="legend-value"><?php echo $program['value']; ?>%</span>
                                        </div>
                                    </div>
                                    <?php endforeach; ?>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>

    <!-- Pass PHP data to JavaScript -->
    <script>
        // Pass PHP data to JavaScript
        window.DASHBOARD_DATA = {
            stats: <?php echo json_encode($stats); ?>,
            chartData: <?php echo json_encode($chartData); ?>,
            userInfo: <?php echo json_encode($currentUser); ?>
        };
    </script>

<?php
// Incluir footer moderno
include '../includes/footer_modern.php';
?>
