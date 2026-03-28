<?php
require_once('../../config/database.php');

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['paquete_id'])) {
    $paqueteID = $_POST['paquete_id'];
    $nombre = $_POST['nombre'];
    $descripcion = $_POST['descripcion'];
    $precio = floatval($_POST['precio']);
    $duracion = intval($_POST['duracion']);
    $activo = isset($_POST['activo']) ? 1 : 0;

    $stmt = $conn->prepare("UPDATE Paquetes SET Nombre = ?, Descripcion = ?, Precio = ?, DuracionDias = ?, Activo = ? WHERE PaqueteID = ?");
    $stmt->execute([$nombre, $descripcion, $precio, $duracion, $activo, $paqueteID]);

    // Limpiar componentes anteriores
    $conn->prepare("DELETE FROM PaqueteComponentes WHERE PaqueteID = ?")->execute([$paqueteID]);

    // Agregar nuevos
    if (!empty($_POST['componentes'])) {
        foreach ($_POST['componentes'] as $componente) {
            [$tipo, $itemID] = explode('_', $componente);
            $cantidad = 1;

            if ($tipo === 'Habitacion') {
                $cantidad = $duracion;
            } elseif (!empty($_POST['cantidades'][$tipo . '_' . $itemID])) {
                $cantidad = intval($_POST['cantidades'][$tipo . '_' . $itemID]);
            }

            $conn->prepare("INSERT INTO PaqueteComponentes (PaqueteID, Tipo, ItemID, Cantidad) VALUES (?, ?, ?, ?)")
                ->execute([$paqueteID, $tipo, $itemID, $cantidad]);
        }
    }

    header("Location: listar_paquetes.php");
    exit;
}
?>
