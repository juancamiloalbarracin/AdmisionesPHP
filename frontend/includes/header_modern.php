<?php
/**
 * ===================================================================
 * UNIMINUTO HEADER MODERN - NAVEGACIÓN STICKY AVANZADA
 * ===================================================================
 * Archivo: header_modern.php
 * Descripción: Header moderno con navegación sticky y efectos 3D
 * Fecha: 2025
 */

// Iniciar sesión si no está iniciada
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Configuración base
$baseUrl = 'http://localhost:8000';
$apiUrl = $baseUrl . '/api';
$frontendUrl = 'http://localhost:3000';

// Verificar autenticación
$isAuthenticated = isset($_SESSION['user_id']) && isset($_SESSION['jwt_token']);
$currentUser = $isAuthenticated ? $_SESSION['user_data'] ?? null : null;

// Obtener página actual
$currentPage = basename($_SERVER['PHP_SELF'], '.php');

// Configuración de navegación
$navigationItems = [
    [
        'id' => 'dashboard',
        'title' => 'Dashboard',
        'url' => 'main_modern.php',
        'icon' => 'fas fa-tachometer-alt',
        'badge' => null,
        'active' => $currentPage === 'main_modern'
    ],
    [
        'id' => 'applications',
        'title' => 'Solicitudes',
        'url' => 'applications.php',
        'icon' => 'fas fa-file-alt',
        'badge' => '3',
        'active' => $currentPage === 'applications'
    ],
    [
        'id' => 'documents',
        'title' => 'Documentos',
        'url' => 'documents.php',
        'icon' => 'fas fa-folder-open',
        'badge' => null,
        'active' => $currentPage === 'documents'
    ],
    [
        'id' => 'profile',
        'title' => 'Mi Perfil',
        'url' => 'profile.php',
        'icon' => 'fas fa-user-circle',
        'badge' => null,
        'active' => $currentPage === 'profile'
    ],
    [
        'id' => 'support',
        'title' => 'Soporte',
        'url' => 'support.php',
        'icon' => 'fas fa-headset',
        'badge' => null,
        'active' => $currentPage === 'support'
    ]
];
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="Sistema de Admisiones - Uniminuto">
    <meta name="keywords" content="Uniminuto, Admisiones, Estudiantes, Universidad">
    <meta name="author" content="Uniminuto">
    <meta name="robots" content="index, follow">
    
    <title><?php echo $pageTitle ?? 'Sistema de Admisiones'; ?> - Uniminuto</title>
    
    <!-- Favicon moderno -->
    <link rel="icon" type="image/svg+xml" href="../assets/images/favicon.svg">
    <link rel="icon" type="image/png" href="../assets/images/favicon-32x32.png">
    <link rel="apple-touch-icon" href="../assets/images/apple-touch-icon.png">
    
    <!-- Preconnect para mejorar rendimiento -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link rel="preconnect" href="https://cdnjs.cloudflare.com">
    
    <!-- CSS Framework -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    
    <!-- Iconos -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css" rel="stylesheet">
    
    <!-- Fuentes -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800;900&display=swap" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=JetBrains+Mono:wght@400;500;600&display=swap" rel="stylesheet">
    
    <!-- Animaciones -->
    <link href="https://cdn.jsdelivr.net/npm/animate.css@4.1.1/animate.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/aos@2.3.4/dist/aos.css" rel="stylesheet">
    
    <!-- CSS personalizado -->
    <link href="../assets/css/navigation_modern.css" rel="stylesheet">
    
    <?php if (!empty($additionalCSS)): ?>
        <?php foreach ($additionalCSS as $css): ?>
            <link href="<?php echo $css; ?>" rel="stylesheet">
        <?php endforeach; ?>
    <?php endif; ?>
</head>
<body class="modern-layout" data-bs-theme="dark">
    <!-- Page Loading Overlay -->
    <div id="page-loading-overlay" class="page-loader">
        <div class="loader-content">
            <div class="loader-spinner">
                <div class="spinner-ring"></div>
                <div class="spinner-ring"></div>
                <div class="spinner-ring"></div>
            </div>
            <div class="loader-text">
                <span class="loader-title">Uniminuto</span>
                <span class="loader-subtitle">Cargando experiencia...</span>
            </div>
        </div>
    </div>

    <!-- Top Progress Bar -->
    <div id="scroll-progress-bar" class="scroll-progress"></div>

    <!-- Modern Navigation Header -->
    <header class="modern-header" id="main-header">
        <!-- Primary Navigation -->
        <nav class="navbar-primary">
            <div class="navbar-container">
                <!-- Brand Section -->
                <div class="navbar-brand-section">
                    <button class="mobile-menu-toggle d-lg-none" id="mobile-menu-toggle">
                        <span class="hamburger-line"></span>
                        <span class="hamburger-line"></span>
                        <span class="hamburger-line"></span>
                    </button>
                    
                    <a href="main_modern.php" class="brand-link">
                        <div class="brand-logo">
                            <div class="logo-icon">
                                <i class="fas fa-graduation-cap"></i>
                            </div>
                            <div class="logo-animation-bg"></div>
                        </div>
                        <div class="brand-text">
                            <h1 class="brand-name">Uniminuto</h1>
                            <span class="brand-tagline">Admissions Portal</span>
                        </div>
                    </a>
                </div>

                <!-- Navigation Menu -->
                <div class="navbar-navigation" id="main-navigation">
                    <ul class="nav-menu">
                        <?php foreach ($navigationItems as $item): ?>
                        <li class="nav-item <?php echo $item['active'] ? 'active' : ''; ?>">
                            <a href="<?php echo $item['url']; ?>" class="nav-link" data-page="<?php echo $item['id']; ?>">
                                <div class="nav-icon">
                                    <i class="<?php echo $item['icon']; ?>"></i>
                                    <?php if ($item['badge']): ?>
                                    <span class="nav-badge"><?php echo $item['badge']; ?></span>
                                    <?php endif; ?>
                                </div>
                                <span class="nav-text"><?php echo $item['title']; ?></span>
                                <div class="nav-indicator"></div>
                            </a>
                        </li>
                        <?php endforeach; ?>
                    </ul>
                </div>

                <!-- Right Section -->
                <div class="navbar-actions">
                    <!-- Global Search -->
                    <div class="search-container">
                        <button class="search-trigger" id="global-search-trigger">
                            <i class="fas fa-search"></i>
                        </button>
                        
                        <div class="search-overlay" id="search-overlay">
                            <div class="search-modal">
                                <div class="search-header">
                                    <div class="search-input-wrapper">
                                        <i class="fas fa-search search-icon"></i>
                                        <input type="text" class="global-search-input" placeholder="Buscar solicitudes, documentos..." id="global-search-input">
                                        <button class="search-close" id="search-close">
                                            <i class="fas fa-times"></i>
                                        </button>
                                    </div>
                                </div>
                                <div class="search-content">
                                    <div class="search-shortcuts">
                                        <h6>Accesos rápidos</h6>
                                        <div class="shortcut-grid">
                                            <a href="#" class="shortcut-item">
                                                <i class="fas fa-plus"></i>
                                                <span>Nueva Solicitud</span>
                                            </a>
                                            <a href="#" class="shortcut-item">
                                                <i class="fas fa-upload"></i>
                                                <span>Subir Documento</span>
                                            </a>
                                            <a href="#" class="shortcut-item">
                                                <i class="fas fa-calendar"></i>
                                                <span>Agendar Cita</span>
                                            </a>
                                            <a href="#" class="shortcut-item">
                                                <i class="fas fa-question-circle"></i>
                                                <span>Ayuda</span>
                                            </a>
                                        </div>
                                    </div>
                                    <div class="search-results" id="search-results">
                                        <!-- Los resultados se cargarán dinámicamente -->
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Notifications -->
                    <div class="notifications-container">
                        <button class="notifications-trigger" id="notifications-trigger">
                            <i class="fas fa-bell"></i>
                            <span class="notification-count" data-count="3">3</span>
                            <div class="notification-pulse"></div>
                        </button>
                        
                        <div class="notifications-panel" id="notifications-panel">
                            <div class="notifications-header">
                                <h6>Notificaciones</h6>
                                <div class="notifications-actions">
                                    <button class="btn-mark-all-read" title="Marcar todas como leídas">
                                        <i class="fas fa-check-double"></i>
                                    </button>
                                    <button class="btn-settings" title="Configurar notificaciones">
                                        <i class="fas fa-cog"></i>
                                    </button>
                                </div>
                            </div>
                            <div class="notifications-content">
                                <div class="notification-item unread">
                                    <div class="notification-avatar">
                                        <i class="fas fa-check-circle text-success"></i>
                                    </div>
                                    <div class="notification-content">
                                        <div class="notification-title">Solicitud Aprobada</div>
                                        <div class="notification-text">Tu solicitud de Ingeniería de Sistemas ha sido aprobada</div>
                                        <div class="notification-time">hace 5 minutos</div>
                                    </div>
                                    <div class="notification-actions">
                                        <button class="btn-view" title="Ver detalles">
                                            <i class="fas fa-eye"></i>
                                        </button>
                                    </div>
                                </div>

                                <div class="notification-item unread">
                                    <div class="notification-avatar">
                                        <i class="fas fa-upload text-info"></i>
                                    </div>
                                    <div class="notification-content">
                                        <div class="notification-title">Documento Recibido</div>
                                        <div class="notification-text">Se ha recibido tu certificado académico</div>
                                        <div class="notification-time">hace 1 hora</div>
                                    </div>
                                    <div class="notification-actions">
                                        <button class="btn-view" title="Ver documento">
                                            <i class="fas fa-download"></i>
                                        </button>
                                    </div>
                                </div>

                                <div class="notification-item">
                                    <div class="notification-avatar">
                                        <i class="fas fa-calendar text-warning"></i>
                                    </div>
                                    <div class="notification-content">
                                        <div class="notification-title">Cita Programada</div>
                                        <div class="notification-text">Entrevista el 20 de agosto a las 10:00 AM</div>
                                        <div class="notification-time">hace 2 horas</div>
                                    </div>
                                    <div class="notification-actions">
                                        <button class="btn-view" title="Ver calendario">
                                            <i class="fas fa-calendar-alt"></i>
                                        </button>
                                    </div>
                                </div>
                            </div>
                            <div class="notifications-footer">
                                <a href="#" class="btn-view-all">Ver todas las notificaciones</a>
                            </div>
                        </div>
                    </div>

                    <!-- User Profile -->
                    <div class="user-profile-container">
                        <button class="user-profile-trigger" id="user-profile-trigger">
                            <div class="user-avatar">
                                <img src="https://ui-avatars.com/api/?name=<?php echo urlencode($currentUser['nombres'] ?? 'Usuario'); ?>&background=FF8C00&color=fff&size=40" alt="Avatar" class="avatar-img">
                                <div class="status-indicator online"></div>
                            </div>
                            <div class="user-info d-none d-md-block">
                                <div class="user-name"><?php echo htmlspecialchars($currentUser['nombres'] ?? 'Usuario'); ?></div>
                                <div class="user-role">Estudiante</div>
                            </div>
                            <i class="fas fa-chevron-down dropdown-arrow"></i>
                        </button>
                        
                        <div class="user-profile-panel" id="user-profile-panel">
                            <div class="profile-header">
                                <div class="profile-avatar">
                                    <img src="https://ui-avatars.com/api/?name=<?php echo urlencode($currentUser['nombres'] ?? 'Usuario'); ?>&background=FF8C00&color=fff&size=60" alt="Avatar">
                                    <div class="avatar-overlay">
                                        <i class="fas fa-camera"></i>
                                    </div>
                                </div>
                                <div class="profile-info">
                                    <h6><?php echo htmlspecialchars($currentUser['nombres'] ?? 'Usuario'); ?></h6>
                                    <p><?php echo htmlspecialchars($currentUser['email'] ?? ''); ?></p>
                                    <div class="profile-stats">
                                        <div class="stat-item">
                                            <span class="stat-value">3</span>
                                            <span class="stat-label">Solicitudes</span>
                                        </div>
                                        <div class="stat-item">
                                            <span class="stat-value">12</span>
                                            <span class="stat-label">Documentos</span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="profile-menu">
                                <a href="#" class="profile-menu-item">
                                    <i class="fas fa-user"></i>
                                    <span>Mi Perfil</span>
                                    <i class="fas fa-chevron-right"></i>
                                </a>
                                <a href="#" class="profile-menu-item">
                                    <i class="fas fa-cog"></i>
                                    <span>Configuración</span>
                                    <i class="fas fa-chevron-right"></i>
                                </a>
                                <a href="#" class="profile-menu-item">
                                    <i class="fas fa-shield-alt"></i>
                                    <span>Privacidad</span>
                                    <i class="fas fa-chevron-right"></i>
                                </a>
                                <a href="#" class="profile-menu-item">
                                    <i class="fas fa-question-circle"></i>
                                    <span>Ayuda y Soporte</span>
                                    <i class="fas fa-chevron-right"></i>
                                </a>
                                <div class="menu-divider"></div>
                                <a href="logout.php" class="profile-menu-item text-danger">
                                    <i class="fas fa-sign-out-alt"></i>
                                    <span>Cerrar Sesión</span>
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </nav>

        <!-- Breadcrumb Navigation -->
        <nav class="breadcrumb-nav" id="breadcrumb-nav">
            <div class="breadcrumb-container">
                <div class="breadcrumb-content">
                    <div class="breadcrumb-path">
                        <a href="main_modern.php" class="breadcrumb-item">
                            <i class="fas fa-home"></i>
                            <span>Inicio</span>
                        </a>
                        <span class="breadcrumb-separator">
                            <i class="fas fa-chevron-right"></i>
                        </span>
                        <span class="breadcrumb-current"><?php echo $pageTitle ?? 'Dashboard'; ?></span>
                    </div>
                    <div class="breadcrumb-actions">
                        <button class="btn-quick-action" title="Acción rápida">
                            <i class="fas fa-plus"></i>
                        </button>
                        <button class="btn-favorite" title="Añadir a favoritos">
                            <i class="far fa-bookmark"></i>
                        </button>
                    </div>
                </div>
            </div>
        </nav>
    </header>

    <!-- Mobile Navigation Overlay -->
    <div class="mobile-nav-overlay" id="mobile-nav-overlay">
        <div class="mobile-nav-content">
            <div class="mobile-nav-header">
                <div class="mobile-brand">
                    <div class="brand-logo small">
                        <div class="logo-icon">
                            <i class="fas fa-graduation-cap"></i>
                        </div>
                    </div>
                    <span>Uniminuto</span>
                </div>
                <button class="mobile-nav-close" id="mobile-nav-close">
                    <i class="fas fa-times"></i>
                </button>
            </div>
            <div class="mobile-nav-menu">
                <?php foreach ($navigationItems as $item): ?>
                <a href="<?php echo $item['url']; ?>" class="mobile-nav-item <?php echo $item['active'] ? 'active' : ''; ?>">
                    <div class="mobile-nav-icon">
                        <i class="<?php echo $item['icon']; ?>"></i>
                        <?php if ($item['badge']): ?>
                        <span class="nav-badge"><?php echo $item['badge']; ?></span>
                        <?php endif; ?>
                    </div>
                    <span class="mobile-nav-text"><?php echo $item['title']; ?></span>
                    <i class="fas fa-chevron-right"></i>
                </a>
                <?php endforeach; ?>
            </div>
            <div class="mobile-nav-footer">
                <div class="mobile-user-info">
                    <div class="mobile-avatar">
                        <img src="https://ui-avatars.com/api/?name=<?php echo urlencode($currentUser['nombres'] ?? 'Usuario'); ?>&background=FF8C00&color=fff&size=40" alt="Avatar">
                    </div>
                    <div class="mobile-user-details">
                        <div class="mobile-user-name"><?php echo htmlspecialchars($currentUser['nombres'] ?? 'Usuario'); ?></div>
                        <div class="mobile-user-email"><?php echo htmlspecialchars($currentUser['email'] ?? ''); ?></div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Main Content Wrapper -->
    <main class="main-wrapper" id="main-wrapper">
        <!-- El contenido de las páginas se insertará aquí -->
