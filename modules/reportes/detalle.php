<?php 
define('BASE_URL', '/posada/');
require_once('../../config/database.php');
include('../../includes/header.php');

if (!isset($_GET['id'])) {
    header("Location: listar.php");
    exit();
}

$ventaId = $_GET['id'];

// Obtener datos de la venta
$queryVenta = "SELECT v.*, 
               CONCAT(c.Nombre, ' ', c.Apellido) as ClienteNombre,
               u.NombreUsuario as Vendedor
               FROM Ventas v
               LEFT JOIN Clientes c ON v.ClienteID = c.ClienteID
               JOIN Usuarios u ON v.UsuarioID = u.UsuarioID
               WHERE v.VentaID = ?";
$stmtVenta = $conn->prepare($queryVenta);
$stmtVenta->execute([$ventaId]);
$venta = $stmtVenta->fetch(PDO::FETCH_ASSOC);

if (!$venta) {
    header("Location: listar.php?error=1");
    exit();
}

// Obtener items de la venta con costos y ganancias
$items = [];
$totalCostos = 0;
$totalGanancias = 0;

// Función para obtener costos de productos
function obtenerCostoProducto($conn, $productoId) {
    $query = "SELECT PrecioCompra FROM Productos WHERE ProductoID = ?";
    $stmt = $conn->prepare($query);
    $stmt->execute([$productoId]);
    $producto = $stmt->fetch(PDO::FETCH_ASSOC);
    return $producto ? $producto['PrecioCompra'] : 0;
}

// Función para obtener costos de platillos
function obtenerCostoPlatillo($conn, $platilloId) {
    $query = "SELECT CostoFabricacion FROM Platillos WHERE PlatilloID = ?";
    $stmt = $conn->prepare($query);
    $stmt->execute([$platilloId]);
    $platillo = $stmt->fetch(PDO::FETCH_ASSOC);
    return $platillo ? $platillo['CostoFabricacion'] : 0;
}

// Función para obtener costos de servicios
function obtenerCostoServicio($conn, $servicioId) {
    $query = "SELECT costom FROM Servicios WHERE ServicioID = ?";
    $stmt = $conn->prepare($query);
    $stmt->execute([$servicioId]);
    $servicio = $stmt->fetch(PDO::FETCH_ASSOC);
    return $servicio ? $servicio['costom'] : 0;
}

// Función para obtener costos de habitaciones
function obtenerCostoHabitacion($conn, $habitacionId, $noches) {
    $query = "SELECT costom FROM Habitaciones WHERE HabitacionID = ?";
    $stmt = $conn->prepare($query);
    $stmt->execute([$habitacionId]);
    $habitacion = $stmt->fetch(PDO::FETCH_ASSOC);
    return $habitacion ? $habitacion['costom'] * $noches : 0;
}

// Función para obtener costos de paquetes (asumimos 70% del precio como costo)
function obtenerCostoPaquete($precio) {
    return $precio * 0.7;
}

// Productos
$queryItems = "SELECT * FROM VentaDetalles WHERE VentaID = ?";
$stmtItems = $conn->prepare($queryItems);
$stmtItems->execute([$ventaId]);
$itemsProductos = $stmtItems->fetchAll(PDO::FETCH_ASSOC);

foreach ($itemsProductos as $item) {
    $queryProducto = "SELECT Nombre FROM Productos WHERE ProductoID = ?";
    $stmtProducto = $conn->prepare($queryProducto);
    $stmtProducto->execute([$item['ProductoID']]);
    $producto = $stmtProducto->fetch(PDO::FETCH_ASSOC);
    
    $costoUnitario = obtenerCostoProducto($conn, $item['ProductoID']);
    $costoTotal = $costoUnitario * $item['Cantidad'];
    $ganancia = $item['Subtotal'] - $costoTotal;
    
    $totalCostos += $costoTotal;
    $totalGanancias += $ganancia;
    
    $items[] = [
        'tipo' => 'Producto',
        'nombre' => $producto['Nombre'],
        'cantidad' => $item['Cantidad'],
        'precio' => $item['PrecioUnitario'],
        'descuento' => $item['Descuento'],
        'subtotal' => $item['Subtotal'],
        'costo' => $costoTotal,
        'ganancia' => $ganancia
    ];
}

// Servicios y habitaciones
$queryServicios = "SELECT * FROM VentaServicios WHERE VentaID = ?";
$stmtServicios = $conn->prepare($queryServicios);
$stmtServicios->execute([$ventaId]);
$itemsServicios = $stmtServicios->fetchAll(PDO::FETCH_ASSOC);

foreach ($itemsServicios as $item) {
    if ($item['Tipo'] == 'Servicio') {
        $queryServicio = "SELECT Nombre FROM Servicios WHERE ServicioID = ?";
        $stmtServicio = $conn->prepare($queryServicio);
        $stmtServicio->execute([$item['ItemID']]);
        $servicio = $stmtServicio->fetch(PDO::FETCH_ASSOC);
        
        $costoUnitario = obtenerCostoServicio($conn, $item['ItemID']);
        $costoTotal = $costoUnitario * $item['Cantidad'];
        $ganancia = $item['Subtotal'] - $costoTotal;
        
        $totalCostos += $costoTotal;
        $totalGanancias += $ganancia;
        
        $items[] = [
            'tipo' => 'Servicio',
            'nombre' => $servicio['Nombre'],
            'cantidad' => $item['Cantidad'],
            'precio' => $item['PrecioUnitario'],
            'descuento' => $item['Descuento'],
            'subtotal' => $item['Subtotal'],
            'costo' => $costoTotal,
            'ganancia' => $ganancia
        ];
    } elseif ($item['Tipo'] == 'Habitacion') {
        $queryHabitacion = "SELECT h.Numero, t.Nombre as Tipo 
                            FROM Habitaciones h
                            JOIN TiposHabitacion t ON h.TipoHabitacionID = t.TipoHabitacionID
                            WHERE h.HabitacionID = ?";
        $stmtHabitacion = $conn->prepare($queryHabitacion);
        $stmtHabitacion->execute([$item['ItemID']]);
        $habitacion = $stmtHabitacion->fetch(PDO::FETCH_ASSOC);
        
        $noches = (new DateTime($item['FechaFin']))->diff(new DateTime($item['FechaInicio']))->days;
        $costoTotal = obtenerCostoHabitacion($conn, $item['ItemID'], $noches);
        $ganancia = $item['Subtotal'] - $costoTotal;
        
        $totalCostos += $costoTotal;
        $totalGanancias += $ganancia;
        
        $items[] = [
            'tipo' => 'Habitación',
            'nombre' => "Hab. {$habitacion['Numero']} ({$habitacion['Tipo']})",
            'cantidad' => $item['Cantidad'],
            'precio' => $item['PrecioUnitario'],
            'descuento' => $item['Descuento'],
            'subtotal' => $item['Subtotal'],
            'fechaInicio' => $item['FechaInicio'],
            'fechaFin' => $item['FechaFin'],
            'costo' => $costoTotal,
            'ganancia' => $ganancia
        ];
    } elseif ($item['Tipo'] == 'Paquete') {
        $queryPaquete = "SELECT Nombre FROM Paquetes WHERE PaqueteID = ?";
        $stmtPaquete = $conn->prepare($queryPaquete);
        $stmtPaquete->execute([$item['ItemID']]);
        $paquete = $stmtPaquete->fetch(PDO::FETCH_ASSOC);
        
        $costoTotal = obtenerCostoPaquete($item['Subtotal']);
        $ganancia = $item['Subtotal'] - $costoTotal;
        
        $totalCostos += $costoTotal;
        $totalGanancias += $ganancia;
        
        $items[] = [
            'tipo' => 'Paquete',
            'nombre' => $paquete['Nombre'],
            'cantidad' => $item['Cantidad'],
            'precio' => $item['PrecioUnitario'],
            'descuento' => $item['Descuento'],
            'subtotal' => $item['Subtotal'],
            'fechaInicio' => $item['FechaInicio'],
            'fechaFin' => $item['FechaFin'],
            'costo' => $costoTotal,
            'ganancia' => $ganancia
        ];
    }
}

// Platillos (dishes) from restaurant orders
$queryPlatillos = "SELECT vd.*, p.Nombre as PlatilloNombre 
                   FROM VentasRestaurante vr
                   JOIN OrdenDetalles vd ON vr.OrdenID = vd.OrdenID
                   JOIN Platillos p ON vd.PlatilloID = p.PlatilloID
                   WHERE vr.VentaID = ?";
$stmtPlatillos = $conn->prepare($queryPlatillos);
$stmtPlatillos->execute([$ventaId]);
$itemsPlatillos = $stmtPlatillos->fetchAll(PDO::FETCH_ASSOC);

foreach ($itemsPlatillos as $item) {
    $costoUnitario = obtenerCostoPlatillo($conn, $item['PlatilloID']);
    $costoTotal = $costoUnitario * $item['Cantidad'];
    $ganancia = ($item['PrecioUnitario'] * $item['Cantidad']) - $costoTotal;
    
    $totalCostos += $costoTotal;
    $totalGanancias += $ganancia;
    
    $items[] = [
        'tipo' => 'Platillo',
        'nombre' => $item['PlatilloNombre'],
        'cantidad' => $item['Cantidad'],
        'precio' => $item['PrecioUnitario'],
        'descuento' => 0.00,
        'subtotal' => $item['PrecioUnitario'] * $item['Cantidad'],
        'notas' => $item['Notas'],
        'costo' => $costoTotal,
        'ganancia' => $ganancia
    ];
}

$fecha = new DateTime($venta['FechaHora']);
?>

<div class="container">
    <h2>Detalle de Venta #<?= $ventaId ?></h2>
    
    <div class="row mb-4">
        <div class="col-md-6">
            <div class="card">
                <div class="card-header bg-primary text-white">
                    <h5 class="mb-0">Información de la Venta</h5>
                </div>
                <div class="card-body">
                    <div class="row mb-2">
                        <div class="col-md-4 fw-bold">Fecha:</div>
                        <div class="col-md-8"><?= $fecha->format('d/m/Y H:i') ?></div>
                    </div>
                    <div class="row mb-2">
                        <div class="col-md-4 fw-bold">Cliente:</div>
                        <div class="col-md-8"><?= $venta['ClienteNombre'] ?? 'Consumidor Final' ?></div>
                    </div>
                    <div class="row mb-2">
                        <div class="col-md-4 fw-bold">Vendedor:</div>
                        <div class="col-md-8"><?= $venta['Vendedor'] ?></div>
                    </div>
                    <div class="row mb-2">
                        <div class="col-md-4 fw-bold">Método Pago:</div>
                        <div class="col-md-8"><?= $venta['MetodoPago'] ?></div>
                    </div>
                    <div class="row">
                        <div class="col-md-4 fw-bold">Estado:</div>
                        <div class="col-md-8">
                            <span class="badge bg-<?= $venta['Estado'] == 'Completada' ? 'success' : ($venta['Estado'] == 'Pendiente' ? 'warning' : 'danger') ?>">
                                <?= $venta['Estado'] ?>
                            </span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-6">
            <div class="card">
                <div class="card-header bg-primary text-white">
                    <h5 class="mb-0">Resumen Financiero</h5>
                </div>
                <div class="card-body">
                    <div class="row mb-2">
                        <div class="col-md-6 fw-bold">Subtotal:</div>
                        <div class="col-md-6 text-end">$<?= number_format($venta['Subtotal'], 2) ?></div>
                    </div>
                    <div class="row mb-2">
                        <div class="col-md-6 fw-bold">Descuento:</div>
                        <div class="col-md-6 text-end">$<?= number_format($venta['Descuento'], 2) ?></div>
                    </div>
                    <div class="row mb-2">
                        <div class="col-md-6 fw-bold">Impuestos:</div>
                        <div class="col-md-6 text-end">$<?= number_format($venta['Impuesto'], 2) ?></div>
                    </div>
                    <div class="row mb-2 fw-bold">
                        <div class="col-md-6">Total:</div>
                        <div class="col-md-6 text-end">$<?= number_format($venta['Total'], 2) ?></div>
                    </div>
                    <div class="row mb-2">
                        <div class="col-md-6 fw-bold">Total Costos:</div>
                        <div class="col-md-6 text-end">$<?= number_format($totalCostos, 2) ?></div>
                    </div>
                    <div class="row fw-bold">
                        <div class="col-md-6">Total Ganancias:</div>
                        <div class="col-md-6 text-end">$<?= number_format($totalGanancias, 2) ?></div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <div class="card mb-4">
        <div class="card-header bg-primary text-white">
            <h5 class="mb-0">Detalle de Items con Costos y Ganancias</h5>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-striped">
                    <thead>
                        <tr>
                            <th>Tipo</th>
                            <th>Descripción</th>
                            <th>Cantidad</th>
                            <th>P. Unitario</th>
                            <th>Descuento</th>
                            <th>Subtotal</th>
                            <th>Costo</th>
                            <th>Ganancia</th>
                            <th>% Ganancia</th>
                            <?php if (array_column($items, 'fechaInicio')): ?>
                                <th>Fechas</th>
                            <?php endif; ?>
                            <?php if (array_column($items, 'notas')): ?>
                                <th>Notas</th>
                            <?php endif; ?>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($items as $item): ?>
                        <tr>
                            <td><?= $item['tipo'] ?></td>
                            <td><?= $item['nombre'] ?></td>
                            <td><?= $item['cantidad'] ?></td>
                            <td>$<?= number_format($item['precio'], 2) ?></td>
                            <td>$<?= number_format($item['descuento'], 2) ?></td>
                            <td>$<?= number_format($item['subtotal'], 2) ?></td>
                            <td>$<?= number_format($item['costo'], 2) ?></td>
                            <td>$<?= number_format($item['ganancia'], 2) ?></td>
                            <td><?= $item['subtotal'] > 0 ? number_format(($item['ganancia'] / $item['subtotal']) * 100, 2) : '0.00' ?>%</td>
                            <?php if (isset($item['fechaInicio'])): ?>
                                <td>
                                    <?= (new DateTime($item['fechaInicio']))->format('d/m/Y') ?> - 
                                    <?= (new DateTime($item['fechaFin']))->format('d/m/Y') ?>
                                </td>
                            <?php endif; ?>
                            <?php if (isset($item['notas'])): ?>
                                <td><?= $item['notas'] ?></td>
                            <?php endif; ?>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    
    <div class="d-flex justify-content-between no-print">
        <a href="ingresos.php" class="btn btn-secondary">
            <i class="fas fa-arrow-left"></i> Volver al listado
        </a>
        <div>
            <button type="button" class="btn btn-secondary me-2" onclick="printReport()">
                <i class="fas fa-print"></i> Imprimir
            </button>
            <a href="factura.php?id=<?= $ventaId ?>" class="btn btn-info me-2">
                <i class="fas fa-file-invoice"></i> Ver Factura
            </a>
        
        </div>
    </div>
</div>

<!-- Estilos para impresión -->
<style>
    @media print {
        body * {
            visibility: hidden;
            margin: 0;
            padding: 0;
        }
        
        .print-content, .print-content * {
            visibility: visible;
        }
        
        .print-content {
            position: absolute;
            left: 0;
            top: 0;
            width: 100%;
            padding: 20px;
        }
        
        .no-print, .no-print * {
            display: none !important;
        }
        
        table {
            width: 100%;
            border-collapse: collapse;
            font-size: 12px;
        }
        
        table, th, td {
            border: 1px solid #ddd;
        }
        
        th, td {
            padding: 6px;
            text-align: left;
        }
        
        th {
            background-color: #f2f2f2 !important;
            -webkit-print-color-adjust: exact;
            print-color-adjust: exact;
        }
        
        .card-header {
            background-color: #0d6efd !important;
            color: white !important;
            -webkit-print-color-adjust: exact;
            print-color-adjust: exact;
        }
        
        .badge {
            border: 1px solid #000;
            color: #000;
            background-color: transparent !important;
        }
        
        h2 {
            margin-bottom: 15px;
            text-align: center;
            font-size: 20px;
        }
        
        h5 {
            font-size: 16px;
        }
        
        .card {
            border: 1px solid #ddd;
            margin-bottom: 15px;
        }
        
        .row {
            margin-bottom: 10px;
        }
    }
    
    @page {
        size: auto;
        margin: 10mm;
    }
</style>

<script>
    function printReport() {
        // Crear contenido para imprimir
        const printContent = document.createElement('div');
        printContent.className = 'print-content';
        
        // Agregar título
        const title = document.createElement('h2');
        title.textContent = 'Detalle de Venta #<?= $ventaId ?> - POSADA DEL MAR';
        printContent.appendChild(title);
        
        // Agregar información de la venta
        const infoDiv = document.createElement('div');
        infoDiv.className = 'row mb-4';
        infoDiv.innerHTML = `
            <div class="col-md-6">
                <div class="card">
                    <div class="card-header bg-primary text-white">
                        <h5 class="mb-0">Información de la Venta</h5>
                    </div>
                    <div class="card-body">
                        <div class="row mb-2">
                            <div class="col-md-4 fw-bold">Fecha:</div>
                            <div class="col-md-8"><?= $fecha->format('d/m/Y H:i') ?></div>
                        </div>
                        <div class="row mb-2">
                            <div class="col-md-4 fw-bold">Cliente:</div>
                            <div class="col-md-8"><?= $venta['ClienteNombre'] ?? 'Consumidor Final' ?></div>
                        </div>
                        <div class="row mb-2">
                            <div class="col-md-4 fw-bold">Vendedor:</div>
                            <div class="col-md-8"><?= $venta['Vendedor'] ?></div>
                        </div>
                        <div class="row mb-2">
                            <div class="col-md-4 fw-bold">Método Pago:</div>
                            <div class="col-md-8"><?= $venta['MetodoPago'] ?></div>
                        </div>
                        <div class="row">
                            <div class="col-md-4 fw-bold">Estado:</div>
                            <div class="col-md-8">
                                <span class="badge bg-<?= $venta['Estado'] == 'Completada' ? 'success' : ($venta['Estado'] == 'Pendiente' ? 'warning' : 'danger') ?>">
                                    <?= $venta['Estado'] ?>
                                </span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-6">
                <div class="card">
                    <div class="card-header bg-primary text-white">
                        <h5 class="mb-0">Resumen Financiero</h5>
                    </div>
                    <div class="card-body">
                        <div class="row mb-2">
                            <div class="col-md-6 fw-bold">Subtotal:</div>
                            <div class="col-md-6 text-end">$<?= number_format($venta['Subtotal'], 2) ?></div>
                        </div>
                        <div class="row mb-2">
                            <div class="col-md-6 fw-bold">Descuento:</div>
                            <div class="col-md-6 text-end">$<?= number_format($venta['Descuento'], 2) ?></div>
                        </div>
                        <div class="row mb-2">
                            <div class="col-md-6 fw-bold">Impuestos:</div>
                            <div class="col-md-6 text-end">$<?= number_format($venta['Impuesto'], 2) ?></div>
                        </div>
                        <div class="row mb-2 fw-bold">
                            <div class="col-md-6">Total:</div>
                            <div class="col-md-6 text-end">$<?= number_format($venta['Total'], 2) ?></div>
                        </div>
                        <div class="row mb-2">
                            <div class="col-md-6 fw-bold">Total Costos:</div>
                            <div class="col-md-6 text-end">$<?= number_format($totalCostos, 2) ?></div>
                        </div>
                        <div class="row fw-bold">
                            <div class="col-md-6">Total Ganancias:</div>
                            <div class="col-md-6 text-end">$<?= number_format($totalGanancias, 2) ?></div>
                        </div>
                    </div>
                </div>
            </div>
        `;
        printContent.appendChild(infoDiv);
        
        // Agregar tabla de items
        const itemsDiv = document.createElement('div');
        itemsDiv.className = 'card mb-4';
        itemsDiv.innerHTML = `
            <div class="card-header bg-primary text-white">
                <h5 class="mb-0">Detalle de Items con Costos y Ganancias</h5>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-striped">
                        <thead>
                            <tr>
                                <th>Tipo</th>
                                <th>Descripción</th>
                                <th>Cantidad</th>
                                <th>P. Unitario</th>
                                <th>Descuento</th>
                                <th>Subtotal</th>
                                <th>Costo</th>
                                <th>Ganancia</th>
                                <th>% Ganancia</th>
                                <?php if (array_column($items, 'fechaInicio')): ?>
                                    <th>Fechas</th>
                                <?php endif; ?>
                                <?php if (array_column($items, 'notas')): ?>
                                    <th>Notas</th>
                                <?php endif; ?>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($items as $item): ?>
                            <tr>
                                <td><?= $item['tipo'] ?></td>
                                <td><?= $item['nombre'] ?></td>
                                <td><?= $item['cantidad'] ?></td>
                                <td>$<?= number_format($item['precio'], 2) ?></td>
                                <td>$<?= number_format($item['descuento'], 2) ?></td>
                                <td>$<?= number_format($item['subtotal'], 2) ?></td>
                                <td>$<?= number_format($item['costo'], 2) ?></td>
                                <td>$<?= number_format($item['ganancia'], 2) ?></td>
                                <td><?= $item['subtotal'] > 0 ? number_format(($item['ganancia'] / $item['subtotal']) * 100, 2) : '0.00' ?>%</td>
                                <?php if (isset($item['fechaInicio'])): ?>
                                    <td>
                                        <?= (new DateTime($item['fechaInicio']))->format('d/m/Y') ?> - 
                                        <?= (new DateTime($item['fechaFin']))->format('d/m/Y') ?>
                                    </td>
                                <?php endif; ?>
                                <?php if (isset($item['notas'])): ?>
                                    <td><?= $item['notas'] ?></td>
                                <?php endif; ?>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        `;
        printContent.appendChild(itemsDiv);
        
        // Agregar pie de página
        const footer = document.createElement('div');
        footer.className = 'text-center mt-4 text-muted';
        footer.innerHTML = `
            <p class="mb-1">¡Gracias por su preferencia!</p>
            <p class="mb-1">Este documento es para uso interno. Consérvelo para cualquier aclaración.</p>
            <p class="mb-0">Impreso el ${new Date().toLocaleString('es-ES')}</p>
        `;
        printContent.appendChild(footer);
        
        // Abrir ventana de impresión
        const originalContent = document.body.innerHTML;
        document.body.innerHTML = printContent.outerHTML;
        window.print();
        document.body.innerHTML = originalContent;
    }
</script>

<?php include('../../includes/footer.php'); ?>