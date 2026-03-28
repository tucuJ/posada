<?php 
define('BASE_URL', '/posada/');
require_once('../../../config/database.php');
include('../../../includes/header.php');
?>

<div class="container">
    <h2>Agregar Tipo de Habitación</h2>
    
    <form action="procesar.php" method="post">
        <div class="row">
            <div class="col-md-6">
                <div class="form-group mb-3">
                    <label for="nombre" class="form-label">Nombre del Tipo</label>
                    <input type="text" class="form-control" id="nombre" name="nombre" required>
                </div>
                
                <div class="form-group mb-3">
                    <label for="capacidad" class="form-label">Capacidad (personas)</label>
                    <input type="number" class="form-control" id="capacidad" name="capacidad" min="1" max="10" value="2" required>
                </div>
            </div>
            
            <div class="col-md-6">
                <div class="form-group mb-3">
                    <label for="precio" class="form-label">Precio por Noche</label>
                    <input type="number" step="0.01" min="0" class="form-control" id="precio" name="precio" required>
                </div>
                
                <div class="form-group mb-3">
                    <label for="descripcion" class="form-label">Descripción</label>
                    <textarea class="form-control" id="descripcion" name="descripcion" rows="2"></textarea>
                </div>
            </div>
        </div>
        
        <div class="d-flex justify-content-between mt-4">
            <button type="submit" class="btn btn-primary" name="agregar">
                <i class="fas fa-save"></i> Guardar Tipo
            </button>
            <a href="listar.php" class="btn btn-secondary">
                <i class="fas fa-times"></i> Cancelar
            </a>
        </div>
    </form>
</div>

<?php include('../../../includes/footer.php'); ?>