<?php
require_once '../../../config/database.php';

// Verificar permisos del usuario
session_start();
if (!($_SESSION['rol'] === 'Admin' || $_SESSION['rol'] === 'Gerente' || $_SESSION['rol'] === 'Restaurante' || $_SESSION['rol'] === 'Recepcion')) {
    header('Location: /dashboard.php');
    exit;
}

// Obtener ID de la orden
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    header('Location: listar.php?error=ID no válido');
    exit;
}

$id = (int)$_GET['id'];

try {
    // Verificar estado actual de la orden
    $stmt = $pdo->prepare("SELECT Estado FROM OrdenesRestaurante WHERE OrdenID = ?");
    $stmt->execute([$id]);
    $estado = $stmt->fetchColumn();

    if ($estado === 'Cancelado' || $estado === 'Entregado') {
        header('Location: detalle.php?id=' . $id . '&error=No se puede cancelar una orden ' . $estado);
        exit;
    }

    // Cancelar la orden
    $stmt = $pdo->prepare("UPDATE OrdenesRestaurante SET Estado = 'Cancelado' WHERE OrdenID = ?");
    $stmt->execute([$id]);

    // Cancelar también los detalles
    $stmt = $pdo->prepare("UPDATE OrdenDetalles SET Estado = 'Cancelado' WHERE OrdenID = ?");
    $stmt->execute([$id]);

    header('Location: detalle.php?id=' . $id . '&success=1');
    exit;
} catch (PDOException $e) {
    header('Location: detalle.php?id=' . $id . '&error=Error al cancelar la orden');
    exit;
}