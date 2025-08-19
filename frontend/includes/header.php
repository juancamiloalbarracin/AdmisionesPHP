<?php
/**
 * SISTEMA DE ADMISIONES UDC - HEADER COMÚN
 * =======================================
 * Archivo: header.php
 * Descripción: Header común para todas las páginas del frontend
 */

// Iniciar sesión si no está iniciada
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Configuración base
$baseUrl = 'http://localhost:8000';
$apiUrl = $baseUrl . '/api';
$frontendUrl = 'http://localhost:3000'; // URL del frontend

// Verificar si el usuario está autenticado
$isAuthenticated = isset($_SESSION['user_id']) && isset($_SESSION['jwt_token']);
$currentUser = $isAuthenticated ? $_SESSION['user_data'] ?? null : null;

// Obtener página actual para navegación activa
$currentPage = basename($_SERVER['PHP_SELF'], '.php');
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="Sistema de Admisiones - Uniminuto">
    <meta name="keywords" content="Uniminuto, Admisiones, Estudiantes">
    <meta name="author" content="Uniminuto">
    
    <title><?php echo $pageTitle ?? 'Sistema de Admisiones'; ?> - Uniminuto</title>
    
    <!-- Favicon -->
    <link rel="icon" type="image/x-icon" href="../assets/images/favicon.ico">
    
    <!-- CSS Principal -->
    <link href="../assets/css/common.css" rel="stylesheet">
    
    <!-- Bootstrap Icons -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css" rel="stylesheet">
    
    <!-- Font Awesome -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    
    <!-- CSS adicional específico de página -->
    <?php if (isset($additionalCSS)): ?>
        <?php foreach ($additionalCSS as $css): ?>
            <link href="<?php echo $css; ?>" rel="stylesheet">
        <?php endforeach; ?>
    <?php endif; ?>
    
    <style>
        /* Estilos específicos del header */
        .header-container {
            background: linear-gradient(135deg, #1e4d72 0%, #2980b9 100%);
            color: white;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        
        .brand-container {
            display: flex;
            align-items: center;
            gap: 1rem;
            padding: 1rem 0;
        }
        
        .logo-image {
            width: 60px;
            height: 60px;
            border-radius: 8px;
            border: 2px solid rgba(255,255,255,0.2);
        }
        
        .brand-text h1 {
            margin: 0;
            font-size: 1.5rem;
            font-weight: 700;
        }
        
        .brand-text p {
            margin: 0;
            font-size: 0.9rem;
            opacity: 0.9;
        }
        
        .user-info {
            display: flex;
            align-items: center;
            gap: 1rem;
            color: white;
        }
        
        .user-avatar {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            background: rgba(255,255,255,0.2);
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: bold;
        }
        
        .navbar-custom {
            background: white;
            border-bottom: 1px solid #e0e0e0;
            padding: 0;
        }
        
        .nav-links {
            display: flex;
            list-style: none;
            margin: 0;
            padding: 0;
        }
        
        .nav-links li {
            margin: 0;
        }
        
        .nav-links a {
            display: block;
            padding: 1rem 1.5rem;
            color: #333;
            text-decoration: none;
            font-weight: 500;
            transition: all 0.3s ease;
            border-bottom: 3px solid transparent;
        }
        
        .nav-links a:hover,
        .nav-links a.active {
            color: #1e4d72;
            background: rgba(30, 77, 114, 0.05);
            border-bottom-color: #1e4d72;
        }
        
        .nav-links a i {
            margin-right: 0.5rem;
        }
        
        @media (max-width: 768px) {
            .brand-container {
                flex-direction: column;
                text-align: center;
                gap: 0.5rem;
            }
            
            .nav-links {
                flex-direction: column;
            }
            
            .nav-links a {
                border-left: 3px solid transparent;
                border-bottom: none;
            }
            
            .nav-links a:hover,
            .nav-links a.active {
                border-left-color: #1e4d72;
                border-bottom-color: transparent;
            }
        }
    </style>
</head>
<body>
    <!-- Contenedor de alertas -->
    <div class="alert-container"></div>
    
    <!-- Header Principal -->
    <header class="header-container">
        <div class="container">
            <div class="row justify-content-between align-items-center">
                <div class="col-auto">
                    <div class="brand-container">
                        <img src="../assets/images/logo-udc.png" 
                             alt="Logo UDC" 
                             class="logo-image"
                             onerror="this.style.display='none'">
                        <div class="brand-text">
                            <h1>Sistema de Admisiones</h1>
                            <p>Uniminuto</p>
                        </div>
                    </div>
                </div>
                
                <?php if ($isAuthenticated && $currentUser): ?>
                <div class="col-auto">
                    <div class="user-info">
                        <div class="user-avatar">
                            <?php echo strtoupper(substr(($currentUser['nombres'] ?? $currentUser['nombre'] ?? 'U'), 0, 1)); ?>
                        </div>
                        <div>
                            <div class="user-name"><?php echo htmlspecialchars(trim(($currentUser['nombres'] ?? $currentUser['nombre'] ?? '') . ' ' . ($currentUser['apellidos'] ?? $currentUser['apellido'] ?? ''))); ?></div>
                            <div class="user-email" style="font-size: 0.8rem; opacity: 0.8;">
                                <?php echo htmlspecialchars($currentUser['email']); ?>
                            </div>
                        </div>
                        <div class="dropdown">
                            <button class="btn btn-link text-white" type="button" data-bs-toggle="dropdown">
                                <i class="fas fa-chevron-down"></i>
                            </button>
                            <ul class="dropdown-menu">
                                <li><a class="dropdown-item" href="profile.php"><i class="fas fa-user"></i> Mi Perfil</a></li>
                                <li><hr class="dropdown-divider"></li>
                                <li><a class="dropdown-item logout-link" href="#"><i class="fas fa-sign-out-alt"></i> Cerrar Sesión</a></li>
                            </ul>
                        </div>
                    </div>
                </div>
                <?php endif; ?>
            </div>
        </div>
    </header>

    <!-- Navegación -->
    <?php if ($isAuthenticated): ?>
    <nav class="navbar-custom">
        <div class="container">
            <ul class="nav-links">
                <li>
                    <a href="main.php" class="<?php echo $currentPage === 'main' ? 'active' : ''; ?>">
                        <i class="fas fa-home"></i>Inicio
                    </a>
                </li>
                <li>
                    <a href="info-personal.php" class="<?php echo $currentPage === 'info-personal' ? 'active' : ''; ?>">
                        <i class="fas fa-user"></i>Información Personal
                    </a>
                </li>
                <li>
                    <a href="info-academica.php" class="<?php echo $currentPage === 'info-academica' ? 'active' : ''; ?>">
                        <i class="fas fa-graduation-cap"></i>Información Académica
                    </a>
                </li>
                <li>
                    <a href="solicitudes.php" class="<?php echo $currentPage === 'solicitudes' ? 'active' : ''; ?>">
                        <i class="fas fa-file-alt"></i>Mis Solicitudes
                    </a>
                </li>
                <li>
                    <a href="radicar.php" class="<?php echo $currentPage === 'radicar' ? 'active' : ''; ?>">
                        <i class="fas fa-plus-circle"></i>Radicar Solicitud
                    </a>
                </li>
            </ul>
        </div>
    </nav>
    <?php endif; ?>

    <!-- Contenido Principal -->
    <main class="main-content">
        <!-- Flash Messages -->
        <?php if (isset($_SESSION['flash_message'])): ?>
            <div class="flash-message" data-type="<?php echo $_SESSION['flash_type'] ?? 'info'; ?>">
                <?php echo htmlspecialchars($_SESSION['flash_message']); ?>
            </div>
            <?php unset($_SESSION['flash_message'], $_SESSION['flash_type']); ?>
        <?php endif; ?>
        
        <!-- Error Messages -->
        <?php if (isset($_SESSION['error_message'])): ?>
            <div class="flash-message" data-type="danger">
                <?php echo htmlspecialchars($_SESSION['error_message']); ?>
            </div>
            <?php unset($_SESSION['error_message']); ?>
        <?php endif; ?>
        
        <!-- Success Messages -->
        <?php if (isset($_SESSION['success_message'])): ?>
            <div class="flash-message" data-type="success">
                <?php echo htmlspecialchars($_SESSION['success_message']); ?>
            </div>
            <?php unset($_SESSION['success_message']); ?>
        <?php endif; ?>
