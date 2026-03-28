<?php
define('BASE_URL', '/posada/');
require_once('../../../config/database.php');
include('../../../includes/header.php');

// Procesar el formulario de agregar mesa
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $numero = $_POST['numero'];
    $capacidad = $_POST['capacidad'];
    $ubicacion = $_POST['ubicacion'];
    $estado = $_POST['estado'];
    
    try {
        $query = "INSERT INTO MesasRestaurante (Numero, Capacidad, Ubicacion, Estado) 
                  VALUES (?, ?, ?, ?)";
        $stmt = $conn->prepare($query);
        $stmt->execute([$numero, $capacidad, $ubicacion, $estado]);
        
        $_SESSION['success'] = "Mesa agregada correctamente";
            echo "<script>window.location.href='listar.php?success=1';</script>";
        exit();
    } catch (PDOException $e) {
        if ($e->getCode() == 23000) {
            $error = "El número de mesa ya existe";
        } else {
            $error = "Error al agregar la mesa: " . $e->getMessage();
        }
    }
}
?>

<div class="container">
    <h2>Agregar Nueva Mesa</h2>
    
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
                    <input type="text" class="form-control" id="numero" name="numero" required>
                </div>
                
                <div class="mb-3">
                    <label for="capacidad" class="form-label">Capacidad</label>
                    <input type="number" class="form-control" id="capacidad" name="capacidad" min="1" value="4" required>
                </div>
                
                <div class="mb-3">
                    <label for="ubicacion" class="form-label">Ubicación</label>
                    <input type="text" class="form-control" id="ubicacion" name="ubicacion">
                </div>
                
                <div class="mb-3">
                    <label for="estado" class="form-label">Estado</label>
                    <select class="form-select" id="estado" name="estado" required>
                        <option value="Disponible">Disponible</option>
                        <option value="Ocupada">Ocupada</option>
                        <option value="Reservada">Reservada</option>
                        <option value="Mantenimiento">Mantenimiento</option>
                    </select>
                </div>
                
                <div class="d-flex justify-content-between">
                    <a href="listar.php" class="btn btn-secondary">
                        <i class="fas fa-arrow-left"></i> Cancelar
                    </a>
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save"></i> Guardar Mesa
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<?php include('../../../includes/footer.php'); ?>