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

// Calcular el total de días en el período
$totalDiasPeriodo = (new DateTime($fechaInicio))->diff(new DateTime($fechaFin))->days + 1;

// Obtener datos de ocupación basados exclusivamente en reservaciones
$query = "SELECT 
            h.HabitacionID,
            h.Numero,
            t.Nombre as Tipo,
            COUNT(r.ReservacionID) as TotalReservas,
            SUM(
                DATEDIFF(
                    LEAST(r.FechaSalida, ?),
                    GREATEST(r.FechaEntrada, ?)
                )
            ) as DiasOcupados,
            ROUND(
                SUM(
                    DATEDIFF(
                        LEAST(r.FechaSalida, ?),
                        GREATEST(r.FechaEntrada, ?)
                    )
                ) / ? * 100, 
                2
            ) as PorcentajeOcupacion
          FROM Habitaciones h
          JOIN TiposHabitacion t ON h.TipoHabitacionID = t.TipoHabitacionID
          LEFT JOIN Reservaciones r ON h.HabitacionID = r.HabitacionID
              AND r.FechaSalida > ? 
              AND r.FechaEntrada < ?
              AND r.Estado NOT IN ('Cancelada')
          GROUP BY h.HabitacionID
          ORDER BY PorcentajeOcupacion DESC";

$stmt = $conn->prepare($query);
$params = [
    $fechaFin, $fechaInicio,  // Para DiasOcupados
    $fechaFin, $fechaInicio,  // Para PorcentajeOcupacion
    $totalDiasPeriodo,        // Total de días en el período
    $fechaInicio, $fechaFin   // Para el JOIN
];
$stmt->execute($params);
$ocupacion = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Calcular promedios
$totalHabitaciones = count($ocupacion);
$ocupacionPromedio = $totalHabitaciones > 0 ? 
    array_sum(array_column($ocupacion, 'PorcentajeOcupacion')) / $totalHabitaciones : 0;
?>

<div class="container">
    <h2>Reporte de Ocupación Basado en Reservaciones</h2>
    
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
                        <small class="d-block">(<?= $totalDiasPeriodo ?> días)</small>
                    </p>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card text-white bg-success">
                <div class="card-body">
                    <h5 class="card-title">Ocupación Promedio</h5>
                    <p class="card-text h4"><?= round($ocupacionPromedio, 2) ?>%</p>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card text-white bg-info">
                <div class="card-body">
                    <h5 class="card-title">Total Habitaciones</h5>
                    <p class="card-text h4"><?= $totalHabitaciones ?></p>
                </div>
            </div>
        </div>
    </div>
    
    <div class="card">
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-striped table-hover">
                    <thead class="table-dark">
                        <tr>
                            <th>Habitación</th>
                            <th>Tipo</th>
                            <th>Reservas</th>
                            <th>Días Ocupados</th>
                            <th>% Ocupación</th>
                            <th>Gráfico</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($ocupacion as $hab): ?>
                        <tr>
                            <td><?= $hab['Numero'] ?></td>
                            <td><?= $hab['Tipo'] ?></td>
                            <td><?= $hab['TotalReservas'] ?></td>
                            <td><?= $hab['DiasOcupados'] ?? 0 ?></td>
                            <td><?= $hab['PorcentajeOcupacion'] ?>%</td>
                            <td>
                                <div class="progress">
                                    <div class="progress-bar bg-success" role="progressbar" 
                                         style="width: <?= $hab['PorcentajeOcupacion'] ?>%" 
                                         aria-valuenow="<?= $hab['PorcentajeOcupacion'] ?>" 
                                         aria-valuemin="0" aria-valuemax="100">
                                    </div>
                                </div>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<script>
// Script para sincronizar las fechas mínimas/máximas
document.addEventListener('DOMContentLoaded', function() {
    const fechaInicio = document.getElementById('fechaInicio');
    const fechaFin = document.getElementById('fechaFin');
    
    fechaInicio.addEventListener('change', function() {
        fechaFin.min = this.value;
    });
    
    fechaFin.addEventListener('change', function() {
        fechaInicio.max = this.value;
    });
});
</script>

<?php include('../../../includes/footer.php'); ?>