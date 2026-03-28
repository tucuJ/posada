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

try {
    // Activar la categoría
    $stmt = $pdo->prepare("UPDATE CategoriasPlatillos SET Activo = 1 WHERE CategoriaPlatilloID = ?");
    $stmt->execute([$id]);

    header('Location: listar.php?success=1');
    exit;
} catch (PDOException $e) {
    header('Location: listar.php?error=Error al activar la categoría');
    exit;
}