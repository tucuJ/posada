<?php
require_once('../../../config/database.php');
include('../../../includes/header.php');

if ($_SESSION['rol'] != 'Admin') {
    header("Location: ../dashboard.php");
    exit();
}

$empleado_id = $_GET['empleado_id'] ?? null;

if (!$empleado_id) {
    $_SESSION['mensaje'] = [
        'tipo' => 'danger',
        'texto' => 'No se especificó un empleado'
    ];
    header("Location: ../empleados/listar.php");
    exit();
}

$stmt_empleado = $conn->prepare("SELECT * FROM Empleados WHERE EmpleadoID = ?");
$stmt_empleado->execute([$empleado_id]);
$empleado = $stmt_empleado->fetch(PDO::FETCH_ASSOC);

if (!$empleado) {
    $_SESSION['mensaje'] = [
        'tipo' => 'danger',
        'texto' => 'Empleado no encontrado'
    ];
    header("Location: ../empleados/listar.php");
    exit();
}

// Obtener usuarios y contar cuántos tiene el empleado
$query = "SELECT * FROM Usuarios WHERE EmpleadoID = ? ORDER BY Activo DESC, Rol";
$stmt = $conn->prepare($query);
$stmt->execute([$empleado_id]);
$usuarios = $stmt->fetchAll(PDO::FETCH_ASSOC);

$cantidad_usuarios = count($usuarios);
$puede_agregar = ($cantidad_usuarios < 2) || ($_SESSION['rol'] == 'Admin');
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Usuarios de <?= $empleado['Nombre'] . ' ' . $empleado['Apellido'] ?></title>
    <style>
        .max-users-alert {
            background-color: #fff3cd;
            border-left: 4px solid #ffc107;
            padding: 10px;
            margin-bottom: 20px;
        }
    </style>
</head>
<body>
    <div class="container mt-4">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h2>Usuarios de: <?= $empleado['Nombre'] . ' ' . $empleado['Apellido'] ?></h2>
            <div>
                <a href="../listar.php" class="btn btn-secondary">Volver a Empleados</a>
                <?php if ($cantidad_usuarios <= 2): ?>
                    <a href="agregar.php?empleado_id=<?= $empleado_id ?>" class="btn btn-primary">Nuevo Usuario</a>
                <?php endif; ?>
            </div>
        </div>
        
        <?php if ($cantidad_usuarios >= 2): ?>
            <div class="max-users-alert">
                <i class="fas fa-info-circle"></i> Este empleado ya tiene el máximo de 2 usuarios permitidos.
            </div>
        <?php endif; ?>
        
        <?php if (isset($_SESSION['mensaje'])): ?>
            <div class="alert alert-<?= $_SESSION['mensaje']['tipo'] ?>"><?= $_SESSION['mensaje']['texto'] ?></div>
            <?php unset($_SESSION['mensaje']); ?>
        <?php endif; ?>
        
        <table class="table table-striped">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Usuario</th>
                    <th>Rol</th>
                    <th>Estado</th>
                    <th>Acciones</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($usuarios)): ?>
                    <tr>
                        <td colspan="5" class="text-center">No hay usuarios registrados para este empleado</td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($usuarios as $usuario): ?>
                    <tr>
                        <td><?= $usuario['UsuarioID'] ?></td>
                        <td><?= $usuario['NombreUsuario'] ?></td>
                        <td>
                            <span class="badge bg-<?= $usuario['Rol'] == 'Admin' ? 'primary' : 'info' ?>">
                                <?= $usuario['Rol'] ?>
                            </span>
                        </td>
                        <td>
                            <span class="badge bg-<?= $usuario['Activo'] ? 'success' : 'secondary' ?>">
                                <?= $usuario['Activo'] ? 'Activo' : 'Inactivo' ?>
                            </span>
                        </td>
                        <td>
                            <a href="editar.php?id=<?= $usuario['UsuarioID'] ?>&empleado_id=<?= $empleado_id ?>" class="btn btn-sm btn-warning">Editar</a>
                            <form action="cambiar_estado.php" method="post" style="display:inline;">
                                <input type="hidden" name="usuario_id" value="<?= $usuario['UsuarioID'] ?>">
                                <input type="hidden" name="empleado_id" value="<?= $empleado_id ?>">
                                <input type="hidden" name="nuevo_estado" value="<?= $usuario['Activo'] ? '0' : '1' ?>">
                                <button type="submit" class="btn btn-sm btn-<?= $usuario['Activo'] ? 'danger' : 'success' ?>">
                                    <?= $usuario['Activo'] ? 'Desactivar' : 'Activar' ?>
                                </button>
                            </form>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</body>
</html>
<?php include('../../../includes/footer.php'); ?>