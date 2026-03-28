<?php
require_once('../../config/database.php');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        if (isset($_POST['agregar'])) {
            // Validar y sanitizar datos
            $codigo = trim($_POST['codigo']);
            $nombre = trim($_POST['nombre']);
            $categoria = intval($_POST['categoria']);
            $proveedor = !empty($_POST['proveedor']) ? intval($_POST['proveedor']) : null;
            $descripcion = trim($_POST['descripcion']);
            $precio_compra = floatval($_POST['precioCompra']);
            $precio_venta = floatval($_POST['precioVenta']);
            $stock = intval($_POST['stock']);
            $stock_minimo = intval($_POST['stockMinimo']);
            $unidad_medida = $_POST['unidadMedida'];
            
            // Validaciones básicas
            if (empty($nombre) || $precio_compra <= 0 || $precio_venta <= 0 || $stock < 0 || $stock_minimo < 0) {
                throw new Exception("Datos inválidos");
            }
            
            // Iniciar transacción
            $conn->beginTransaction();
            
            // Insertar producto (incluyendo Activo = 1)
            $query = "INSERT INTO Productos (
                CodigoBarras, Nombre, Descripcion, CategoriaID, ProveedorID,
                PrecioCompra, PrecioVenta, Stock, StockMinimo, UnidadMedida, Activo
            ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 1)";
            
            $stmt = $conn->prepare($query);
            $stmt->execute([
                $codigo, $nombre, $descripcion, $categoria, $proveedor,
                $precio_compra, $precio_venta, $stock, $stock_minimo, $unidad_medida
            ]);
            
            // Obtener ID del producto insertado
            $producto_id = $conn->lastInsertId();
            
            // Registrar movimiento de inventario
            $query_mov = "INSERT INTO InventarioMovimientos (
                ProductoID, Tipo, Cantidad, PrecioUnitario, UsuarioID, Referencia
            ) VALUES (?, 'Entrada', ?, ?, ?, 'Registro inicial')";
            
            // Asumimos que el usuario está en sesión, si no, usar 1 como default
            $usuario_id = isset($_SESSION['user_id']) ? $_SESSION['user_id'] : 1;
            
            $stmt_mov = $conn->prepare($query_mov);
            $stmt_mov->execute([$producto_id, $stock, $precio_compra, $usuario_id]);
            
            // Confirmar transacción
            $conn->commit();
            
            // Redireccionar con mensaje de éxito
            header("Location: listar.php?success=1");
            exit();
        }
        
// ... (código anterior se mantiene igual)

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        if (isset($_POST['editar'])) {
            // Obtener y validar datos
            $id = intval($_POST['id']);
            $codigo = trim($_POST['codigo']);
            $nombre = trim($_POST['nombre']);
            $categoria = intval($_POST['categoria']);
            $proveedor = !empty($_POST['proveedor']) ? intval($_POST['proveedor']) : null;
            $descripcion = trim($_POST['descripcion']);
            $precio_compra = floatval($_POST['precioCompra']);
            $precio_venta = floatval($_POST['precioVenta']);
            $nuevo_stock = intval($_POST['stock']);
            $stock_minimo = intval($_POST['stockMinimo']);
            $unidad_medida = $_POST['unidadMedida'];
            $activo = intval($_POST['activo']);

            // Validaciones básicas
            if (empty($nombre) || $precio_compra <= 0 || $precio_venta <= 0 || $nuevo_stock < 0 || $stock_minimo < 0) {
                throw new Exception("Datos inválidos");
            }

            // Iniciar transacción
            $conn->beginTransaction();

            // 1. Obtener stock actual para comparar
            $query = "SELECT Stock FROM Productos WHERE ProductoID = ?";
            $stmt = $conn->prepare($query);
            $stmt->execute([$id]);
            $stock_actual = $stmt->fetchColumn();

            // 2. Actualizar producto
            $query = "UPDATE Productos SET 
                      CodigoBarras = ?, Nombre = ?, Descripcion = ?, CategoriaID = ?, ProveedorID = ?,
                      PrecioCompra = ?, PrecioVenta = ?, Stock = ?, StockMinimo = ?, UnidadMedida = ?, Activo = ?
                      WHERE ProductoID = ?";
            
            $stmt = $conn->prepare($query);
            $stmt->execute([
                $codigo, $nombre, $descripcion, $categoria, $proveedor,
                $precio_compra, $precio_venta, $nuevo_stock, $stock_minimo, $unidad_medida, $activo,
                $id
            ]);

            // 3. Registrar movimiento de inventario si el stock cambió
            if ($nuevo_stock != $stock_actual) {
                $diferencia = $nuevo_stock - $stock_actual;
                $tipo_movimiento = ($diferencia > 0) ? 'Ajuste Positivo' : 'Ajuste Negativo';
                
                $query_mov = "INSERT INTO InventarioMovimientos (
                    ProductoID, Tipo, Cantidad, PrecioUnitario, UsuarioID, Referencia, Notas
                ) VALUES (?, ?, ?, ?, ?, ?, ?)";
                
                $usuario_id = isset($_SESSION['user_id']) ? $_SESSION['user_id'] : 1;
                $referencia = "Ajuste manual desde edición";
                $notas = "Stock anterior: $stock_actual. Nuevo stock: $nuevo_stock";
                
                $stmt_mov = $conn->prepare($query_mov);
                $stmt_mov->execute([
                    $id, $tipo_movimiento, abs($diferencia), $precio_compra, 
                    $usuario_id, $referencia, $notas
                ]);
            }

            // Confirmar transacción
            $conn->commit();

            header("Location: listar.php?success=2");
            exit();
        }
    } catch(PDOException $e) {
        // Revertir transacción en caso de error
        if ($conn->inTransaction()) {
            $conn->rollBack();
        }
        error_log("Error al actualizar producto: " . $e->getMessage());
        header("Location: editar.php?id=$id&error=1");
        exit();
    } catch(Exception $e) {
        if ($conn->inTransaction()) {
            $conn->rollBack();
        }
        header("Location: editar.php?id=$id&error=1");
        exit();
    }
}
// ... (resto del código se mantiene igual)
    } catch(Exception $e) {
        // Revertir transacción en caso de error
        if ($conn->inTransaction()) {
            $conn->rollBack();
        }
        header("Location: agregar.php?error=1");
        exit();
    }
}

// Si no es POST o no hay acción válida
header("Location: listar.php");
exit();
?>