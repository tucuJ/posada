<?php
define('BASE_URL', '/posada/');
require_once('../../../config/database.php');
include('../../../includes/header.php');

if (!isset($_GET['id'])) {
    header("Location: listar.php");
    exit();
}

$mesaId = $_GET['id'];

// Obtener datos de la mesa
$query = "SELECT * FROM MesasRestaurante WHERE MesaID = ?";
$stmt = $conn->prepare($query);
$stmt->execute([$mesaId]);
$mesa = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$mesa) {
    header("Location: listar.php?error=1");
    exit();
}

// Procesar el formulario de edición
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $numero = $_POST['numero'];
    $capacidad = $_POST['capacidad'];
    $ubicacion = $_POST['ubicacion'];
    $estado = $_POST['estado'];
    
    try {
        $query = "UPDATE MesasRestaurante 
                  SET Numero = ?, Capacidad = ?, Ubicacion = ?, Estado = ?
                  WHERE MesaID = ?";
        $stmt = $conn->prepare($query);
        $stmt->execute([$numero, $capacidad, $ubicacion, $estado, $mesaId]);
        
        $_SESSION['success'] = "Mesa actualizada correctamente";
            echo "<script>window.location.href='listar.php';</script>";
        exit();
    } catch (PDOException $e) {
        if ($e->getCode() == 23000) {
            $error = "El número de mesa ya existe";
        } else {
            $error = "Error al actualizar la mesa: " . $e->getMessage();
        }
    }
}
?>

<div class="container">
    <h2>Editar Mesa #<?= htmlspecialchars($mesa['Numero']) ?></h2>
    
    <div class="card">
        <div class="card-header bg-primary text-white">
            <h5 class="mb-0">Datos de la Mesa</h5>
        </div>
        <div class="card-body">
            <?php if (isset($error)): ?>
                <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
            <?php endif; ?>
            
            <form method="POST">
                <div class="mb-3">
                    <label for="numero" class="form-label">Número de Mesa</label>
                    <input type="text" class="form-control" id="numero" name="numero" 
                           value="<?= htmlspecialchars($mesa['Numero']) ?>" required>
                </div>
                
                <div class="mb-3">
                    <label for="capacidad" class="form-label">Capacidad</label>
                    <input type="number" class="form-control" id="capacidad" name="capacidad" 
                           min="1" value="<?= htmlspecialchars($mesa['Capacidad']) ?>" required>
                </div>
                
                <div class="mb-3">
                    <label for="ubicacion" class="form-label">Ubicación</label>
                    <input type="text" class="form-control" id="ubicacion" name="ubicacion" 
                           value="<?= htmlspecialchars($mesa['Ubicacion']) ?>">
                </div>
                
                <div class="mb-3">
                    <label for="estado" class="form-label">Estado</label>
                    <select class="form-select" id="estado" name="estado" required>
                        <option value="Disponible" <?= $mesa['Estado'] == 'Disponible' ? 'selected' : '' ?>>Disponible</option>
                        <option value="Ocupada" <?= $mesa['Estado'] == 'Ocupada' ? 'selected' : '' ?>>Ocupada</option>
                        <option value="Reservada" <?= $mesa['Estado'] == 'Reservada' ? 'selected' : '' ?>>Reservada</option>
                        <option value="Mantenimiento" <?= $mesa['Estado'] == 'Mantenimiento' ? 'selected' : '' ?>>Mantenimiento</option>
                    </select>
                </div>
                
                <div class="d-flex justify-content-between">
                    <a href="listar.php" class="btn btn-secondary">
                        <i class="fas fa-arrow-left"></i> Cancelar
                    </a>
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save"></i> Guardar Cambios
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<?php include('../../../includes/footer.php'); ?>