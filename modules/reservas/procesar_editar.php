<?php
require_once('../../config/database.php');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = $_POST['ReservacionID'];
    $cliente = $_POST['ClienteID'];
    $habitacion = $_POST['HabitacionID'];
    $entrada = $_POST['FechaEntrada'];
    $salida = $_POST['FechaSalida'];
    $estado = $_POST['Estado'];

    try {
        $query = "UPDATE Reservaciones 
                  SET ClienteID = ?, HabitacionID = ?, FechaEntrada = ?, FechaSalida = ?, Estado = ? 
                  WHERE ReservacionID = ?";
        $stmt = $conn->prepare($query);
        $stmt->execute([$cliente, $habitacion, $entrada, $salida, $estado, $id]);

        header("Location: listar.php?success=2");
        exit();
    } catch (PDOException $e) {
        header("Location: listar.php?error=" . urlencode($e->getMessage()));
        exit();
    }
}
?>
