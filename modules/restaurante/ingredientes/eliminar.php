<?php
require_once '../../../config/database.php';

// Verificar permisos del usuario
session_start();
if (!($_SESSION['rol'] === 'Admin' || $_SESSION['rol'] === 'Gerente' || $_SESSION['rol'] === 'Restaurante' || $_SESSION['rol'] === 'Bodega')) {
    header('Location: /dashboard.php');
    exit;
}

// Obtener ID del ingrediente
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    header('Location: listar.php?error=ID no válido');
    exit;
}

$id = (int)$_GET['id'];

// Verificar si el ingrediente está en uso en recetas
try {
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM Recetas WHERE IngredienteID = ?");
    $stmt->execute([$id]);
    $enUso = $stmt->fetchColumn();

    if ($enUso) {
        header('Location: listar.php?error=No se puede desactivar el ingrediente porque está en uso en recetas');
        exit;
    }

    // Desactivar el ingrediente (borrado lógico)
    $stmt = $pdo->prepare("UPDATE Ingredientes SET Activo = 0 WHERE IngredienteID = ?");
    $stmt->execute([$id]);

    header('Location: listar.php?success=1');
    exit;
} catch (PDOException $e) {
    header('Location: listar.php?error=Error al desactivar el ingrediente');
    exit;
}