<?php 
define('BASE_URL', '/posada/');
require_once('../../../config/database.php');
include('../../../includes/header.php');

// Obtener parámetros de fecha
$fecha = $_GET['fecha'] ?? date('Y-m-d');

// Obtener resumen por tipo de venta
$queryResumen = "SELECT 
                 Tipo,
                 COUNT(*) as Cantidad,
                 SUM(Subtotal) as Subtotal,
                 SUM(Impuesto) as Impuesto,
                 SUM(Total) as Total
                 FROM Ventas
                 WHERE DATE(FechaHora) = ?
                 AND Estado = 'Completada'
                 GROUP BY Tipo";
$stmtResumen = $conn->prepare($queryResumen);
$stmtResumen->execute([$fecha]);
$resumen = $stmtResumen->fetchAll(PDO::FETCH_ASSOC);

// Obtener ventas del día
$queryVentas = "SELECT v.*, 
                CONCAT(c.Nombre, ' ', c.Apellido) as ClienteNombre
                FROM Ventas v
                LEFT JOIN Clientes c ON v.ClienteID = c.ClienteID
                WHERE DATE(v.FechaHora) = ?
                AND v.Estado = 'Completada'
                ORDER BY v.FechaHora DESC";
$stmtVentas = $conn->prepare($queryVentas);
$stmtVentas->execute([$fecha]);
$ventas = $stmtVentas->fetchAll(PDO::FETCH_ASSOC);

// Calcular totales generales
$totales = [
    'Cantidad' => array_sum(array_column($resumen, 'Cantidad')),
    'Subtotal' => array_sum(array_column($resumen, 'Subtotal')),
    'Impuesto' => array_sum(array_column($resumen, 'Impuesto')),
    'Total' => array_sum(array_column($resumen, 'Total'))
];
?>

<div class="container">
    <h2>Reporte Diario de Ventas</h2>
    
    <div class="card mb-4">
        <div class="card-body">
            <form method="get" class="row g-3">
                <div class="col-md-4">
                    <label for="fecha" class="form-label">Fecha</label>
                    <input type="date" class="form-control" id="fecha" name="fecha" 
                           value="<?= $fecha ?>" max="<?= date('Y-m-d') ?>">
                </div>
                <div class="col-md-4 d-flex align-items-end">
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-filter"></i> Generar Reporte
                    </button>
                </div>
            </form>
        </div>
    </div>
    
    <div class="card mb-4">
        <div class="card-header bg-primary text-white">
            <h5 class="mb-0">Resumen por Tipo de Venta - <?= (new DateTime($fecha))->format('d/m/Y') ?></h5>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-striped">
                    <thead>
                        <tr>
                            <th>Tipo</th>
                            <th>Cantidad</th>
                            <th>Subtotal</th>
                            <th>Impuestos</th>
                            <th>Total</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($resumen as $item): ?>
                        <tr>
                            <td><?= $item['Tipo'] ?></td>
                            <td><?= $item['Cantidad'] ?></td>
                            <td>$<?= number_format($item['Subtotal'], 2) ?></td>
                            <td>$<?= number_format($item['Impuesto'], 2) ?></td>
                            <td>$<?= number_format($item['Total'], 2) ?></td>
                        </tr>
                        <?php endforeach; ?>
                        <tr class="table-success fw-bold">
                            <td>Total General</td>
                            <td><?= $totales['Cantidad'] ?></td>
                            <td>$<?= number_format($totales['Subtotal'], 2) ?></td>
                            <td>$<?= number_format($totales['Impuesto'], 2) ?></td>
                            <td>$<?= number_format($totales['Total'], 2) ?></td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    
    <div class="card">
        <div class="card-header bg-primary text-white">
            <h5 class="mb-0">Detalle de Ventas</h5>
        </div>
        <div class="card-body">
            <?php if (count($ventas) > 0): ?>
                <div class="table-responsive">
                    <table class="table table-striped">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Hora</th>
                                <th>Cliente</th>
                                <th>Tipo</th>
                                <th>Subtotal</th>
                                <th>Impuestos</th>
                                <th>Total</th>
                                <th>Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($ventas as $venta): 
                                $hora = (new DateTime($venta['FechaHora']))->format('H:i');
                            ?>
                            <tr>
                                <td>#<?= $venta['VentaID'] ?></td>
                                <td><?= $hora ?></td>
                                <td><?= $venta['ClienteNombre'] ?? 'Consumidor Final' ?></td>
                                <td><?= $venta['Tipo'] ?></td>
                                <td>$<?= number_format($venta['Subtotal'], 2) ?></td>
                                <td>$<?= number_format($venta['Impuesto'], 2) ?></td>
                                <td>$<?= number_format($venta['Total'], 2) ?></td>
                                <td>
                                    <a href="../detalle.php?id=<?= $venta['VentaID'] ?>" class="btn btn-sm btn-info">
                                        <i class="fas fa-eye"></i>
                                    </a>
                                    <a href="../factura.php?id=<?= $venta['VentaID'] ?>" class="btn btn-sm btn-secondary">
                                        <i class="fas fa-print"></i>
                                    </a>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php else: ?>
                <div class="alert alert-info">No hay ventas registradas para esta fecha</div>
            <?php endif; ?>
        </div>
    </div>
</div>

<?php include('../../../includes/footer.php'); ?>