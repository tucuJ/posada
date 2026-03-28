<?php 
define('BASE_URL', '/posada/');
require_once('../../../config/database.php');
include('../../../includes/header.php');

// Obtener parámetros de fecha (últimos 30 días por defecto)
$fechaInicio = $_GET['fechaInicio'] ?? date('Y-m-d', strtotime('-30 days'));
$fechaFin = $_GET['fechaFin'] ?? date('Y-m-d');

// Validar fechas
if ($fechaInicio > $fechaFin) {
    $temp = $fechaInicio;
    $fechaInicio = $fechaFin;
    $fechaFin = $temp;
}

// Obtener datos de ingresos por habitación
$queryHabitaciones = "SELECT 
                        t.Nombre as Tipo,
                        COUNT(vs.VentaServicioID) as TotalVentas,
                        SUM(vs.Subtotal) as Subtotal,
                        SUM(vs.Subtotal * 0.16) as Impuestos,
                        SUM(vs.Subtotal * 1.16) as Total
                      FROM VentaServicios vs
                      JOIN Ventas v ON vs.VentaID = v.VentaID
                      JOIN Habitaciones h ON vs.ItemID = h.HabitacionID
                      JOIN TiposHabitacion t ON h.TipoHabitacionID = t.TipoHabitacionID
                      WHERE v.FechaHora BETWEEN ? AND ?
                      AND vs.Tipo = 'Habitacion'
                      GROUP BY t.TipoHabitacionID
                      ORDER BY Total DESC";

$stmtHabitaciones = $conn->prepare($queryHabitaciones);
$stmtHabitaciones->execute([$fechaInicio, $fechaFin . ' 23:59:59']);
$ingresosHabitaciones = $stmtHabitaciones->fetchAll(PDO::FETCH_ASSOC);

// Obtener datos de ingresos por servicios
$queryServicios = "SELECT 
                     s.Nombre as Servicio,
                     COUNT(vs.VentaServicioID) as TotalVentas,
                     SUM(vs.Subtotal) as Subtotal,
                     SUM(vs.Subtotal * 0.16) as Impuestos,
                     SUM(vs.Subtotal * 1.16) as Total
                   FROM VentaServicios vs
                   JOIN Ventas v ON vs.VentaID = v.VentaID
                   JOIN Servicios s ON vs.ItemID = s.ServicioID
                   WHERE v.FechaHora BETWEEN ? AND ?
                   AND vs.Tipo = 'Servicio'
                   GROUP BY s.ServicioID
                   ORDER BY Total DESC";

$stmtServicios = $conn->prepare($queryServicios);
$stmtServicios->execute([$fechaInicio, $fechaFin . ' 23:59:59']);
$ingresosServicios = $stmtServicios->fetchAll(PDO::FETCH_ASSOC);

// Obtener datos de ingresos por productos
$queryProductos = "SELECT 
                     p.Nombre as Producto,
                     c.Nombre as Categoria,
                     SUM(vd.Cantidad) as TotalUnidades,
                     SUM(vd.Subtotal) as Subtotal,
                     SUM(vd.Subtotal * 0.16) as Impuestos,
                     SUM(vd.Subtotal * 1.16) as Total
                   FROM VentaDetalles vd
                   JOIN Ventas v ON vd.VentaID = v.VentaID
                   JOIN Productos p ON vd.ProductoID = p.ProductoID
                   JOIN CategoriasProductos c ON p.CategoriaID = c.CategoriaID
                   WHERE v.FechaHora BETWEEN ? AND ?
                   GROUP BY p.ProductoID
                   ORDER BY Total DESC";

$stmtProductos = $conn->prepare($queryProductos);
$stmtProductos->execute([$fechaInicio, $fechaFin . ' 23:59:59']);
$ingresosProductos = $stmtProductos->fetchAll(PDO::FETCH_ASSOC);

// Calcular totales generales
$totalGeneral = 0;
$totales = [
    'Habitaciones' => 0,
    'Servicios' => 0,
    'Productos' => 0
];

foreach ($ingresosHabitaciones as $ingreso) {
    $totales['Habitaciones'] += $ingreso['Total'];
    $totalGeneral += $ingreso['Total'];
}

foreach ($ingresosServicios as $ingreso) {
    $totales['Servicios'] += $ingreso['Total'];
    $totalGeneral += $ingreso['Total'];
}

foreach ($ingresosProductos as $ingreso) {
    $totales['Productos'] += $ingreso['Total'];
    $totalGeneral += $ingreso['Total'];
}
?>

<div class="container">
    <h2>Reporte de Ingresos</h2>
    
    <div class="card mb-4">
        <div class="card-body">
            <form method="get" class="row g-3">
                <div class="col-md-4">
                    <label for="fechaInicio" class="form-label">Fecha Inicio</label>
                    <input type="date" class="form-control" id="fechaInicio" name="fechaInicio" 
                           value="<?= $fechaInicio ?>" max="<?= $fechaFin ?>">
                </div>
                <div class="col-md-4">
                    <label for="fechaFin" class="form-label">Fecha Fin</label>
                    <input type="date" class="form-control" id="fechaFin" name="fechaFin" 
                           value="<?= $fechaFin ?>" min="<?= $fechaInicio ?>">
                </div>
                <div class="col-md-4 d-flex align-items-end">
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-filter"></i> Filtrar
                    </button>
                </div>
            </form>
        </div>
    </div>
    
    <div class="row mb-4">
        <div class="col-md-4">
            <div class="card text-white bg-primary">
                <div class="card-body">
                    <h5 class="card-title">Período Analizado</h5>
                    <p class="card-text h4">
                        <?= (new DateTime($fechaInicio))->format('d/m/Y') ?> - 
                        <?= (new DateTime($fechaFin))->format('d/m/Y') ?>
                    </p>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card text-white bg-success">
                <div class="card-body">
                    <h5 class="card-title">Ingresos Totales</h5>
                    <p class="card-text h4">$<?= number_format($totalGeneral, 2) ?></p>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card text-white bg-info">
                <div class="card-body">
                    <h5 class="card-title">Distribución</h5>
                    <p class="card-text">
                        Habitaciones: <?= round(($totales['Habitaciones']/$totalGeneral)*100, 2) ?>%<br>
                        Servicios: <?= round(($totales['Servicios']/$totalGeneral)*100, 2) ?>%<br>
                        Productos: <?= round(($totales['Productos']/$totalGeneral)*100, 2) ?>%
                    </p>
                </div>
            </div>
        </div>
    </div>
    
    <div class="card mb-4">
        <div class="card-header bg-primary text-white">
            <h5 class="mb-0">Ingresos por Habitaciones</h5>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-striped table-hover">
                    <thead class="table-dark">
                        <tr>
                            <th>Tipo de Habitación</th>
                            <th>Reservas</th>
                            <th>Subtotal</th>
                            <th>Impuestos</th>
                            <th>Total</th>
                            <th>% del Total</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($ingresosHabitaciones as $ingreso): ?>
                        <tr>
                            <td><?= $ingreso['Tipo'] ?></td>
                            <td><?= $ingreso['TotalVentas'] ?></td>
                            <td>$<?= number_format($ingreso['Subtotal'], 2) ?></td>
                            <td>$<?= number_format($ingreso['Impuestos'], 2) ?></td>
                            <td>$<?= number_format($ingreso['Total'], 2) ?></td>
                            <td><?= round(($ingreso['Total']/$totalGeneral)*100, 2) ?>%</td>
                        </tr>
                        <?php endforeach; ?>
                        <tr class="table-success fw-bold">
                            <td>Total Habitaciones</td>
                            <td><?= array_sum(array_column($ingresosHabitaciones, 'TotalVentas')) ?></td>
                            <td>$<?= number_format(array_sum(array_column($ingresosHabitaciones, 'Subtotal')), 2) ?></td>
                            <td>$<?= number_format(array_sum(array_column($ingresosHabitaciones, 'Impuestos')), 2) ?></td>
                            <td>$<?= number_format($totales['Habitaciones'], 2) ?></td>
                            <td><?= round(($totales['Habitaciones']/$totalGeneral)*100, 2) ?>%</td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    
    <div class="card mb-4">
        <div class="card-header bg-primary text-white">
            <h5 class="mb-0">Ingresos por Servicios</h5>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-striped table-hover">
                    <thead class="table-dark">
                        <tr>
                            <th>Servicio</th>
                            <th>Ventas</th>
                            <th>Subtotal</th>
                            <th>Impuestos</th>
                            <th>Total</th>
                            <th>% del Total</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($ingresosServicios as $ingreso): ?>
                        <tr>
                            <td><?= $ingreso['Servicio'] ?></td>
                            <td><?= $ingreso['TotalVentas'] ?></td>
                            <td>$<?= number_format($ingreso['Subtotal'], 2) ?></td>
                            <td>$<?= number_format($ingreso['Impuestos'], 2) ?></td>
                            <td>$<?= number_format($ingreso['Total'], 2) ?></td>
                            <td><?= round(($ingreso['Total']/$totalGeneral)*100, 2) ?>%</td>
                        </tr>
                        <?php endforeach; ?>
                        <tr class="table-success fw-bold">
                            <td>Total Servicios</td>
                            <td><?= array_sum(array_column($ingresosServicios, 'TotalVentas')) ?></td>
                            <td>$<?= number_format(array_sum(array_column($ingresosServicios, 'Subtotal')), 2) ?></td>
                            <td>$<?= number_format(array_sum(array_column($ingresosServicios, 'Impuestos')), 2) ?></td>
                            <td>$<?= number_format($totales['Servicios'], 2) ?></td>
                            <td><?= round(($totales['Servicios']/$totalGeneral)*100, 2) ?>%</td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    
    <div class="card">
        <div class="card-header bg-primary text-white">
            <h5 class="mb-0">Ingresos por Productos</h5>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-striped table-hover">
                    <thead class="table-dark">
                        <tr>
                            <th>Producto</th>
                            <th>Categoría</th>
                            <th>Unidades</th>
                            <th>Subtotal</th>
                            <th>Impuestos</th>
                            <th>Total</th>
                            <th>% del Total</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($ingresosProductos as $ingreso): ?>
                        <tr>
                            <td><?= $ingreso['Producto'] ?></td>
                            <td><?= $ingreso['Categoria'] ?></td>
                            <td><?= $ingreso['TotalUnidades'] ?></td>
                            <td>$<?= number_format($ingreso['Subtotal'], 2) ?></td>
                            <td>$<?= number_format($ingreso['Impuestos'], 2) ?></td>
                            <td>$<?= number_format($ingreso['Total'], 2) ?></td>
                            <td><?= round(($ingreso['Total']/$totalGeneral)*100, 2) ?>%</td>
                        </tr>
                        <?php endforeach; ?>
                        <tr class="table-success fw-bold">
                            <td colspan="2">Total Productos</td>
                            <td><?= array_sum(array_column($ingresosProductos, 'TotalUnidades')) ?></td>
                            <td>$<?= number_format(array_sum(array_column($ingresosProductos, 'Subtotal')), 2) ?></td>
                            <td>$<?= number_format(array_sum(array_column($ingresosProductos, 'Impuestos')), 2) ?></td>
                            <td>$<?= number_format($totales['Productos'], 2) ?></td>
                            <td><?= round(($totales['Productos']/$totalGeneral)*100, 2) ?>%</td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<?php include('../../../includes/footer.php'); ?>