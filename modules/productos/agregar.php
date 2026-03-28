<?php 
define('BASE_URL', '/posada/');
require_once('../../config/database.php');
include('../../includes/header.php');

// Obtener categorías activas
try {
    $query = "SELECT CategoriaID, Nombre FROM CategoriasProductos WHERE Activo = 1";
    $categorias = $conn->query($query)->fetchAll(PDO::FETCH_ASSOC);
} catch(PDOException $e) {
    $categorias = [];
    // Podrías registrar el error en un log aquí
}

// Obtener proveedores activos
try {
    $query = "SELECT ProveedorID, Nombre FROM Proveedores WHERE Activo = 1";
    $proveedores = $conn->query($query)->fetchAll(PDO::FETCH_ASSOC);
} catch(PDOException $e) {
    $proveedores = [];
    // Podrías registrar el error en un log aquí
}
?>

<div class="container">
    <h2 class="my-4">Agregar Nuevo Producto</h2>
    
    <?php if(isset($_GET['error'])): ?>
        <div class="alert alert-danger">
            Error al guardar el producto. Por favor intente nuevamente.
        </div>
    <?php endif; ?>
    
    <form id="formProducto" action="procesar.php" method="post" enctype="multipart/form-data">
        <div class="row">
            <div class="col-md-6">
                <div class="form-group">
                    <label for="codigo">Código de Barras</label>
                    <input type="text" class="form-control" id="codigo" name="codigo" required>
                </div>
                
                <div class="form-group">
                    <label for="nombre">Nombre del Producto</label>
                    <input type="text" class="form-control" id="nombre" name="nombre" required>
                </div>
                
                <div class="form-group">
                    <label for="categoria">Categoría</label>
                    <select class="form-control" id="categoria" name="categoria" required>
                        <option value="">Seleccione una categoría</option>
                        <?php foreach ($categorias as $cat): ?>
                            <option value="<?= htmlspecialchars($cat['CategoriaID']) ?>">
                                <?= htmlspecialchars($cat['Nombre']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                
                <div class="form-group">
                    <label for="proveedor">Proveedor</label>
                    <div class="input-group">
                        <select class="form-control" id="proveedor" name="proveedor">
                            <option value="">Seleccione un proveedor</option>
                            <?php foreach ($proveedores as $prov): ?>
                                <option value="<?= htmlspecialchars($prov['ProveedorID']) ?>">
                                    <?= htmlspecialchars($prov['Nombre']) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                        
                    </div>
                </div>
                
                <div class="form-group">
                    <label for="descripcion">Descripción</label>
                    <textarea class="form-control" id="descripcion" name="descripcion" rows="3"></textarea>
                </div>
            </div>
            
            <div class="col-md-6">
                <div class="form-group">
                    <label for="precioCompra">Precio de Compra</label>
                    <div class="input-group">
                        <div class="input-group-prepend">
                            <span class="input-group-text">$</span>
                        </div>
                        <input type="number" step="0.01" min="0" class="form-control" id="precioCompra" name="precioCompra" required>
                    </div>
                </div>
                
                <div class="form-group">
                    <label for="precioVenta">Precio de Venta</label>
                    <div class="input-group">
                        <div class="input-group-prepend">
                            <span class="input-group-text">$</span>
                        </div>
                        <input type="number" step="0.01" min="0" class="form-control" id="precioVenta" name="precioVenta" required>
                    </div>
                </div>
                
                <div class="form-group">
                    <label for="stock">Stock Inicial</label>
                    <input type="number" class="form-control" id="stock" name="stock" min="0" required>
                </div>
                
                <div class="form-group">
                    <label for="stockMinimo">Stock Mínimo</label>
                    <input type="number" class="form-control" id="stockMinimo" name="stockMinimo" min="0" value="5" required>
                </div>
                
                <div class="form-group">
                    <label for="unidadMedida">Unidad de Medida</label>
                    <select class="form-control" id="unidadMedida" name="unidadMedida">
                        <option value="Unidad">Unidad</option>
                        <option value="Litro">Litro</option>
                        <option value="Kilogramo">Kilogramo</option>
                        <option value="Gramo">Gramo</option>
                        <option value="Paquete">Paquete</option>
                        <option value="Caja">Caja</option>
                    </select>
                </div>
            </div>
        </div>
        
        <div class="form-group mt-4">
            <button type="submit" class="btn btn-primary" name="agregar">
                <i class="fas fa-save"></i> Guardar Producto
            </button>
            <a href="listar.php" class="btn btn-secondary">
                <i class="fas fa-times"></i> Cancelar
            </a>
        </div>
    </form>
</div>

<?php include('../../includes/footer.php'); ?>