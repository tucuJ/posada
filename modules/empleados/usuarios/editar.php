<?php
session_start();
require_once('../../../config/database.php');
include('../../../includes/header.php');

if ($_SESSION['rol'] != 'Admin') {
    header("Location: ../dashboard.php");
    exit();
}

$usuario_id = $_GET['id'] ?? null;
$empleado_id = $_GET['empleado_id'] ?? null;

if (!$usuario_id || !$empleado_id) {
    header("Location: ../empleados/listar.php");
    exit();
}

$query = "SELECT * FROM Usuarios WHERE UsuarioID = ?";
$stmt = $conn->prepare($query);
$stmt->execute([$usuario_id]);
$usuario = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$usuario) {
    header("Location: listar.php?empleado_id=" . $empleado_id);
    exit();
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Editar Usuario</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    <div class="container mt-4">
        <h2>Editar Usuario</h2>
        
        <?php if (isset($_SESSION['mensaje'])): ?>
            <div class="alert alert-<?= $_SESSION['mensaje']['tipo'] ?>"><?= $_SESSION['mensaje']['texto'] ?></div>
            <?php unset($_SESSION['mensaje']); ?>
        <?php endif; ?>
        
        <form action="actualizar.php" method="post">
            <input type="hidden" name="usuario_id" value="<?= $usuario['UsuarioID'] ?>">
            <input type="hidden" name="empleado_id" value="<?= $empleado_id ?>">
            
            <div class="mb-3">
                <label class="form-label">Nombre de usuario</label>
                <input type="text" class="form-control" name="NombreUsuario" value="<?= $usuario['NombreUsuario'] ?>" required>
            </div>
            
            <div class="mb-3">
                <label class="form-label">Nueva contraseña (dejar en blanco para no cambiar)</label>
                <input type="password" class="form-control" name="contrasena">
            </div>
            
            <div class="mb-3">
                <label class="form-label">Rol</label>
                <select class="form-select" name="rol" required>
                    <option value="Empleado" <?= $usuario['Rol'] == 'Empleado' ? 'selected' : '' ?>>Empleado</option>
                    <option value="Admin" <?= $usuario['Rol'] == 'Admin' ? 'selected' : '' ?>>Administrador</option>
                </select>
            </div>
            
            <div class="mb-3">
                <label class="form-label">Estado</label>
                <select class="form-select" name="activo" required>
                    <option value="1" <?= $usuario['Activo'] ? 'selected' : '' ?>>Activo</option>
                    <option value="0" <?= !$usuario['Activo'] ? 'selected' : '' ?>>Inactivo</option>
                </select>
            </div>
            
            <button type="submit" class="btn btn-primary">Guardar Cambios</button>
            <a href="listar.php?empleado_id=<?= $empleado_id ?>" class="btn btn-secondary">Cancelar</a>
        </form>
    </div>
</body>
</html>
<?php include('../../../includes/footer.php'); ?>