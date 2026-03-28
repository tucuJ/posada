<?php
require_once('../../config/database.php');

if (isset($_GET['id']) && isset($_GET['estado'])) {
    $habitacion_id = $_GET['id'];
    $nuevo_estado = $_GET['estado'];
    
    // Validar estado
    $estados_validos = ['Disponible', 'Ocupada', 'Reservada', 'Mantenimiento'];
    
    if (in_array($nuevo_estado, $estados_validos)) {
        try {
            $query = "UPDATE Habitaciones SET Estado = ? WHERE HabitacionID = ?";
            $stmt = $conn->prepare($query);
            $stmt->execute([$nuevo_estado, $habitacion_id]);
            
            header("Location: listar.php?success=3");
        } catch(PDOException $e) {
            header("Location: listar.php?error=1");
        }
    } else {
        header("Location: listar.php?error=3");
    }
} else {
    header("Location: listar.php");
}
exit();
?>