<?php
require_once '../../../config/database.php';
require_once '../../../includes/header.php';

// Verificar permisos del usuario
if (!($_SESSION['rol'] === 'Admin' || $_SESSION['rol'] === 'Gerente' || $_SESSION['rol'] === 'Restaurante' || $_SESSION['rol'] === 'Bodega')) {
    header('Location: /dashboard.php');
    exit;
}

$title = 'Restaurante - Agregar Ingrediente';
$error = '';

// Procesar el formulario
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nombre = trim(string: $_POST['nombre']);
    $cantidad = trim(string: $_POST['cantidad']);
    $precio = trim(string: $_POST['precio']);
    $descripcion = trim($_POST['descripcion']);
    $unidad = trim($_POST['unidad']);
    $stock = isset($_POST['stock']) ? (float)$_POST['stock'] : 0;
    $minimo = isset($_POST['minimo']) ? (float)$_POST['minimo'] : 1;
    $activo = isset($_POST['activo']) ? 1 : 0;

    // Validaciones
    if (empty($nombre)) {
        $error = 'El nombre del ingrediente es requerido.';
    } elseif (!is_numeric($stock) || $stock < 0) {
        $error = 'El stock debe ser un número positivo.';
    } elseif (!is_numeric($minimo) || $minimo < 0) {
        $error = 'El stock mínimo debe ser un número positivo.';
    } else {
        try {
            // Verificar si el ingrediente ya existe
            $stmt = $pdo->prepare("SELECT COUNT(*) FROM Ingredientes WHERE Nombre = ?");
            $stmt->execute([$nombre]);
            $existe = $stmt->fetchColumn();

            if ($existe) {
                $error = 'Ya existe un ingrediente con ese nombre.';
            } else {
                // Insertar nuevo ingrediente
                $stmt = $pdo->prepare("INSERT INTO Ingredientes (Nombre, Descripcion, UnidadMedida, Stock, StockMinimo, Activo, precio, cantidad) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
                $stmt->execute([$nombre, $descripcion, $unidad, $stock, $minimo, $activo, $precio, $cantidad]);
    echo "<script>window.location.href='listar.php?success=1';</script>";

                exit;
            }
        } catch (PDOException $e) {
            $error = 'Error al agregar el ingrediente: ' . $e->getMessage();
        }
    }
}
?>

<div class="container mt-4">
    <h1 class="mb-4"><?= $title ?></h1>
    
    <div class="card">
        <div class="card-header">
            <i class="fas fa-plus"></i> Nuevo Ingrediente
        </div>
        <div class="card-body">
            <?php if ($error): ?>
                <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
            <?php endif; ?>
            
            <form method="post">
                <div class="mb-3">
                    <label for="nombre" class="form-label">Nombre *</label>
                    <input type="text" class="form-control" id="nombre" name="nombre" required 
                           value="<?= isset($_POST['nombre']) ? htmlspecialchars($_POST['nombre']) : '' ?>">
                </div>
                
                <div class="mb-3">
                    <label for="descripcion" class="form-label">Descripción</label>
                    <textarea class="form-control" id="descripcion" name="descripcion" rows="2"><?= 
                        isset($_POST['descripcion']) ? htmlspecialchars($_POST['descripcion']) : '' ?></textarea>
                </div>
                
                <div class="row mb-3">
                    <div class="col-md-4">
                        <label for="unidad" class="form-label">Unidad de Medida *</label>
                        <select class="form-select" id="unidad" name="unidad" required>
                            <option value="">Seleccionar...</option>
                            <option value="kg" <?= isset($_POST['unidad']) && $_POST['unidad'] === 'kg' ? 'selected' : '' ?>>Kilogramos (kg)</option>
                            <option value="g" <?= isset($_POST['unidad']) && $_POST['unidad'] === 'g' ? 'selected' : '' ?>>Gramos (g)</option>
                            <option value="l" <?= isset($_POST['unidad']) && $_POST['unidad'] === 'l' ? 'selected' : '' ?>>Litros (l)</option>
                            <option value="ml" <?= isset($_POST['unidad']) && $_POST['unidad'] === 'ml' ? 'selected' : '' ?>>Mililitros (ml)</option>
                            <option value="unidad" <?= isset($_POST['unidad']) && $_POST['unidad'] === 'unidad' ? 'selected' : '' ?>>Unidades</option>
                        </select>
                    </div>
                    
                    <div class="col-md-4">
                        <label for="stock" class="form-label">Stock Inicial</label>
                        <input type="number" step="0.001" class="form-control" id="stock" name="stock" 
                               value="<?= isset($_POST['stock']) ? htmlspecialchars($_POST['stock']) : '0' ?>">
                    </div>
                    
                    <div class="col-md-4">
                        <label for="minimo" class="form-label">Stock Mínimo</label>
                        <input type="number" step="0.001" class="form-control" id="minimo" name="minimo" 
                               value="<?= isset($_POST['minimo']) ? htmlspecialchars($_POST['minimo']) : '1' ?>">
                    </div>
                </div>
                <div class="form-group">
                    <label for="precioCompra">Precio de Compra X Unidad</label>
                    <div class="input-group">
                        <div class="input-group-prepend">
                            <span class="input-group-text">$</span>
                        </div>
                        <input type="number" step="0.01" min="0" class="form-control" id="precio" name="precio" required>
                    </div>
                </div>
                
                <div class="form-group">
                    <label for="stock">Unidad x Precio</label>
                    <input type="number" class="form-control" id="cantidad" name="cantidad" min="1" required>
                </div>


                <div class="mb-3 form-check">
                    <input type="checkbox" class="form-check-input" id="activo" name="activo" checked>
                    <label class="form-check-label" for="activo">Activo</label>
                </div>
                
                <div class="d-grid gap-2 d-md-flex justify-content-md-end">
                    <a href="listar.php" class="btn btn-secondary me-md-2">
                        <i class="fas fa-arrow-left"></i> Cancelar
                    </a>
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save"></i> Guardar
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<?php require_once '../../../includes/footer.php'; ?>