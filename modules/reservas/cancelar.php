<?php
require_once('../../config/database.php');

if (!isset($_GET['id'])) {
    header("Location: listar.php?error=ID inválido");
    exit();
}

$reservaId = $_GET['id'];

try {
    $query = "UPDATE Reservaciones SET Estado = 'Cancelada' WHERE ReservacionID = ?";
    $stmt = $conn->prepare($query);
    $stmt->execute([$reservaId]);

    header("Location: listar.php?success=5");
    exit();
} catch (PDOException $e) {
    header("Location: listar.php?error=" . urlencode($e->getMessage()));
    exit();
}
