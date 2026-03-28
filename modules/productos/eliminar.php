<?php
require_once('../../config/database.php');

// Verificar si la solicitud es GET y tiene ID
if ($_SERVER['REQUEST_METHOD'] === 'GET' && isset($_GET['id'])) {
    $producto_id = intval($_GET['id']);
    
    try {
        // Verificar si el producto existe
        $query = "SELECT Activo FROM Productos WHERE ProductoID = ?";
        $stmt = $conn->prepare($query);
        $stmt->execute([$producto_id]);
        $producto = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if (!$producto) {
            header("Location: listar.php?error=2"); // Producto no encontrado
            exit();
        }
        
        // Cambiar estado (toggle) sin importar el stock
        $nuevoEstado = $producto['Activo'] ? 0 : 1;
        $successCode = $producto['Activo'] ? 3 : 4; // 3=Desactivado, 4=Activado
        
        $query = "UPDATE Productos SET Activo = ? WHERE ProductoID = ?";
        $stmt = $conn->prepare($query);
        $stmt->execute([$nuevoEstado, $producto_id]);
        
        header("Location: listar.php?success=$successCode");
        exit();
        
    } catch(PDOException $e) {
        error_log("Error al cambiar estado de producto: " . $e->getMessage());
        header("Location: listar.php?error=1"); // Error de base de datos
        exit();
    }
} else {
    header("Location: listar.php?error=3"); // ID inválido
    exit();
}