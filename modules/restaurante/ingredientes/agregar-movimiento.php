<?php
require_once '../../../config/database.php';
require_once '../../../includes/header.php';

// Verificar permisos del usuario
if (!($_SESSION['rol'] === 'Admin' || $_SESSION['rol'] === 'Gerente' || $_SESSION['rol'] === 'Restaurante' || $_SESSION['rol'] === 'Bodega')) {
    header('Location: /dashboard.php');
    exit;
}

$title = 'Restaurante - Agregar Movimiento de Ingrediente';
$error = '';

// Obtener todos los ingredientes activos
$query = "SELECT IngredienteID, Nombre, UnidadMedida FROM Ingredientes WHERE Activo = 1 ORDER BY Nombre";
$stmt = $pdo->query($query);
$ingredientes = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Procesar el formulario
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $ingredienteID = (int)$_POST['ingrediente'];
    $tipo = $_POST['tipo'];
    $cantidad = (float)$_POST['cantidad'];
    $referencia = trim($_POST['referencia']);
    $notas = trim($_POST['notas']);

    // Validaciones
    if (empty($ingredienteID)) {
        $error = 'Debe seleccionar un ingrediente.';
    } elseif (!in_array($tipo, ['Entrada', 'Salida'])) {
        $error = 'Tipo de movimiento no válido.';
    } elseif (!is_numeric($cantidad) || $cantidad <= 0) {
        $error = 'La cantidad debe ser un número positivo.';
    } else {
        try {
            $pdo->beginTransaction();

            // Insertar el movimiento
            $stmt = $pdo->prepare("INSERT INTO IngredientesMovimientos (ingredientesID, Tipo, Cantidad, Referencia, Notas, UsuarioID) 
                                  VALUES (?, ?, ?, ?, ?, ?)");
            $stmt->execute([$ingredienteID, $tipo, $cantidad, $referencia, $notas, $_SESSION['usuario_id']]);

            // Actualizar el stock del ingrediente
            $operacion = $tipo === 'Entrada' ? '+' : '-';
            $stmt = $pdo->prepare("UPDATE Ingredientes SET Stock = Stock $operacion ? WHERE IngredienteID = ?");
            $stmt->execute([$cantidad, $ingredienteID]);

            $pdo->commit();
            echo "<script>window.location.href='movimientos.php?success=1';</script>";

            exit;
        } catch (PDOException $e) {
            $pdo->rollBack();
            $error = 'Error al registrar el movimiento: ' . $e->getMessage();
        }
    }
}
?>

<div class="container mt-4">
    <h1 class="mb-4"><?= $title ?></h1>
    
    <div class="card">
        <div class="card-header">
            <i class="fas fa-plus"></i> Nuevo Movimiento
        </div>
        <div class="card-body">
            <?php if ($error): ?>
                <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
            <?php endif; ?>
            
            <form method="post">
                <div class="row mb-3">
                    <div class="col-md-6">
                        <label for="ingrediente" class="form-label">Ingrediente *</label>
                        <select class="form-select" id="ingrediente" name="ingrediente" required>
                            <option value="">Seleccionar...</option>
                            <?php foreach ($ingredientes as $ing): ?>
                                <option value="<?= $ing['IngredienteID'] ?>" <?= isset($_POST['ingrediente']) && $_POST['ingrediente'] == $ing['IngredienteID'] ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($ing['Nombre']) ?> (<?= htmlspecialchars($ing['UnidadMedida']) ?>)
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    
                    <div class="col-md-6">
                        <label for="tipo" class="form-label">Tipo *</label>
                        <select class="form-select" id="tipo" name="tipo" required>
                            <option value="">Seleccionar...</option>
                            <option value="Entrada" <?= isset($_POST['tipo']) && $_POST['tipo'] === 'Entrada' ? 'selected' : '' ?>>Entrada</option>
                            <option value="Salida" <?= isset($_POST['tipo']) && $_POST['tipo'] === 'Salida' ? 'selected' : '' ?>>Salida</option>
                        </select>
                    </div>
                </div>
                
                <div class="row mb-3">
                    <div class="col-md-4">
                        <label for="cantidad" class="form-label">Cantidad *</label>
                        <input type="number"  class="form-control" id="cantidad" name="cantidad" required 
                               value="<?= isset($_POST['cantidad']) ? htmlspecialchars($_POST['cantidad']) : '' ?>">
                    </div>
                    
                    <div class="col-md-8">
                        <label for="referencia" class="form-label">Referencia</label>
                        <input type="text" class="form-control" id="referencia" name="referencia" 
                               value="<?= isset($_POST['referencia']) ? htmlspecialchars($_POST['referencia']) : '' ?>">
                    </div>
                </div>
                
                <div class="mb-3">
                    <label for="notas" class="form-label">Notas</label>
                    <textarea class="form-control" id="notas" name="notas" rows="2"><?= 
                        isset($_POST['notas']) ? htmlspecialchars($_POST['notas']) : '' ?></textarea>
                </div>
                
                <div class="d-grid gap-2 d-md-flex justify-content-md-end">
                    <a href="movimientos.php" class="btn btn-secondary me-md-2">
                        <i class="fas fa-arrow-left"></i> Cancelar
                    </a>
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save"></i> Guardar Movimiento
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<?php require_once '../../../includes/footer.php'; ?>