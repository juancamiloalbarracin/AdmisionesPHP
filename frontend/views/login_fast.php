<?php
/**
 * LOGIN ULTRA RÁPIDO
 * Versión optimizada para XAMPP
 */

session_start();

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email'] ?? '');
    $password = trim($_POST['password'] ?? '');
    
    if (empty($email) || empty($password)) {
        $error = 'Complete todos los campos';
    } else {
        // Conexión directa PDO optimizada
        try {
            $dsn = "mysql:host=localhost;dbname=admisiones_udc;charset=utf8mb4";
            $pdo = new PDO($dsn, 'root', '', [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_PERSISTENT => false, // Sin conexiones persistentes
                PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8mb4"
            ]);
            
            $stmt = $pdo->prepare("SELECT id, email, password_hash, nombres, apellidos FROM usuarios WHERE email = ? AND activo = 1 LIMIT 1");
            $stmt->execute([$email]);
            $user = $stmt->fetch();
            
            if ($user && password_verify($password, $user['password_hash'])) {
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['jwt_token'] = 'fast-login-' . time();
                $_SESSION['user_data'] = [
                    'id' => $user['id'],
                    'email' => $user['email'],
                    'nombres' => $user['nombres'],
                    'apellidos' => $user['apellidos']
                ];
                
                // Redirigir al puerto que funciona
                header('Location: http://localhost:3000/views/main_modern.php', true, 303);
                exit();
            } else {
                $error = 'Credenciales inválidas';
            }
        } catch (Exception $e) {
            $error = 'Error: ' . $e->getMessage();
        }
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login Rápido - Uniminuto</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        body {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', system-ui, sans-serif;
        }
        .login-container {
            background: rgba(255, 255, 255, 0.98);
            backdrop-filter: blur(20px);
            border-radius: 20px;
            padding: 2.5rem;
            box-shadow: 0 20px 60px rgba(0,0,0,0.15);
            width: 100%;
            max-width: 420px;
            border: 1px solid rgba(255, 255, 255, 0.2);
        }
        .login-header { 
            text-align: center; 
            margin-bottom: 2.5rem; 
        }
        .brand-container {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 1rem;
            margin-bottom: 1.5rem;
        }
        .logo-image {
            width: 60px;
            height: 60px;
            border-radius: 12px;
            border: 2px solid rgba(30, 77, 114, 0.2);
            object-fit: cover;
        }
        .brand-text {
            text-align: left;
        }
        .brand-text h1 {
            margin: 0;
            font-size: 1.5rem;
            font-weight: 700;
            color: #1e4d72;
            background: linear-gradient(135deg, #1e4d72, #2980b9);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }
        .brand-text p {
            margin: 0;
            font-size: 0.9rem;
            color: #64748b;
            opacity: 0.8;
        }
        .login-logo {
            width: 80px; height: 80px; margin: 0 auto 1rem;
            background: linear-gradient(135deg, #1e4d72, #2980b9);
            border-radius: 50%; display: flex; align-items: center;
            justify-content: center; color: white; font-size: 2rem;
            box-shadow: 0 10px 30px rgba(30, 77, 114, 0.3);
        }
        .page-title {
            color: #1e4d72;
            font-size: 2rem;
            font-weight: 700;
            margin-bottom: 0.5rem;
        }
        .page-subtitle {
            color: #64748b;
            font-size: 1rem;
            margin-bottom: 1.5rem;
        }
        .btn-login {
            width: 100%; padding: 0.9rem;
            background: linear-gradient(135deg, #28a745, #20c997);
            border: none; color: white; border-radius: 12px; font-weight: 600;
            font-size: 1rem;
            transition: all 0.3s ease;
        }
        .btn-login:hover { 
            transform: translateY(-2px); 
            box-shadow: 0 8px 25px rgba(40, 167, 69, 0.3);
        }
        .btn-register {
            width: 100%; padding: 0.9rem;
            background: linear-gradient(135deg, #6f42c1, #e83e8c);
            border: none; color: white; border-radius: 12px; font-weight: 600;
            font-size: 1rem;
            transition: all 0.3s ease;
            margin-top: 1rem;
        }
        .btn-register:hover { 
            transform: translateY(-2px); 
            box-shadow: 0 8px 25px rgba(111, 66, 193, 0.3);
        }
        .form-control {
            border-radius: 10px;
            padding: 0.8rem 1rem;
            border: 2px solid #e2e8f0;
            transition: all 0.3s ease;
        }
        .form-control:focus {
            border-color: #28a745;
            box-shadow: 0 0 0 0.2rem rgba(40, 167, 69, 0.25);
            transform: translateY(-1px);
        }
        .form-label {
            font-weight: 600;
            color: #374151;
            margin-bottom: 0.5rem;
        }
        .badge-fast {
            background: linear-gradient(135deg, #28a745, #20c997);
            color: white; font-size: 0.75rem;
            padding: 0.4rem 0.8rem; border-radius: 15px;
            font-weight: 600;
        }
        .divider {
            text-align: center;
            margin: 1.5rem 0;
            position: relative;
        }
        .divider::before {
            content: '';
            position: absolute;
            top: 50%;
            left: 0;
            right: 0;
            height: 1px;
            background: #e2e8f0;
        }
        .divider span {
            background: white;
            padding: 0 1rem;
            color: #64748b;
            font-size: 0.9rem;
        }
        .quick-actions {
            display: flex;
            gap: 0.5rem;
            justify-content: center;
            margin-top: 1.5rem;
        }
        .quick-actions a {
            padding: 0.5rem 1rem;
            border-radius: 8px;
            text-decoration: none;
            font-size: 0.9rem;
            transition: all 0.3s ease;
        }
        .quick-actions .btn-outline-primary {
            border: 1px solid #2980b9;
            color: #2980b9;
        }
        .quick-actions .btn-outline-primary:hover {
            background: #2980b9;
            color: white;
            transform: translateY(-1px);
        }
    </style>
</head>
<body>
    <div class="login-container">
        <div class="login-header">
            <!-- Brand Header con Logo -->
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
            
            <div class="login-logo"><i class="fas fa-bolt"></i></div>
            <h2 class="page-title">Acceso Rápido</h2>
            <span class="badge-fast">ULTRA FAST</span>
            <p class="page-subtitle">Ingresa a tu cuenta de forma instantánea</p>
        </div>
        
        <?php if ($error): ?>
            <div class="alert alert-danger">
                <i class="fas fa-exclamation-triangle"></i> <?= htmlspecialchars($error) ?>
            </div>
        <?php endif; ?>
        
        <form method="POST">
            <div class="mb-3">
                <label for="email" class="form-label">Email</label>
                <input type="email" class="form-control" id="email" name="email" required
                       value="cnavarroi@unicartagena.edu.co"
                       placeholder="ejemplo@uniminuto.edu.co">
            </div>
            
            <div class="mb-3">
                <label for="password" class="form-label">Contraseña</label>
                <input type="password" class="form-control" id="password" name="password" required
                       value="123123ADad."
                       placeholder="Ingrese su contraseña">
            </div>
            
            <button type="submit" class="btn btn-login">
                <i class="fas fa-rocket"></i> Iniciar Sesión
            </button>
        </form>
        
        <div class="divider">
            <span>o</span>
        </div>
        
        <a href="signup_fast.php" class="btn btn-register">
            <i class="fas fa-user-plus"></i> Crear Cuenta Nueva
        </a>
        
        <div class="quick-actions">
            <a href="http://localhost:3000/views/main_modern.php" class="btn-outline-primary">
                <i class="fas fa-external-link-alt"></i> Ir al menú
            </a>
        </div>
        
        <div class="text-center mt-4">
            <small class="text-muted">
                <i class="fas fa-tachometer-alt"></i> 
                PDO optimizado - Máxima velocidad
            </small>
        </div>
    </div>
</body>
</html>
