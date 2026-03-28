<?php
require_once('../../config/database.php');

if (isset($_POST['cliente'], $_POST['habitacion'], $_POST['fechaEntrada'], $_POST['fechaSalida'], $_POST['adultos'])) {
    $clienteId = $_POST['cliente'];
    $habitacionId = $_POST['habitacion'];
    $fechaEntrada = $_POST['fechaEntrada'];
    $fechaSalida = $_POST['fechaSalida'];
    $adultos = $_POST['adultos'];
    $ninos = isset($_POST['ninos']) ? $_POST['ninos'] : 0;
    $notas = isset($_POST['notas']) ? $_POST['notas'] : '';

    // Ingresar la reserva en la base de datos
    $query = "
        INSERT INTO Reservas (ClienteID, HabitacionID, FechaEntrada, FechaSalida, Adultos, Ninos, Notas)
        VALUES (:clienteId, :habitacionId, :fechaEntrada, :fechaSalida, :adultos, :ninos, :notas)
    ";
    $stmt = $conn->prepare($query);
    $stmt->bindParam(':clienteId', $clienteId);
    $stmt->bindParam(':habitacionId', $habitacionId);
    $stmt->bindParam(':fechaEntrada', $fechaEntrada);
    $stmt->bindParam(':fechaSalida', $fechaSalida);
    $stmt->bindParam(':adultos', $adultos);
    $stmt->bindParam(':ninos', $ninos);
    $stmt->bindParam(':notas', $notas);

    if ($stmt->execute()) {
        echo "<script>alert('Reserva confirmada exitosamente.'); window.location.href = 'listar.php';</script>";
    } else {
        echo "<script>alert('Hubo un error al realizar la reserva.'); window.location.href = 'nueva_reserva.php';</script>";
    }
}
?>
