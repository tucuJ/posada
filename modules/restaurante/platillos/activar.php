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
    // Activar el platillo (borrado lógico inverso)
    $stmt = $pdo->prepare("UPDATE Platillos SET Activo = 1 WHERE PlatilloID = ?");
    $stmt->execute([$id]);

    header('Location: listar.php?success=1');
    exit;
} catch (PDOException $e) {
    header('Location: listar.php?error=Error al activar el platillo');
    exit;
}