<?php
require_once '../../../config/database.php';
require_once '../../../includes/header.php';

// Verificar permisos del usuario
if (!($_SESSION['rol'] === 'Admin' || $_SESSION['rol'] === 'Gerente' || $_SESSION['rol'] === 'Restaurante')) {
    header('Location: /dashboard.php');
    exit;
}

$title = 'Restaurante - Receta del Platillo';
$error = '';

// Obtener ID del platillo
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    header('Location: listar.php?error=ID no válido');
    exit;
}

$id = (int)$_GET['id'];

// Obtener datos del platillo
try {
    $stmt = $pdo->prepare("SELECT * FROM Platillos WHERE PlatilloID = ?");
    $stmt->execute([$id]);
    $platillo = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$platillo) {
        header('Location: listar.php?error=Platillo no encontrado');
        exit;
    }
} catch (PDOException $e) {
    header('Location: listar.php?error=Error al obtener el platillo');
    exit;
}

// Obtener ingredientes activos para el select
$queryIngredientes = "SELECT * FROM Ingredientes WHERE Activo = 1 ORDER BY Nombre";
$stmtIngredientes = $pdo->query($queryIngredientes);
$ingredientes = $stmtIngredientes->fetchAll(PDO::FETCH_ASSOC);

// Obtener receta actual del platillo con información de costos
$queryReceta = "SELECT r.*, i.Nombre, i.UnidadMedida, i.Precio, i.Cantidad AS CantidadUnidad
                FROM Recetas r
                JOIN Ingredientes i ON r.IngredienteID = i.IngredienteID
                WHERE r.PlatilloID = ?
                ORDER BY i.Nombre";
$stmtReceta = $pdo->prepare($queryReceta);
$stmtReceta->execute([$id]);
$recetaActual = $stmtReceta->fetchAll(PDO::FETCH_ASSOC);

// Calcular costo total de la receta
$costoTotalReceta = 0;
foreach ($recetaActual as $ingrediente) {
    if ($ingrediente['Precio'] > 0 && $ingrediente['CantidadUnidad'] > 0) {
        // Calcular el costo proporcional del ingrediente en la receta
        $costoIngrediente = ($ingrediente['Precio'] / $ingrediente['CantidadUnidad']) * $ingrediente['Cantidad'];
        $costoTotalReceta += $costoIngrediente;
    }
}

// Manejar mensajes
if (isset($_GET['new'])) {
    $alertType = 'success';
    $alertMessage = 'Platillo creado correctamente. Ahora puedes agregar los ingredientes de la receta.';
} elseif (isset($_GET['success'])) {
    $alertType = 'success';
    $alertMessage = 'Receta actualizada correctamente.';
} elseif (isset($_GET['error'])) {
    $alertType = 'danger';
    $alertMessage = htmlspecialchars($_GET['error']);
}

// Procesar agregar ingrediente a la receta
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['agregar_ingrediente'])) {
    $ingredienteID = (int)$_POST['ingrediente'];
    $cantidad = (float)$_POST['cantidad'];
    $notas = trim($_POST['notas']);

    // Validaciones
    if (empty($ingredienteID)) {
        $error = 'Debe seleccionar un ingrediente.';
    } elseif (!is_numeric($cantidad) || $cantidad <= 0) {
        $error = 'La cantidad debe ser un número positivo.';
    } else {
        try {
            // Verificar si el ingrediente ya está en la receta
            $stmt = $pdo->prepare("SELECT COUNT(*) FROM Recetas WHERE PlatilloID = ? AND IngredienteID = ?");
            $stmt->execute([$id, $ingredienteID]);
            $existe = $stmt->fetchColumn();

            if ($existe) {
                $error = 'Este ingrediente ya está en la receta.';
            } else {
                // Agregar ingrediente a la receta
                $stmt = $pdo->prepare("INSERT INTO Recetas (PlatilloID, IngredienteID, Cantidad, Notas) VALUES (?, ?, ?, ?)");
                $stmt->execute([$id, $ingredienteID, $cantidad, $notas]);
                echo "<script>window.location.href='recetas.php?id=$id.&success=1';</script>";
                exit;
            }
        } catch (PDOException $e) {
            $error = 'Error al agregar el ingrediente: ' . $e->getMessage();
        }
    }
}

// Procesar eliminar ingrediente de la receta
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['eliminar_ingrediente'])) {
    $recetaID = (int)$_POST['receta_id'];

    try {
        $stmt = $pdo->prepare("DELETE FROM Recetas WHERE RecetaID = ?");
        $stmt->execute([$recetaID]);
        echo "<script>window.location.href='recetas.php?id=$id.&success=1';</script>";
        exit;
    } catch (PDOException $e) {
        $error = 'Error al eliminar el ingrediente: ' . $e->getMessage();
    }
}
?>

<div class="container mt-4">
    <h1 class="mb-4"><?= $title ?></h1>
    
    <!-- Mostrar alerta si hay mensajes -->
    <?php if (isset($alertMessage)): ?>
        <div class="alert alert-<?= $alertType ?> alert-dismissible fade show" role="alert">
            <?= $alertMessage ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    <?php endif; ?>
    
    <div class="card mb-4">
        <div class="card-header">
            <i class="fas fa-info-circle"></i> Información del Platillo
        </div>
        <div class="card-body">
            <div class="row">
                <div class="col-md-6">
                    <h5><?= htmlspecialchars($platillo['Nombre']) ?></h5>
                    <p><?= htmlspecialchars($platillo['Descripcion']) ?: 'Sin descripción' ?></p>
                    <div class="alert alert-info">
                        <strong>Costo estimado de preparación:</strong> $<?= number_format($costoTotalReceta, 2) ?>
                        <br>
                        <strong>Precio de venta:</strong> $<?= number_format($platillo['PrecioVenta'] ?? 0, 2) ?>
                        <?php if (isset($platillo['PrecioVenta']) && $costoTotalReceta > 0): ?>
                            <br>
                            <strong>Margen bruto:</strong> 
                            <?= number_format((($platillo['PrecioVenta'] - $costoTotalReceta) / $platillo['PrecioVenta']) * 100, 2) ?>%
                        <?php endif; ?>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="d-flex justify-content-end">
                        <a href="editar.php?id=<?= $id ?>" class="btn btn-outline-primary me-2">
                            <i class="fas fa-edit"></i> Editar Platillo
                        </a>
                        <a href="listar.php" class="btn btn-outline-secondary">
                            <i class="fas fa-arrow-left"></i> Volver
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <div class="card mb-4">
        <div class="card-header">
            <i class="fas fa-list"></i> Ingredientes de la Receta
        </div>
        <div class="card-body">
            <?php if ($error): ?>
                <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
            <?php endif; ?>
            
            <form method="post" class="mb-4">
                <div class="row g-3">
                    <div class="col-md-5">
                        <label for="ingrediente" class="form-label">Ingrediente *</label>
                        <select class="form-select" id="ingrediente" name="ingrediente" required>
                            <option value="">Seleccionar...</option>
                            <?php foreach ($ingredientes as $ing): ?>
                                <option value="<?= $ing['IngredienteID'] ?>" <?= isset($_POST['ingrediente']) && $_POST['ingrediente'] == $ing['IngredienteID'] ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($ing['Nombre']) ?> (<?= htmlspecialchars($ing['UnidadMedida']) ?>)
                                    <?php if ($ing['precio'] > 0): ?>
                                        - $<?= number_format($ing['precio'], 2) ?> por <?= $ing['cantidad'] ?> <?= $ing['UnidadMedida'] ?>
                                    <?php endif; ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    
                    <div class="col-md-3">
                        <label for="cantidad" class="form-label">Cantidad *</label>
                        <input type="number" step="0.1" class="form-control" id="cantidad" name="cantidad" required 
                               value="<?= isset($_POST['cantidad']) ? htmlspecialchars($_POST['cantidad']) : '' ?>">
                    </div>
                    
                    <div class="col-md-3">
                        <label for="notas" class="form-label">Notas</label>
                        <input type="text" class="form-control" id="notas" name="notas" 
                               value="<?= isset($_POST['notas']) ? htmlspecialchars($_POST['notas']) : '' ?>">
                    </div>
                    
                    <div class="col-md-1 d-flex align-items-end">
                        <button type="submit" name="agregar_ingrediente" class="btn btn-success">
                            <i class="fas fa-plus"></i> Agregar
                        </button>
                    </div>
                </div>
            </form>
            
            <div class="table-responsive">
                <table class="table table-striped">
                    <thead>
                        <tr>
                            <th>Ingrediente</th>
                            <th>Cantidad</th>
                            <th>Costo Unitario</th>
                            <th>Costo Total</th>
                            <th>Notas</th>
                            <th>Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($recetaActual)): ?>
                            <tr>
                                <td colspan="6" class="text-center">No hay ingredientes en esta receta</td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($recetaActual as $item): 
                                $costoUnitario = ($item['Precio'] > 0 && $item['CantidadUnidad'] > 0) 
                                    ? $item['Precio'] / $item['CantidadUnidad'] 
                                    : 0;
                                $costoTotalIngrediente = $costoUnitario * $item['Cantidad'];
                            ?>
                                <tr>
                                    <td><?= htmlspecialchars($item['Nombre']) ?> (<?= htmlspecialchars($item['UnidadMedida']) ?>)</td>
                                    <td><?= number_format($item['Cantidad'], 2) ?></td>
                                    <td>$<?= number_format($costoUnitario, 2) ?></td>
                                    <td>$<?= number_format($costoTotalIngrediente, 2) ?></td>
                                    <td><?= htmlspecialchars($item['Notas']) ?: 'N/A' ?></td>
                                    <td>
                                        <form method="post" style="display:inline;">
                                            <input type="hidden" name="receta_id" value="<?= $item['RecetaID'] ?>">
                                            <button type="submit" name="eliminar_ingrediente" class="btn btn-danger btn-sm" 
                                                    onclick="return confirm('¿Eliminar este ingrediente de la receta?')">
                                                <i class="fas fa-trash-alt"></i> Eliminar
                                            </button>
                                        </form>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                            <tr class="table-primary">
                                <td colspan="3" class="text-end"><strong>Total:</strong></td>
                                <td><strong>$<?= number_format($costoTotalReceta, 2) ?></strong></td>
                                <td colspan="2"></td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<?php require_once '../../../includes/footer.php'; ?>