<?php
require_once('../../config/database.php');

if (!isset($_GET['id'])) {
    header("Location: listar.php");
    exit();
}

$reservaId = $_GET['id'];

try {
    $stmt = $conn->prepare("SELECT r.*, h.HabitacionID, t.PrecioNoche 
                            FROM Reservaciones r
                            JOIN Habitaciones h ON r.HabitacionID = h.HabitacionID
                            JOIN TiposHabitacion t ON h.TipoHabitacionID = t.TipoHabitacionID
                            WHERE r.ReservacionID = ?");
    $stmt->execute([$reservaId]);
    $reserva = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$reserva) {
        header("Location: listar.php?error=Reserva no encontrada");
        exit();
    }

    if ($reserva['Estado'] != 'Confirmada') {
        header("Location: listar.php?error=No se puede hacer check-out en este estado");
        exit();
    }

    // Usar fechas originales
    $fechaEntrada = new DateTime($reserva['FechaEntrada']);
    $fechaSalida = new DateTime($reserva['FechaSalida']);
    $noches = max(1, $fechaEntrada->diff($fechaSalida)->days); // Al menos 1 noche

    $precioNoche = floatval($reserva['PrecioNoche']);
    $subtotal = $noches * $precioNoche;
 
    $totalFinal = $subtotal ;

    // Insertar venta
    $stmt = $conn->prepare("INSERT INTO Ventas 
        (ClienteID, UsuarioID, FechaHora, Tipo, Subtotal, Impuesto, Total, MetodoPago, Estado)
        VALUES (?, 1, NOW(), 'Habitacion', ?, ?, ?, 'Efectivo', 'Completada')");
    $stmt->execute([$reserva['ClienteID'], $subtotal, $subtotal, $totalFinal]);
    $ventaId = $conn->lastInsertId();

    // Insertar detalle de venta con noches
    $stmt = $conn->prepare("INSERT INTO VentaServicios 
        (VentaID, Tipo, ItemID, FechaInicio, FechaFin, Cantidad, PrecioUnitario, Descuento, Subtotal)
        VALUES (?, 'Habitacion', ?, ?, ?, ?, ?, 0, ?)");
    $stmt->execute([
        $ventaId,
        $reserva['HabitacionID'],
        $reserva['FechaEntrada'],
        $reserva['FechaSalida'],
        $noches,
        $precioNoche,
        $subtotal
    ]);

    // Actualizar reserva y registrar el check-out
    $conn->prepare("UPDATE Reservaciones SET Estado = 'Completada' WHERE ReservacionID = ?")->execute([$reservaId]);
    $conn->prepare("INSERT INTO RegistroCheckout (ReservacionID, FechaHora, UsuarioID, VentaID) 
                    VALUES (?, NOW(), 1, ?)")->execute([$reservaId, $ventaId]);

    header("Location: listar.php?success=4");
    exit();
} catch (PDOException $e) {
    header("Location: listar.php?error=" . urlencode($e->getMessage()));
    exit();
}
