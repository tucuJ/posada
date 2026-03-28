<?php
require_once '../../config/database.php';
require_once '../../includes/header.php';

if (!isset($_GET['id'])) {
    header("Location: index.php");
    exit();
}

$ventaID = $_GET['id'];

// Obtener información de la venta
$stmt = $conn->prepare("SELECT v.*, c.Nombre AS ClienteNombre, c.Apellido AS ClienteApellido 
                       FROM Ventas v
                       JOIN Clientes c ON v.ClienteID = c.ClienteID
                       WHERE v.VentaID = ?");
$stmt->execute([$ventaID]);
$venta = $stmt->fetch(PDO::FETCH_ASSOC);

// Obtener detalles del paquete
$stmt = $conn->prepare("SELECT * FROM VentaServicios WHERE VentaID = ? AND Tipo = 'Paquete'");
$stmt->execute([$ventaID]);
$paqueteVenta = $stmt->fetch(PDO::FETCH_ASSOC);

// Obtener información del paquete
$stmt = $conn->prepare("SELECT * FROM Paquetes WHERE PaqueteID = ?");
$stmt->execute([$paqueteVenta['ItemID']]);
$paquete = $stmt->fetch(PDO::FETCH_ASSOC);

// Obtener componentes de la venta
$servicios = $conn->prepare("SELECT vs.*, 
                           CASE 
                               WHEN vs.Tipo = 'Habitacion' THEN h.Numero
                               WHEN vs.Tipo = 'Servicio' THEN s.Nombre
                           END AS NombreItem,
                           CASE 
                               WHEN vs.Tipo = 'Habitacion' THEN th.PrecioNoche
                               WHEN vs.Tipo = 'Servicio' THEN s.Precio
                           END AS PrecioUnitario,
                           vs.Cantidad,
                           vs.Subtotal
                           FROM VentaServicios vs
                           LEFT JOIN Habitaciones h ON vs.Tipo = 'Habitacion' AND vs.ItemID = h.HabitacionID
                           LEFT JOIN TiposHabitacion th ON h.TipoHabitacionID = th.TipoHabitacionID
                           LEFT JOIN Servicios s ON vs.Tipo = 'Servicio' AND vs.ItemID = s.ServicioID
                           WHERE vs.VentaID = ? AND vs.Tipo != 'Paquete'");
$servicios->execute([$ventaID]);
$servicios = $servicios->fetchAll(PDO::FETCH_ASSOC);

// Obtener productos de la venta
$productos = $conn->prepare("SELECT vd.*, p.Nombre, p.PrecioVenta 
                            FROM VentaDetalles vd
                            JOIN Productos p ON vd.ProductoID = p.ProductoID
                            WHERE vd.VentaID = ?");
$productos->execute([$ventaID]);
$productos = $productos->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Venta Completada</title>
</head>
<body>
    <div class="container mt-5">
        <div class="card">
            <div class="card-header">
                <h2>Venta de Paquete Completada</h2>
            </div>
            <div class="card-body">
                <div class="row mb-4">
                    <div class="col-md-6">
                        <h4>Información del Cliente</h4>
                        <p><strong>Nombre:</strong> <?php echo htmlspecialchars($venta['ClienteNombre'] . ' ' . $venta['ClienteApellido']); ?></p>
                        <p><strong>Fecha:</strong> <?php echo date('d/m/Y H:i', strtotime($venta['FechaHora'])); ?></p>
                    </div>
                    <div class="col-md-6 text-end">
                        <h4>Factura #<?php echo $ventaID; ?></h4>
                        <p><strong>Método de Pago:</strong> <?php echo htmlspecialchars($venta['MetodoPago']); ?></p>
                    </div>
                </div>
                
                <h4>Paquete: <?php echo htmlspecialchars($paquete['Nombre']); ?></h4>
                <p><?php echo htmlspecialchars($paquete['Descripcion']); ?></p>
                <p><strong>Duración:</strong> <?php echo $paquete['DuracionDias']; ?> días</p>
                <p><strong>Fecha de Inicio:</strong> <?php echo date('d/m/Y', strtotime($paqueteVenta['FechaInicio'])); ?></p>
                <p><strong>Fecha de Fin:</strong> <?php echo date('d/m/Y', strtotime($paqueteVenta['FechaFin'])); ?></p>
                
                <hr>
                
                <h5>Detalles del Paquete</h5>
                <table class="table table-bordered">
                    <thead>
                        <tr>
                            <th>Tipo</th>
                            <th>Item</th>
                            <th>Fecha/Duración</th>
                            <th>Cantidad</th>
                            <th>Precio Unitario</th>
                            <th>Subtotal</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($servicios as $servicio): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($servicio['Tipo']); ?></td>
                            <td><?php echo htmlspecialchars($servicio['NombreItem']); ?></td>
                            <td>
                                <?php if ($servicio['Tipo'] == 'Habitacion'): ?>
                                    <?php echo date('d/m/Y', strtotime($servicio['FechaInicio'])); ?> a <?php echo date('d/m/Y', strtotime($servicio['FechaFin'])); ?>
                                <?php else: ?>
                                    <?php echo $paquete['DuracionDias']; ?> días
                                <?php endif; ?>
                            </td>
                            <td><?php echo $servicio['Cantidad']; ?></td>
                            <td>$<?php echo number_format($servicio['PrecioUnitario'], 2); ?></td>
                            <td>$<?php echo number_format($servicio['Subtotal'], 2); ?></td>
                        </tr>
                        <?php endforeach; ?>
                        
                        <?php foreach ($productos as $producto): ?>
                        <tr>
                            <td>Producto</td>
                            <td><?php echo htmlspecialchars($producto['Nombre']); ?></td>
                            <td>-</td>
                            <td><?php echo $producto['Cantidad']; ?></td>
                            <td>$<?php echo number_format($producto['PrecioVenta'], 2); ?></td>
                            <td>$<?php echo number_format($producto['Subtotal'], 2); ?></td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                    <tfoot>
                        <tr>
                            <th colspan="5" class="text-end">Total:</th>
                            <th>$<?php echo number_format($venta['Total'], 2); ?></th>
                        </tr>
                    </tfoot>
                </table>
                
                <div class="text-center mt-4">
                    <a href="imprimir_factura.php?id=<?php echo $ventaID; ?>" class="btn btn-primary me-2">Imprimir Factura</a>
                    <a href="formulario_paquete.php" class="btn btn-success">Nueva Venta</a>
                </div>
            </div>
        </div>
    </div>
<?php require_once '../../includes/footer.php';
