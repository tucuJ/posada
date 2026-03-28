<?php
require_once('../../../config/database.php');
include('../../../includes/header.php');

if ($_SESSION['rol'] != 'Admin') {
    header("Location: ../dashboard.php");
    exit();
}

$empleado_id = $_GET['empleado_id'] ?? null;

if (!$empleado_id) {
    header("Location: ../empleados/listar.php");
    exit();
}

$stmt_empleado = $conn->prepare("SELECT * FROM Empleados WHERE EmpleadoID = ?");
$stmt_empleado->execute([$empleado_id]);
$empleado = $stmt_empleado->fetch(PDO::FETCH_ASSOC);

if (!$empleado) {
    header("Location: ../empleados/listar.php");
    exit();
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Agregar Usuario</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    <div class="container mt-4">
        <h2>Agregar Usuario para <?= $empleado['Nombre'] . ' ' . $empleado['Apellido'] ?></h2>
        
        <?php if (isset($_SESSION['mensaje'])): ?>
            <div class="alert alert-<?= $_SESSION['mensaje']['tipo'] ?>"><?= $_SESSION['mensaje']['texto'] ?></div>
            <?php unset($_SESSION['mensaje']); ?>
        <?php endif; ?>
        
        <form action="guardar.php" method="post">
            <input type="hidden" name="empleado_id" value="<?= $empleado_id ?>">
            
            <div class="mb-3">
                <label class="form-label">Nombre de usuario</label>
                <input type="text" class="form-control" name="NombreUsuario" required>
            </div>
            
            <div class="mb-3">
                <label class="form-label">Contraseña</label>
                <input type="password" class="form-control" name="contrasena" required>
            </div>
            
            <div class="mb-3">
                <label class="form-label">Rol</label>
                <select class="form-select" name="rol" required>
                    <option value="Empleado">Empleado</option>
                    <option value="Admin">Administrador</option>
                </select>
            </div>
            
            <div class="mb-3">
                <label class="form-label">Estado</label>
                <select class="form-select" name="activo" required>
                    <option value="1">Activo</option>
                    <option value="0">Inactivo</option>
                </select>
            </div>
            
            <button type="submit" class="btn btn-primary">Guardar</button>
            <a href="listar.php?empleado_id=<?= $empleado_id ?>" class="btn btn-secondary">Cancelar</a>
        </form>
    </div>
</body>
</html>
<?php include('../../../includes/footer.php'); ?>