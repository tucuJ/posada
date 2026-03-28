<?php
require_once('../../config/database.php');

if (!isset($_GET['id'])) {
    header("Location: listar.php");
    exit();
}

$reservaId = $_GET['id'];

try {
    $stmt = $conn->prepare("SELECT * FROM Reservaciones WHERE ReservacionID = ?");
    $stmt->execute([$reservaId]);
    $reserva = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$reserva) {
        header("Location: listar.php?error=Reserva no encontrada");
        exit();
    }

    if (!in_array($reserva['Estado'], ['Pendiente', 'Confirmada'])) {
        header("Location: listar.php?error=No se puede hacer check-in en este estado");
        exit();
    }

    $hoy = date('Y-m-d');
    if ($hoy < $reserva['FechaEntrada']) {
        $diasAnticipacion = (new DateTime($reserva['FechaEntrada']))->diff(new DateTime($hoy))->days;
        if ($diasAnticipacion > 15) {
            header("Location: listar.php?error=Check-in solo permitido hasta 15 días antes");
            exit();
        }
    } elseif ($hoy > $reserva['FechaSalida']) {
        header("Location: listar.php?error=La fecha de salida ya pasó");
        exit();
    }

    // Actualizar reserva y habitación
    $conn->prepare("UPDATE Reservaciones SET Estado = 'Confirmada' WHERE ReservacionID = ?")->execute([$reservaId]);
    $conn->prepare("INSERT INTO RegistroCheckin (ReservacionID, FechaHora, UsuarioID) VALUES (?, NOW(), 1)")->execute([$reservaId]);

    header("Location: listar.php?success=3");
    exit();
} catch (PDOException $e) {
    header("Location: listar.php?error=" . urlencode($e->getMessage()));
    exit();
}
