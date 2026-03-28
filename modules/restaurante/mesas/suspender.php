<?php session_start();

define('BASE_URL', '/posada/');
require_once('../../../config/database.php');

// Verificar que tenga el ID
if (!isset($_GET['id'])) {
    $_SESSION['error'] = "Solicitud inválida: falta el ID";
    header("Location: listar.php");
    exit();
}

// Obtener y validar el ID
$mesaId = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);

if ($mesaId === false || $mesaId <= 0) {
    $_SESSION['error'] = "ID de mesa inválido";
    header("Location: listar.php");
    exit();
}

try {
    // 1. Verificar si la mesa existe
    $query = "SELECT Estado FROM MesasRestaurante WHERE MesaID = ?";
    $stmt = $conn->prepare($query);
    $stmt->execute([$mesaId]);
    $mesa = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$mesa) {
        $_SESSION['error'] = "La mesa no existe";
        header("Location: listar.php");
        exit();
    }

    // 2. Verificar si la mesa está en uso
    $query = "SELECT COUNT(*) FROM OrdenesRestaurante 
              WHERE MesaID = ? AND Estado NOT IN ('Cancelado', 'Completado')";
    $stmt = $conn->prepare($query);
    $stmt->execute([$mesaId]);
    $enUso = $stmt->fetchColumn();
    
    if ($enUso > 0) {
        $_SESSION['error'] = "No se puede suspender la mesa porque tiene órdenes activas";
        header("Location: listar.php");
        exit();
    }

    // 3. Cambiar el estado a "Suspendida"
    $query = "UPDATE MesasRestaurante SET Estado = 'mantenimiento' WHERE MesaID = ?";
    $stmt = $conn->prepare($query);
    $stmt->execute([$mesaId]);
    
    $_SESSION['success'] = "Mesa suspendida correctamente";
    header("Location: listar.php");
    exit();

} catch (PDOException $e) {
    $_SESSION['error'] = "Error en la base de datos: " . $e->getMessage();
    header("Location: listar.php");
    exit();
}