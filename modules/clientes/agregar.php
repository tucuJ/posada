<?php 
define('BASE_URL', '/posada/');
require_once('../../config/database.php');
include('../../includes/header.php');
?>

<div class="container">
    <h2>Agregar Nuevo Cliente</h2>
    
    <form action="procesar.php" method="post">
        <div class="row">
            <div class="col-md-6">
                <div class="form-group mb-3">
                    <label for="nombre" class="form-label">Nombre</label>
                    <input type="text" class="form-control" id="nombre" name="nombre" required>
                </div>
                
                <div class="form-group mb-3">
                    <label for="apellido" class="form-label">Apellido</label>
                    <input type="text" class="form-control" id="apellido" name="apellido" required>
                </div>
                
                <div class="form-group mb-3">
                    <label for="tipoDocumento" class="form-label">Tipo de Documento</label>
                    <select class="form-select" id="tipoDocumento" name="tipoDocumento" required>
                        <option value="CED">Cédula</option>
                        <option value="PAS">Pasaporte</option>
                        <option value="RUC">RUC</option>
                    </select>
                </div>
                
                <div class="form-group mb-3">
                    <label for="numeroDocumento" class="form-label">Número de Documento</label>
                    <input type="text" class="form-control" id="numeroDocumento" name="numeroDocumento" required>
                </div>
            </div>
            
            <div class="col-md-6">
                <div class="form-group mb-3">
                    <label for="telefono" class="form-label">Teléfono</label>
                    <input type="tel" class="form-control" id="telefono" name="telefono">
                </div>
                
                <div class="form-group mb-3">
                    <label for="email" class="form-label">Email</label>
                    <input type="email" class="form-control" id="email" name="email">
                </div>
                
                <div class="form-group mb-3">
                    <label for="direccion" class="form-label">Dirección</label>
                    <textarea class="form-control" id="direccion" name="direccion" rows="2"></textarea>
                </div>
            </div>
        </div>
        
        <div class="d-flex justify-content-between mt-3">
            <button type="submit" class="btn btn-primary" name="agregar">
                <i class="fas fa-save"></i> Guardar Cliente
            </button>
            <a href="listar.php" class="btn btn-secondary">
                <i class="fas fa-times"></i> Cancelar
            </a>
        </div>
    </form>
</div>

<?php include('../../includes/footer.php'); ?>