<?php
require_once('../../config/database.php');

// Verificar si se recibe el ID del servicio
if (isset($_GET['id'])) {
    $servicio_id = $_GET['id'];

    // Eliminar el servicio
    $stmt = $conn->prepare("DELETE FROM Servicios WHERE ServicioID = ?");
    $stmt->execute([$servicio_id]);

    echo "<script>window.location.href='listar_servicio.php';</script>";
    exit();
} else {
    echo "ID de servicio no proporcionado.";
    exit();
}
?>
