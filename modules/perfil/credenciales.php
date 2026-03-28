<?php
require_once('../../config/database.php');
 include('../../includes/header.php');

// Verificar que el usuario es administrador
if ($_SESSION['rol'] != 'Admin') {
    header("Location: /posada_del_mar/dashboard.php");
    exit();
}

// Obtener información del usuario
$query = "SELECT * FROM Usuarios WHERE UsuarioID = ?";
$stmt = $conn->prepare($query);
$stmt->execute([$_SESSION['usuario_id']]);
$usuario = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$usuario) {
    header("Location: /posada_del_mar/dashboard.php");
    exit();
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cambiar Credenciales - Posada del Mar</title>

    <style>
        .password-container {
            position: relative;
        }
        .password-toggle {
            cursor: pointer;
            position: absolute;
            right: 10px;
            top: 50%;
            transform: translateY(-50%);
        }
        .card-credentials {
            max-width: 600px;
            margin: 0 auto;
        }  .container{background-color:white;
        }
    </style>
</head>
<body>
    
    <div class="container py-5">
        <div class="row justify-content-center">
            <div class="col-lg-8">
                <nav aria-label="breadcrumb" class="mb-4">
                    <ol class="breadcrumb">
                        <li class="breadcrumb-item"><a href="/posada/modules/perfil/">Mi Perfil</a></li>
                        <li class="breadcrumb-item active" aria-current="page">Cambiar Credenciales</li>
                    </ol>
                </nav>
                
                <div class="card card-credentials shadow-sm">
                    <div class="card-header bg-primary text-white">
                        <h4 class="mb-0"><i class="fas fa-key me-2"></i>Cambiar Credenciales de Administrador</h4>
                    </div>
                    
                    <div class="card-body">
                        <?php if (isset($_SESSION['mensaje_credenciales'])): ?>
                            <div class="alert alert-<?= $_SESSION['mensaje_credenciales']['tipo'] ?> alert-dismissible fade show">
                                <?= $_SESSION['mensaje_credenciales']['texto'] ?>
                                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                            </div>
                            <?php unset($_SESSION['mensaje_credenciales']); ?>
                        <?php endif; ?>
                        
                        <form action="guardar_credenciales.php" method="post" id="credencialesForm">
                            <div class="mb-4">
                                <label for="nombre_usuario_actual" class="form-label">Nombre de Usuario Actual</label>
                                <input type="text" class="form-control" id="nombre_usuario_actual" 
                                       value="<?= htmlspecialchars($usuario['NombreUsuario']) ?>" readonly>
                            </div>
                            
                            <div class="mb-4">
                                <label for="nuevo_usuario" class="form-label">Nuevo Nombre de Usuario</label>
                                <input type="text" class="form-control" id="nuevo_usuario" name="nuevo_usuario" 
                                       value="<?= htmlspecialchars($usuario['NombreUsuario']) ?>">
                                <small class="text-muted">Mínimo 4 caracteres, sin espacios</small>
                            </div>
                            
                            <div class="mb-4 password-container">
                                <label for="contrasena_actual" class="form-label">Contraseña Actual*</label>
                                <input type="password" class="form-control" id="contrasena_actual" name="contrasena_actual" required>
                                <i class="fas fa-eye password-toggle" onclick="togglePassword('contrasena_actual')"></i>
                                <small class="text-muted">Requerida para confirmar cambios</small>
                            </div>
                            
                            <div class="mb-4 password-container">
                                <label for="nueva_contrasena" class="form-label">Nueva Contraseña</label>
                                <input type="password" class="form-control" id="nueva_contrasena" name="nueva_contrasena">
                                <i class="fas fa-eye password-toggle" onclick="togglePassword('nueva_contrasena')"></i>
                                <small class="text-muted">Mínimo 8 caracteres. Dejar en blanco para no cambiar</small>
                            </div>
                            
                            <div class="mb-4 password-container">
                                <label for="confirmar_contrasena" class="form-label">Confirmar Nueva Contraseña</label>
                                <input type="password" class="form-control" id="confirmar_contrasena" name="confirmar_contrasena">
                                <i class="fas fa-eye password-toggle" onclick="togglePassword('confirmar_contrasena')"></i>
                            </div>
                            
                            <div class="d-flex justify-content-between mt-4">
                                <a href="index.php" class="btn btn-outline-secondary">
                                    <i class="fas fa-arrow-left me-1"></i> Cancelar
                                </a>
                                <button type="submit" class="btn btn-primary">
                                    <i class="fas fa-save me-1"></i> Guardar Cambios
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        function togglePassword(id) {
            const input = document.getElementById(id);
            const icon = input.nextElementSibling;
            
            if (input.type === 'password') {
                input.type = 'text';
                icon.classList.replace('fa-eye', 'fa-eye-slash');
            } else {
                input.type = 'password';
                icon.classList.replace('fa-eye-slash', 'fa-eye');
            }
        }
        
        document.getElementById('credencialesForm').addEventListener('submit', function(e) {
            const nuevaContrasena = document.getElementById('nueva_contrasena').value;
            const confirmarContrasena = document.getElementById('confirmar_contrasena').value;
            
            if (nuevaContrasena !== confirmarContrasena) {
                e.preventDefault();
                alert('Las contraseñas no coinciden');
                return false;
            }
            
            if (nuevaContrasena && nuevaContrasena.length < 8) {
                e.preventDefault();
                alert('La nueva contraseña debe tener al menos 8 caracteres');
                return false;
            }
            
            const nuevoUsuario = document.getElementById('nuevo_usuario').value;
            if (nuevoUsuario.length < 4 || nuevoUsuario.includes(' ')) {
                e.preventDefault();
                alert('El nombre de usuario debe tener al menos 4 caracteres y no contener espacios');
                return false;
            }
        });
    </script>
</body>
</html>
    <?php include('../../includes/footer.php'); ?>
