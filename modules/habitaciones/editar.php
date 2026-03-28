<?php 
define('BASE_URL', '/posada/');
require_once('../../config/database.php');
include('../../includes/header.php');

if (!isset($_GET['id'])) {
    header("Location: listar.php");
    exit();
}

$habitacion_id = $_GET['id'];

// Obtener datos de la habitación
$query = "SELECT h.*, t.Nombre as TipoNombre 
          FROM Habitaciones h
          JOIN TiposHabitacion t ON h.TipoHabitacionID = t.TipoHabitacionID
          WHERE h.HabitacionID = ?";
$stmt = $conn->prepare($query);
$stmt->execute([$habitacion_id]);
$habitacion = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$habitacion) {
    header("Location: listar.php?error=2");
    exit();
}

// Obtener tipos de habitación
$query = "SELECT * FROM TiposHabitacion ORDER BY Nombre";
$stmt = $conn->query($query);
$tipos = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<div class="container">
    <h2>Editar Habitación <?= $habitacion['Numero'] ?></h2>
    
    <form action="procesar.php" method="post">
        <input type="hidden" name="id" value="<?= $habitacion_id ?>">
        
        <div class="row">
            <div class="col-md-6">
                <div class="form-group mb-3">
                    <label for="numero" class="form-label">Número de Habitación</label>
                    <input type="text" class="form-control" id="numero" name="numero" 
                           value="<?= htmlspecialchars($habitacion['Numero']) ?>" required>
                </div>
                
                <div class="form-group mb-3">
                    <label for="tipo" class="form-label">Tipo de Habitación</label>
                    <select class="form-select" id="tipo" name="tipo" required>
                        <option value="">Seleccione un tipo</option>
                        <?php foreach ($tipos as $tipo): ?>
                            <option value="<?= $tipo['TipoHabitacionID'] ?>" 
                                <?= ($tipo['TipoHabitacionID'] == $habitacion['TipoHabitacionID']) ? 'selected' : '' ?>>
                                <?= $tipo['Nombre'] ?> ($<?= number_format($tipo['PrecioNoche'], 2) ?>)
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>
            
            <div class="col-md-6">
                <div class="form-group mb-3">
                    <label for="estado" class="form-label">Estado Actual</label>
                    <select class="form-select" id="estado" name="estado" required>
                        <option value="Disponible" <?= ($habitacion['Estado'] == 'Disponible') ? 'selected' : '' ?>>Disponible</option>
                
                        <option value="Mantenimiento" <?= ($habitacion['Estado'] == 'Mantenimiento') ? 'selected' : '' ?>>Mantenimiento</option>
                    </select>
                </div>            
                
                <div class="mb-3">
                    <label for="costom" class="form-label">Costo de Mantenimiento por día</label>
                    <div class="input-group">
                        <span class="input-group-text">$</span>
                        <input type="number" min="0" step="0.01" class="form-control" 
                               id="costom" name="costom"  value="<?= htmlspecialchars($habitacion['costom']) ?>">
                    </div>
                </div>
                
                <div class="form-group ">
                    <label for="notas" class="form-label">Notas Adicionales</label>
                    <textarea class="form-control" id="notas" name="notas" rows="2"><?= htmlspecialchars($habitacion['Notas']) ?></textarea>
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

<?php include('../../includes/footer.php'); ?>