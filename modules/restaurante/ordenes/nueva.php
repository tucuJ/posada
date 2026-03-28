<?php
require_once '../../../config/database.php';
require_once '../../../includes/header.php';

// Verificar permisos
if (!($_SESSION['rol'] === 'Admin' || $_SESSION['rol'] === 'Gerente' || $_SESSION['rol'] === 'Restaurante' || $_SESSION['rol'] === 'Recepcion')) {
    header('Location: /dashboard.php');
    exit;
}

$title = 'Restaurante - Nueva Orden';
$error = '';

// Obtener datos necesarios
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

// Procesar el formulario
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $clienteID = isset($_POST['cliente']) ? (int)$_POST['cliente'] : null;
    $tipo = $_POST['tipo'];
    $mesaID = $tipo === 'Restaurante' ? (int)$_POST['mesa'] : null;
    $habitacionID = $tipo === 'Habitacion' ? (int)$_POST['habitacion'] : null;
    $notas = trim($_POST['notas']);
    $platillosSeleccionados = $_POST['platillos'] ?? [];

    // Validaciones
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

            // Insertar orden
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
            $ordenID = $pdo->lastInsertId();

            // Insertar detalles (sin consumo de ingredientes)
            foreach ($detalles as $detalle) {
                $stmt = $pdo->prepare("INSERT INTO OrdenDetalles 
                                    (OrdenID, PlatilloID, Cantidad, PrecioUnitario, Estado) 
                                    VALUES (?, ?, ?, ?, 'Pendiente')");
                $stmt->execute([$ordenID, $detalle['platilloID'], $detalle['cantidad'], $detalle['precio']]);
            }

            // Actualizar estado de mesa/habitación si es necesario
            if ($mesaID) {
                $pdo->prepare("UPDATE MesasRestaurante SET Estado = 'Ocupada' WHERE MesaID = ?")->execute([$mesaID]);
            }

            $pdo->commit();
            echo "<script>window.location.href='detalle.php?id=$ordenID&success=1';</script>";
            exit;
        } catch (PDOException $e) {
            $pdo->rollBack();
            $error = 'Error al crear la orden: ' . $e->getMessage();
        }
    }
}
?>

<!-- El resto del código HTML permanece igual -->
<div class="container mt-4">
    <h1 class="mb-4"><?= $title ?></h1>
    
    <div class="card">
        <div class="card-header">
            <i class="fas fa-plus"></i> Nueva Orden
        </div>
        <div class="card-body">
            <?php if ($error): ?>
                <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
            <?php endif; ?>
            
            <form method="post">
                <div class="row mb-3">
                    <div class="col-md-4">
                        <label for="tipo" class="form-label">Tipo de Orden *</label>
                        <select class="form-select" id="tipo" name="tipo" required>
                            <option value="">Seleccionar...</option>
                            <option value="Restaurante" <?= isset($_POST['tipo']) && $_POST['tipo'] === 'Restaurante' ? 'selected' : '' ?>>Restaurante (Mesas)</option>
                            <option value="Habitacion" <?= isset($_POST['tipo']) && $_POST['tipo'] === 'Habitacion' ? 'selected' : '' ?>>Habitación</option>
                            <option value="ParaLlevar" <?= isset($_POST['tipo']) && $_POST['tipo'] === 'ParaLlevar' ? 'selected' : '' ?>>Para Llevar (Encargo)</option>
                        </select>
                    </div>
                    
                    <div class="col-md-4" id="mesaContainer" style="display: none;">
                        <label for="mesa" class="form-label">Mesa *</label>
                        <select class="form-select" id="mesa" name="mesa">
                            <option value="">Seleccionar mesa...</option>
                            <?php foreach ($mesas as $mesa): ?>
                                <option value="<?= $mesa['MesaID'] ?>" <?= isset($_POST['mesa']) && $_POST['mesa'] == $mesa['MesaID'] ? 'selected' : '' ?>>
                                    Mesa #<?= htmlspecialchars($mesa['Numero']) ?> (Capacidad: <?= $mesa['Capacidad'] ?>)
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    
                    <div class="col-md-4" id="habitacionContainer" style="display: none;">
                        <label for="habitacion" class="form-label">Habitación *</label>
                        <select class="form-select" id="habitacion" name="habitacion">
                            <option value="">Seleccionar habitación...</option>
                            <?php foreach ($habitaciones as $hab): ?>
                                <option value="<?= $hab['HabitacionID'] ?>" <?= isset($_POST['habitacion']) && $_POST['habitacion'] == $hab['HabitacionID'] ? 'selected' : '' ?>>
                                    Hab. #<?= htmlspecialchars($hab['Numero']) ?> (<?= htmlspecialchars($hab['Tipo']) ?>)
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    
                    <div class="col-md-4">
                        <label for="cliente" class="form-label">Cliente</label>
                        <select class="form-select" id="cliente" name="cliente">
                            <option value="">Seleccionar cliente...</option>
                            <?php foreach ($clientes as $cliente): ?>
                                <option value="<?= $cliente['ClienteID'] ?>" <?= isset($_POST['cliente']) && $_POST['cliente'] == $cliente['ClienteID'] ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($cliente['NombreCompleto']) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>
                
                <div class="mb-3">
                    <label for="notas" class="form-label">Notas/Especificaciones</label>
                    <textarea class="form-control" id="notas" name="notas" rows="2"><?= isset($_POST['notas']) ? htmlspecialchars($_POST['notas']) : '' ?></textarea>
                </div>
                
                <h5 class="mt-4 mb-3">Seleccionar Platillos</h5>
                
                <div class="row">
                    <?php foreach ($platillosPorCategoria as $categoria => $platillosCategoria): ?>
                        <div class="col-md-6 mb-4">
                            <div class="card">
                                <div class="card-header bg-light">
                                    <h6 class="mb-0"><?= htmlspecialchars($categoria) ?></h6>
                                </div>
                                <div class="card-body">
                                    <table class="table table-sm">
                                        <thead>
                                            <tr>
                                                <th>Platillo</th>
                                                <th>Precio</th>
                                                <th>Cantidad</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php foreach ($platillosCategoria as $platillo): ?>
                                                <tr>
                                                    <td><?= htmlspecialchars($platillo['Nombre']) ?></td>
                                                    <td>$<?= number_format($platillo['PrecioVenta'], 2) ?></td>
                                                    <td>
                                                        <input type="number" min="0" max="10" class="form-control form-control-sm" 
                                                               name="platillos[<?= $platillo['PlatilloID'] ?>]" 
                                                               value="<?= isset($_POST['platillos'][$platillo['PlatilloID']]) ? htmlspecialchars($_POST['platillos'][$platillo['PlatilloID']]) : 0 ?>">
                                                    </td>
                                                </tr>
                                            <?php endforeach; ?>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
                
                <div class="d-grid gap-2 d-md-flex justify-content-md-end mt-4">
                    <a href="listar.php" class="btn btn-secondary me-md-2">
                        <i class="fas fa-arrow-left"></i> Cancelar
                    </a>
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save"></i> Guardar Orden
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<?php require_once '../../../includes/footer.php'; ?>

<script>
$(document).ready(function() {
    // Mostrar/ocultar campos según tipo de orden
    $('#tipo').change(function() {
        const tipo = $(this).val();
        
        // Ocultar todos los contenedores primero
        $('#mesaContainer, #habitacionContainer').hide();
        $('#mesa, #habitacion').prop('required', false);
        
        // Mostrar los campos necesarios según el tipo
        if (tipo === 'Restaurante') {
            $('#mesaContainer').show();
            $('#mesa').prop('required', true);
        } else if (tipo === 'Habitacion') {
            $('#habitacionContainer').show();
            $('#habitacion').prop('required', true);
        }
    }).trigger('change');
});
</script>