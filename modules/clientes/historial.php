<?php 
define('BASE_URL', '/posada/');
require_once('../../config/database.php');
include('../../includes/header.php');

if (!isset($_GET['id'])) {
    header("Location: listar.php");
    exit();
}

$clienteId = $_GET['id'];

// Obtener datos básicos del cliente
$query = "SELECT CONCAT(Nombre, ' ', Apellido) as NombreCompleto 
          FROM Clientes WHERE ClienteID = ?";
$stmt = $conn->prepare($query);
$stmt->execute([$clienteId]);
$nombreCliente = $stmt->fetchColumn();

if (!$nombreCliente) {
    header("Location: listar.php?error=1");
    exit();
}

// Obtener parámetros de fechas
$fechaInicio = $_GET['fechaInicio'] ?? date('Y-m-d', strtotime('-3 months'));
$fechaFin = $_GET['fechaFin'] ?? date('Y-m-d');

// Validar fechas
if ($fechaInicio > $fechaFin) {
    $temp = $fechaInicio;
    $fechaInicio = $fechaFin;
    $fechaFin = $temp;
}

// Obtener historial combinado (reservas y compras)
$query = "(
    SELECT 
        'reserva' as Tipo,
        r.ReservacionID as ID,
        r.FechaEntrada as Fecha,
        CONCAT('Habitación ', h.Numero) as Descripcion,
        DATEDIFF(r.FechaSalida, r.FechaEntrada) as Cantidad,
        t.PrecioNoche as PrecioUnitario,
        (DATEDIFF(r.FechaSalida, r.FechaEntrada) * t.PrecioNoche) as Subtotal,
        0 as Impuestos,
        (DATEDIFF(r.FechaSalida, r.FechaEntrada) * t.PrecioNoche) as Total,
        r.Estado
    FROM Reservaciones r
    JOIN Habitaciones h ON r.HabitacionID = h.HabitacionID
    JOIN TiposHabitacion t ON h.TipoHabitacionID = t.TipoHabitacionID
    WHERE r.ClienteID = ?
    AND r.FechaEntrada BETWEEN ? AND ?
)
UNION ALL
(
    SELECT 
        'venta' as Tipo,
        v.VentaID as ID,
        v.FechaHora as Fecha,
        CASE 
            WHEN v.Tipo = 'Producto' THEN 'Venta de productos'
            WHEN v.Tipo = 'Servicio' THEN 'Servicios adicionales'
            ELSE v.Tipo
        END as Descripcion,
        1 as Cantidad,
        v.Total as PrecioUnitario,
        v.Subtotal as Subtotal,
        v.Impuesto as Impuestos,
        v.Total as Total,
        v.Estado
    FROM Ventas v
    WHERE v.ClienteID = ?
    AND v.FechaHora BETWEEN ? AND ?
)
ORDER BY Fecha DESC";

$stmt = $conn->prepare($query);
$params = [
    $clienteId, $fechaInicio, $fechaFin . ' 23:59:59',
    $clienteId, $fechaInicio, $fechaFin . ' 23:59:59'
];
$stmt->execute($params);
$historial = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Calcular totales
$totalReservas = 0;
$totalVentas = 0;

foreach ($historial as $item) {
    if ($item['Tipo'] == 'reserva') {
        $totalReservas += $item['Total'];
    } else {
        $totalVentas += $item['Total'];
    }
}
?>

<div class="container">
    <h2>Historial Completo: <?= htmlspecialchars($nombreCliente) ?></h2>
    
    <div class="card mb-4">
        <div class="card-body">
            <form method="get" class="row g-3">
                <input type="hidden" name="id" value="<?= $clienteId ?>">
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
        <div class="col-md-6">
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
        <div class="col-md-3">
            <div class="card text-white bg-success">
                <div class="card-body">
                    <h5 class="card-title">Total Reservas</h5>
                    <p class="card-text h4">$<?= number_format($totalReservas, 2) ?></p>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card text-white bg-info">
                <div class="card-body">
                    <h5 class="card-title">Total Ventas</h5>
                    <p class="card-text h4">$<?= number_format($totalVentas, 2) ?></p>
                </div>
            </div>
        </div>
    </div>
    
    <div class="card">
        <div class="card-header bg-primary text-white">
            <h5 class="mb-0">Detalle de Actividad</h5>
        </div>
        <div class="card-body">
            <?php if (count($historial) > 0): ?>
                <div class="table-responsive">
                    <table class="table table-striped table-hover">
                        <thead class="table-dark">
                            <tr>
                                <th>Fecha</th>
                                <th>Tipo</th>
                                <th>Descripción</th>
                                <th>Cantidad</th>
                                <th>Precio Unitario</th>
                                <th>Subtotal</th>
                                <th>Impuestos</th>
                                <th>Total</th>
                                <th>Estado</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($historial as $item): 
                                $estadoClass = [
                                    'Pendiente' => 'warning',
                                    'Confirmada' => 'success',
                                    'Completada' => 'success',
                                    'Cancelada' => 'danger',
                                    'NoShow' => 'secondary'
                                ][$item['Estado']];
                                
                                $fecha = new DateTime($item['Fecha']);
                            ?>
                            <tr>
                                <td><?= $fecha->format('d/m/Y H:i') ?></td>
                                <td><?= ucfirst($item['Tipo']) ?></td>
                                <td><?= $item['Descripcion'] ?></td>
                                <td><?= $item['Cantidad'] ?></td>
                                <td>$<?= number_format($item['PrecioUnitario'], 2) ?></td>
                                <td>$<?= number_format($item['Subtotal'], 2) ?></td>
                                <td>$<?= number_format($item['Impuestos'], 2) ?></td>
                                <td>$<?= number_format($item['Total'], 2) ?></td>
                                <td>
                                    <span class="badge bg-<?= $estadoClass ?>">
                                        <?= $item['Estado'] ?>
                                    </span>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php else: ?>
                <div class="alert alert-info">No hay registros para este período</div>
            <?php endif; ?>
        </div>
    </div>
</div>

<?php include('../../includes/footer.php'); ?>