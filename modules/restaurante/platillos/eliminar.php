<?php
require_once '../../../config/database.php';

// Verificar permisos del usuario
session_start();
if (!($_SESSION['rol'] === 'Admin' || $_SESSION['rol'] === 'Gerente' || $_SESSION['rol'] === 'Restaurante')) {
    header('Location: /dashboard.php');
    exit;
}

// Obtener ID del platillo
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    header('Location: listar.php?error=ID no válido');
    exit;
}

$id = (int)$_GET['id'];

try {
    // Verificar si el platillo tiene órdenes asociadas
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM OrdenDetalles WHERE PlatilloID = ?");
    $stmt->execute([$id]);
    $tieneOrdenes = $stmt->fetchColumn();

    if ($tieneOrdenes) {
        header('Location: listar.php?error=No se puede desactivar el platillo porque tiene órdenes asociadas');
        exit;
    }

    // Desactivar el platillo (borrado lógico)
    $stmt = $pdo->prepare("UPDATE Platillos SET Activo = 0 WHERE PlatilloID = ?");
    $stmt->execute([$id]);

    header('Location: listar.php?success=1');
    exit;
} catch (PDOException $e) {
    header('Location: listar.php?error=Error al desactivar el platillo');
    exit;
}