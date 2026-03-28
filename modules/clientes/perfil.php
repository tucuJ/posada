<?php 
define('BASE_URL', '/posada/');
require_once('../../config/database.php');
include('../../includes/header.php');

if (!isset($_GET['id'])) {
    header("Location: listar.php");
    exit();
}

$clienteId = $_GET['id'];

// Obtener datos del cliente
$query = "SELECT * FROM Clientes WHERE ClienteID = ?";
$stmt = $conn->prepare($query);
$stmt->execute([$clienteId]);
$cliente = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$cliente) {
    header("Location: listar.php?error=1");
    exit();
}

// Obtener historial de reservas
$queryReservas = "SELECT r.*, h.Numero as Habitacion, t.Nombre as TipoHabitacion,
                  DATEDIFF(r.FechaSalida, r.FechaEntrada) as Noches,
                  (DATEDIFF(r.FechaSalida, r.FechaEntrada) * t.PrecioNoche) as TotalEstimado
                  FROM Reservaciones r
                  JOIN Habitaciones h ON r.HabitacionID = h.HabitacionID
                  JOIN TiposHabitacion t ON h.TipoHabitacionID = t.TipoHabitacionID
                  WHERE r.ClienteID = ?
                  ORDER BY r.FechaEntrada DESC";
$reservas = $conn->prepare($queryReservas);
$reservas->execute([$clienteId]);
$reservas = $reservas->fetchAll(PDO::FETCH_ASSOC);

// Obtener historial de compras
$queryCompras = "SELECT v.*, SUM(vd.Subtotal) as SubtotalProductos
                 FROM Ventas v
                 LEFT JOIN VentaDetalles vd ON v.VentaID = vd.VentaID
                 WHERE v.ClienteID = ?
                 GROUP BY v.VentaID
                 ORDER BY v.FechaHora DESC";
$compras = $conn->prepare($queryCompras);
$compras->execute([$clienteId]);
$compras = $compras->fetchAll(PDO::FETCH_ASSOC);
?>

<div class="container">
    <h2>Perfil del Cliente</h2>
    
    <div class="row mb-4">
        <div class="col-md-6">
            <div class="card">
                <div class="card-header bg-primary text-white">
                    <h5 class="mb-0">Información Personal</h5>
                </div>
                <div class="card-body">
                    <div class="row mb-3">
                        <div class="col-md-4 fw-bold">Nombre:</div>
                        <div class="col-md-8"><?= "{$cliente['Nombre']} {$cliente['Apellido']}" ?></div>
                    </div>
                    <div class="row mb-3">
                        <div class="col-md-4 fw-bold">Documento:</div>
                        <div class="col-md-8"><?= "{$cliente['TipoDocumento']}-{$cliente['NumeroDocumento']}" ?></div>
                    </div>
                    <div class="row mb-3">
                        <div class="col-md-4 fw-bold">Teléfono:</div>
                        <div class="col-md-8"><?= $cliente['Telefono'] ?? 'N/A' ?></div>
                    </div>
                    <div class="row mb-3">
                        <div class="col-md-4 fw-bold">Email:</div>
                        <div class="col-md-8"><?= $cliente['Email'] ?? 'N/A' ?></div>
                    </div>
                    <div class="row">
                        <div class="col-md-4 fw-bold">Dirección:</div>
                        <div class="col-md-8"><?= $cliente['Direccion'] ?? 'N/A' ?></div>
                    </div>
                </div>
                <div class="card-footer text-end">
                    <a href="editar.php?id=<?= $clienteId ?>" class="btn btn-warning">
                        <i class="fas fa-edit"></i> Editar
                    </a>
                </div>
            </div>
        </div>
        
        <div class="col-md-6">
            <div class="card">
                <div class="card-header bg-primary text-white">
                    <h5 class="mb-0">Estadísticas</h5>
                </div>
                <div class="card-body">
                    <div class="row mb-3">
                        <div class="col-md-6 fw-bold">Total Reservas:</div>
                        <div class="col-md-6"><?= count($reservas) ?></div>
                    </div>
                    <div class="row mb-3">
                        <div class="col-md-6 fw-bold">Total Compras:</div>
                        <div class="col-md-6"><?= count($compras) ?></div>
                    </div>
                    <div class="row mb-3">
                        <div class="col-md-6 fw-bold">Gasto Total:</div>
                        <div class="col-md-6">
                            $<?= number_format(array_sum(array_column($compras, 'Total')) + 
                                 array_sum(array_column($reservas, 'TotalEstimado')), 2) ?>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6 fw-bold">Fecha Registro:</div>
                        <div class="col-md-6">
                            <?= (new DateTime($cliente['FechaRegistro']))->format('d/m/Y H:i') ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <div class="card mb-4">
        <div class="card-header bg-primary text-white">
            <h5 class="mb-0">Historial de Reservas</h5>
        </div>
        <div class="card-body">
            <?php if (count($reservas) > 0): ?>
                <div class="table-responsive">
                    <table class="table table-striped">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Habitación</th>
                                <th>Fechas</th>
                                <th>Noches</th>
                                <th>Total</th>
                                <th>Estado</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($reservas as $reserva): 
                                $estadoClass = [
                                    'Pendiente' => 'warning',
                                    'Confirmada' => 'success',
                                    'Cancelada' => 'danger',
                                    'NoShow' => 'secondary',
                                    'Completada' => 'info'
                                ][$reserva['Estado']];
                            ?>
                            <tr>
                                <td>#<?= $reserva['ReservacionID'] ?></td>
                                <td><?= "{$reserva['Habitacion']} ({$reserva['TipoHabitacion']})" ?></td>
                                <td>
                                    <?= (new DateTime($reserva['FechaEntrada']))->format('d/m/Y') ?> - 
                                    <?= (new DateTime($reserva['FechaSalida']))->format('d/m/Y') ?>
                                </td>
                                <td><?= $reserva['Noches'] ?></td>
                                <td>$<?= number_format($reserva['TotalEstimado'], 2) ?></td>
                                <td>
                                    <span class="badge bg-<?= $estadoClass ?>">
                                        <?= $reserva['Estado'] ?>
                                    </span>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php else: ?>
                <div class="alert alert-info">Este cliente no tiene reservas registradas</div>
            <?php endif; ?>
        </div>
    </div>
    
    <div class="card">
        <div class="card-header bg-primary text-white">
            <h5 class="mb-0">Historial de Compras</h5>
        </div>
        <div class="card-body">
            <?php if (count($compras) > 0): ?>
                <div class="table-responsive">
                    <table class="table table-striped">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Fecha</th>
                                <th>Tipo</th>
                                <th>Subtotal</th>
                                <th>Impuestos</th>
                                <th>Total</th>
                                <th>Estado</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($compras as $compra): 
                                $estadoClass = [
                                    'Pendiente' => 'warning',
                                    'Completada' => 'success',
                                    'Cancelada' => 'danger'
                                ][$compra['Estado']];
                            ?>
                            <tr>
                                <td>#<?= $compra['VentaID'] ?></td>
                                <td><?= (new DateTime($compra['FechaHora']))->format('d/m/Y H:i') ?></td>
                                <td><?= $compra['Tipo'] ?></td>
                                <td>$<?= number_format($compra['Subtotal'], 2) ?></td>
                                <td>$<?= number_format($compra['Impuesto'], 2) ?></td>
                                <td>$<?= number_format($compra['Total'], 2) ?></td>
                                <td>
                                    <span class="badge bg-<?= $estadoClass ?>">
                                        <?= $compra['Estado'] ?>
                                    </span>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php else: ?>
                <div class="alert alert-info">Este cliente no tiene compras registradas</div>
            <?php endif; ?>
        </div>
    </div>
</div>

<?php include('../../includes/footer.php'); ?>