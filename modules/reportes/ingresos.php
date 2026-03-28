
<?php 
define('BASE_URL', '/posada/');
require_once('../../config/database.php');
include('../../includes/header.php');

// Manejar mensajes
if (isset($_GET['success'])) {
    $messages = [
        '1' => 'Venta registrada correctamente',
        '2' => 'Venta anulada correctamente'
    ];
    echo "<div class='alert alert-success'>{$messages[$_GET['success']]}</div>";
}

if (isset($_GET['error'])) {
    echo "<div class='alert alert-danger'>Error al procesar la solicitud</div>";
}

// Obtener parámetros de filtrado
$fechaInicio = $_GET['fechaInicio'] ?? date('Y-m-d', strtotime('-7 days'));
$fechaFin = $_GET['fechaFin'] ?? date('Y-m-d');
$estado = $_GET['estado'] ?? 'Completada';
$busqueda = $_GET['busqueda'] ?? '';

// Validar fechas
if ($fechaInicio > $fechaFin) {
    $temp = $fechaInicio;
    $fechaInicio = $fechaFin;
    $fechaFin = $temp;
}

// Construir consulta SQL para ventas
$query = "SELECT v.*, 
          CONCAT(c.Nombre, ' ', c.Apellido) as Cliente,
          u.NombreUsuario as Vendedor
          FROM Ventas v
          LEFT JOIN Clientes c ON v.ClienteID = c.ClienteID
          JOIN Usuarios u ON v.UsuarioID = u.UsuarioID
          WHERE v.FechaHora BETWEEN ? AND ?";

$params = [$fechaInicio . ' 00:00:00', $fechaFin . ' 23:59:59'];

// Agregar condición de estado si no es vacío
if (!empty($estado)) {
    $query .= " AND v.Estado = ?";
    $params[] = $estado;
}

// Agregar condición de búsqueda si existe
if (!empty($busqueda)) {
    $query .= " AND (v.VentaID LIKE ? OR c.Nombre LIKE ? OR c.Apellido LIKE ? OR u.NombreUsuario LIKE ? OR v.Tipo LIKE ?)";
    $searchTerm = "%$busqueda%";
    $params = array_merge($params, [$searchTerm, $searchTerm, $searchTerm, $searchTerm, $searchTerm]);
}

$query .= " ORDER BY v.FechaHora DESC";

$stmt = $conn->prepare($query);
$stmt->execute($params);
$ventas = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Función para calcular costos por tipo de venta
function calcularCostosPorTipo($conn, $tipo, $fechaInicio, $fechaFin) {
    $resultados = [
        'totalVentas' => 0,
        'totalCostos' => 0,
        'totalGanancias' => 0,
        'cantidad' => 0
    ];
    
    switch($tipo) {
        case 'Platillos':
            $query = "SELECT 
                        SUM(od.Cantidad) as cantidad,
                        SUM(od.Cantidad * p.CostoFabricacion) as totalCostos,
                        SUM(od.Cantidad * od.PrecioUnitario) as totalVentas,
                        SUM(od.Cantidad * (od.PrecioUnitario - p.CostoFabricacion)) as totalGanancias
                      FROM OrdenDetalles od
                      JOIN Platillos p ON od.PlatilloID = p.PlatilloID
                      JOIN OrdenesRestaurante o ON od.OrdenID = o.OrdenID
                      JOIN VentasRestaurante vr ON o.OrdenID = vr.OrdenID
                      JOIN Ventas v ON vr.VentaID = v.VentaID
                      WHERE v.FechaHora BETWEEN ? AND ?";
            break;
            
        case 'Habitacion':
            $query = "SELECT 
                        COUNT(*) as cantidad,
                        SUM(h.costom * DATEDIFF(vs.FechaFin, vs.FechaInicio)) as totalCostos,
                        SUM(vs.Subtotal) as totalVentas,
                        SUM(vs.Subtotal - (h.costom * DATEDIFF(vs.FechaFin, vs.FechaInicio))) as totalGanancias
                      FROM VentaServicios vs
                      JOIN Habitaciones h ON vs.ItemID = h.HabitacionID
                      JOIN Ventas v ON vs.VentaID = v.VentaID
                      WHERE vs.Tipo = 'Habitacion' 
                      AND v.FechaHora BETWEEN ? AND ?";
            break;
            
        case 'Servicio':
            $query = "SELECT 
                        COUNT(*) as cantidad,
                        SUM(s.costom * vs.Cantidad) as totalCostos,
                        SUM(vs.Subtotal) as totalVentas,
                        SUM(vs.Subtotal - (s.costom * vs.Cantidad)) as totalGanancias
                      FROM VentaServicios vs
                      JOIN Servicios s ON vs.ItemID = s.ServicioID
                      JOIN Ventas v ON vs.VentaID = v.VentaID
                      WHERE vs.Tipo = 'Servicio' 
                      AND v.FechaHora BETWEEN ? AND ?";
            break;
            
        case 'Paquete':
            $query = "SELECT 
                        COUNT(*) as cantidad,
                        SUM(p.Precio * 0.7) as totalCostos,
                        SUM(vs.Subtotal) as totalVentas,
                        SUM(vs.Subtotal - (p.Precio * 0.7)) as totalGanancias
                      FROM VentaServicios vs
                      JOIN Paquetes p ON vs.ItemID = p.PaqueteID
                      JOIN Ventas v ON vs.VentaID = v.VentaID
                      WHERE vs.Tipo = 'Paquete' 
                      AND v.FechaHora BETWEEN ? AND ?";
            break;
            
        case 'Producto':
            $query = "SELECT 
                        SUM(vd.Cantidad) as cantidad,
                        SUM(vd.Cantidad * pr.PrecioCompra) as totalCostos,
                        SUM(vd.Subtotal) as totalVentas,
                        SUM(vd.Subtotal - (vd.Cantidad * pr.PrecioCompra)) as totalGanancias
                      FROM VentaDetalles vd
                      JOIN Productos pr ON vd.ProductoID = pr.ProductoID
                      JOIN Ventas v ON vd.VentaID = v.VentaID
                      WHERE v.Tipo = 'Producto'
                      AND v.FechaHora BETWEEN ? AND ?";
            break;
    }
    
    $stmt = $conn->prepare($query);
    $stmt->execute([$fechaInicio . ' 00:00:00', $fechaFin . ' 23:59:59']);
    $data = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($data) {
        $resultados = [
            'totalVentas' => $data['totalVentas'] ?? 0,
            'totalCostos' => $data['totalCostos'] ?? 0,
            'totalGanancias' => $data['totalGanancias'] ?? 0,
            'cantidad' => $data['cantidad'] ?? 0
        ];
    }
    
    return $resultados;
}

// Obtener estadísticas por tipo
$statsPlatillos = calcularCostosPorTipo($conn, 'Platillos', $fechaInicio, $fechaFin);
$statsHabitaciones = calcularCostosPorTipo($conn, 'Habitacion', $fechaInicio, $fechaFin);
$statsServicios = calcularCostosPorTipo($conn, 'Servicio', $fechaInicio, $fechaFin);
$statsPaquetes = calcularCostosPorTipo($conn, 'Paquete', $fechaInicio, $fechaFin);
$statsProductos = calcularCostosPorTipo($conn, 'Producto', $fechaInicio, $fechaFin);

// Calcular total general
$totalVentas = $statsPlatillos['totalVentas'] + $statsHabitaciones['totalVentas'] + 
               $statsServicios['totalVentas'] + $statsPaquetes['totalVentas'] + 
               $statsProductos['totalVentas'];
               
$totalCostos = $statsPlatillos['totalCostos'] + $statsHabitaciones['totalCostos'] + 
               $statsServicios['totalCostos'] + $statsPaquetes['totalCostos'] + 
               $statsProductos['totalCostos'];
               
$totalGanancias = $statsPlatillos['totalGanancias'] + $statsHabitaciones['totalGanancias'] + 
                  $statsServicios['totalGanancias'] + $statsPaquetes['totalGanancias'] + 
                  $statsProductos['totalGanancias'];

// Función para obtener detalles de costos por venta
function obtenerDetallesCostosVenta($conn, $ventaID, $tipoVenta) {
    $detalles = [];
    
    switch($tipoVenta) {
        case 'Restaurante':
            $query = "SELECT 
                        p.Nombre as item,
                        od.Cantidad,
                        od.PrecioUnitario as precio,
                        p.CostoFabricacion as costo_unitario,
                        (od.Cantidad * p.CostoFabricacion) as costo_total,
                        (od.Cantidad * od.PrecioUnitario) as venta_total,
                        (od.Cantidad * (od.PrecioUnitario - p.CostoFabricacion)) as ganancia
                      FROM OrdenDetalles od
                      JOIN Platillos p ON od.PlatilloID = p.PlatilloID
                      JOIN OrdenesRestaurante o ON od.OrdenID = o.OrdenID
                      JOIN VentasRestaurante vr ON o.OrdenID = vr.OrdenID
                      WHERE vr.VentaID = ?";
            break;
            
        case 'Habitacion':
            $query = "SELECT 
                        CONCAT('Hab. ', h.Numero, ' (', th.Nombre, ')') as item,
                        1 as Cantidad,
                        vs.PrecioUnitario as precio,
                        h.costom as costo_unitario,
                        (h.costom * DATEDIFF(vs.FechaFin, vs.FechaInicio)) as costo_total,
                        vs.Subtotal as venta_total,
                        (vs.Subtotal - (h.costom * DATEDIFF(vs.FechaFin, vs.FechaInicio))) as ganancia
                      FROM VentaServicios vs
                      JOIN Habitaciones h ON vs.ItemID = h.HabitacionID
                      JOIN TiposHabitacion th ON h.TipoHabitacionID = th.TipoHabitacionID
                      WHERE vs.VentaID = ? AND vs.Tipo = 'Habitacion'";
            break;
            
        case 'Servicio':
            $query = "SELECT 
                        s.Nombre as item,
                        vs.Cantidad,
                        vs.PrecioUnitario as precio,
                        s.costom as costo_unitario,
                        (s.costom * vs.Cantidad) as costo_total,
                        vs.Subtotal as venta_total,
                        (vs.Subtotal - (s.costom * vs.Cantidad)) as ganancia
                      FROM VentaServicios vs
                      JOIN Servicios s ON vs.ItemID = s.ServicioID
                      WHERE vs.VentaID = ? AND vs.Tipo = 'Servicio'";
            break;
            
        case 'Paquete':
            $query = "SELECT 
                        p.Nombre as item,
                        1 as Cantidad,
                        vs.PrecioUnitario as precio,
                        (vs.PrecioUnitario * 0.7) as costo_unitario,
                        (vs.PrecioUnitario * 0.7) as costo_total,
                        vs.Subtotal as venta_total,
                        (vs.Subtotal - (vs.PrecioUnitario * 0.7)) as ganancia
                      FROM VentaServicios vs
                      JOIN Paquetes p ON vs.ItemID = p.PaqueteID
                      WHERE vs.VentaID = ? AND vs.Tipo = 'Paquete'";
            break;
            
        case 'Producto':
            $query = "SELECT 
                        pr.Nombre as item,
                        vd.Cantidad,
                        vd.PrecioUnitario as precio,
                        pr.PrecioCompra as costo_unitario,
                        (vd.Cantidad * pr.PrecioCompra) as costo_total,
                        vd.Subtotal as venta_total,
                        (vd.Subtotal - (vd.Cantidad * pr.PrecioCompra)) as ganancia
                      FROM VentaDetalles vd
                      JOIN Productos pr ON vd.ProductoID = pr.ProductoID
                      WHERE vd.VentaID = ?";
            break;
    }
    
    $stmt = $conn->prepare($query);
    $stmt->execute([$ventaID]);
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}
?>
<div class="container">
    <h2>Reporte de Ventas</h2>
    
    <div class="card mb-4">
        <div class="card-body">
            <form method="get" class="row g-3">
                <div class="col-md-2">
                    <label for="fechaInicio" class="form-label">Fecha Inicio</label>
                    <input type="date" class="form-control" id="fechaInicio" name="fechaInicio" 
                           value="<?= $fechaInicio ?>" max="<?= $fechaFin ?>">
                </div>
                <div class="col-md-2">
                    <label for="fechaFin" class="form-label">Fecha Fin</label>
                    <input type="date" class="form-control" id="fechaFin" name="fechaFin" 
                           value="<?= $fechaFin ?>" min="<?= $fechaInicio ?>">
                </div>
                <div class="col-md-2">
                    <label for="estado" class="form-label">Estado</label>
                    <select class="form-select" id="estado" name="estado">
                        <option value="Completada" <?= $estado == 'Completada' ? 'selected' : '' ?>>Completadas</option>
                        <option value="Pendiente" <?= $estado == 'Pendiente' ? 'selected' : '' ?>>Pendientes</option>
                        <option value="Cancelada" <?= $estado == 'Cancelada' ? 'selected' : '' ?>>Canceladas</option>
                        <option value="">Todas</option>
                    </select>
                </div>
                <div class="col-md-3">
                    <label for="busqueda" class="form-label">Buscar</label>
                    <input type="text" class="form-control" id="busqueda" name="busqueda" 
                           value="<?= htmlspecialchars($busqueda) ?>" placeholder="ID, Cliente, Vendedor o Tipo">
                </div>
                <div class="col-md-3 d-flex align-items-end">
                    <button type="submit" class="btn btn-primary me-2">
                        <i class="fas fa-filter"></i> Filtrar
                    </button>
                    <button type="button" class="btn btn-secondary me-2" onclick="printReport()">
                        <i class="fas fa-print"></i> Imprimir
                    </button>
                    <a href="nueva.php" class="btn btn-success">
                        <i class="fas fa-plus"></i> Nueva
                    </a>
                </div>
            </form>
        </div>
    </div>
    
    <!-- Resumen de estadísticas generales -->
    <div class="row mb-4">
        <div class="col-md-4">
            <div class="card">
                <div class="card-header bg-primary text-white">
                    <h5 class="mb-0">Resumen General</h5>
                </div>
                <div class="card-body">
                    <div class="row mb-2">
                        <div class="col-md-6 fw-bold">Total Ventas:</div>
                        <div class="col-md-6 text-end">$<?= number_format($totalVentas, 2) ?></div>
                    </div>
                    <div class="row mb-2">
                        <div class="col-md-6 fw-bold">Total Costos:</div>
                        <div class="col-md-6 text-end">$<?= number_format($totalCostos, 2) ?></div>
                    </div>
                    <div class="row mb-2">
                        <div class="col-md-6 fw-bold">Total Ganancias:</div>
                        <div class="col-md-6 text-end">$<?= number_format($totalGanancias, 2) ?></div>
                    </div>
                    <div class="row">
                        <div class="col-md-6 fw-bold">% Ganancia:</div>
                        <div class="col-md-6 text-end"><?= $totalVentas > 0 ? number_format(($totalGanancias / $totalVentas) * 100, 2) : '0.00' ?>%</div>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Estadísticas de Platillos -->
        <div class="col-md-4">
            <div class="card">
                <div class="card-header bg-success text-white">
                    <h5 class="mb-0">Platillos</h5>
                </div>
                <div class="card-body">
                    <div class="row mb-2">
                        <div class="col-md-6 fw-bold">Vendidos:</div>
                        <div class="col-md-6 text-end"><?= $statsPlatillos['cantidad'] ?></div>
                    </div>
                    <div class="row mb-2">
                        <div class="col-md-6 fw-bold">Ventas:</div>
                        <div class="col-md-6 text-end">$<?= number_format($statsPlatillos['totalVentas'], 2) ?></div>
                    </div>
                    <div class="row mb-2">
                        <div class="col-md-6 fw-bold">Costos:</div>
                        <div class="col-md-6 text-end">$<?= number_format($statsPlatillos['totalCostos'], 2) ?></div>
                    </div>
                    <div class="row">
                        <div class="col-md-6 fw-bold">% Ganancia:</div>
                        <div class="col-md-6 text-end"><?= $statsPlatillos['totalVentas'] > 0 ? number_format(($statsPlatillos['totalGanancias'] / $statsPlatillos['totalVentas']) * 100, 2) : '0.00' ?>%</div>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Estadísticas de Habitaciones -->
        <div class="col-md-4">
            <div class="card">
                <div class="card-header bg-info text-white">
                    <h5 class="mb-0">Habitaciones</h5>
                </div>
                <div class="card-body">
                    <div class="row mb-2">
                        <div class="col-md-6 fw-bold">Noches:</div>
                        <div class="col-md-6 text-end"><?= $statsHabitaciones['cantidad'] ?></div>
                    </div>
                    <div class="row mb-2">
                        <div class="col-md-6 fw-bold">Ventas:</div>
                        <div class="col-md-6 text-end">$<?= number_format($statsHabitaciones['totalVentas'], 2) ?></div>
                    </div>
                    <div class="row mb-2">
                        <div class="col-md-6 fw-bold">Costos:</div>
                        <div class="col-md-6 text-end">$<?= number_format($statsHabitaciones['totalCostos'], 2) ?></div>
                    </div>
                    <div class="row">
                        <div class="col-md-6 fw-bold">% Ganancia:</div>
                        <div class="col-md-6 text-end"><?= $statsHabitaciones['totalVentas'] > 0 ? number_format(($statsHabitaciones['totalGanancias'] / $statsHabitaciones['totalVentas']) * 100, 2) : '0.00' ?>%</div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Segunda fila de estadísticas -->
    <div class="row mb-4">
        <!-- Estadísticas de Servicios -->
        <div class="col-md-4">
            <div class="card">
                <div class="card-header bg-warning text-dark">
                    <h5 class="mb-0">Servicios</h5>
                </div>
                <div class="card-body">
                    <div class="row mb-2">
                        <div class="col-md-6 fw-bold">Cantidad:</div>
                        <div class="col-md-6 text-end"><?= $statsServicios['cantidad'] ?></div>
                    </div>
                    <div class="row mb-2">
                        <div class="col-md-6 fw-bold">Ventas:</div>
                        <div class="col-md-6 text-end">$<?= number_format($statsServicios['totalVentas'], 2) ?></div>
                    </div>
                    <div class="row mb-2">
                        <div class="col-md-6 fw-bold">Costos:</div>
                        <div class="col-md-6 text-end">$<?= number_format($statsServicios['totalCostos'], 2) ?></div>
                    </div>
                    <div class="row">
                        <div class="col-md-6 fw-bold">% Ganancia:</div>
                        <div class="col-md-6 text-end"><?= $statsServicios['totalVentas'] > 0 ? number_format(($statsServicios['totalGanancias'] / $statsServicios['totalVentas']) * 100, 2) : '0.00' ?>%</div>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Estadísticas de Paquetes -->
        <div class="col-md-4">
            <div class="card">
                <div class="card-header bg-danger text-white">
                    <h5 class="mb-0">Paquetes</h5>
                </div>
                <div class="card-body">
                    <div class="row mb-2">
                        <div class="col-md-6 fw-bold">Vendidos:</div>
                        <div class="col-md-6 text-end"><?= $statsPaquetes['cantidad'] ?></div>
                    </div>
                    <div class="row mb-2">
                        <div class="col-md-6 fw-bold">Ventas:</div>
                        <div class="col-md-6 text-end">$<?= number_format($statsPaquetes['totalVentas'], 2) ?></div>
                    </div>
                    <div class="row mb-2">
                        <div class="col-md-6 fw-bold">Costos:</div>
                        <div class="col-md-6 text-end">$<?= number_format($statsPaquetes['totalCostos'], 2) ?></div>
                    </div>
                    <div class="row">
                        <div class="col-md-6 fw-bold">% Ganancia:</div>
                        <div class="col-md-6 text-end"><?= $statsPaquetes['totalVentas'] > 0 ? number_format(($statsPaquetes['totalGanancias'] / $statsPaquetes['totalVentas']) * 100, 2) : '0.00' ?>%</div>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Estadísticas de Productos -->
        <div class="col-md-4">
            <div class="card">
                <div class="card-header bg-secondary text-white">
                    <h5 class="mb-0">Productos</h5>
                </div>
                <div class="card-body">
                    <div class="row mb-2">
                        <div class="col-md-6 fw-bold">Vendidos:</div>
                        <div class="col-md-6 text-end"><?= $statsProductos['cantidad'] ?></div>
                    </div>
                    <div class="row mb-2">
                        <div class="col-md-6 fw-bold">Ventas:</div>
                        <div class="col-md-6 text-end">$<?= number_format($statsProductos['totalVentas'], 2) ?></div>
                    </div>
                    <div class="row mb-2">
                        <div class="col-md-6 fw-bold">Costos:</div>
                        <div class="col-md-6 text-end">$<?= number_format($statsProductos['totalCostos'], 2) ?></div>
                    </div>
                    <div class="row">
                        <div class="col-md-6 fw-bold">% Ganancia:</div>
                        <div class="col-md-6 text-end"><?= $statsProductos['totalVentas'] > 0 ? number_format(($statsProductos['totalGanancias'] / $statsProductos['totalVentas']) * 100, 2) : '0.00' ?>%</div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <div class="table-responsive">
        <table class="table table-striped table-hover">
            <thead class="">
                <tr>
                    <th>ID</th>
                    <th>Fecha/Hora</th>
                    <th>Cliente</th>
                    <th>Tipo</th>
                    <th>Vendedor</th>
                    <th>Subtotal</th>
                    <th>Impuestos</th>
                    <th>Total</th>
                    <th>Costos</th>
                    <th>Ganancia</th>
                    <th>% Ganancia</th>
                    <th>Estado</th>
                    <th class="no-print">Acciones</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($ventas as $venta): 
                    $estadoClass = [
                        'Completada' => 'success',
                        'Pendiente' => 'warning',
                        'Cancelada' => 'danger'
                    ][$venta['Estado']];
                    
                    $fecha = new DateTime($venta['FechaHora']);
                    
                    // Obtener detalles de costos para esta venta
                    $detallesCostos = obtenerDetallesCostosVenta($conn, $venta['VentaID'], $venta['Tipo']);
                    
                    $costoTotal = 0;
                    $gananciaTotal = 0;
                    
                    foreach ($detallesCostos as $detalle) {
                        $costoTotal += $detalle['costo_total'];
                        $gananciaTotal += $detalle['ganancia'];
                    }
                    
                    $porcentajeGanancia = $venta['Total'] > 0 ? ($gananciaTotal / $venta['Total']) * 100 : 0;
                ?>
                <tr>
                    <td>#<?= $venta['VentaID'] ?></td>
                    <td><?= $fecha->format('d/m/Y H:i') ?></td>
                    <td><?= $venta['Cliente'] ?? 'Consumidor Final' ?></td>
                    <td><?= $venta['Tipo'] ?></td>
                    <td><?= $venta['Vendedor'] ?></td>
                    <td>$<?= number_format($venta['Subtotal'], 2) ?></td>
                    <td>$<?= number_format($venta['Impuesto'], 2) ?></td>
                    <td>$<?= number_format($venta['Total'], 2) ?></td>
                    <td>$<?= number_format($costoTotal, 2) ?></td>
                    <td>$<?= number_format($gananciaTotal, 2) ?></td>
                    <td><?= number_format($porcentajeGanancia, 2) ?>%</td>
                    <td>
                        <span class="badge bg-<?= $estadoClass ?>">
                            <?= $venta['Estado'] ?>
                        </span>
                    </td>
                    <td class="no-print">
                        <a href="detalle.php?id=<?= $venta['VentaID'] ?>" class="btn btn-sm btn-info" title="Ver detalle">
                            <i class="fas fa-eye"></i>
                        </a>
                        <a href="factura.php?id=<?= $venta['VentaID'] ?>" class="btn btn-sm btn-secondary" title="Factura">
                            <i class="fas fa-file-invoice"></i>
                        </a>
                        
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
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
        
        @page {
            size: auto;
            margin: 10mm;
        }
        
        .card {
            margin-bottom: 15px;
            border: 1px solid #ddd;
        }
        
        .card-header {
            color: white !important;
            -webkit-print-color-adjust: exact;
            print-color-adjust: exact;
        }
        
        .bg-primary {
            background-color: #0d6efd !important;
        }
        
        .bg-success {
            background-color: #198754 !important;
        }
        
        .bg-info {
            background-color: #0dcaf0 !important;
        }
        
        .bg-warning {
            background-color: #ffc107 !important;
        }
        
        .bg-danger {
            background-color: #dc3545 !important;
        }
        
        .bg-secondary {
            background-color: #6c757d !important;
        }
    }
</style>

<script>
    function printReport() {
        // Crear contenido para imprimir
        const printContent = document.createElement('div');
        printContent.className = 'print-content';
        
        // Agregar título
        const title = document.createElement('h2');
        title.textContent = 'Reporte de Ventas';
        printContent.appendChild(title);
        
        // Agregar rango de fechas
        const fechaInicio = document.getElementById('fechaInicio').value;
        const fechaFin = document.getElementById('fechaFin').value;
        const subtitle = document.createElement('p');
        subtitle.textContent = `Desde: ${fechaInicio} - Hasta: ${fechaFin}`;
        subtitle.style.textAlign = 'center';
        subtitle.style.marginBottom = '20px';
        printContent.appendChild(subtitle);
        
        // Agregar resumen de estadísticas
        const statsRow1 = document.createElement('div');
        statsRow1.className = 'row mb-4';
        statsRow1.innerHTML = `
            <div class="col-md-4">
                <div class="card">
                    <div class="card-header bg-primary text-white">
                        <h5 class="mb-0">Resumen General</h5>
                    </div>
                    <div class="card-body">
                        <div class="row mb-2">
                            <div class="col-md-6 fw-bold">Total Ventas:</div>
                            <div class="col-md-6 text-end">$<?= number_format($totalVentas, 2) ?></div>
                        </div>
                        <div class="row mb-2">
                            <div class="col-md-6 fw-bold">Total Costos:</div>
                            <div class="col-md-6 text-end">$<?= number_format($totalCostos, 2) ?></div>
                        </div>
                        <div class="row mb-2">
                            <div class="col-md-6 fw-bold">Total Ganancias:</div>
                            <div class="col-md-6 text-end">$<?= number_format($totalGanancias, 2) ?></div>
                        </div>
                        <div class="row">
                            <div class="col-md-6 fw-bold">% Ganancia:</div>
                            <div class="col-md-6 text-end"><?= $totalVentas > 0 ? number_format(($totalGanancias / $totalVentas) * 100, 2) : '0.00' ?>%</div>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="col-md-4">
                <div class="card">
                    <div class="card-header bg-success text-white">
                        <h5 class="mb-0">Platillos</h5>
                    </div>
                    <div class="card-body">
                        <div class="row mb-2">
                            <div class="col-md-6 fw-bold">Vendidos:</div>
                            <div class="col-md-6 text-end"><?= $statsPlatillos['cantidad'] ?></div>
                        </div>
                        <div class="row mb-2">
                            <div class="col-md-6 fw-bold">Ventas:</div>
                            <div class="col-md-6 text-end">$<?= number_format($statsPlatillos['totalVentas'], 2) ?></div>
                        </div>
                        <div class="row mb-2">
                            <div class="col-md-6 fw-bold">Costos:</div>
                            <div class="col-md-6 text-end">$<?= number_format($statsPlatillos['totalCostos'], 2) ?></div>
                        </div>
                        <div class="row">
                            <div class="col-md-6 fw-bold">% Ganancia:</div>
                            <div class="col-md-6 text-end"><?= $statsPlatillos['totalVentas'] > 0 ? number_format(($statsPlatillos['totalGanancias'] / $statsPlatillos['totalVentas']) * 100, 2) : '0.00' ?>%</div>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="col-md-4">
                <div class="card">
                    <div class="card-header bg-info text-white">
                        <h5 class="mb-0">Habitaciones</h5>
                    </div>
                    <div class="card-body">
                        <div class="row mb-2">
                            <div class="col-md-6 fw-bold">Noches:</div>
                            <div class="col-md-6 text-end"><?= $statsHabitaciones['cantidad'] ?></div>
                        </div>
                        <div class="row mb-2">
                            <div class="col-md-6 fw-bold">Ventas:</div>
                            <div class="col-md-6 text-end">$<?= number_format($statsHabitaciones['totalVentas'], 2) ?></div>
                        </div>
                        <div class="row mb-2">
                            <div class="col-md-6 fw-bold">Costos:</div>
                            <div class="col-md-6 text-end">$<?= number_format($statsHabitaciones['totalCostos'], 2) ?></div>
                        </div>
                        <div class="row">
                            <div class="col-md-6 fw-bold">% Ganancia:</div>
                            <div class="col-md-6 text-end"><?= $statsHabitaciones['totalVentas'] > 0 ? number_format(($statsHabitaciones['totalGanancias'] / $statsHabitaciones['totalVentas']) * 100, 2) : '0.00' ?>%</div>
                        </div>
                    </div>
                </div>
            </div>
        `;
        printContent.appendChild(statsRow1);
        
        const statsRow2 = document.createElement('div');
        statsRow2.className = 'row mb-4';
        statsRow2.innerHTML = `
            <div class="col-md-4">
                <div class="card">
                    <div class="card-header bg-warning text-dark">
                        <h5 class="mb-0">Servicios</h5>
                    </div>
                    <div class="card-body">
                        <div class="row mb-2">
                            <div class="col-md-6 fw-bold">Cantidad:</div>
                            <div class="col-md-6 text-end"><?= $statsServicios['cantidad'] ?></div>
                        </div>
                        <div class="row mb-2">
                            <div class="col-md-6 fw-bold">Ventas:</div>
                            <div class="col-md-6 text-end">$<?= number_format($statsServicios['totalVentas'], 2) ?></div>
                        </div>
                        <div class="row mb-2">
                            <div class="col-md-6 fw-bold">Costos:</div>
                            <div class="col-md-6 text-end">$<?= number_format($statsServicios['totalCostos'], 2) ?></div>
                        </div>
                        <div class="row">
                            <div class="col-md-6 fw-bold">% Ganancia:</div>
                            <div class="col-md-6 text-end"><?= $statsServicios['totalVentas'] > 0 ? number_format(($statsServicios['totalGanancias'] / $statsServicios['totalVentas']) * 100, 2) : '0.00' ?>%</div>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="col-md-4">
                <div class="card">
                    <div class="card-header bg-danger text-white">
                        <h5 class="mb-0">Paquetes</h5>
                    </div>
                    <div class="card-body">
                        <div class="row mb-2">
                            <div class="col-md-6 fw-bold">Vendidos:</div>
                            <div class="col-md-6 text-end"><?= $statsPaquetes['cantidad'] ?></div>
                        </div>
                        <div class="row mb-2">
                            <div class="col-md-6 fw-bold">Ventas:</div>
                            <div class="col-md-6 text-end">$<?= number_format($statsPaquetes['totalVentas'], 2) ?></div>
                        </div>
                        <div class="row mb-2">
                            <div class="col-md-6 fw-bold">Costos:</div>
                            <div class="col-md-6 text-end">$<?= number_format($statsPaquetes['totalCostos'], 2) ?></div>
                        </div>
                        <div class="row">
                            <div class="col-md-6 fw-bold">% Ganancia:</div>
                            <div class="col-md-6 text-end"><?= $statsPaquetes['totalVentas'] > 0 ? number_format(($statsPaquetes['totalGanancias'] / $statsPaquetes['totalVentas']) * 100, 2) : '0.00' ?>%</div>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="col-md-4">
                <div class="card">
                    <div class="card-header bg-secondary text-white">
                        <h5 class="mb-0">Productos</h5>
                    </div>
                    <div class="card-body">
                        <div class="row mb-2">
                            <div class="col-md-6 fw-bold">Vendidos:</div>
                            <div class="col-md-6 text-end"><?= $statsProductos['cantidad'] ?></div>
                        </div>
                        <div class="row mb-2">
                            <div class="col-md-6 fw-bold">Ventas:</div>
                            <div class="col-md-6 text-end">$<?= number_format($statsProductos['totalVentas'], 2) ?></div>
                        </div>
                        <div class="row mb-2">
                            <div class="col-md-6 fw-bold">Costos:</div>
                            <div class="col-md-6 text-end">$<?= number_format($statsProductos['totalCostos'], 2) ?></div>
                        </div>
                        <div class="row">
                            <div class="col-md-6 fw-bold">% Ganancia:</div>
                            <div class="col-md-6 text-end"><?= $statsProductos['totalVentas'] > 0 ? number_format(($statsProductos['totalGanancias'] / $statsProductos['totalVentas']) * 100, 2) : '0.00' ?>%</div>
                        </div>
                    </div>
                </div>
            </div>
        `;
        printContent.appendChild(statsRow2);
        
        // Crear tabla para imprimir (sin columna de acciones)
        const originalTable = document.querySelector('table');
        const printTable = document.createElement('table');
        printTable.className = 'table table-striped';
        
        // Crear encabezados (excluyendo la columna de acciones)
        const thead = document.createElement('thead');
        const headerRow = document.createElement('tr');
        
        // Obtener todos los encabezados excepto el último (Acciones)
        const headers = originalTable.querySelectorAll('th:not(:last-child)');
        headers.forEach(header => {
            const th = document.createElement('th');
            th.textContent = header.textContent;
            headerRow.appendChild(th);
        });
        
        thead.appendChild(headerRow);
        printTable.appendChild(thead);
        
        // Crear cuerpo de la tabla (excluyendo la columna de acciones)
        const tbody = document.createElement('tbody');
        const rows = originalTable.querySelectorAll('tbody tr');
        
        rows.forEach(row => {
            const tr = document.createElement('tr');
            // Obtener todas las celdas excepto la última (Acciones)
            const cells = row.querySelectorAll('td:not(:last-child)');
            
            cells.forEach(cell => {
                const td = document.createElement('td');
                td.innerHTML = cell.innerHTML;
                tr.appendChild(td);
            });
            
            tbody.appendChild(tr);
        });
        
        printTable.appendChild(tbody);
        printContent.appendChild(printTable);
        
        // Agregar pie de página
        const footer = document.createElement('div');
        footer.className = 'text-end mt-3';
        footer.innerHTML = `<small>Impreso el ${new Date().toLocaleString()}</small>`;
        printContent.appendChild(footer);
        
        // Abrir ventana de impresión
        const originalContent = document.body.innerHTML;
        document.body.innerHTML = printContent.outerHTML;
        window.print();
        document.body.innerHTML = originalContent;
    }
</script>

<?php include('../../includes/footer.php'); ?>