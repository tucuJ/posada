<?php
session_start();
require_once('config/database.php');

// Si ya está logueado, redirigir al dashboard
if (isset($_SESSION['usuario_id'])) {
    header("Location: dashboard.php");
    exit();
}

// Procesar formulario de login
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $usuario = $_POST['usuario'] ?? '';
    $contrasena = $_POST['contrasena'] ?? '';

    try {
        // Consulta simplificada - solo tabla usuarios
        $query = "SELECT * FROM Usuarios WHERE NombreUsuario = ? AND Activo = 1";
        $stmt = $conn->prepare($query);
        $stmt->execute([$usuario]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        // Verificación simplificada (puedes usar password_verify si las contraseñas están hasheadas)
        if ($user && $contrasena === $user['Contrasena']) {
            // Iniciar sesión con datos básicos
            $_SESSION['usuario_id'] = $user['UsuarioID'];
            $_SESSION['usuario_nombre'] = $user['NombreUsuario'];
            $_SESSION['nombre_completo'] = $user['NombreCompleto'];
            $_SESSION['rol'] = $user['Rol'];

            // Redirigir al dashboard
            header("Location: dashboard.php");
            exit();
        } else {
            $error = "Usuario o contraseña incorrectos";
        }
    } catch(PDOException $e) {
        $error = "Error al iniciar sesión";
    }
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login Simple</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <style>
        :root {
            --primary-color: #4361ee;
            --secondary-color: #3f37c9;
            --accent-color: #4895ef;
            --light-color: #f8f9fa;
            --dark-color: #212529;
            --error-color: #f72585;
            --success-color: #4cc9f0;
        }
        
        body {
            background: linear-gradient(135deg, #f5f7fa 0%, #c3cfe2 100%);
            font-family: 'Segoe UI', Roboto, Oxygen, Ubuntu, Cantarell, 'Open Sans', sans-serif;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0;
            padding: 20px;
        }
        
        .login-container {
            max-width: 420px;
            width: 100%;
            animation: fadeIn 0.6s ease-out;
        }
        
        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(20px); }
            to { opacity: 1; transform: translateY(0); }
        }
        
        .login-card {
            border-radius: 12px;
            overflow: hidden;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
            border: none;
            transition: transform 0.3s ease;
        }
        
        .login-card:hover {
            transform: translateY(-5px);
        }
        
        .card-header {
            background: var(--primary-color);
            color: white;
            text-align: center;
            padding: 25px;
            position: relative;
            overflow: hidden;
        }
        
        .card-header::before {
            content: "";
            position: absolute;
            top: -50%;
            left: -50%;
            width: 200%;
            height: 200%;
            background: linear-gradient(
                to bottom right,
                rgba(255, 255, 255, 0.1) 0%,
                rgba(255, 255, 255, 0) 60%
            );
            transform: rotate(30deg);
        }
        
        .card-header h3 {
            position: relative;
            font-weight: 600;
            margin: 0;
        }
        
        .card-body {
            padding: 30px;
            background: white;
        }
        
        .form-control {
            border-radius: 8px;
            padding: 12px 15px;
            border: 1px solid #e0e0e0;
            transition: all 0.3s;
        }
        
        .form-control:focus {
            border-color: var(--accent-color);
            box-shadow: 0 0 0 0.25rem rgba(72, 149, 239, 0.25);
        }
        
        .input-icon {
            position: relative;
        }
        
        .input-icon i {
            position: absolute;
            left: 15px;
            top: 50%;
            transform: translateY(-50%);
            color: var(--primary-color);
        }
        
        .input-icon input {
            padding-left: 45px;
        }
        
        .btn-login {
            background: var(--primary-color);
            border: none;
            border-radius: 8px;
            padding: 12px;
            font-weight: 600;
            letter-spacing: 0.5px;
            transition: all 0.3s;
            text-transform: uppercase;
            font-size: 0.9rem;
        }
        
        .btn-login:hover {
            background: var(--secondary-color);
            transform: translateY(-2px);
        }
        
        .btn-login:active {
            transform: translateY(0);
        }
        
        .alert {
            border-radius: 8px;
            border: none;
        }
        
        .alert-danger {
            background-color: var(--error-color);
            color: white;
        }
        
        .forgot-link {
            color: var(--primary-color);
            text-decoration: none;
            font-size: 0.9rem;
            transition: all 0.3s;
            display: inline-block;
            margin-top: 10px;
        }
        
        .forgot-link:hover {
            color: var(--secondary-color);
            text-decoration: underline;
        }
        
        .brand-logo {
            width: 90px;
            margin-bottom: 15px;
        }
        
        .waves {
            position: fixed;
            bottom: 0;
            left: 0;
            width: 100%;
            overflow: hidden;
            line-height: 0;
            z-index: -1;
        }
        
        .waves svg {
            position: relative;
            display: block;
            width: calc(100% + 1.3px);
            height: 120px;
        }
        
        .waves .shape-fill {
            fill: var(--primary-color);
            opacity: 0.1;
        }
    </style>
</head>
<body>
    <div class="login-container">
        <div class="login-card card">
            <div class="card-header">
                <img src="usuario.png" alt="Logo" class="brand-logo">
                <h3>INICIAR SESIÓN</h3>
            </div>
            <div class="card-body">
                <?php if (isset($error)): ?>
                    <div class="alert alert-danger alert-dismissible fade show">
                        <i class="fas fa-exclamation-circle me-2"></i>
                        <?= htmlspecialchars($error) ?>
                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                <?php endif; ?>

                <form method="post" class="mt-3">
                    <div class="mb-4 input-icon">
                        <i class="fas fa-user"></i>
                        <input type="text" class="form-control" id="usuario" name="usuario" placeholder="Nombre de usuario" required>
                    </div>
                    <div class="mb-4 input-icon">
                        <i class="fas fa-lock"></i>
                        <input type="password" class="form-control" id="contrasena" name="contrasena" placeholder="Contraseña" required>
                    </div>
                    <button type="submit" class="btn btn-primary btn-login w-100 mb-3">
                        <i class="fas fa-sign-in-alt me-2"></i> ACCEDER
                    </button>
                    <div class="text-center">
                        <a href="#" class="forgot-link">¿Olvidaste tu contraseña?</a>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <div class="waves">
        <svg data-name="Layer 1" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 1200 120" preserveAspectRatio="none">
            <path d="M0,0V46.29c47.79,22.2,103.59,32.17,158,28,70.36-5.37,136.33-33.31,206.8-37.5C438.64,32.43,512.34,53.67,583,72.05c69.27,18,138.3,24.88,209.4,13.08,36.15-6,69.85-17.84,104.45-29.34C989.49,25,1113-14.29,1200,52.47V0Z" opacity=".25" class="shape-fill"></path>
            <path d="M0,0V15.81C13,36.92,27.64,56.86,47.69,72.05,99.41,111.27,165,111,224.58,91.58c31.15-10.15,60.09-26.07,89.67-39.8,40.92-19,84.73-46,130.83-49.67,36.26-2.85,70.9,9.42,98.6,31.56,31.77,25.39,62.32,62,103.63,73,40.44,10.79,81.35-6.69,119.13-24.28s75.16-39,116.92-43.05c59.73-5.85,113.28,22.88,168.9,38.84,30.2,8.66,59,6.17,87.09-7.5,22.43-10.89,48-26.93,60.65-49.24V0Z" opacity=".5" class="shape-fill"></path>
            <path d="M0,0V5.63C149.93,59,314.09,71.32,475.83,42.57c43-7.64,84.23-20.12,127.61-26.46,59-8.63,112.48,12.24,165.56,35.4C827.93,77.22,886,95.24,951.2,90c86.53-7,172.46-45.71,248.8-84.81V0Z" class="shape-fill"></path>
        </svg>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>