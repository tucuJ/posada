<?php
require_once('../../config/database.php');
session_start();

if (!isset($_GET['id'])) {
    header("Location: listar.php");
    exit();
}

$ventaId = $_GET['id'];
$usuarioId = $_SESSION['usuario_id'] ?? 1; // Asegura que haya un usuario registrado (usa 1 por defecto)

try {
    // Verificar si la venta existe y su estado
    $query = "SELECT Estado FROM Ventas WHERE VentaID = ?";
    $stmt = $conn->prepare($query);
    $stmt->execute([$ventaId]);
    $estado = $stmt->fetchColumn();

    if (!$estado) {
        header("Location: listar.php?error=1"); // Venta no encontrada
        exit();
    }

    if ($estado === 'Cancelada') {
        header("Location: listar.php?error=2"); // Ya está cancelada
        exit();
    }

    // Iniciar transacción
    $conn->beginTransaction();

    // Reponer productos si existen en la venta
    $queryItems = "SELECT ProductoID, Cantidad FROM VentaDetalles WHERE VentaID = ?";
    $stmtItems = $conn->prepare($queryItems);
    $stmtItems->execute([$ventaId]);
    $productos = $stmtItems->fetchAll(PDO::FETCH_ASSOC);

    foreach ($productos as $prod) {
        $updateProd = "UPDATE Productos SET Stock = Stock + ? WHERE ProductoID = ?";
        $stmtUpdateProd = $conn->prepare($updateProd);
        $stmtUpdateProd->execute([$prod['Cantidad'], $prod['ProductoID']]);
    }

    // Liberar habitaciones si están asociadas como servicio
    $queryHab = "SELECT ItemID FROM VentaServicios WHERE VentaID = ? AND Tipo = 'Habitacion'";
    $stmtHab = $conn->prepare($queryHab);
    $stmtHab->execute([$ventaId]);
    $habitaciones = $stmtHab->fetchAll(PDO::FETCH_COLUMN);

    if (!empty($habitaciones)) {
        $placeholders = implode(',', array_fill(0, count($habitaciones), '?'));
        $queryUpdateHab = "UPDATE Habitaciones SET Estado = 'Disponible' WHERE HabitacionID IN ($placeholders)";
        $stmtUpdateHab = $conn->prepare($queryUpdateHab);
        $stmtUpdateHab->execute($habitaciones);



         $query = "UPDATE Reservaciones SET Estado = 'Cancelada' WHERE ReservacionID = ?";
    $stmt = $conn->prepare($query);
    $stmt->execute([$reservaId]);
    }

    // Actualizar estado de la venta
    $updateVenta = "UPDATE Ventas SET Estado = 'Cancelada' WHERE VentaID = ?";
    $stmtUpdateVenta = $conn->prepare($updateVenta);
    $stmtUpdateVenta->execute([$ventaId]);

    // Registrar la anulación
    $queryAnulacion = "INSERT INTO VentasAnulaciones (VentaID, FechaAnulacion, UsuarioID, Motivo) 
                       VALUES (?, NOW(), ?, 'Anulación manual')";
    $stmtAnulacion = $conn->prepare($queryAnulacion);
    $stmtAnulacion->execute([$ventaId, $usuarioId]);

    // Confirmar
    $conn->commit();

    header("Location: listar.php?success=2");
    exit();

} catch (PDOException $e) {
    $conn->rollBack();

    // Solo para desarrollo: muestra el error
    echo "Error: " . $e->getMessage();
    exit();

    // Producción:
    // header("Location: listar.php?error=3");
    // exit();
}
?>
