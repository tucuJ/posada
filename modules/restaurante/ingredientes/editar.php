<?php
require_once '../../../config/database.php';
require_once '../../../includes/header.php';

// Verificar permisos del usuario
if (!($_SESSION['rol'] === 'Admin' || $_SESSION['rol'] === 'Gerente' || $_SESSION['rol'] === 'Restaurante' || $_SESSION['rol'] === 'Bodega')) {
    header('Location: /dashboard.php');
    exit;
}

$title = 'Restaurante - Editar Ingrediente';
$error = '';

// Obtener ID del ingrediente
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    header('Location: listar.php?error=ID no válido');
    exit;
}

$id = (int)$_GET['id'];

// Obtener datos del ingrediente
try {
    $stmt = $pdo->prepare("SELECT * FROM Ingredientes WHERE IngredienteID = ?");
    $stmt->execute([$id]);
    $ingrediente = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$ingrediente) {
        header('Location: listar.php?error=Ingrediente no encontrado');
        exit;
    }
} catch (PDOException $e) {
    header('Location: listar.php?error=Error al obtener el ingrediente');
    exit;
}

// Procesar el formulario
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nombre = trim($_POST['nombre']);
    $descripcion = trim($_POST['descripcion']);
    $unidad = trim($_POST['unidad']);
    $minimo = isset($_POST['minimo']) ? (float)$_POST['minimo'] : 1;
    $activo = isset($_POST['activo']) ? 1 : 0;
    $cantidad = trim(string: $_POST['cantidad']);
    $precio = trim(string: $_POST['precio']);
    // Solo permitir modificar el stock si es admin
    $stock = $ingrediente['Stock']; // Mantener el valor actual por defecto
    if ($_SESSION['rol'] === 'Admin') {
        $stock = isset($_POST['stock']) ? (float)$_POST['stock'] : $ingrediente['Stock'];
    }

    // Validaciones
    if (empty($nombre)) {
        $error = 'El nombre del ingrediente es requerido.';
    } elseif (!is_numeric($stock) || $stock < 0) {
        $error = 'El stock debe ser un número positivo.';
    } elseif (!is_numeric($minimo) || $minimo < 0) {
        $error = 'El stock mínimo debe ser un número positivo.';
    } else {
        try {
            // Verificar si el nombre ya existe en otro ingrediente
            $stmt = $pdo->prepare("SELECT COUNT(*) FROM Ingredientes WHERE Nombre = ? AND IngredienteID != ?");
            $stmt->execute([$nombre, $id]);
            $existe = $stmt->fetchColumn();

            if ($existe) {
                $error = 'Ya existe otro ingrediente con ese nombre.';
            } else {
                // Actualizar el ingrediente
                $stmt = $pdo->prepare("UPDATE Ingredientes SET Nombre = ?, Descripcion = ?, UnidadMedida = ?, Stock = ?, StockMinimo = ?, Activo = ?, precio = ? , cantidad = ? WHERE IngredienteID = ?");
                $stmt->execute([$nombre, $descripcion, $unidad, $stock, $minimo, $activo, $precio, $cantidad, $id]);

                // Registrar movimiento en el inventario si el stock cambió y es admin
                if ($_SESSION['rol'] === 'Admin' && $stock != $ingrediente['Stock']) {
                    $diferencia = $stock - $ingrediente['Stock'];
                    $tipo = $diferencia > 0 ? 'Entrada' : 'Salida';
                    
                    $stmt = $pdo->prepare("INSERT INTO IngredientesMovimientos (ProductoID, Tipo, Cantidad, Referencia, Notas, UsuarioID) 
                                          VALUES (?, ?, ?, 'Ajuste manual', 'Ajuste de stock por administrador', ?)");
                    $stmt->execute([$id, $tipo, abs($diferencia), $_SESSION['usuario_id']]);
                }

                echo "<script>window.location.href='listar.php?success=1';</script>";
                exit;
            }
        } catch (PDOException $e) {
            $error = 'Error al actualizar el ingrediente: ' . $e->getMessage();
        }
    }
}
?>

<div class="container mt-4">
    <h1 class="mb-4"><?= $title ?></h1>
    
    <div class="card">
        <div class="card-header">
            <i class="fas fa-edit"></i> Editar Ingrediente
        </div>
        <div class="card-body">
            <?php if ($error): ?>
                <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
            <?php endif; ?>
            
            <form method="post">
                <div class="mb-3">
                    <label for="nombre" class="form-label">Nombre *</label>
                    <input type="text" class="form-control" id="nombre" name="nombre" required 
                           value="<?= htmlspecialchars($ingrediente['Nombre']) ?>">
                </div>
                
                <div class="mb-3">
                    <label for="descripcion" class="form-label">Descripción</label>
                    <textarea class="form-control" id="descripcion" name="descripcion" rows="2"><?= 
                        htmlspecialchars($ingrediente['Descripcion']) ?></textarea>
                </div>
                
                <div class="row mb-3">
                    <div class="col-md-4">
                        <label for="unidad" class="form-label">Unidad de Medida *</label>
                        <select class="form-select" id="unidad" name="unidad" required>
                            <option value="">Seleccionar...</option>
                            <option value="kg" <?= $ingrediente['UnidadMedida'] === 'kg' ? 'selected' : '' ?>>Kilogramos (kg)</option>
                            <option value="g" <?= $ingrediente['UnidadMedida'] === 'g' ? 'selected' : '' ?>>Gramos (g)</option>
                            <option value="l" <?= $ingrediente['UnidadMedida'] === 'l' ? 'selected' : '' ?>>Litros (l)</option>
                            <option value="ml" <?= $ingrediente['UnidadMedida'] === 'ml' ? 'selected' : '' ?>>Mililitros (ml)</option>
                            <option value="unidad" <?= $ingrediente['UnidadMedida'] === 'unidad' ? 'selected' : '' ?>>Unidades</option>
                        </select>
                    </div>
                    
                    <div class="col-md-4">
                        <label for="stock" class="form-label">Stock Actual</label>
                        <?php if ($_SESSION['rol'] === 'Admin'): ?>
                            <input type="number" step="0.001" class="form-control" id="stock" name="stock" 
                                   value="<?= htmlspecialchars($ingrediente['Stock']) ?>">
                        <?php else: ?>
                            <input type="text" class="form-control" value="<?= number_format($ingrediente['Stock'], 3) ?>" readonly>
                        <?php endif; ?>
                    </div>
                    
                    <div class="col-md-4">
                        <label for="minimo" class="form-label">Stock Mínimo</label>
                        <input type="number" step="0.001" class="form-control" id="minimo" name="minimo" 
                               value="<?= htmlspecialchars($ingrediente['StockMinimo']) ?>">
                    </div>
                </div>
                
                <div class="form-group">
                    <label for="precioCompra">Precio de Compra X Unidad</label>
                    <div class="input-group">
                        <div class="input-group-prepend">
                            <span class="input-group-text">$</span>
                        </div>
                        <input type="number" step="0.01" min="0" class="form-control" id="precio" name="precio"  value="<?= htmlspecialchars($ingrediente['precio']) ?>" required>
                    </div>
                </div>
                
                <div class="form-group">
                    <label for="stock">Unidad x Precio</label>
                    <input type="number" class="form-control" id="cantidad" name="cantidad"  required 
                     value="<?= htmlspecialchars($ingrediente['cantidad']) ?>">"
                </div>

                <div class="mb-3 form-check">
                    <input type="checkbox" class="form-check-input" id="activo" name="activo" 
                        <?= $ingrediente['Activo'] ? 'checked' : '' ?>>
                    <label class="form-check-label" for="activo">Activo</label>
                </div>
                
                <div class="d-grid gap-2 d-md-flex justify-content-md-end">
                    <a href="listar.php" class="btn btn-secondary me-md-2">
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

<?php require_once '../../../includes/footer.php'; ?>