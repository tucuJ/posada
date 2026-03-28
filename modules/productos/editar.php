<?php 
define('BASE_URL', '/posada/');
require_once('../../config/database.php');
include('../../includes/header.php');

// Verificar si se recibió un ID válido
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    header("Location: listar.php?error=3");
    exit();
}

$producto_id = intval($_GET['id']);

// Obtener los datos del producto
try {
    $query = "SELECT * FROM Productos WHERE ProductoID = ?";
    $stmt = $conn->prepare($query);
    $stmt->execute([$producto_id]);
    $producto = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$producto) {
        header("Location: listar.php?error=2");
        exit();
    }
} catch(PDOException $e) {
    header("Location: listar.php?error=1");
    exit();
}

// Obtener categorías y proveedores activos
try {
    $query = "SELECT CategoriaID, Nombre FROM CategoriasProductos WHERE Activo = 1";
    $categorias = $conn->query($query)->fetchAll(PDO::FETCH_ASSOC);
    
    $query = "SELECT ProveedorID, Nombre FROM Proveedores WHERE Activo = 1";
    $proveedores = $conn->query($query)->fetchAll(PDO::FETCH_ASSOC);
} catch(PDOException $e) {
    $categorias = $proveedores = [];
}
?>

<div class="container">
    <h2 class="my-4">Editar Producto</h2>
    
    <?php if(isset($_GET['error'])): ?>
        <div class="alert alert-danger">
            Error al actualizar el producto. Por favor intente nuevamente.
        </div>
    <?php endif; ?>
    
    <form id="formProducto" action="procesar.php" method="post">
        <input type="hidden" name="id" value="<?= htmlspecialchars($producto['ProductoID']) ?>">
        
        <div class="row">
            <div class="col-md-6">
                <div class="form-group">
                    <label for="codigo">Código de Barras</label>
                    <input type="text" class="form-control" id="codigo" name="codigo" 
                           value="<?= htmlspecialchars($producto['CodigoBarras']) ?>" required>
                </div>
                
                <div class="form-group">
                    <label for="nombre">Nombre del Producto</label>
                    <input type="text" class="form-control" id="nombre" name="nombre" 
                           value="<?= htmlspecialchars($producto['Nombre']) ?>" required>
                </div>
                
                <div class="form-group">
                    <label for="categoria">Categoría</label>
                    <select class="form-control" id="categoria" name="categoria" required>
                        <option value="">Seleccione una categoría</option>
                        <?php foreach ($categorias as $cat): ?>
                            <option value="<?= htmlspecialchars($cat['CategoriaID']) ?>" 
                                <?= ($cat['CategoriaID'] == $producto['CategoriaID']) ? 'selected' : '' ?>>
                                <?= htmlspecialchars($cat['Nombre']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                
                <div class="form-group">
                    <label for="proveedor">Proveedor</label>
                    <select class="form-control" id="proveedor" name="proveedor">
                        <option value="">Seleccione un proveedor</option>
                        <?php foreach ($proveedores as $prov): ?>
                            <option value="<?= htmlspecialchars($prov['ProveedorID']) ?>" 
                                <?= ($prov['ProveedorID'] == $producto['ProveedorID']) ? 'selected' : '' ?>>
                                <?= htmlspecialchars($prov['Nombre']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                
                <div class="form-group">
                    <label for="descripcion">Descripción</label>
                    <textarea class="form-control" id="descripcion" name="descripcion" rows="3"><?= htmlspecialchars($producto['Descripcion']) ?></textarea>
                </div>
            </div>
            
            <div class="col-md-6">
                <div class="form-group">
                    <label for="precioCompra">Precio de Compra</label>
                    <div class="input-group">
                        <div class="input-group-prepend">
                            <span class="input-group-text">$</span>
                        </div>
                        <input type="number" step="0.01" min="0" class="form-control" id="precioCompra" 
                               name="precioCompra" value="<?= htmlspecialchars($producto['PrecioCompra']) ?>" required>
                    </div>
                </div>
                
                <div class="form-group">
                    <label for="precioVenta">Precio de Venta</label>
                    <div class="input-group">
                        <div class="input-group-prepend">
                            <span class="input-group-text">$</span>
                        </div>
                        <input type="number" step="0.01" min="0" class="form-control" id="precioVenta" 
                               name="precioVenta" value="<?= htmlspecialchars($producto['PrecioVenta']) ?>" required>
                    </div>
                </div>
                
                <div class="form-group">
                    <label for="stock">Stock Actual</label>
                    <input type="number" class="form-control" id="stock" name="stock" 
                           value="<?= htmlspecialchars($producto['Stock']) ?>" min="0" required>
                    <small class="form-text text-muted">Modificar este valor actualizará el inventario</small>
                </div>
                
                <div class="form-group">
                    <label for="stockMinimo">Stock Mínimo</label>
                    <input type="number" class="form-control" id="stockMinimo" name="stockMinimo" 
                           value="<?= htmlspecialchars($producto['StockMinimo']) ?>" min="0" required>
                </div>
                
                <div class="form-group">
                    <label for="unidadMedida">Unidad de Medida</label>
                    <select class="form-control" id="unidadMedida" name="unidadMedida">
                        <option value="Unidad" <?= ($producto['UnidadMedida'] == 'Unidad') ? 'selected' : '' ?>>Unidad</option>
                        <option value="Litro" <?= ($producto['UnidadMedida'] == 'Litro') ? 'selected' : '' ?>>Litro</option>
                        <option value="Kilogramo" <?= ($producto['UnidadMedida'] == 'Kilogramo') ? 'selected' : '' ?>>Kilogramo</option>
                        <option value="Gramo" <?= ($producto['UnidadMedida'] == 'Gramo') ? 'selected' : '' ?>>Gramo</option>
                        <option value="Paquete" <?= ($producto['UnidadMedida'] == 'Paquete') ? 'selected' : '' ?>>Paquete</option>
                        <option value="Caja" <?= ($producto['UnidadMedida'] == 'Caja') ? 'selected' : '' ?>>Caja</option>
                    </select>
                </div>
                
                <div class="form-group">
                    <label for="activo">Estado</label>
                    <select class="form-control" id="activo" name="activo">
                        <option value="1" <?= ($producto['Activo'] == 1) ? 'selected' : '' ?>>Activo</option>
                        <option value="0" <?= ($producto['Activo'] == 0) ? 'selected' : '' ?>>Inactivo</option>
                    </select>
                </div>
            </div>
        </div>
        
        <div class="form-group mt-4">
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