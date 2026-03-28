<?php 
define('BASE_URL', '/posada/');
require_once('../../config/database.php');
include('../../includes/header.php');

// Verificar permisos
if (!($_SESSION['rol'] === 'Admin' || $_SESSION['rol'] === 'Recepcion')) {
    header('Location: /dashboard.php');
    exit;
}

// Obtener tipos de habitación
$query = "SELECT * FROM TiposHabitacion  ORDER BY Nombre";
$stmt = $conn->query($query);
$tipos = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Manejar mensajes de error
$error = '';
if (isset($_GET['error'])) {
    switch ($_GET['error']) {
        case '1': $error = 'El número de habitación ya existe'; break;
        case '2': $error = 'Error al guardar la habitación'; break;
        default: $error = 'Ocurrió un error';
    }
}
?>

<div class="container">
    <h2 class="mb-4"><i class="fas fa-door-open"></i> Agregar Nueva Habitación</h2>
    
    <?php if ($error): ?>
        <div class="alert alert-danger alert-dismissible fade show">
            <?= $error ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    <?php endif; ?>
    
    <form action="procesar.php" method="post" class="needs-validation" novalidate>
        <div class="row">
            <div class="col-md-6">
                <div class="mb-3">
                    <label for="numero" class="form-label">Número de Habitación *</label>
                    <input type="text" class="form-control" id="numero" name="numero" 
                           pattern="[A-Za-z0-9-]+" title="Solo letras, números y guiones" required>
                    <div class="invalid-feedback">Por favor ingrese un número de habitación válido</div>
                </div>
                
                <div class="mb-3">
                    <label for="tipo" class="form-label">Tipo de Habitación *</label>
                    <select class="form-select" id="tipo" name="tipo" required>
                        <option value="">Seleccione un tipo</option>
                        <?php foreach ($tipos as $tipo): ?>
                            <option value="<?= $tipo['TipoHabitacionID'] ?>">
                                <?= htmlspecialchars($tipo['Nombre']) ?> 
                                ($<?= number_format($tipo['PrecioNoche'], 2) ?>)
                            </option>
                        <?php endforeach; ?>
                    </select>
                    <div class="invalid-feedback">Seleccione un tipo de habitación</div>
                </div>
            </div>
            
            <div class="col-md-6">
                <div class="mb-3">
                    <label for="estado" class="form-label">Estado Inicial *</label>
                    <select class="form-select" id="estado" name="estado" required>
                        <option value="Disponible">Disponible</option>
                        <option value="Mantenimiento">Mantenimiento</option>
                        <option value="Ocupada">Ocupada</option>
                    </select>
                </div>
                
                <div class="mb-3">
                    <label for="costom" class="form-label">Costo de Mantenimiento por día</label>
                    <div class="input-group">
                        <span class="input-group-text">$</span>
                        <input type="number" min="0" step="0.01" class="form-control" 
                               id="costom" name="costom" value="0">
                    </div>
                </div>
            </div>
        </div>
        
        <div class="mb-3">
            <label for="notas" class="form-label">Notas Adicionales</label>
            <textarea class="form-control" id="notas" name="notas" rows="3" 
                      placeholder="Detalles especiales de la habitación..."></textarea>
        </div>
        
        <div class="d-flex justify-content-between mt-4">
            <button type="submit" class="btn btn-primary" name="agregar">
                <i class="fas fa-save"></i> Guardar Habitación
            </button>
            <a href="listar.php" class="btn btn-secondary">
                <i class="fas fa-times"></i> Cancelar
            </a>
        </div>
    </form>
</div>

<?php include('../../includes/footer.php'); ?>

<script>
// Validación del formulario
(function() {
    'use strict';
    window.addEventListener('load', function() {
        var forms = document.getElementsByClassName('needs-validation');
        Array.prototype.filter.call(forms, function(form) {
            form.addEventListener('submit', function(event) {
                if (form.checkValidity() === false) {
                    event.preventDefault();
                    event.stopPropagation();
                }
                form.classList.add('was-validated');
            }, false);
        });
    }, false);
})();
</script>