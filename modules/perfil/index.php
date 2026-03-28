<?php

require_once('../../config/database.php');
include('../../includes/header.php');


// Obtener información del usuario y empleado
$query = "SELECT u.*, e.* 
          FROM Usuarios u
          JOIN Empleados e ON u.EmpleadoID = e.EmpleadoID
          WHERE u.UsuarioID = ?";
$stmt = $conn->prepare($query);
$stmt->execute([$_SESSION['usuario_id']]);
$usuario = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$usuario) {
    header("Location: /posada_del_mar/dashboard.php");
    exit();
}

// Verificar si el usuario actual es admin
$esAdminActual = ($_SESSION['rol'] == 'Admin');
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mi Perfil - Posada del Mar</title>
   
    <style>
        .profile-header {
            background: linear-gradient(135deg, #4361ee 0%, #3f37c9 100%);
            color: white;
            border-radius: 15px 15px 0 0;
        }
        .admin-badge {
            background-color: #dc3545;
            font-size: 0.8rem;
        }
    </style>
</head>
<body>
    
    <div class="container py-5">
        <div class="row justify-content-center">
            <div class="col-lg-8">
                <div class="card shadow-sm">
                    <div class="profile-header text-center p-4">
                        <div class="d-flex justify-content-center mb-3">
                            <img src="https://ui-avatars.com/api/?name=<?= urlencode($usuario['Nombre'] . '+' . $usuario['Apellido']) ?>&background=random" 
                                 class="rounded-circle" 
                                 style="width: 150px; height: 150px; object-fit: cover; border: 5px solid white;"
                                 alt="Foto de perfil">
                        </div>
                        <h3><?= htmlspecialchars($usuario['Nombre'] . ' ' . $usuario['Apellido']) ?></h3>
                        <span class="badge <?= $usuario['Rol'] == 'Admin' ? 'admin-badge' : 'bg-primary' ?>">
                            <?= htmlspecialchars($usuario['Rol']) ?>
                            <?= $usuario['Rol'] == 'Admin' ? ' <i class="fas fa-shield-alt"></i>' : '' ?>
                        </span>
                    </div>
                    
                    <div class="card-body p-4">
                        <?php if (isset($_SESSION['mensaje'])): ?>
                            <div class="alert alert-<?= $_SESSION['mensaje']['tipo'] ?> alert-dismissible fade show">
                                <?= $_SESSION['mensaje']['texto'] ?>
                                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                            </div>
                            <?php unset($_SESSION['mensaje']); ?>
                        <?php endif; ?>
                        
                        <div class="row mb-4">
                            <!-- ... (campos de información del perfil) ... -->
                        </div>
                        
                       
<div class="d-flex justify-content-between">
    <a href="/posada_del_mar/dashboard.php" class="btn btn-outline-secondary">
        <i class="fas fa-arrow-left me-1"></i> Volver al Dashboard
    </a>
    
    <div>
        <?php if ($usuario['Rol'] != 'Admin' || $esAdminActual): ?>
            <a href="editar.php" class="btn btn-primary me-2">
                <i class="fas fa-edit me-1"></i> Editar Perfil
            </a>
        <?php endif; ?>
        
        <?php if ($esAdminActual): ?>
            <a href="credenciales.php" class="btn btn-warning">
                <i class="fas fa-key me-1"></i> Cambiar Credenciales
            </a>
        <?php else: ?>
                                <button class="btn btn-secondary" disabled>
                                    <i class="fas fa-lock me-1"></i> Solo editable por el Admin
                                </button>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

</body>
</html>
<?php include('../../includes/footer.php');
