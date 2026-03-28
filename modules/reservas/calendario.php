<?php
require_once('../../config/database.php');
require_once('../../includes/header.php');

// Fecha seleccionada para ver ocupación (por defecto hoy)
$fechaSeleccionada = isset($_GET['fecha']) ? $_GET['fecha'] : date('Y-m-d');
$fechaDt = DateTime::createFromFormat('Y-m-d', $fechaSeleccionada);
if (!$fechaDt) {
    $fechaDt = new DateTime();
    $fechaSeleccionada = $fechaDt->format('Y-m-d');
}

// Determinar mes y año del calendario (se puede navegar por mes)
$mes = isset($_GET['mes']) ? (int)$_GET['mes'] : (int)$fechaDt->format('n');
$ano = isset($_GET['ano']) ? (int)$_GET['ano'] : (int)$fechaDt->format('Y');

// Calcular mes anterior y siguiente
$mes_anterior = $mes - 1 < 1 ? 12 : $mes - 1;
$ano_anterior = $mes - 1 < 1 ? $ano - 1 : $ano;
$mes_siguiente = $mes + 1 > 12 ? 1 : $mes + 1;
$ano_siguiente = $mes + 1 > 12 ? $ano + 1 : $ano;

// Para resaltar el día seleccionado en el mes actual
$diaSeleccionado = ($fechaDt->format('n') == $mes && $fechaDt->format('Y') == $ano)
    ? (int)$fechaDt->format('j')
    : 0;

// Obtener todas las habitaciones
$query_habitaciones = "SELECT h.HabitacionID, h.Numero, th.Nombre AS TipoHabitacion 
                       FROM habitaciones h
                       JOIN tiposhabitacion th ON h.TipoHabitacionID = th.TipoHabitacionID
                       ORDER BY h.Numero";
$habitacionMap = [];
$habitaciones = $conn->query($query_habitaciones)->fetchAll(PDO::FETCH_ASSOC);

// Llenar el mapa de habitaciones por ID (para mostrar detalles en el resumen del día)
foreach ($habitaciones as $h) {
    $habitacionMap[$h['HabitacionID']] = $h;
}

// Obtener todas las reservaciones activas (sin importar su estado en la tabla habitaciones)
$primer_dia_mes = date("$ano-$mes-01");
$ultimo_dia_mes = date("$ano-$mes-t");
$query_reservaciones = "SELECT r.ReservacionID, r.HabitacionID, r.FechaEntrada, r.FechaSalida, r.Estado,
                               c.Nombre AS ClienteNombre, c.Apellido AS ClienteApellido
                        FROM reservaciones r
                        JOIN clientes c ON r.ClienteID = c.ClienteID
                        WHERE (
                            (r.FechaEntrada BETWEEN :inicio_mes AND :fin_mes) OR
                            (r.FechaSalida BETWEEN :inicio_mes AND :fin_mes) OR
                            (:inicio_mes BETWEEN r.FechaEntrada AND r.FechaSalida) OR
                            (:fin_mes BETWEEN r.FechaEntrada AND r.FechaSalida)
                        )";
$stmt_reservaciones = $conn->prepare($query_reservaciones);
$stmt_reservaciones->execute([
    ':inicio_mes' => $primer_dia_mes,
    ':fin_mes' => $ultimo_dia_mes
]);
$reservaciones = $stmt_reservaciones->fetchAll(PDO::FETCH_ASSOC);

// Organizar reservaciones por habitación
$reservas_por_habitacion = [];
foreach ($reservaciones as $reserva) {
    $reservas_por_habitacion[$reserva['HabitacionID']][] = $reserva;
}

// Obtener ocupación para el día seleccionado
$ocupadasEnFecha = [];
$tsSeleccionado = strtotime($fechaSeleccionada);
foreach ($reservaciones as $reserva) {
    $tsEntrada = strtotime($reserva['FechaEntrada']);
    $tsSalida = strtotime($reserva['FechaSalida']);

    if ($tsSeleccionado >= $tsEntrada && $tsSeleccionado <= $tsSalida) {
        $habId = $reserva['HabitacionID'];
        $estadoDia = 'Ocupada';
        if ($tsSeleccionado === $tsEntrada) {
            $estadoDia = 'Check-in';
        } elseif ($tsSeleccionado === $tsSalida) {
            $estadoDia = 'Check-out';
        }

        $ocupadasEnFecha[$habId] = [
            'ReservacionID' => $reserva['ReservacionID'],
            'ClienteNombre' => $reserva['ClienteNombre'],
            'ClienteApellido' => $reserva['ClienteApellido'],
            'Numero' => $habitacionMap[$habId]['Numero'] ?? '',
            'TipoHabitacion' => $habitacionMap[$habId]['TipoHabitacion'] ?? '',
            'EstadoDia' => $estadoDia,
        ];
    }
}

// Nombres de los meses
$nombres_meses = [
    1 => 'Enero', 2 => 'Febrero', 3 => 'Marzo', 4 => 'Abril',
    5 => 'Mayo', 6 => 'Junio', 7 => 'Julio', 8 => 'Agosto',
    9 => 'Septiembre', 10 => 'Octubre', 11 => 'Noviembre', 12 => 'Diciembre'
];
?>

<div class="container">
    <h2>Calendario de Disponibilidad de las Habitaciones - <?= $nombres_meses[$mes] ?> <?= $ano ?></h2>
    <p class="text-muted">Basado exclusivamente en reservaciones registradas</p>
    
    <div class="row mb-4">
        <div class="col-md-6">
            <form class="row g-2" method="get" action="">
                <div class="col-auto">
                    <label for="fecha" class="form-label mb-0">Seleccionar fecha</label>
                    <input type="date" id="fecha" name="fecha" class="form-control" value="<?= htmlspecialchars($fechaSeleccionada) ?>">
                </div>
                <div class="col-auto align-self-end">
                    <button type="submit" class="btn btn-primary">Ir</button>
                </div>
            </form>
        </div>
        <div class="col-md-6 text-end">
            <div class="btn-group">
                <?php
                $fechaNavegacionAnterior = sprintf('%04d-%02d-01', $ano_anterior, $mes_anterior);
                $fechaNavegacionSiguiente = sprintf('%04d-%02d-01', $ano_siguiente, $mes_siguiente);
                $fechaNavegacionActual = date('Y-m-01');
                ?>
                <a href="?mes=<?= $mes_anterior ?>&ano=<?= $ano_anterior ?>&fecha=<?= $fechaNavegacionAnterior ?>" class="btn btn-primary">
                    <i class="fas fa-chevron-left"></i> <?= $nombres_meses[$mes_anterior] ?>
                </a>
                <a href="?mes=<?= date('n') ?>&ano=<?= date('Y') ?>&fecha=<?= $fechaNavegacionActual ?>" class="btn btn-secondary">
                    Mes Actual
                </a>
                <a href="?mes=<?= $mes_siguiente ?>&ano=<?= $ano_siguiente ?>&fecha=<?= $fechaNavegacionSiguiente ?>" class="btn btn-primary">
                    <?= $nombres_meses[$mes_siguiente] ?> <i class="fas fa-chevron-right"></i>
                </a>
            </div>
        </div>
    </div>

    <div class="row mb-4">
        <div class="col-12">
            <div class="card">
                <div class="card-header bg-secondary text-white">
                    Ocupación para <?= (new DateTime($fechaSeleccionada))->format('d/m/Y') ?>
                </div>
                <div class="card-body">
                    <?php if (empty($ocupadasEnFecha)): ?>
                        <div class="alert alert-success mb-0">No hay habitaciones ocupadas el día seleccionado.</div>
                    <?php else: ?>
                        <div class="list-group">
                            <?php foreach ($ocupadasEnFecha as $ocupada): ?>
                                <a href="editar.php?id=<?= $ocupada['ReservacionID'] ?>" class="list-group-item list-group-item-action d-flex justify-content-between align-items-start">
                                    <div>
                                        <strong>Hab. <?= htmlspecialchars($ocupada['Numero']) ?></strong> - <?= htmlspecialchars($ocupada['TipoHabitacion']) ?><br>
                                        <small>Reserva #<?= $ocupada['ReservacionID'] ?> - <?= htmlspecialchars($ocupada['ClienteNombre'] . ' ' . $ocupada['ClienteApellido']) ?></small>
                                    </div>
                                    <span class="badge bg-danger rounded-pill align-self-center"><?= htmlspecialchars($ocupada['EstadoDia']) ?></span>
                                </a>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
    
    <div class="table-responsive">
        <table class="table table-bordered">
            <thead class="table-dark">
                <tr>
                    <th>Habitación</th>
                    <?php
                    // Generar encabezados de días
                    $dias_en_mes = cal_days_in_month(CAL_GREGORIAN, $mes, $ano);
                    for ($dia = 1; $dia <= $dias_en_mes; $dia++) {
                        $fecha = date("$ano-$mes-$dia");
                        $nombre_dia = date('D', strtotime($fecha));
                        $nombre_dia_es = [
                            'Mon' => 'Lun', 'Tue' => 'Mar', 'Wed' => 'Mié',
                            'Thu' => 'Jue', 'Fri' => 'Vie', 'Sat' => 'Sáb', 'Sun' => 'Dom'
                        ][$nombre_dia];
                        $class = $dia === $diaSeleccionado ? 'table-warning' : '';
                        echo "<th class='$class'>$nombre_dia_es<br>$dia</th>";
                    }
                    ?>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($habitaciones as $habitacion): ?>
                <tr>
                    <td>
                        <strong><?= $habitacion['Numero'] ?></strong><br>
                        <small><?= $habitacion['TipoHabitacion'] ?></small>
                    </td>
                    <?php
                    // Obtener reservaciones para esta habitación
                    $reservas_hab = $reservas_por_habitacion[$habitacion['HabitacionID']] ?? [];
                    
                    // Verificar disponibilidad para cada día del mes
                    for ($dia = 1; $dia <= $dias_en_mes; $dia++) {
                        $fecha_actual = date("$ano-$mes-$dia");
                        $estado = 'Disponible';
                        $tooltip = '';
                        $clase = 'bg-success'; // Disponible por defecto
                        $celdaContenido = null;
                        
                        foreach ($reservas_hab as $reserva) {
                            $fecha_entrada = $reserva['FechaEntrada'];
                            $fecha_salida = $reserva['FechaSalida'];
                            
                            // Verificar si la fecha actual está dentro del rango de reserva
                            if (strtotime($fecha_actual) >= strtotime($fecha_entrada) && 
                                strtotime($fecha_actual) <= strtotime($fecha_salida)) {
                                
                                // Determinar estado según tipo de día
                                if ($fecha_actual == $fecha_entrada) {
                                    $estado = 'Ocupada desde 13:00';
                                    $clase = 'bg-danger';
                                } elseif ($fecha_actual == $fecha_salida) {
                                    $estado = 'Disponible desde 12:00';
                                    $clase = 'bg-info';
                                } else {
                                    $estado = 'Ocupada';
                                    $clase = 'bg-danger';
                                }
                                $resId = $reserva['ReservacionID'];
                                $link = "editar.php?id={$resId}";
                                $celdaContenido = "<a href='$link' class='text-white d-block'>" .
                                                 "<strong>R#{$resId}</strong><br>$estado</a>";
                                
                                $tooltip = "Reserva #{$reserva['ReservacionID']}\n";
                                $tooltip .= "Cliente: {$reserva['ClienteNombre']} {$reserva['ClienteApellido']}\n";
                                $tooltip .= "Check-in: {$reserva['FechaEntrada']}\n";
                                $tooltip .= "Check-out: {$reserva['FechaSalida']}\n";
                                $tooltip .= "Estado: {$reserva['Estado']}";
                                break;
                            }
                        }
                        
                        $celdaClass = $clase . ($dia === $diaSeleccionado ? ' table-warning' : '');
                        echo "<td class='$celdaClass text-white' title='$tooltip'>" .
                             (isset($celdaContenido) ? $celdaContenido : $estado) .
                             "</td>";
                    }
                    ?>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
    
    <div class="mt-4">
        <h4>Leyenda:</h4>
        <div class="d-flex flex-wrap gap-3">
            <div><span class="badge bg-success p-2">Disponible</span> - Sin reservas registradas</div>
            <div><span class="badge bg-info p-2">Disponible desde 12:00</span> - Día de check-out</div>
            <div><span class="badge bg-danger p-2">Ocupada</span> - Reserva activa</div>
        </div>
    </div>
</div>

<?php include('../../includes/footer.php'); ?>