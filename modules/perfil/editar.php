<?php include('../../includes/header.php');

require_once('../../config/database.php');


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

// Verificar permisos de edición
$puedeEditar = ($usuario['Rol'] != 'Admin' || $_SESSION['rol'] == 'Admin');
if (!$puedeEditar) {
    $_SESSION['mensaje'] = [
        'tipo' => 'danger',
        'titulo' => 'Acceso denegado',
        'texto' => 'No tienes permiso para editar este perfil'
    ];
    header("Location: index.php");
    exit();
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Editar Perfil - Posada del Mar</title>
    
    <style>
        .profile-pic-container {
            position: relative;
            width: 150px;
            height: 150px;
            margin: 0 auto 20px;
        }
        .profile-pic {
            width: 100%;
            height: 100%;
            object-fit: cover;
            border-radius: 50%;
            border: 5px solid #fff;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.1);
        }
        .upload-btn {
            position: absolute;
            bottom: 0;
            right: 0;
            background: #4361ee;
            color: white;
            border-radius: 50%;
            width: 40px;
            height: 40px;
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.2);
        }
        .admin-warning {
            border-left: 4px solid #dc3545;
        }
        .container{background-color:white ;
        }
    </style>
</head>
<body>
    
    <div class="container py-5">
        <div class="row justify-content-center">
            <div class="col-lg-8">
                <div class="card shadow-sm">
                    <div class="card-header bg-primary text-white">
                        <h4 class="mb-0"><i class="fas fa-user-edit me-2"></i>Editar Perfil</h4>
                        <?php if ($usuario['Rol'] == 'Admin'): ?>
                            <small class="text-warning"><i class="fas fa-shield-alt me-1"></i> Perfil de Administrador</small>
                        <?php endif; ?>
                    </div>
                    
                    <div class="card-body">
                        <?php if ($usuario['Rol'] == 'Admin'): ?>
                            <div class="alert alert-warning admin-warning">
                                <i class="fas fa-exclamation-triangle me-2"></i>
                                Estás editando un perfil de administrador. Los cambios pueden afectar el sistema.
                            </div>
                        <?php endif; ?>
                        
                        <form action="guardar.php" method="post" enctype="multipart/form-data">
                            <div class="text-center mb-4">
                                <div class="profile-pic-container">
                                    <img src="https://ui-avatars.com/api/?name=<?= urlencode($usuario['Nombre'] . '+' . $usuario['Apellido']) ?>&background=random" 
                                         class="profile-pic" 
                                         id="profile-pic-preview"
                                         alt="Foto de perfil">
                                    <div class="upload-btn" onclick="document.getElementById('foto').click()">
                                        <i class="fas fa-camera"></i>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="row g-3">
                                <div class="col-md-6">
                                    <label for="nombre" class="form-label">Nombre</label>
                                    <input type="text" class="form-control" id="nombre" name="nombre" 
                                           value="<?= htmlspecialchars($usuario['Nombre']) ?>" required>
                                </div>
                                <div class="col-md-6">
                                    <label for="apellido" class="form-label">Apellido</label>
                                    <input type="text" class="form-control" id="apellido" name="apellido" 
                                           value="<?= htmlspecialchars($usuario['Apellido']) ?>" required>
                                </div>
                                
                                <div class="col-md-6">
                                    <label class="form-label">Tipo de Documento</label>
                                    <input type="text" class="form-control" value="<?= htmlspecialchars($usuario['TipoDocumento']) ?>" readonly>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">Número de Documento</label>
                                    <input type="text" class="form-control" value="<?= htmlspecialchars($usuario['NumeroDocumento']) ?>" readonly>
                                </div>
                                
                                <div class="col-md-6">
                                    <label for="telefono" class="form-label">Teléfono</label>
                                    <input type="tel" class="form-control" id="telefono" name="telefono" 
                                           value="<?= htmlspecialchars($usuario['Telefono']) ?>">
                                </div>
                                <div class="col-md-6">
                                    <label for="email" class="form-label">Email</label>
                                    <input type="email" class="form-control" id="email" name="email" 
                                           value="<?= htmlspecialchars($usuario['Email']) ?>">
                                </div>
                                
                                <div class="col-12">
                                    <label for="direccion" class="form-label">Dirección</label>
                                    <textarea class="form-control" id="direccion" name="direccion" rows="2"><?= htmlspecialchars($usuario['Direccion']) ?></textarea>
                                </div>
                                
                                <div class="col-md-6">
                                    <label class="form-label">Cargo</label>
                                    <input type="text" class="form-control" value="<?= htmlspecialchars($usuario['Cargo']) ?>" readonly>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">Fecha de Contratación</label>
                                    <input type="text" class="form-control" 
                                           value="<?= $usuario['FechaContratacion'] ? date('d/m/Y', strtotime($usuario['FechaContratacion'])) : 'No especificada' ?>" 
                                           readonly>
                                </div>
                                
                                <div class="col-12 mt-4">
                                    <div class="d-flex justify-content-between">
                                        <a href="index.php" class="btn btn-outline-secondary">
                                            <i class="fas fa-arrow-left me-1"></i> Cancelar
                                        </a>
                                        <button type="submit" class="btn btn-primary">
                                            <i class="fas fa-save me-1"></i> Guardar Cambios
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
<?php include('../../includes/footer.php');?>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        function previewImage(input) {
            if (input.files && input.files[0]) {
                const reader = new FileReader();
                
                reader.onload = function(e) {
                    document.getElementById('profile-pic-preview').src = e.target.result;
                }
                
                reader.readAsDataURL(input.files[0]);
            }
        }
    </script>
</body>
</html>
