<?php
require_once '../../../config/database.php';
require_once '../../../includes/header.php';

// Verificar permisos
if (!in_array($_SESSION['rol'], ['Admin', 'Gerente', 'Restaurante', 'Recepcion'])) {
    header('Location: /dashboard.php');
    exit;
}

$title = 'Restaurante - Detalle de Orden';
$error = '';

// Validar ID de la orden
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    header('Location: listar.php?error=ID no válido');
    exit;
}

$id = (int)$_GET['id'];

// Obtener información de la orden
try {
    $stmt = $pdo->prepare("
        SELECT o.*, 
        CONCAT(c.Nombre, ' ', c.Apellido) AS ClienteNombre,
        h.Numero AS HabitacionNumero,
        m.Numero AS MesaNumero,
        m.Estado AS MesaEstado,
        u.NombreUsuario
        FROM OrdenesRestaurante o
        LEFT JOIN Clientes c ON o.ClienteID = c.ClienteID
        LEFT JOIN Habitaciones h ON o.HabitacionID = h.HabitacionID
        LEFT JOIN MesasRestaurante m ON o.MesaID = m.MesaID
        LEFT JOIN Usuarios u ON o.UsuarioID = u.UsuarioID
        WHERE o.OrdenID = ?
    ");
    $stmt->execute([$id]);
    $orden = $stmt->fetch();

    if (!$orden) {
        header('Location: listar.php?error=Orden no encontrada');
        exit;
    }
} catch (PDOException $e) {
    header('Location: listar.php?error=Error al obtener la orden');
    exit;
}

// Obtener detalles de la orden
$stmt = $pdo->prepare("
    SELECT d.*, p.Nombre AS PlatilloNombre
    FROM OrdenDetalles d
    JOIN Platillos p ON d.PlatilloID = p.PlatilloID
    WHERE d.OrdenID = ?
");
$stmt->execute([$id]);
$detalles = $stmt->fetchAll();

// Manejar cambio de estado
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['cambiar_estado'])) {
    $nuevoEstado = $_POST['estado'];
    
    try {
        $pdo->beginTransaction();
        
        // Actualizar estado de la orden
        $pdo->prepare("UPDATE OrdenesRestaurante SET Estado = ? WHERE OrdenID = ?")
            ->execute([$nuevoEstado, $id]);
        
        // Actualizar estado de los detalles
        $pdo->prepare("UPDATE OrdenDetalles SET Estado = ? WHERE OrdenID = ?")
            ->execute([$nuevoEstado, $id]);
        
        // Si se cancela, revertir el consumo de ingredientes
        if ($nuevoEstado === 'Cancelado') {
            // Revertir consumo de ingredientes
            $stmtConsumos = $pdo->prepare("
                SELECT ci.* 
                FROM ConsumoIngredientes ci
                JOIN OrdenDetalles od ON ci.OrdenDetalleID = od.DetalleID
                WHERE od.OrdenID = ?
            ");
            $stmtConsumos->execute([$id]);
            $consumos = $stmtConsumos->fetchAll();
            
            foreach ($consumos as $consumo) {
                // Revertir el consumo
                $pdo->prepare("
                    UPDATE Ingredientes 
                    SET Stock = Stock + ? 
                    WHERE IngredienteID = ?
                ")->execute([$consumo['CantidadConsumida'], $consumo['IngredienteID']]);
                
                // Registrar movimiento de reversión
                $pdo->prepare("
                    INSERT INTO ingredientesmovimientos 
                    (ingredientesID, Tipo, Cantidad, Referencia, Notas, UsuarioID, FechaHora)
                    VALUES (?, 'Entrada', ?, ?, ?, ?, NOW())
                ")->execute([
                    $consumo['IngredienteID'], 
                    $consumo['CantidadConsumida'], 
                    'Orden #'.$id, 
                    'Cancelación de orden', 
                    $_SESSION['usuario_id']
                ]);
            }
            
            // Liberar mesa si corresponde
            if ($orden['MesaID'] && $orden['Tipo'] === 'Restaurante') {
                $pdo->prepare("UPDATE MesasRestaurante SET Estado = 'Disponible' WHERE MesaID = ?")
                    ->execute([$orden['MesaID']]);
            }
        }
        
        // Si se marca como entregado, crear venta
        if ($nuevoEstado === 'Entregado') {
            // Verificar si ya existe venta
            $stmtVenta = $pdo->prepare("
                SELECT COUNT(*) 
                FROM VentasRestaurante 
                WHERE OrdenID = ?
            ");
            $stmtVenta->execute([$id]);
            $existeVenta = $stmtVenta->fetchColumn();
            
            if (!$existeVenta) {
                // Crear venta general
                $stmtInsertVenta = $pdo->prepare("
                    INSERT INTO Ventas 
                    (ClienteID, UsuarioID, Tipo, Subtotal, Descuento, Impuesto, Total, MetodoPago, Estado) 
                    VALUES (?, ?, 'Restaurante', ?, 0, ?, ?, 'Efectivo', 'Completada')
                ");
                $stmtInsertVenta->execute([
                    $orden['ClienteID'],
                    $_SESSION['usuario_id'],
                    $orden['Subtotal'],
                    $orden['Impuesto'],
                    $orden['Total']
                ]);
                $ventaID = $pdo->lastInsertId();
                
                // Vincular venta con la orden
                $pdo->prepare("
                    INSERT INTO VentasRestaurante 
                    (VentaID, OrdenID) 
                    VALUES (?, ?)
                ")->execute([$ventaID, $id]);
                
                // Liberar mesa si corresponde
                if ($orden['MesaID'] && $orden['Tipo'] === 'Restaurante') {
                    $pdo->prepare("UPDATE MesasRestaurante SET Estado = 'Disponible' WHERE MesaID = ?")
                        ->execute([$orden['MesaID']]);
                }
            }
        }
        
        $pdo->commit();
        echo "<script>window.location.href='detalle.php?id=$id&success=1';</script>";
        exit;
    } catch (PDOException $e) {
        $pdo->rollBack();
        $error = 'Error al actualizar el estado: ' . $e->getMessage();
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
    <h1 class="mb-4"><?= htmlspecialchars($title) ?></h1>
    
    <!-- Mostrar alertas -->
    <?php if (isset($_GET['success'])): ?>
        <div class="alert alert-success">Operación realizada con éxito.</div>
    <?php endif; ?>
    <?php if ($error): ?>
        <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
    <?php endif; ?>
    
    <div class="card mb-4">
        <div class="card-header">
            <i class="fas fa-info-circle"></i> Información de la Orden
        </div>
        <div class="card-body">
            <div class="row">
                <div class="col-md-6">
                    <h5>Orden #<?= htmlspecialchars($orden['OrdenID']) ?></h5>
                    <p><strong>Fecha/Hora:</strong> <?= date('d/m/Y H:i', strtotime($orden['FechaHora'])) ?></p>
                    <p><strong>Tipo:</strong> 
                        <?php 
                            $tipoText = [
                                'Restaurante' => '<span class="badge bg-primary">Restaurante</span>',
                                'Habitacion' => '<span class="badge bg-info">Habitación</span>',
                                'ParaLlevar' => '<span class="badge bg-warning">Para Llevar</span>'
                            ];
                            echo $tipoText[$orden['Tipo']] ?? $orden['Tipo'];
                        ?>
                    </p>
                    <?php if ($orden['Tipo'] === 'Restaurante' && $orden['MesaNumero']): ?>
                        <p><strong>Mesa:</strong> #<?= htmlspecialchars($orden['MesaNumero']) ?> 
                            (Estado: <span class="badge bg-<?= $orden['MesaEstado'] === 'Disponible' ? 'success' : 'danger' ?>">
                                <?= htmlspecialchars($orden['MesaEstado']) ?>
                            </span>)
                        </p>
                    <?php elseif ($orden['Tipo'] === 'Habitacion' && $orden['HabitacionNumero']): ?>
                        <p><strong>Habitación:</strong> #<?= htmlspecialchars($orden['HabitacionNumero']) ?></p>
                    <?php endif; ?>
                    <p><strong>Cliente:</strong> <?= htmlspecialchars($orden['ClienteNombre']) ?: 'N/A' ?></p>
                </div>
                <div class="col-md-6">
                    <div class="d-flex justify-content-between mb-3">
                        <h5>Estado Actual:</h5>
                        <span class="badge bg-<?= 
                            [
                                'Pendiente' => 'warning',
                                'EnPreparacion' => 'info',
                                'Listo' => 'success',
                                'Entregado' => 'primary',
                                'Cancelado' => 'danger'
                            ][$orden['Estado']]
                        ?>">
                            <?= $orden['Estado'] ?>
                        </span>
                    </div>
                    
                    <?php if ($orden['Estado'] !== 'Cancelado' && $orden['Estado'] !== 'Entregado'): ?>
                        <form method="post" class="mb-3">
                            <div class="input-group">
                                <select class="form-select" name="estado" required>
                                    <option value="">Cambiar estado a...</option>
                                    <option value="EnPreparacion" <?= $orden['Estado'] === 'Pendiente' ? '' : 'disabled' ?>>En Preparación</option>
                                    <option value="Listo" <?= $orden['Estado'] === 'EnPreparacion' ? '' : 'disabled' ?>>Listo</option>
                                    <option value="Entregado" <?= $orden['Estado'] === 'Listo' ? '' : 'disabled' ?>>Entregado</option>
                                    <option value="Cancelado">Cancelar</option>
                                </select>
                                <button type="submit" name="cambiar_estado" class="btn btn-primary">
                                    <i class="fas fa-sync-alt"></i> Actualizar
                                </button>
                            </div>
                            <?php if ($orden['Estado'] === 'Pendiente'): ?>
                                <small class="text-muted">Para cancelar, seleccione "Cancelar" y haga clic en Actualizar</small>
                            <?php endif; ?>
                        </form>
                    <?php endif; ?>
                    
                    <p><strong>Registrado por:</strong> <?= htmlspecialchars($orden['NombreUsuario']) ?></p>
                    <p><strong>Notas:</strong> <?= htmlspecialchars($orden['Notas']) ?: 'Ninguna' ?></p>
                </div>
            </div>
        </div>
    </div>
    
    <div class="card mb-4">
        <div class="card-header">
            <i class="fas fa-utensils"></i> Detalles de la Orden
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table">
                    <thead>
                        <tr>
                            <th>Platillo</th>
                            <th>Cantidad</th>
                            <th>Precio Unitario</th>
                            <th>Subtotal</th>
                            <th>Estado</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($detalles as $detalle): ?>
                            <tr>
                                <td><?= htmlspecialchars($detalle['PlatilloNombre']) ?></td>
                                <td><?= htmlspecialchars($detalle['Cantidad']) ?></td>
                                <td>$<?= number_format($detalle['PrecioUnitario'], 2) ?></td>
                                <td>$<?= number_format($detalle['Cantidad'] * $detalle['PrecioUnitario'], 2) ?></td>
                                <td>
                                    <span class="badge bg-<?= 
                                        [
                                            'Pendiente' => 'warning',
                                            'EnPreparacion' => 'info',
                                            'Listo' => 'success',
                                            'Entregado' => 'primary',
                                            'Cancelado' => 'danger'
                                        ][$detalle['Estado']]
                                    ?>">
                                        <?= $detalle['Estado'] ?>
                                    </span>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                    <tfoot>
                        <tr>
                            <th colspan="3" class="text-end">Subtotal:</th>
                            <td>$<?= number_format($orden['Subtotal'], 2) ?></td>
                            <td></td>
                        </tr>
                        <tr>
                            <th colspan="3" class="text-end">Impuesto (12%):</th>
                            <td>$<?= number_format($orden['Impuesto'], 2) ?></td>
                            <td></td>
                        </tr>
                        <tr>
                            <th colspan="3" class="text-end">Total:</th>
                            <td>$<?= number_format($orden['Total'], 2) ?></td>
                            <td></td>
                        </tr>
                    </tfoot>
                </table>
            </div>
        </div>
    </div>
    
    <div class="d-grid gap-2 d-md-flex justify-content-md-end">
        <a href="listar.php" class="btn btn-secondary me-md-2">
            <i class="fas fa-arrow-left"></i> Volver
        </a>
        <?php if ($orden['Estado'] === 'Pendiente' || $orden['Estado'] === 'EnPreparacion'): ?>
            <a href="editar.php?id=<?= $id ?>" class="btn btn-warning me-md-2">
                <i class="fas fa-edit"></i> Editar
            </a>
        <?php endif; ?>
    </div>
</div>

<?php require_once '../../../includes/footer.php'; ?>