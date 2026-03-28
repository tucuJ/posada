<?php
include('../../includes/header.php');require_once('../../config/database.php');

if (!isset($_SESSION['usuario_id'])) {
    echo "<script>window.location.href='../../login.php';</script>";
    exit();
}

$reservacionID = $_GET['id'] ?? null;

if (!$reservacionID) {
    echo "<script>window.location.href='listar.php';</script>";
    exit();
}

// Obtener datos actuales de la reserva
$stmt = $conn->prepare("SELECT * FROM Reservaciones WHERE ReservacionID = ?");
$stmt->execute([$reservacionID]);
$reserva = $stmt->fetch();

if (!$reserva) {
    $_SESSION['error_message'] = "Reserva no encontrada.";
    echo "<script>window.location.href='listar.php';</script>";
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $clienteID = $_POST['cliente_id'];
    $habitacionID = $_POST['habitacion_id'];
    $fechaEntrada = $_POST['fecha_entrada'];
    $fechaSalida = $_POST['fecha_salida'];
    $estado = $_POST['estado'];

    // Validación: No permitir estado diferente de "Cancelada" si ya hay otra reserva en esas fechas
    $validacion = $conn->prepare("
        SELECT * FROM Reservaciones
        WHERE HabitacionID = :habitacion_id
          AND ReservacionID != :reservacion_id
          AND Estado != 'Cancelada'
          AND (
                (FechaEntrada <= :fecha_salida AND FechaSalida >= :fecha_entrada)
              )
    ");
    $validacion->execute([
        ':habitacion_id' => $habitacionID,
        ':reservacion_id' => $reservacionID,
        ':fecha_entrada' => $fechaEntrada,
        ':fecha_salida' => $fechaSalida,
    ]);
    $conflicto = $validacion->fetch();

    if ($conflicto && $estado != 'Cancelada') {
        $_SESSION['error_message'] = "Ya existe otra reserva activa para esta habitación en las mismas fechas. Solo se permite cambiar a estado 'Cancelada'.";
        echo "<script>window.location.href='editar.php?id=$reservacionID';</script>";
        exit();
    }

    // Actualizar reserva
    $update = $conn->prepare("
        UPDATE Reservaciones SET
            ClienteID = ?, HabitacionID = ?, FechaEntrada = ?, FechaSalida = ?, Estado = ?
        WHERE ReservacionID = ?
    ");
    $update->execute([$clienteID, $habitacionID, $fechaEntrada, $fechaSalida, $estado, $reservacionID]);

    $_SESSION['success_message'] = "Reserva actualizada correctamente.";
    echo "<script>window.location.href='listar.php';</script>";
    exit();
}

// Obtener datos auxiliares
$clientes = $conn->query("SELECT ClienteID, Nombre FROM Clientes")->fetchAll();
$habitaciones = $conn->query("SELECT HabitacionID, Numero FROM Habitaciones")->fetchAll();
$estados = ['Pendiente', 'Confirmada', 'Cancelada'];
?>


<div class="container mt-4">
    <h3>Editar Reserva</h3>
    <form method="POST">
        <div class="mb-3">
            <label class="form-label">Cliente</label>
            <select name="cliente_id" class="form-select" required>
                <?php foreach ($clientes as $cliente): ?>
                    <option value="<?= $cliente['ClienteID'] ?>" <?= $reserva['ClienteID'] == $cliente['ClienteID'] ? 'selected' : '' ?>>
                        <?= htmlspecialchars($cliente['Nombre']) ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>

        <div class="mb-3">
            <label class="form-label">Habitación</label>
            <select name="habitacion_id" class="form-select" required>
                <?php foreach ($habitaciones as $hab): ?>
                    <option value="<?= $hab['HabitacionID'] ?>" <?= $reserva['HabitacionID'] == $hab['HabitacionID'] ? 'selected' : '' ?>>
                        Habitación <?= htmlspecialchars($hab['Numero']) ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>

        <div class="mb-3">
            <label class="form-label">Fecha de Entrada</label>
            <input type="date" name="fecha_entrada" class="form-control" value="<?= $reserva['FechaEntrada'] ?>" required>
        </div>

        <div class="mb-3">
            <label class="form-label">Fecha de Salida</label>
            <input type="date" name="fecha_salida" class="form-control" value="<?= $reserva['FechaSalida'] ?>" required>
        </div>

        <div class="mb-3">
            <label class="form-label">Estado</label>
            <select name="estado" class="form-select" required>
                <?php foreach ($estados as $estadoItem): ?>
                    <option value="<?= $estadoItem ?>" <?= $reserva['Estado'] == $estadoItem ? 'selected' : '' ?>>
                        <?= $estadoItem ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>

        <button type="submit" class="btn btn-primary">Guardar Cambios</button>
        <a href="listar.php" class="btn btn-secondary">Cancelar</a>
    </form>
</div>
<?php include '../../includes/footer.php'; ?>
