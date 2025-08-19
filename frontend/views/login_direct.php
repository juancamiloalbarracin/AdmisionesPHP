<?php
/**
 * LOGIN DIRECTO - Sin API
 * Conecta directamente a la base de datos para diagnosticar
 */

session_start();

// Incluir configuración de base de datos
require_once '../../config/bootstrap.php';

use UDC\SistemaAdmisiones\Utils\Database;

$error = '';
$success = '';

// Procesar formulario
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email'] ?? '');
    $password = trim($_POST['password'] ?? '');
    
    if (empty($email) || empty($password)) {
        $error = 'Complete todos los campos';
    } else {
        try {
            // Optimizar conexión a la base de datos
            $db = Database::getInstance();
            
            // Query optimizada con LIMIT 1
            $sql = "SELECT id, email, password_hash, nombres, apellidos, activo 
                    FROM usuarios 
                    WHERE email = :email AND activo = 1 
                    LIMIT 1";
            
            $user = $db->fetch($sql, [':email' => $email]);
            
            if ($user && password_verify($password, $user['password_hash'])) {
                // Login exitoso - crear sesión
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['jwt_token'] = 'direct-login-token';
                $_SESSION['user_data'] = [
                    'id' => $user['id'],
                    'email' => $user['email'],
                    'nombres' => $user['nombres'],
                    'apellidos' => $user['apellidos']
                ];
                
                // Redirigir al puerto correcto donde está funcionando
                header('Location: http://localhost:3000/views/main_modern.php', true, 303);
                exit();
            } else {
                $error = 'Credenciales inválidas';
            }
        } catch (Exception $e) {
            $error = 'Error de base de datos: ' . $e->getMessage();
        }
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login Directo - Uniminuto</title>
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
        .badge-direct {
            background: #28a745;
            color: white;
            font-size: 0.7rem;
            padding: 0.3rem 0.6rem;
            border-radius: 10px;
        }
    </style>
</head>
<body>
    <div class="login-container">
        <div class="login-header">
            <div class="login-logo">
                <i class="fas fa-university"></i>
            </div>
            <h2>Login Directo <span class="badge-direct">DB DIRECT</span></h2>
            <p class="text-muted">Conexión directa a base de datos</p>
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
                       value="<?= htmlspecialchars($_POST['email'] ?? '') ?>"
                       placeholder="ejemplo@uniminuto.edu.co">
            </div>
            
            <div class="mb-3">
                <label for="password" class="form-label">Contraseña</label>
                <input type="password" class="form-control" id="password" name="password" required
                       placeholder="Ingrese su contraseña">
            </div>
            
            <button type="submit" class="btn btn-login">
                <i class="fas fa-database"></i> Login Directo
            </button>
        </form>
        
        <div class="text-center mt-3">
            <small class="text-muted">
                <i class="fas fa-info-circle"></i> 
                Esta página conecta directamente a la DB sin usar API
            </small>
        </div>
    </div>
</body>
</html>
