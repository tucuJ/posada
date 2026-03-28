<?php
require_once('../../config/database.php');

// Verificar que la solicitud sea POST
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    try {
        $conn->beginTransaction();

        // Insertar el paquete
        $stmt = $conn->prepare("INSERT INTO Paquetes (Nombre, Descripcion, Precio, DuracionDias, Activo) VALUES (?, ?, ?, ?, ?)");
        $stmt->execute([
            $_POST['nombre'],
            $_POST['descripcion'],
            $_POST['precio'],
            $_POST['duracion'],
            isset($_POST['activo']) ? 1 : 0
        ]);
        $paqueteID = $conn->lastInsertId();

        // Insertar los componentes del paquete
        foreach ($_POST['componentes'] as $componente) {
            $tipo = $componente['tipo'];
            $itemID = $componente['item'];
            $cantidad = isset($componente['cantidad']) ? $componente['cantidad'] : 1;

            // Verificar que el tipo y el item sean válidos
            if (!in_array($tipo, ['Producto', 'Servicio', 'Habitacion'])) {
                throw new Exception('Tipo de componente no válido');
            }

            $stmt = $conn->prepare("INSERT INTO PaqueteComponentes (PaqueteID, Tipo, ItemID, Cantidad) VALUES (?, ?, ?, ?)");
            $stmt->execute([$paqueteID, $tipo, $itemID, $cantidad]);
        }

        $conn->commit();
    echo "<script>window.location.href='listar_paquete.php';</script>";
        exit;
    } catch (Exception $e) {
        $conn->rollBack();
        echo "Error: " . $e->getMessage();
    }
} else {
    // Redirigir si no es una solicitud POST
    echo "<script>window.location.href='listar_paquete.php';</script>";
    exit;
}
?>
