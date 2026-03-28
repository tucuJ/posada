<?php
require_once('../../config/database.php');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        if (isset($_POST['items']) && is_array($_POST['items'])) {
            $clienteId = !empty($_POST['cliente']) ? $_POST['cliente'] : null;
            $metodoPago = $_POST['metodoPago'];
            $notas = $_POST['notas'] ?? null;
            $items = $_POST['items'];
            
            // Validar items
            if (count($items) === 0) {
                header("Location: nueva.php?error=1");
                exit();
            }
            
            // Calcular totales
            $subtotal = 0;
            $descuento = 0; // Podría recibirse como parámetro en el futuro
            
            foreach ($items as $item) {
                $subtotal += $item['cantidad'] * $item['precio'];
            }
            
            $impuesto = $subtotal * 0.16; // 16% de IVA
            $total = $subtotal + $impuesto - $descuento;
            
            // Iniciar transacción
            $conn->beginTransaction();
            
            // 1. Crear registro de venta
            $queryVenta = "INSERT INTO Ventas (
                ClienteID, UsuarioID, FechaHora, Tipo, 
                Subtotal, Descuento, Impuesto, Total, 
                MetodoPago, Estado, Notas
            ) VALUES (?, 1, NOW(), 'Producto', ?, ?, ?, ?, ?, 'Completada', ?)";
            
            $stmtVenta = $conn->prepare($queryVenta);
            $stmtVenta->execute([
                $clienteId,
                $subtotal,
                $descuento,
                $impuesto,
                $total,
                $metodoPago,
                $notas
            ]);
            
            $ventaId = $conn->lastInsertId();
            
            // 2. Procesar items
            foreach ($items as $item) {
                if ($item['tipo'] === 'producto') {
                    // Verificar stock
                    $queryStock = "SELECT Stock FROM Productos WHERE ProductoID = ?";
                    $stmtStock = $conn->prepare($queryStock);
                    $stmtStock->execute([$item['id']]);
                    $stock = $stmtStock->fetchColumn();
                    
                    if ($stock < $item['cantidad']) {
                        throw new Exception("Stock insuficiente para {$item['nombre']}");
                    }
                    
                    // Registrar detalle de venta
                    $queryDetalle = "INSERT INTO VentaDetalles (
                        VentaID, ProductoID, Cantidad, PrecioUnitario, Descuento, Subtotal
                    ) VALUES (?, ?, ?, ?, ?, ?)";
                    
                    $subtotalItem = $item['cantidad'] * $item['precio'];
                    
                    $conn->prepare($queryDetalle)->execute([
                        $ventaId,
                        $item['id'],
                        $item['cantidad'],
                        $item['precio'],
                        0, // Descuento por item
                        $subtotalItem
                    ]);
                    
                    // Actualizar stock
                    $queryUpdateStock = "UPDATE Productos SET Stock = Stock - ? WHERE ProductoID = ?";
                    $conn->prepare($queryUpdateStock)->execute([$item['cantidad'], $item['id']]);
                    
                    // Registrar movimiento de inventario
                    $queryMovimiento = "INSERT INTO InventarioMovimientos (
                        ProductoID, Tipo, Cantidad, PrecioUnitario, UsuarioID, Referencia
                    ) VALUES (?, 'Salida', ?, ?, 1, ?)";
                    
                    $conn->prepare($queryMovimiento)->execute([
                        $item['id'],
                        $item['cantidad'],
                        $item['precio'],
                        "Venta #$ventaId"
                    ]);
                } elseif ($item['tipo'] === 'servicio') {
                    // Registrar servicio
                    $queryServicio = "INSERT INTO VentaServicios (
                        VentaID, Tipo, ItemID, Cantidad, PrecioUnitario, Descuento, Subtotal
                    ) VALUES (?, 'Servicio', ?, ?, ?, ?, ?)";
                    
                    $subtotalItem = $item['cantidad'] * $item['precio'];
                    
                    $conn->prepare($queryServicio)->execute([
                        $ventaId,
                        $item['id'],
                        $item['cantidad'],
                        $item['precio'],
                        0, // Descuento por item
                        $subtotalItem
                    ]);
                }
            }
            
            // Confirmar transacción
            $conn->commit();
            
            header("Location: listar.php?success=1");
            exit();
        }
    } catch(PDOException $e) {
        $conn->rollBack();
        header("Location: nueva.php?error=2");
        exit();
    } catch(Exception $e) {
        $conn->rollBack();
        header("Location: nueva.php?error=3&message=" . urlencode($e->getMessage()));
        exit();
    }
}

header("Location: listar.php");
exit();
?>