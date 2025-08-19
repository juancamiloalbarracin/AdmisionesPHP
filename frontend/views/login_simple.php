<?php
/**
 * SISTEMA DE ADMISIONES UDC - LOGIN SIMPLE
 * ========================================
 * Página de login sin JavaScript, solo server-side
 */

session_start();

// Redirigir si ya está autenticado
if (isset($_SESSION['user_id']) && isset($_SESSION['jwt_token'])) {
    header('Location: main_modern.php', true, 303);
    exit();
}

$error = '';
$success = '';

// Procesar formulario
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email'] ?? '');
    $password = trim($_POST['password'] ?? '');
    
    if (empty($email) || empty($password)) {
        $error = 'Complete todos los campos';
    } else {
        // Llamar al API - detectar automáticamente la URL base
        $baseUrl = (isset($_SERVER['HTTPS']) ? 'https://' : 'http://') . $_SERVER['HTTP_HOST'];
        $apiUrl = $baseUrl . '/api/login';
        
        $apiData = json_encode(['email' => $email, 'password' => $password]);
        
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $apiUrl);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $apiData);
        curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_TIMEOUT, 10);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $curlError = curl_error($ch);
        curl_close($ch);
        
        // Debug información
        $debugInfo = "API URL: $apiUrl, HTTP Code: $httpCode, CURL Error: $curlError";
        error_log($debugInfo);
        
        if ($curlError) {
            $error = "Error de conexión CURL: $curlError";
        } elseif ($httpCode === 200 && $response) {
            $clean = preg_replace('/^\xEF\xBB\xBF/', '', trim($response));
            $data = json_decode($clean, true);
            
            if ($data && isset($data['success']) && $data['success'] === true) {
                // Login exitoso
                $_SESSION['user_id'] = $data['user']['id'];
                $_SESSION['jwt_token'] = $data['token'];
                $_SESSION['user_data'] = $data['user'];
                
                header('Location: main_modern.php', true, 303);
                exit();
            } else {
                $error = $data['message'] ?? 'Credenciales inválidas';
            }
        } else {
            $error = "Error HTTP $httpCode. Servidor: $apiUrl";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Iniciar Sesión - Uniminuto</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        body {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        .login-container {
            background: white;
            border-radius: 15px;
            padding: 2rem;
            box-shadow: 0 10px 30px rgba(0,0,0,0.2);
            width: 100%;
            max-width: 400px;
        }
        .login-header {
            text-align: center;
            margin-bottom: 2rem;
        }
        .login-logo {
            width: 80px;
            height: 80px;
            margin: 0 auto 1rem;
            background: linear-gradient(135deg, #1e4d72, #2980b9);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-size: 2rem;
        }
        .btn-login {
            width: 100%;
            padding: 0.8rem;
            background: linear-gradient(135deg, #1e4d72, #2980b9);
            border: none;
            color: white;
            border-radius: 8px;
            font-weight: 500;
        }
        .btn-login:hover {
            transform: translateY(-1px);
            box-shadow: 0 5px 15px rgba(30, 77, 114, 0.3);
        }
        .form-control:focus {
            border-color: #2980b9;
            box-shadow: 0 0 0 0.2rem rgba(41, 128, 185, 0.25);
        }
    </style>
</head>
<body>
    <div class="login-container">
        <div class="login-header">
            <div class="login-logo">
                <i class="fas fa-university"></i>
            </div>
            <h2>Bienvenido</h2>
            <p class="text-muted">Ingresa a tu cuenta de Uniminuto</p>
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
            <div class="mb-3">
                <label for="email" class="form-label">Email</label>
                <input type="email" class="form-control" id="email" name="email" required
                       value="<?= htmlspecialchars($_POST['email'] ?? '') ?>"
                       placeholder="ejemplo@uniminuto.edu.co">
            </div>
            
            <div class="mb-3">
                <label for="password" class="form-label">Contraseña</label>
                <input type="password" class="form-control" id="password" name="password" required
                       placeholder="Ingrese su contraseña">
            </div>
            
            <button type="submit" class="btn btn-login">
                <i class="fas fa-sign-in-alt"></i> Iniciar Sesión
            </button>
        </form>
        
        <div class="text-center mt-3">
            <p>¿No tienes cuenta? <a href="signup_modern.php">Crear cuenta</a></p>
        </div>
    </div>
</body>
</html>
