<?php
require_once('../../config/database.php');

if (!isset($_GET['id'])) {
    header("Location: listar_paquetes.php");
    exit();
}

$paqueteId = $_GET['id'];

// Iniciar transacción
$conn->beginTransaction();

try {
    // Eliminar componentes del paquete
    $queryDeleteComponents = "DELETE FROM PaqueteComponentes WHERE PaqueteID = ?";
    $stmtDeleteComponents = $conn->prepare($queryDeleteComponents);
    $stmtDeleteComponents->execute([$paqueteId]);

    // Eliminar el paquete
    $queryDeletePaquete = "DELETE FROM Paquetes WHERE PaqueteID = ?";
    $stmtDeletePaquete = $conn->prepare($queryDeletePaquete);
    $stmtDeletePaquete->execute([$paqueteId]);

    // Confirmar transacción
    $conn->commit();
    
    header("Location: listar_paquetes.php?success=3");
    exit();
} catch (PDOException $e) {
    $conn->rollBack();
    header("Location: listar_paquetes.php?error=4");
    exit();
}
?>
