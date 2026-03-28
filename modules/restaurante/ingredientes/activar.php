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

try {
    // Activar el ingrediente (borrado lógico inverso)
    $stmt = $pdo->prepare("UPDATE Ingredientes SET Activo = 1 WHERE IngredienteID = ?");
    $stmt->execute([$id]);

    // Registrar el movimiento en el inventario
    $stmt = $pdo->prepare("INSERT INTO IngredientesMovimientos (ingredientesID, Tipo, Cantidad, Referencia, UsuarioID) 
                          VALUES (?, 'Ajuste', 0, 'Reactivación de ingrediente', ?)");
    $stmt->execute([$id, $_SESSION['usuario_id']]);

    header('Location: listar.php?success=1');
    exit;
} catch (PDOException $e) {
    header('Location: listar.php?error=Error al activar el ingrediente: ' . urlencode($e->getMessage()));
    exit;
}