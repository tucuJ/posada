<?php
require_once('../../config/database.php');
include('../../includes/header.php');

// Verificar si el usuario es administrador
if ($_SESSION['rol'] != 'Admin') {
    header("Location: /posada_del_mar/dashboard.php");
    exit();
}

// Consulta para obtener todos los empleados
$query = "SELECT * FROM Empleados ORDER BY Activo DESC, Apellido, Nombre";
$empleados = $conn->query($query);
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestión de Empleados - Posada del Mar</title>

    <style>
        .table-responsive {
            overflow-x: auto;
        }
        .status-badge {
            font-size: 0.85rem;
        }
        .action-buttons {
            white-space: nowrap;
        }
    </style>
</head>
<body>
    
    <div class="container mt-4">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h2><i class="fas fa-users me-2"></i>Gestión de Empleados</h2>
            <a href="agregar.php" class="btn btn-primary">
                <i class="fas fa-plus me-1"></i> Nuevo Empleado
            </a>
        </div>
        
        <?php if (isset($_SESSION['mensaje'])): ?>
            <div class="alert alert-<?= $_SESSION['mensaje']['tipo'] ?> alert-dismissible fade show">
                <?= $_SESSION['mensaje']['texto'] ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
            <?php unset($_SESSION['mensaje']); ?>
        <?php endif; ?>
        
        <div class="card shadow-sm">
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-hover table-striped">
                        <thead class="table-light">
                            <tr>
                                <th>ID</th>
                                <th>Nombre</th>
                                <th>Documento</th>
                                <th>Teléfono</th>
                                <th>Cargo</th>
                                <th>Estado</th>
                                <th>Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php while ($empleado = $empleados->fetch(PDO::FETCH_ASSOC)): ?>
                            <tr>
                                <td><?= htmlspecialchars($empleado['EmpleadoID']) ?></td>
                                <td><?= htmlspecialchars($empleado['Nombre'] . ' ' . $empleado['Apellido']) ?></td>
                                <td><?= htmlspecialchars($empleado['TipoDocumento'] . ' ' . $empleado['NumeroDocumento']) ?></td>
                                <td><?= htmlspecialchars($empleado['Telefono']) ?></td>
                                <td><?= htmlspecialchars($empleado['Cargo']) ?></td>
                                <td>
                                    <span class="badge <?= $empleado['Activo'] ? 'bg-success' : 'bg-secondary' ?> status-badge">
                                        <?= $empleado['Activo'] ? 'Activo' : 'Inactivo' ?>
                                    </span>
                                </td>
                                <td class="action-buttons">
                                    <div class="d-flex gap-2">
                                        <a href="editar.php?id=<?= $empleado['EmpleadoID'] ?>" class="btn btn-sm btn-outline-primary" title="Editar">
                                            <i class="fas fa-edit"></i>
                                        </a>
                                        <a href="usuarios/listar.php?empleado_id=<?= $empleado['EmpleadoID'] ?>" class="btn btn-sm btn-outline-info" title="Gestionar Usuario">
                                            <i class="fas fa-user-cog"></i>
                                        </a>
                                        <?php 
                                        // Verificar si el empleado es admin
                                        $stmt_admin = $conn->prepare("SELECT UsuarioID FROM Usuarios WHERE EmpleadoID = ? AND Rol = 'Admin'");
                                        $stmt_admin->execute([$empleado['EmpleadoID']]);
                                        $es_admin = $stmt_admin->fetch();
                                        ?>
                                        <?php if (!$es_admin): ?>
                                            <form action="acciones.php" method="post" class="d-inline">
                                                <input type="hidden" name="empleado_id" value="<?= $empleado['EmpleadoID'] ?>">
                                                <input type="hidden" name="accion" value="<?= $empleado['Activo'] ? 'desactivar' : 'activar' ?>">
                                                <button type="submit" class="btn btn-sm <?= $empleado['Activo'] ? 'btn-outline-warning' : 'btn-outline-success' ?>">
                                                    <i class="fas <?= $empleado['Activo'] ? 'fa-user-slash' : 'fa-user-check' ?>"></i>
                                                </button>
                                            </form>
                                        <?php else: ?>
                                            <button class="btn btn-sm btn-secondary" disabled title="Admin principal">
                                                <i class="fas fa-shield-alt"></i>
                                            </button>
                                        <?php endif; ?>
                                    </div>
                                </td>
                            </tr>
                            <?php endwhile; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>


</html><?php include('../../includes/footer.php');
