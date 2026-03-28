<?php
require_once '../../../config/database.php';
require_once '../../../includes/header.php';

// Verificar permisos del usuario
$allowedRoles = ['Admin', 'Gerente', 'Restaurante', 'Recepcion'];
if (!in_array($_SESSION['rol'], $allowedRoles)) {
    header('Location: /dashboard.php');
    exit;
}

// Configurar título según acción
$isEdit = isset($_GET['id']);
$title = $isEdit ? 'Restaurante - Editar Orden' : 'Restaurante - Nueva Orden';
$error = '';

// Obtener datos comunes
$clientes = $pdo->query("SELECT ClienteID, CONCAT(Nombre, ' ', Apellido) AS NombreCompleto FROM Clientes ORDER BY Nombre, Apellido")->fetchAll();
$habitaciones = $pdo->query("SELECT h.HabitacionID, h.Numero, t.Nombre AS Tipo FROM Habitaciones h JOIN TiposHabitacion t ON h.TipoHabitacionID = t.TipoHabitacionID ORDER BY h.Numero")->fetchAll();
$mesas = $pdo->query("SELECT * FROM MesasRestaurante WHERE Estado = 'Disponible' ORDER BY Numero")->fetchAll();

// Obtener platillos activos agrupados por categoría
$platillosPorCategoria = [];
$platillos = $pdo->query("
    SELECT p.PlatilloID, p.Nombre, p.PrecioVenta, c.Nombre AS Categoria 
    FROM Platillos p
    LEFT JOIN CategoriasPlatillos c ON p.CategoriaPlatilloID = c.CategoriaPlatilloID
    WHERE p.Activo = 1
    ORDER BY c.Nombre, p.Nombre
")->fetchAll();

foreach ($platillos as $platillo) {
    $categoria = $platillo['Categoria'] ?: 'Sin categoría';
    if (!isset($platillosPorCategoria[$categoria])) {
        $platillosPorCategoria[$categoria] = [];
    }
    $platillosPorCategoria[$categoria][] = $platillo;
}

// Procesamiento para edición
if ($isEdit) {
    $id = (int)$_GET['id'];
    
    // Obtener información de la orden
    $stmt = $pdo->prepare("SELECT * FROM OrdenesRestaurante WHERE OrdenID = ?");
    $stmt->execute([$id]);
    $orden = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$orden) {
        header('Location: listar.php?error=Orden no encontrada');
        exit;
    }

    // Verificar si la orden se puede editar
    if (in_array($orden['Estado'], ['Cancelado', 'Entregado'])) {
        header('Location: detalle.php?id=' . $id . '&error=No se puede editar una orden ' . $orden['Estado']);
        exit;
    }

    // Obtener detalles actuales
    $stmtDetalles = $pdo->prepare("SELECT * FROM OrdenDetalles WHERE OrdenID = ?");
    $stmtDetalles->execute([$id]);
    $detallesActuales = $stmtDetalles->fetchAll(PDO::FETCH_ASSOC);

    $platillosSeleccionados = [];
    foreach ($detallesActuales as $detalle) {
        $platillosSeleccionados[$detalle['PlatilloID']] = $detalle['Cantidad'];
    }
}

// Procesar el formulario
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $clienteID = isset($_POST['cliente']) ? (int)$_POST['cliente'] : null;
    $tipo = $_POST['tipo'];
    $mesaID = $tipo === 'Restaurante' ? (int)$_POST['mesa'] : null;
    $habitacionID = $tipo === 'Habitacion' ? (int)$_POST['habitacion'] : null;
    $notas = trim($_POST['notas']);
    $platillosSeleccionados = $_POST['platillos'] ?? [];

    // Validaciones comunes
    if (empty($tipo)) {
        $error = 'Debe seleccionar un tipo de orden.';
    } elseif ($tipo === 'Restaurante' && empty($mesaID)) {
        $error = 'Debe seleccionar una mesa para órdenes de restaurante.';
    } elseif ($tipo === 'Habitacion' && empty($habitacionID)) {
        $error = 'Debe seleccionar una habitación para órdenes a habitación.';
    } elseif (empty($platillosSeleccionados)) {
        $error = 'Debe seleccionar al menos un platillo.';
    } else {
        try {
            $pdo->beginTransaction();

            // Calcular totales
            $subtotal = 0;
            $detalles = [];
            
            foreach ($platillosSeleccionados as $platilloID => $cantidad) {
                $platilloID = (int)$platilloID;
                $cantidad = (int)$cantidad;
                
                if ($cantidad > 0) {
                    $stmt = $pdo->prepare("SELECT PrecioVenta FROM Platillos WHERE PlatilloID = ?");
                    $stmt->execute([$platilloID]);
                    $precio = $stmt->fetchColumn();
                    
                    $subtotal += $precio * $cantidad;
                    $detalles[] = ['platilloID' => $platilloID, 'cantidad' => $cantidad, 'precio' => $precio];
                }
            }

            $impuesto = $subtotal * 0.12;
            $total = $subtotal + $impuesto;

            if ($isEdit) {
                // Actualizar orden existente
                $stmt = $pdo->prepare("UPDATE OrdenesRestaurante 
                                      SET ClienteID = ?, HabitacionID = ?, MesaID = ?, Tipo = ?, 
                                      Subtotal = ?, Impuesto = ?, Total = ?, Notas = ?
                                      WHERE OrdenID = ?");
                $stmt->execute([
                    $clienteID,
                    $habitacionID,
                    $mesaID,
                    $tipo,
                    $subtotal,
                    $impuesto,
                    $total,
                    $notas,
                    $id
                ]);
            } else {
                // Crear nueva orden
                $stmt = $pdo->prepare("INSERT INTO OrdenesRestaurante 
                                      (ClienteID, HabitacionID, MesaID, UsuarioID, Tipo, Estado, Subtotal, Impuesto, Total, Notas) 
                                      VALUES (?, ?, ?, ?, ?, 'Pendiente', ?, ?, ?, ?)");
                $stmt->execute([
                    $clienteID,
                    $habitacionID,
                    $mesaID,
                    $_SESSION['usuario_id'],
                    $tipo,
                    $subtotal,
                    $impuesto,
                    $total,
                    $notas
                ]);
                $id = $pdo->lastInsertId();
            }

            // Actualizar estado de mesa si es nueva orden
            if (!$isEdit && $mesaID) {
                $pdo->prepare("UPDATE MesasRestaurante SET Estado = 'Ocupada' WHERE MesaID = ?")->execute([$mesaID]);
            }

            $pdo->commit();
            echo "<script>window.location.href='detalle.php?id=$id&success=1';</script>";
            exit;
        } catch (PDOException $e) {
            $pdo->rollBack();
            $error = 'Error al ' . ($isEdit ? 'actualizar' : 'crear') . ' la orden: ' . $e->getMessage();
        }
    }
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($title) ?></title>
</head>
<body>
<div class="container mt-4">
    <h2><?= $isEdit ? 'Editar Orden' : 'Nueva Orden' ?></h2>

    <?php if (!empty($error)): ?>
        <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
    <?php endif; ?>

    <form method="POST">
        <div class="row mb-3">
            <div class="col-md-4">
                <label for="cliente" class="form-label">Cliente</label>
                <select name="cliente" id="cliente" class="form-select">
                    <option value="">Seleccionar</option>
                    <?php foreach ($clientes as $cliente): ?>
                        <option value="<?= $cliente['ClienteID'] ?>"
                            <?= (isset($orden['ClienteID']) && $orden['ClienteID'] == $cliente['ClienteID']) ? 'selected' : '' ?>>
                            <?= htmlspecialchars($cliente['NombreCompleto']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="col-md-4">
                <label for="tipo" class="form-label">Tipo de Orden</label>
                <select name="tipo" id="tipo" class="form-select" onchange="mostrarOpciones()">
                    <option value="">Seleccionar</option>
                    <option value="Restaurante" <?= (isset($orden['Tipo']) && $orden['Tipo'] === 'Restaurante') ? 'selected' : '' ?>>Restaurante</option>
                    <option value="Habitacion" <?= (isset($orden['Tipo']) && $orden['Tipo'] === 'Habitacion') ? 'selected' : '' ?>>Habitación</option>
                </select>
            </div>

            <div class="col-md-4" id="divMesa" style="display: none;">
                <label for="mesa" class="form-label">Mesa</label>
                <select name="mesa" id="mesa" class="form-select">
                    <option value="">Seleccionar</option>
                    <?php foreach ($mesas as $mesa): ?>
                        <option value="<?= $mesa['MesaID'] ?>"
                            <?= (isset($orden['MesaID']) && $orden['MesaID'] == $mesa['MesaID']) ? 'selected' : '' ?>>
                            Mesa <?= htmlspecialchars($mesa['Numero']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="col-md-4" id="divHabitacion" style="display: none;">
                <label for="habitacion" class="form-label">Habitación</label>
                <select name="habitacion" id="habitacion" class="form-select">
                    <option value="">Seleccionar</option>
                    <?php foreach ($habitaciones as $habitacion): ?>
                        <option value="<?= $habitacion['HabitacionID'] ?>"
                            <?= (isset($orden['HabitacionID']) && $orden['HabitacionID'] == $habitacion['HabitacionID']) ? 'selected' : '' ?>>
                            Hab. <?= htmlspecialchars($habitacion['Numero']) ?> (<?= htmlspecialchars($habitacion['Tipo']) ?>)
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
        </div>

        <div class="mb-3">
            <label for="notas" class="form-label">Notas</label>
            <textarea name="notas" id="notas" class="form-control" rows="2"><?= isset($orden['Notas']) ? htmlspecialchars($orden['Notas']) : '' ?></textarea>
        </div>

        <h4>Platillos</h4>
        <?php foreach ($platillosPorCategoria as $categoria => $platillos): ?>
            <div class="card mb-3">
                <div class="card-header bg-light">
                    <?= htmlspecialchars($categoria) ?>
                </div>
                <div class="card-body">
                    <div class="row">
                        <?php foreach ($platillos as $platillo): ?>
                            <div class="col-md-4 mb-2">
                                <label class="form-label">
                                    <?= htmlspecialchars($platillo['Nombre']) ?> - Bs <?= number_format($platillo['PrecioVenta'], 2) ?>
                                </label>
                                <input type="number" name="platillos[<?= $platillo['PlatilloID'] ?>]"
                                       class="form-control" min="0"
                                       value="<?= isset($platillosSeleccionados[$platillo['PlatilloID']]) ? htmlspecialchars($platillosSeleccionados[$platillo['PlatilloID']]) : 0 ?>">
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>

        <div class="mt-4">
            <button type="submit" class="btn btn-primary"><?= $isEdit ? 'Actualizar' : 'Crear' ?> Orden</button>
            <a href="listar.php" class="btn btn-secondary">Cancelar</a>
        </div>
    </form>
</div>

<script>
function mostrarOpciones() {
    var tipo = document.getElementById('tipo').value;
    document.getElementById('divMesa').style.display = (tipo === 'Restaurante') ? 'block' : 'none';
    document.getElementById('divHabitacion').style.display = (tipo === 'Habitacion') ? 'block' : 'none';
}
// Mostrar opciones correctas al cargar la página
document.addEventListener('DOMContentLoaded', function() {
    mostrarOpciones();
});
</script>

<?php require_once '../../../includes/footer.php'; ?>