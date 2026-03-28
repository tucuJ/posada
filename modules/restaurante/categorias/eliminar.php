<?php
require_once '../../../config/database.php';

// Verificar permisos del usuario
session_start();
if (!($_SESSION['rol'] === 'Admin' || $_SESSION['rol'] === 'Gerente' || $_SESSION['rol'] === 'Restaurante')) {
    header('Location: /dashboard.php');
    exit;
}

// Obtener ID de la categoría
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    header('Location: listar.php?error=ID no válido');
    exit;
}

$id = (int)$_GET['id'];

// Verificar si la categoría tiene platillos asociados
try {
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM Platillos WHERE CategoriaPlatilloID = ?");
    $stmt->execute([$id]);
    $tienePlatillos = $stmt->fetchColumn();

    if ($tienePlatillos) {
        header('Location: listar.php?error=No se puede desactivar la categoría porque tiene platillos asociados');
        exit;
    }

    // Desactivar la categoría (borrado lógico)
    $stmt = $pdo->prepare("UPDATE CategoriasPlatillos SET Activo = 0 WHERE CategoriaPlatilloID = ?");
    $stmt->execute([$id]);

    header('Location: listar.php?success=1');
    exit;
} catch (PDOException $e) {
    header('Location: listar.php?error=Error al desactivar la categoría');
    exit;
}