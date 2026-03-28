<?php 
define('BASE_URL', '/posada/');
require_once('../../config/database.php');
include('../../includes/header.php');

// Obtener clientes
$clientes = $conn->query("SELECT * FROM Clientes ORDER BY Nombre, Apellido")->fetchAll(PDO::FETCH_ASSOC);

// Obtener habitaciones disponibles
$queryHabitaciones = "SELECT h.HabitacionID, h.Numero, t.Nombre as Tipo, t.PrecioNoche, t.Capacidad, h.Estado
                      FROM Habitaciones h
                      JOIN TiposHabitacion t ON h.TipoHabitacionID = t.TipoHabitacionID
                      WHERE h.Estado IN ('Disponible')
                      ORDER BY h.Numero";
$habitaciones = $conn->query($queryHabitaciones)->fetchAll(PDO::FETCH_ASSOC);

// Verifica disponibilidad permitiendo reservas consecutivas
function verificarDisponibilidad($habitacionID, $fechaEntrada, $fechaSalida) {
    global $conn;

    $sql = "SELECT * FROM Reservaciones 
            WHERE HabitacionID = :habitacionID 
            AND Estado != 'Cancelada'
            AND NOT (
                FechaSalida <= :fechaEntrada OR
                FechaEntrada >= :fechaSalida
            )";

    $stmt = $conn->prepare($sql);
    $stmt->execute([
        ':habitacionID' => $habitacionID,
        ':fechaEntrada' => $fechaEntrada,
        ':fechaSalida' => $fechaSalida
    ]);
    
    return $stmt->rowCount() == 0;
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $clienteID = $_POST['cliente'];
    $habitacionID = $_POST['habitacion'];
    $fechaEntrada = $_POST['fechaEntrada'];
    $fechaSalida = $_POST['fechaSalida'];
    $adultos = $_POST['adultos'];
    $ninos = $_POST['ninos'];
    $notas = $_POST['notas'];

    // Validación de fechas en el servidor
    if (strtotime($fechaSalida) <= strtotime($fechaEntrada)) {
        $mensaje = "Error: La fecha de salida debe ser posterior a la fecha de entrada.";
    } elseif (!verificarDisponibilidad($habitacionID, $fechaEntrada, $fechaSalida)) {
        $mensaje = "La habitación no está disponible en las fechas seleccionadas.";
    } else {
        $queryReserva = "INSERT INTO Reservaciones 
                        (ClienteID, HabitacionID, FechaEntrada, FechaSalida, Adultos, Ninos, Notas) 
                         VALUES (:clienteID, :habitacionID, :fechaEntrada, :fechaSalida, :adultos, :ninos, :notas)";
                         
        $stmt = $conn->prepare($queryReserva);
        $stmt->execute([
            ':clienteID' => $clienteID,
            ':habitacionID' => $habitacionID,
            ':fechaEntrada' => $fechaEntrada,
            ':fechaSalida' => $fechaSalida,
            ':adultos' => $adultos,
            ':ninos' => $ninos,
            ':notas' => $notas
        ]);

        $mensaje = "Reserva confirmada exitosamente.";
    }
}
?>

<div class="container">
    <h2>Nueva Reserva</h2>

    <?php if (isset($mensaje)): ?>
        <div class="alert <?= strpos($mensaje, 'Error') !== false ? 'alert-danger' : 'alert-success' ?>">
            <?= $mensaje ?>
        </div>
    <?php endif; ?>

    <form id="formReserva" action="" method="post">
        <div class="row">
            <div class="col-md-6">
                <div class="card mb-4">
                    <div class="card-header bg-primary text-white">
                        <h5 class="mb-0">Datos del Cliente</h5>
                    </div>
                    <div class="card-body">
                        <div class="form-group mb-3">
                            <label for="cliente" class="form-label">Cliente</label>
                            <select class="form-select" id="cliente" name="cliente" required>
                                <option value="">Seleccione un cliente</option>
                                <?php foreach ($clientes as $cliente): ?>
                                    <option value="<?= $cliente['ClienteID'] ?>">
                                        <?= "{$cliente['Nombre']} {$cliente['Apellido']}" ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="text-end">
                            <a href="../clientes/agregar.php" class="btn btn-sm btn-outline-primary">
                                <i class="fas fa-plus"></i> Nuevo Cliente
                            </a>
                        </div>
                    </div>
                </div>

                <div class="card mb-4">
                    <div class="card-header bg-primary text-white">
                        <h5 class="mb-0">Detalles de Ocupación</h5>
                    </div>
                    <div class="card-body">
                        <div class="form-group mb-3">
                            <label for="adultos" class="form-label">Adultos</label>
                            <input type="number" min="1" max="10" class="form-control" id="adultos" name="adultos" value="2" required>
                        </div>
                        <div class="form-group mb-3">
                            <label for="ninos" class="form-label">Niños</label>
                            <input type="number" min="0" max="10" class="form-control" id="ninos" name="ninos" value="0">
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-md-6">
                <div class="card mb-4">
                    <div class="card-header bg-primary text-white">
                        <h5 class="mb-0">Habitación y Fechas</h5>
                    </div>
                    <div class="card-body">
                        <div class="form-group mb-3">
                            <label for="habitacion" class="form-label">Habitación</label>
                            <select class="form-select" id="habitacion" name="habitacion" required>
                                <option value="">Seleccione una habitación</option>
                                <?php foreach ($habitaciones as $hab): ?>
                                    <option value="<?= $hab['HabitacionID'] ?>" 
                                            data-precio="<?= $hab['PrecioNoche'] ?>"
                                            data-capacidad="<?= $hab['Capacidad'] ?>"
                                            data-estado="<?= $hab['Estado'] ?>">
                                        <?= "{$hab['Numero']} - {$hab['Tipo']} (\\${$hab['PrecioNoche']}/noche)" ?>
                                        <?= $hab['Estado'] == 'Mantenimiento' ? ' (Mantenimiento)' : '' ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label for="fechaEntrada" class="form-label">Fecha de Entrada</label>
                                <input type="date" class="form-control" id="fechaEntrada" name="fechaEntrada" min="<?= date('Y-m-d') ?>" required>
                            </div>
                            <div class="col-md-6">
                                <label for="fechaSalida" class="form-label">Fecha de Salida</label>
                                <input type="date" class="form-control" id="fechaSalida" name="fechaSalida" min="<?= date('Y-m-d', strtotime('+1 day')) ?>" required>
                                <div id="errorFecha" class="invalid-feedback" style="display: none;">
                                    La fecha de salida debe ser posterior a la de entrada
                                </div>
                            </div>
                        </div>

                        <div id="infoReserva"></div>
                    </div>
                </div>

                <div class="card mb-4">
                    <div class="card-header bg-primary text-white">
                        <h5 class="mb-0">Notas Adicionales</h5>
                    </div>
                    <div class="card-body">
                        <textarea class="form-control" id="notas" name="notas" rows="3" placeholder="Preferencias del cliente, servicios adicionales, etc."></textarea>
                    </div>
                </div>
            </div>
        </div>

        <div class="d-flex justify-content-between mt-3">
            <button type="submit" class="btn btn-primary" name="crear" id="btnConfirmar">
                <i class="fas fa-save"></i> Confirmar Reserva
            </button>
            <a href="listar.php" class="btn btn-secondary">
                <i class="fas fa-times"></i> Cancelar
            </a>
        </div>
    </form>
</div>

<script>
$(document).ready(function() {
    // Validación de fechas en el cliente
    $('#fechaEntrada').change(function() {
        const fechaEntrada = new Date($(this).val());
        const fechaSalidaInput = $('#fechaSalida');
        
        if ($(this).val()) {
            // Establecer fecha mínima de salida (día siguiente al de entrada)
            const minFechaSalida = new Date(fechaEntrada);
            minFechaSalida.setDate(minFechaSalida.getDate() + 1);
            
            const minFechaStr = minFechaSalida.toISOString().split('T')[0];
            fechaSalidaInput.attr('min', minFechaStr);
            
            // Si la fecha de salida actual es anterior a la nueva fecha mínima, actualizarla
            if (fechaSalidaInput.val() && new Date(fechaSalidaInput.val()) < minFechaSalida) {
                fechaSalidaInput.val(minFechaStr);
            }
        }
    });

    // Validar al cambiar fecha de salida
    $('#fechaSalida').change(function() {
        const fechaEntrada = new Date($('#fechaEntrada').val());
        const fechaSalida = new Date($(this).val());
        
        if (fechaSalida <= fechaEntrada) {
            $(this).addClass('is-invalid');
            $('#errorFecha').show();
            $('#btnConfirmar').prop('disabled', true);
        } else {
            $(this).removeClass('is-invalid');
            $('#errorFecha').hide();
            $('#btnConfirmar').prop('disabled', false);
        }
    });

    // Validar antes de enviar el formulario
    $('#formReserva').submit(function(e) {
        const fechaEntrada = new Date($('#fechaEntrada').val());
        const fechaSalida = new Date($('#fechaSalida').val());
        
        if (fechaSalida <= fechaEntrada) {
            e.preventDefault();
            $('#fechaSalida').addClass('is-invalid');
            $('#errorFecha').show();
            $('#btnConfirmar').prop('disabled', true);
            
            // Mostrar alerta
            alert('Error: La fecha de salida debe ser posterior a la fecha de entrada.');
        }
    });
});
</script>

<?php include('../../includes/footer.php'); ?>
