<?php 
define('BASE_URL', '/posada/');
require_once('../../../config/database.php');
include('../../../includes/header.php');

if (!isset($_GET['id'])) {
    header("Location: listar.php");
    exit();
}

$tipo_id = $_GET['id'];

// Obtener datos del tipo
$query = "SELECT * FROM TiposHabitacion WHERE TipoHabitacionID = ?";
$stmt = $conn->prepare($query);
$stmt->execute([$tipo_id]);
$tipo = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$tipo) {
    header("Location: listar.php?error=2");
    exit();
}
?>

<div class="container">
    <h2>Editar Tipo de Habitación</h2>
    
    <form action="procesar.php" method="post">
        <input type="hidden" name="id" value="<?= $tipo_id ?>">
        
        <div class="row">
            <div class="col-md-6">
                <div class="form-group mb-3">
                    <label for="nombre" class="form-label">Nombre del Tipo</label>
                    <input type="text" class="form-control" id="nombre" name="nombre" 
                           value="<?= htmlspecialchars($tipo['Nombre']) ?>" required>
                </div>
                
                <div class="form-group mb-3">
                    <label for="capacidad" class="form-label">Capacidad (personas)</label>
                    <input type="number" class="form-control" id="capacidad" name="capacidad" 
                           min="1" max="10" value="<?= $tipo['Capacidad'] ?>" required>
                </div>
            </div>
            
            <div class="col-md-6">
                <div class="form-group mb-3">
                    <label for="precio" class="form-label">Precio por Noche</label>
                    <input type="number" step="0.01" min="0" class="form-control" id="precio" 
                           name="precio" value="<?= $tipo['PrecioNoche'] ?>" required>
                </div>
                
                <div class="form-group mb-3">
                    <label for="descripcion" class="form-label">Descripción</label>
                    <textarea class="form-control" id="descripcion" name="descripcion" rows="2"><?= htmlspecialchars($tipo['Descripcion']) ?></textarea>
                </div>
            </div>
        </div>
        
        <div class="d-flex justify-content-between mt-4">
            <button type="submit" class="btn btn-primary" name="editar">
                <i class="fas fa-save"></i> Guardar Cambios
            </button>
            <a href="listar.php" class="btn btn-secondary">
                <i class="fas fa-times"></i> Cancelar
            </a>
        </div>
    </form>
</div>

<?php include('../../../includes/footer.php'); ?>