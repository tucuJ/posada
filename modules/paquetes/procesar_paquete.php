<?php
// Iniciar sesión y verificar autenticación
session_start();
require_once('../../config/database.php');

if (!isset($_SESSION['usuario_id'])) {
    header("Location: login.php");
    exit();
}

// Función principal para procesar paquetes
function procesarPaquete($paqueteID, $clienteID, $usuarioID, $fechaInicio, $metodoPago) {
    global $conn;
    
    try {
        $conn->beginTransaction();
        
        // 1. Obtener información del paquete
        $paquete = obtenerInformacionPaquete($paqueteID);
        
        // 2. Crear la venta principal
        $ventaID = crearVentaPrincipal($clienteID, $usuarioID, $paquete, $metodoPago);
        
        // 3. Registrar el paquete en VentaServicios
        registrarVentaPaquete($ventaID, $paqueteID, $fechaInicio, $paquete);
        
        // 4. Procesar componentes del paquete
        procesarComponentesPaquete($ventaID, $paqueteID, $fechaInicio, $paquete['DuracionDias'], $clienteID, $usuarioID);
        
        $conn->commit();
        return ['success' => true, 'ventaID' => $ventaID];
    } catch (Exception $e) {
        $conn->rollBack();
        return ['success' => false, 'error' => $e->getMessage()];
    }
}

// Función para obtener información del paquete
function obtenerInformacionPaquete($paqueteID) {
    global $conn;
    
    $stmt = $conn->prepare("SELECT * FROM Paquetes WHERE PaqueteID = ?");
    $stmt->execute([$paqueteID]);
    $paquete = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$paquete) {
        throw new Exception("Paquete no encontrado");
    }
    
    return $paquete;
}

// Función para crear la venta principal
function crearVentaPrincipal($clienteID, $usuarioID, $paquete, $metodoPago) {
    global $conn;
    
    $stmt = $conn->prepare("INSERT INTO Ventas (ClienteID, UsuarioID, Tipo, Subtotal, Descuento, Total, MetodoPago, Estado) 
                           VALUES (?, ?, 'Paquete', ?, 0, ?, ?, 'Completada')");
    $stmt->execute([$clienteID, $usuarioID, $paquete['Precio'], $paquete['Precio'], $metodoPago]);
    
    return $conn->lastInsertId();
}

// Función para registrar el paquete en VentaServicios
function registrarVentaPaquete($ventaID, $paqueteID, $fechaInicio, $paquete) {
    global $conn;
    
    $stmt = $conn->prepare("INSERT INTO VentaServicios (VentaID, Tipo, ItemID, FechaInicio, FechaFin, PrecioUnitario, Subtotal) 
                           VALUES (?, 'Paquete', ?, ?, DATE_ADD(?, INTERVAL ? DAY), ?, ?)");
    $stmt->execute([
        $ventaID, 
        $paqueteID, 
        $fechaInicio, 
        $fechaInicio, 
        $paquete['DuracionDias'], 
        $paquete['Precio'], 
        $paquete['Precio']
    ]);
}

// Función para procesar componentes del paquete
function procesarComponentesPaquete($ventaID, $paqueteID, $fechaInicio, $duracionDias, $clienteID, $usuarioID) {
    global $conn;
    
    $stmt = $conn->prepare("SELECT * FROM PaqueteComponentes WHERE PaqueteID = ?");
    $stmt->execute([$paqueteID]);
    $componentes = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    foreach ($componentes as $componente) {
        switch ($componente['Tipo']) {
            case 'Habitacion':
                procesarHabitacionPaquete($ventaID, $componente['ItemID'], $fechaInicio, $duracionDias, $clienteID, $usuarioID);
                break;
                
            case 'Servicio':
                procesarServicioPaquete($ventaID, $componente['ItemID'], $fechaInicio, $duracionDias);
                break;
                
            case 'Producto':
                procesarProductoPaquete($ventaID, $componente['ItemID'], $componente['Cantidad']);
                break;
        }
    }
}

//
function verificarDisponibilidad($habitacionID, $fechaEntrada, $fechaSalida) {
    global $conn;

    $sql = "SELECT * FROM Reservaciones 
            WHERE HabitacionID = :habitacionID 
            AND Estado != 'Cancelada'
            AND NOT (
                FechaSalida <= :fechaEntrada OR
                FechaEntrada >= :fechaSalida
            )";

    $stmt = $conn->prepare($sql);
    $stmt->execute([
        ':habitacionID' => $habitacionID,
        ':fechaEntrada' => $fechaEntrada,
        ':fechaSalida' => $fechaSalida
    ]);

    return $stmt->rowCount() == 0;
}

function procesarHabitacionPaquete($ventaID, $habitacionID, $fechaInicio, $duracionDias, $clienteID, $usuarioID) {
    global $conn;

    $fechaEntrada = date('Y-m-d', strtotime($fechaInicio));
    $fechaSalida = date('Y-m-d', strtotime("$fechaEntrada + $duracionDias days"));

    if (!verificarDisponibilidad($habitacionID, $fechaEntrada, $fechaSalida)) {
        throw new Exception("La habitación no está disponible para las fechas seleccionadas");
    }

    $stmt = $conn->prepare("SELECT h.*, t.PrecioNoche FROM Habitaciones h
                            JOIN TiposHabitacion t ON h.TipoHabitacionID = t.TipoHabitacionID
                            WHERE h.HabitacionID = ?");
    $stmt->execute([$habitacionID]);
    $habitacion = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$habitacion) {
        throw new Exception("Habitación no encontrada");
    }

    $precioTotal = $habitacion['PrecioNoche'] * $duracionDias;

    $stmt = $conn->prepare("INSERT INTO VentaServicios (VentaID, Tipo, ItemID, FechaInicio, FechaFin, PrecioUnitario, Subtotal) 
                            VALUES (?, 'Habitacion', ?, ?, ?, ?, ?)");
    $stmt->execute([
        $ventaID, 
        $habitacionID, 
        $fechaEntrada, 
        $fechaSalida, 
        $habitacion['PrecioNoche'], 
        $precioTotal
    ]);

    $stmt = $conn->prepare("INSERT INTO Reservaciones (ClienteID, HabitacionID, FechaEntrada, FechaSalida, Estado, Total)
                            VALUES (?, ?, ?, ?, 'Completada', ?)");
    $stmt->execute([
        $clienteID,
        $habitacionID,
        $fechaEntrada,
        $fechaSalida,
        $precioTotal
    ]);

}


// Función para procesar servicio en paquete
function procesarServicioPaquete($ventaID, $servicioID, $fechaInicio, $duracionDias) {
    global $conn;
    
    // Obtener información del servicio
    $stmt = $conn->prepare("SELECT * FROM Servicios WHERE ServicioID = ?");
    $stmt->execute([$servicioID]);
    $servicio = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$servicio) {
        throw new Exception("Servicio no encontrado");
    }
    
    $fechaFin = date('Y-m-d', strtotime("$fechaInicio + $duracionDias days"));
    $precioTotal = $servicio['Precio'] * $duracionDias;
    
    // Registrar en VentaServicios
    $stmt = $conn->prepare("INSERT INTO VentaServicios (VentaID, Tipo, ItemID, FechaInicio, FechaFin, PrecioUnitario, Subtotal) 
                           VALUES (?, 'Servicio', ?, ?, ?, ?, ?)");
    $stmt->execute([
        $ventaID, 
        $servicioID, 
        $fechaInicio, 
        $fechaFin, 
        $servicio['Precio'], 
        $precioTotal
    ]);
}

// Función para procesar producto en paquete
function procesarProductoPaquete($ventaID, $productoID, $cantidad) {
    global $conn;
    
    // Obtener información del producto
    $stmt = $conn->prepare("SELECT * FROM Productos WHERE ProductoID = ?");
    $stmt->execute([$productoID]);
    $producto = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$producto) {
        throw new Exception("Producto no encontrado");
    }
    
    // Verificar stock
    if ($producto['Stock'] < $cantidad) {
        throw new Exception("Stock insuficiente para el producto: " . $producto['Nombre']);
    }
    
    $precioTotal = $producto['PrecioVenta'] * $cantidad;
    
    // Registrar en VentaDetalles
    $stmt = $conn->prepare("INSERT INTO VentaDetalles (VentaID, ProductoID, Cantidad, PrecioUnitario, Subtotal) 
                           VALUES (?, ?, ?, ?, ?)");
    $stmt->execute([
        $ventaID, 
        $productoID, 
        $cantidad, 
        $producto['PrecioVenta'], 
        $precioTotal
    ]);
    
    // Actualizar stock
    $stmt = $conn->prepare("UPDATE Productos SET Stock = Stock - ? WHERE ProductoID = ?");
    $stmt->execute([$cantidad, $productoID]);
    
    // Registrar movimiento de inventario
    $stmt = $conn->prepare("INSERT INTO InventarioMovimientos (ProductoID, Tipo, Cantidad, PrecioUnitario, UsuarioID, Referencia) 
                           VALUES (?, 'Salida', ?, ?, ?, ?)");
    $stmt->execute([
        $productoID, 
        $cantidad, 
        $producto['PrecioVenta'], 
        $_SESSION['usuario_id'],
        "Venta Paquete #$ventaID"
    ]);
}

// Procesar el formulario de paquete
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Validar datos de entrada
    $paqueteID = filter_input(INPUT_POST, 'paquete_id', FILTER_VALIDATE_INT);
    $clienteID = filter_input(INPUT_POST, 'cliente_id', FILTER_VALIDATE_INT);
    $fechaInicio = filter_input(INPUT_POST, 'fecha_inicio', FILTER_SANITIZE_STRING);
    $metodoPago = filter_input(INPUT_POST, 'metodo_pago', FILTER_SANITIZE_STRING);
    
    if (!$paqueteID || !$clienteID || !$fechaInicio || !$metodoPago) {
        die('Datos del formulario inválidos');
    }
    
    // Procesar el paquete
    $resultado = procesarPaquete(
        $paqueteID, 
        $clienteID, 
        $_SESSION['usuario_id'], 
        $fechaInicio, 
        $metodoPago
    );
    
    // Manejar el resultado
    if ($resultado['success']) {
        header("Location: venta_completada.php?id=" . $resultado['ventaID']);
        exit();
    } else {
        // Guardar el error en sesión para mostrarlo después
        $_SESSION['error_paquete'] = $resultado['error'];
        header("Location: formulario_paquete.php?error=1");
        exit();
    }
} else {
    // Si no es POST, redirigir
    header("Location: form_paquete.php");
    exit();
}
?>