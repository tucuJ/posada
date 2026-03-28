<?php 
define('BASE_URL', '/posada/');
require_once('../../config/database.php');
include('../../includes/header.php');

// Manejar mensajes
if (isset($_GET['success'])) {
    $messages = [
        '1' => 'Reserva creada exitosamente',
        '2' => 'Reserva actualizada',
        '3' => 'Check-in realizado',
        '4' => 'Check-out completado',
        '5' => 'Reserva cancelada'
    ];
    echo "<div class='alert alert-success'>{$messages[$_GET['success']]}</div>";
}

if (isset($_GET['error'])) {
    echo "<div class='alert alert-danger'>Error al procesar la solicitud: ".htmlspecialchars($_GET['error'])."</div>";
}

// Obtener reservas
$query = "SELECT r.*, 
          c.Nombre as ClienteNombre, c.Apellido as ClienteApellido,
          h.Numero as HabitacionNumero,
          DATEDIFF(r.FechaSalida, r.FechaEntrada) as Noches,
          t.PrecioNoche,
          (DATEDIFF(r.FechaSalida, r.FechaEntrada) * t.PrecioNoche) as TotalEstimado
          FROM Reservaciones r
          JOIN Clientes c ON r.ClienteID = c.ClienteID
          JOIN Habitaciones h ON r.HabitacionID = h.HabitacionID
          JOIN TiposHabitacion t ON h.TipoHabitacionID = t.TipoHabitacionID
          ORDER BY r.FechaEntrada DESC";
$reservas = $conn->query($query)->fetchAll(PDO::FETCH_ASSOC);
?>

<div class="container">
    <h2>Gestión de Reservas</h2>
    
    <div class="d-flex justify-content-between mb-4">
        <a href="nueva.php" class="btn btn-primary">
            <i class="fas fa-plus"></i> Nueva Reserva
        </a>   <a href="Calendario.php" class="btn btn-secondary">
            <i class="fas fa-plus"></i> Ver calendario 
        </a>
        <div>
            <a href="reportes/ocupacion.php" class="btn btn-info">
                <i class="fas fa-chart-bar"></i> Ocupación
            </a>
            <a href="reportes/ingresos.php" class="btn btn-success">
                <i class="fas fa-dollar-sign"></i> Ingresos
            </a>
        </div>
    </div>
    
    <div class="table-responsive">
        <table class="table table-striped table-hover">
            <thead class="">
                <tr>
                    <th>ID</th>
                    <th>Cliente</th>
                    <th>Habitación</th>
                    <th>Fechas</th>
                    <th>Noches</th>
                    <th>Total</th>
                    <th>Estado</th>
                    <th>Acciones</th>
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
                    
                    $hoy = new DateTime();
                    $entrada = new DateTime($reserva['FechaEntrada']);
                    $salida = new DateTime($reserva['FechaSalida']);
                ?>
                <tr>
                    <td>#<?= $reserva['ReservacionID'] ?></td>
                    <td><?= "{$reserva['ClienteNombre']} {$reserva['ClienteApellido']}" ?></td>
                    <td><?= $reserva['HabitacionNumero'] ?></td>
                    <td>
                        <?= $entrada->format('d/m/Y') ?> - <?= $salida->format('d/m/Y') ?>
                    </td>
                    <td><?= $reserva['Noches'] ?></td>
                    <td>$<?= number_format($reserva['TotalEstimado'], 2) ?></td>
                    <td>
                        <span class="badge bg-<?= $estadoClass ?>">
                            <?= $reserva['Estado'] ?>
                        </span>
                    </td>
                    <td>
    <?php
        $estado = $reserva['Estado'];
    ?>

    <!-- Botón Check-in -->
    <?php if ($estado == 'Pendiente'): ?>
        <a href="checkin.php?id=<?= $reserva['ReservacionID'] ?>" 
           class="btn btn-sm btn-success" title="Check-in"
           onclick="return confirm('¿Registrar Check-in?')">
            <i class="fas fa-door-open"></i> Check-in
        </a>
    <?php endif; ?>

    <!-- Botón Check-out -->
    <?php if ($estado == 'Confirmada'): ?>
        <a href="checkout.php?id=<?= $reserva['ReservacionID'] ?>" 
           class="btn btn-sm btn-primary" title="Check-out"
           onclick="return confirm('¿Registrar Check-out?')">
            <i class="fas fa-door-closed"></i> Check-out
        </a>
    <?php endif; ?>

    <!-- Botón Editar: Siempre visible -->
    <a href="editar.php?id=<?= $reserva['ReservacionID'] ?>" 
       class="btn btn-sm btn-warning" title="Editar">
        <i class="fas fa-edit"></i> Editar
    </a>

    <!-- Botón Cancelar -->
    <?php if (!in_array($estado, ['Cancelada', 'Completada'])): ?>
        <a href="cancelar.php?id=<?= $reserva['ReservacionID'] ?>" 
           class="btn btn-sm btn-danger" title="Cancelar"
           onclick="return confirm('¿Cancelar esta reserva?')">
            <i class="fas fa-times"></i> Cancelar
        </a>
    <?php endif; ?>
</td>

                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>

<?php include('../../includes/footer.php'); ?>