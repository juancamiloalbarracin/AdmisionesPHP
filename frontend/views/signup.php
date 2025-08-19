<?php
/**
 * REGISTRO RÁPIDO - UNIMINUTO
 * Página optimizada para registro de usuarios
 */

session_start();

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nombres = trim($_POST['nombres'] ?? '');
    $apellidos = trim($_POST['apellidos'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $password = trim($_POST['password'] ?? '');
    $confirmPassword = trim($_POST['confirm_password'] ?? '');
    $tipoDocumento = trim($_POST['tipo_documento'] ?? 'CC');
    $numeroDocumento = trim($_POST['numero_documento'] ?? '');
    $telefono = trim($_POST['telefono'] ?? '');
    
    // Validaciones
    if (empty($nombres) || empty($apellidos) || empty($email) || empty($password)) {
        $error = 'Complete todos los campos obligatorios';
    } elseif ($password !== $confirmPassword) {
        $error = 'Las contraseñas no coinciden';
    } elseif (strlen($password) < 6) {
        $error = 'La contraseña debe tener al menos 6 caracteres';
    } else {
        try {
            // Conexión directa PDO
            $dsn = "mysql:host=localhost;dbname=admisiones_udc;charset=utf8mb4";
            $pdo = new PDO($dsn, 'root', '', [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
            ]);
            
            // Verificar si el email ya existe
            $stmt = $pdo->prepare("SELECT id FROM usuarios WHERE email = ? LIMIT 1");
            $stmt->execute([$email]);
            
            if ($stmt->fetch()) {
                $error = 'El email ya está registrado';
            } else {
                // Crear usuario
                $passwordHash = password_hash($password, PASSWORD_DEFAULT);
                
                $stmt = $pdo->prepare("INSERT INTO usuarios (email, password_hash, nombres, apellidos, tipo_documento, numero_documento, telefono, activo, fecha_registro) VALUES (?, ?, ?, ?, ?, ?, ?, 1, NOW())");
                
                $stmt->execute([
                    $email, 
                    $passwordHash, 
                    $nombres, 
                    $apellidos, 
                    $tipoDocumento,
                    $numeroDocumento,
                    $telefono
                ]);
                
                $success = '¡Registro exitoso! Ya puedes iniciar sesión.';
                
                // Limpiar formulario
                $nombres = $apellidos = $email = $numeroDocumento = $telefono = '';
            }
        } catch (Exception $e) {
            $error = 'Error en el registro: ' . $e->getMessage();
        }
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Registro Rápido - Uniminuto</title>
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
            padding: 2rem 1rem;
        }
        .register-container {
            background: rgba(255, 255, 255, 0.98);
            backdrop-filter: blur(20px);
            border-radius: 20px;
            padding: 2.5rem;
            box-shadow: 0 20px 60px rgba(0,0,0,0.15);
            width: 100%;
            max-width: 500px;
            border: 1px solid rgba(255, 255, 255, 0.2);
        }
        .register-header { 
            text-align: center; 
            margin-bottom: 2rem; 
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
            border: 2px solid rgba(111, 66, 193, 0.2);
            object-fit: cover;
        }
        .brand-text h1 {
            margin: 0;
            font-size: 1.5rem;
            font-weight: 700;
            color: #6f42c1;
            background: linear-gradient(135deg, #6f42c1, #e83e8c);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
        }
        .brand-text p {
            margin: 0;
            font-size: 0.9rem;
            color: #64748b;
        }
        .register-logo {
            width: 80px; height: 80px; margin: 0 auto 1rem;
            background: linear-gradient(135deg, #6f42c1, #e83e8c);
            border-radius: 50%; display: flex; align-items: center;
            justify-content: center; color: white; font-size: 2rem;
            box-shadow: 0 10px 30px rgba(111, 66, 193, 0.3);
        }
        .page-title {
            color: #6f42c1;
            font-size: 2rem;
            font-weight: 700;
            margin-bottom: 0.5rem;
        }
        .btn-register {
            width: 100%; padding: 0.9rem;
            background: linear-gradient(135deg, #6f42c1, #e83e8c);
            border: none; color: white; border-radius: 12px; font-weight: 600;
            font-size: 1rem;
            transition: all 0.3s ease;
        }
        .btn-register:hover { 
            transform: translateY(-2px); 
            box-shadow: 0 8px 25px rgba(111, 66, 193, 0.3);
        }
        .btn-login {
            width: 100%; padding: 0.9rem;
            background: linear-gradient(135deg, #28a745, #20c997);
            border: none; color: white; border-radius: 12px; font-weight: 600;
            margin-top: 1rem;
        }
        .form-control {
            border-radius: 10px;
            padding: 0.8rem 1rem;
            border: 2px solid #e2e8f0;
            transition: all 0.3s ease;
        }
        .form-control:focus {
            border-color: #6f42c1;
            box-shadow: 0 0 0 0.2rem rgba(111, 66, 193, 0.25);
            transform: translateY(-1px);
        }
        .form-label {
            font-weight: 600;
            color: #374151;
            margin-bottom: 0.5rem;
        }
        .badge-fast {
            background: linear-gradient(135deg, #6f42c1, #e83e8c);
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
        .row {
            margin-left: -0.5rem;
            margin-right: -0.5rem;
        }
        .col-md-6 {
            padding-left: 0.5rem;
            padding-right: 0.5rem;
        }
    </style>
</head>
<body>
    <div class="register-container">
        <div class="register-header">
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
            
            <div class="register-logo"><i class="fas fa-user-plus"></i></div>
            <h2 class="page-title">Crear Cuenta</h2>
            <span class="badge-fast">REGISTRO RÁPIDO</span>
            <p class="text-muted">Únete a la comunidad Uniminuto</p>
        </div>
        
        <?php if ($error): ?>
            <div class="alert alert-danger">
                <i class="fas fa-exclamation-triangle"></i> <?= htmlspecialchars($error) ?>
            </div>
        <?php endif; ?>
        
        <?php if ($success): ?>
            <div class="alert alert-success">
                <i class="fas fa-check-circle"></i> <?= htmlspecialchars($success) ?>
            </div>
        <?php endif; ?>
        
        <form method="POST">
            <div class="row mb-3">
                <div class="col-md-6">
                    <label for="nombres" class="form-label">Nombres *</label>
                    <input type="text" class="form-control" id="nombres" name="nombres" required
                           value="<?= htmlspecialchars($nombres ?? '') ?>"
                           placeholder="Ej: Juan Carlos">
                </div>
                <div class="col-md-6">
                    <label for="apellidos" class="form-label">Apellidos *</label>
                    <input type="text" class="form-control" id="apellidos" name="apellidos" required
                           value="<?= htmlspecialchars($apellidos ?? '') ?>"
                           placeholder="Ej: García López">
                </div>
            </div>
            
            <div class="mb-3">
                <label for="email" class="form-label">Email *</label>
                <input type="email" class="form-control" id="email" name="email" required
                       value="<?= htmlspecialchars($email ?? '') ?>"
                       placeholder="ejemplo@uniminuto.edu.co">
            </div>
            
            <div class="row mb-3">
                <div class="col-md-6">
                    <label for="password" class="form-label">Contraseña *</label>
                    <input type="password" class="form-control" id="password" name="password" required
                           placeholder="Mínimo 6 caracteres">
                </div>
                <div class="col-md-6">
                    <label for="confirm_password" class="form-label">Confirmar *</label>
                    <input type="password" class="form-control" id="confirm_password" name="confirm_password" required
                           placeholder="Repita la contraseña">
                </div>
            </div>
            
            <div class="row mb-3">
                <div class="col-md-6">
                    <label for="tipo_documento" class="form-label">Tipo Doc.</label>
                    <select class="form-control" id="tipo_documento" name="tipo_documento">
                        <option value="CC">Cédula (CC)</option>
                        <option value="TI">Tarjeta Identidad (TI)</option>
                        <option value="CE">Cédula Extranjería (CE)</option>
                        <option value="PP">Pasaporte (PP)</option>
                    </select>
                </div>
                <div class="col-md-6">
                    <label for="numero_documento" class="form-label">Número</label>
                    <input type="text" class="form-control" id="numero_documento" name="numero_documento"
                           value="<?= htmlspecialchars($numeroDocumento ?? '') ?>"
                           placeholder="Ej: 12345678">
                </div>
            </div>
            
            <div class="mb-3">
                <label for="telefono" class="form-label">Teléfono</label>
                <input type="tel" class="form-control" id="telefono" name="telefono"
                       value="<?= htmlspecialchars($telefono ?? '') ?>"
                       placeholder="Ej: +57 300 123 4567">
            </div>
            
            <button type="submit" class="btn btn-register">
                <i class="fas fa-user-plus"></i> Crear Cuenta
            </button>
        </form>
        
        <div class="divider">
            <span>¿Ya tienes cuenta?</span>
        </div>
        
        <a href="login.php" class="btn btn-login">
            <i class="fas fa-sign-in-alt"></i> Iniciar Sesión
        </a>
        
        <div class="text-center mt-4">
            <small class="text-muted">
                <i class="fas fa-shield-alt"></i> 
                Tus datos están protegidos y encriptados
            </small>
        </div>
    </div>
</body>
</html>
