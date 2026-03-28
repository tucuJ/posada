    <?php include('../../includes/header.php'); ?>
<?php
require_once('../../config/database.php');


// Verificar si el usuario es administrador
if ($_SESSION['rol'] != 'Admin') {
    header("Location: /posada_del_mar/dashboard.php");
    exit();
}

$empleado_id = $_GET['id'] ?? null;

if (!$empleado_id) {
    header("Location: index.php");
    exit();
}

// Obtener información del empleado
$query = "SELECT * FROM Empleados WHERE EmpleadoID = ?";
$stmt = $conn->prepare($query);
$stmt->execute([$empleado_id]);
$empleado = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$empleado) {
    header("Location: index.php");
    exit();
}

// Verificar si el empleado es admin
$stmt_admin = $conn->prepare("SELECT UsuarioID FROM Usuarios WHERE EmpleadoID = ? AND Rol = 'Admin'");
$stmt_admin->execute([$empleado_id]);
$es_admin = $stmt_admin->fetch();
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Editar Empleado - Posada del Mar</title>
    <style>
        .form-section {
            background-color: #f8f9fa;
            border-radius: 10px;
            padding: 20px;
            margin-bottom: 20px;
        }
        .status-badge {
            font-size: 0.9rem;
        }
        .admin-alert {
            border-left: 4px solid #dc3545;
        }
    </style>
</head>
<body>
    
    <div class="container mt-4">
        <nav aria-label="breadcrumb" class="mb-4">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="/posada/dashboard.php">Dashboard</a></li>
                <li class="breadcrumb-item"><a href="listar.php">Empleados</a></li>
                <li class="breadcrumb-item active" aria-current="page">Editar Empleado</li>
            </ol>
        </nav>
        
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h2><i class="fas fa-user-edit me-2"></i>Editar Empleado</h2>
            <span class="badge <?= $empleado['Activo'] ? 'bg-success' : 'bg-secondary' ?> status-badge">
                <?= $empleado['Activo'] ? 'Activo' : 'Inactivo' ?>
            </span>
        </div>
        
        <div class="card shadow-sm">
            <div class="card-body">
                <?php if (isset($_SESSION['mensaje'])): ?>
                    <div class="alert alert-<?= $_SESSION['mensaje']['tipo'] ?> alert-dismissible fade show">
                        <?= $_SESSION['mensaje']['texto'] ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                    <?php unset($_SESSION['mensaje']); ?>
                <?php endif; ?>
                
                <?php if ($es_admin): ?>
                    <div class="alert alert-warning admin-alert">
                        <i class="fas fa-exclamation-triangle me-2"></i>
                        Este empleado es un administrador del sistema. Tenga cuidado con los cambios.
                    </div>
                <?php endif; ?>
                
                <form action="guardar.php" method="post">
                    <input type="hidden" name="empleado_id" value="<?= $empleado['EmpleadoID'] ?>">
                    
                    <div class="form-section">
                        <h5 class="mb-4"><i class="fas fa-id-card me-2"></i>Información Personal</h5>
                        
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label for="nombre" class="form-label">Nombre*</label>
                                <input type="text" class="form-control" id="nombre" name="nombre" 
                                       value="<?= htmlspecialchars($empleado['Nombre']) ?>" required>
                            </div>
                            <div class="col-md-6">
                                <label for="apellido" class="form-label">Apellido*</label>
                                <input type="text" class="form-control" id="apellido" name="apellido" 
                                       value="<?= htmlspecialchars($empleado['Apellido']) ?>" required>
                            </div>
                            
                            <div class="col-md-4">
                                <label for="tipo_documento" class="form-label">Tipo de Documento*</label>
                                <select class="form-select" id="tipo_documento" name="tipo_documento" required>
                                    <option value="CED" <?= $empleado['TipoDocumento'] == 'CED' ? 'selected' : '' ?>>Cédula</option>
                                    <option value="PAS" <?= $empleado['TipoDocumento'] == 'PAS' ? 'selected' : '' ?>>Pasaporte</option>
                                </select>
                            </div>
                            <div class="col-md-8">
                                <label for="numero_documento" class="form-label">Número de Documento*</label>
                                <input type="text" class="form-control" id="numero_documento" name="numero_documento" 
                                       value="<?= htmlspecialchars($empleado['NumeroDocumento']) ?>" required>
                            </div>
                        </div>
                    </div>
                    
                    <div class="form-section">
                        <h5 class="mb-4"><i class="fas fa-address-book me-2"></i>Información de Contacto</h5>
                        
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label for="telefono" class="form-label">Teléfono</label>
                                <input type="tel" class="form-control" id="telefono" name="telefono" 
                                       value="<?= htmlspecialchars($empleado['Telefono']) ?>">
                            </div>
                            <div class="col-md-6">
                                <label for="email" class="form-label">Email</label>
                                <input type="email" class="form-control" id="email" name="email" 
                                       value="<?= htmlspecialchars($empleado['Email']) ?>">
                            </div>
                            
                            <div class="col-12">
                                <label for="direccion" class="form-label">Dirección</label>
                                <textarea class="form-control" id="direccion" name="direccion" rows="2"><?= htmlspecialchars($empleado['Direccion']) ?></textarea>
                            </div>
                        </div>
                    </div>
                    
                    <div class="form-section">
                        <h5 class="mb-4"><i class="fas fa-briefcase me-2"></i>Información Laboral</h5>
                        
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label for="cargo" class="form-label">Cargo*</label>
                                <input type="text" class="form-control" id="cargo" name="cargo" 
                                       value="<?= htmlspecialchars($empleado['Cargo']) ?>" required>
                            </div>
                            <div class="col-md-6">
                                <label for="fecha_contratacion" class="form-label">Fecha de Contratación</label>
                                <input type="date" class="form-control" id="fecha_contratacion" name="fecha_contratacion" 
                                       value="<?= $empleado['FechaContratacion'] ? htmlspecialchars($empleado['FechaContratacion']) : '' ?>">
                            </div>
                            
                            <div class="col-12 mt-3">
                                <div class="form-check form-switch">
                                    <input class="form-check-input" type="checkbox" id="activo" name="activo" 
                                           <?= $empleado['Activo'] ? 'checked' : '' ?> <?= $es_admin ? 'disabled' : '' ?>>
                                    <label class="form-check-label" for="activo">
                                        Empleado Activo
                                        <?= $es_admin ? '(No se puede desactivar un administrador)' : '' ?>
                                    </label>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="d-flex justify-content-between mt-4">
                        <a href="listar.php" class="btn btn-outline-secondary">
                            <i class="fas fa-arrow-left me-1"></i> Volver
                        </a>
                        <div>
                            <a href="usuarios/?empleado_id=<?= $empleado['EmpleadoID'] ?>" class="btn btn-info me-2">
                                <i class="fas fa-user-cog me-1"></i> Gestionar Usuario
                            </a>
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-save me-1"></i> Guardar Cambios
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script>
        // Validación básica del formulario
        document.querySelector('form').addEventListener('submit', function(e) {
            const nombre = document.getElementById('nombre').value.trim();
            const apellido = document.getElementById('apellido').value.trim();
            const documento = document.getElementById('numero_documento').value.trim();
            const cargo = document.getElementById('cargo').value.trim();
            
            if (!nombre || !apellido || !documento || !cargo) {
                e.preventDefault();
                alert('Por favor complete todos los campos obligatorios');
                return false;
            }
        });
    </script>
</body>
</html>   <?php include('../../includes/footer.php'); ?>
