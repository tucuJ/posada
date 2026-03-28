<?php
require_once '../../../config/database.php';
require_once '../../../includes/header.php';

// Verificar permisos del usuario
if (!($_SESSION['rol'] === 'Admin' || $_SESSION['rol'] === 'Gerente' || $_SESSION['rol'] === 'Restaurante')) {
    header('Location: /dashboard.php');
    exit;
}

$title = 'Restaurante - Reporte de Ventas y Costos';
$alertType = '';
$alertMessage = '';

// Manejar mensajes de éxito/error
if (isset($_GET['success'])) {
    $alertType = 'success';
    $alertMessage = 'Reporte generado con éxito.';
} elseif (isset($_GET['error'])) {
    $alertType = 'danger';
    $alertMessage = 'Ocurrió un error: ' . htmlspecialchars($_GET['error']);
}

// Obtener parámetros de filtrado
$fechaInicio = isset($_GET['fecha_inicio']) ? $_GET['fecha_inicio'] : date('Y-m-01');
$fechaFin = isset($_GET['fecha_fin']) ? $_GET['fecha_fin'] : date('Y-m-d');
$tipo = isset($_GET['tipo']) ? $_GET['tipo'] : '';
$categoria = isset($_GET['categoria']) ? (int)$_GET['categoria'] : '';

// Obtener categorías para el select
$queryCategorias = "SELECT * FROM CategoriasPlatillos WHERE Activo = 1 ORDER BY Nombre";
$stmtCategorias = $pdo->query($queryCategorias);
$categorias = $stmtCategorias->fetchAll(PDO::FETCH_ASSOC);

// Construir consulta para el reporte con información de costos
$query = "SELECT 
            p.PlatilloID,
            p.Nombre AS PlatilloNombre,
            p.PrecioVenta,
            p.CostoFabricacion,
            c.Nombre AS Categoria,
            COUNT(od.DetalleID) AS CantidadVendida,
            SUM(od.Cantidad) AS TotalPlatillos,
            SUM(od.PrecioUnitario * od.Cantidad) AS TotalVenta,
            SUM(od.Cantidad * p.CostoFabricacion) AS TotalCosto
          FROM OrdenDetalles od
          JOIN Platillos p ON od.PlatilloID = p.PlatilloID
          LEFT JOIN CategoriasPlatillos c ON p.CategoriaPlatilloID = c.CategoriaPlatilloID
          JOIN OrdenesRestaurante o ON od.OrdenID = o.OrdenID
          WHERE DATE(o.FechaHora) BETWEEN ? AND ?
          AND o.Estado = 'Entregado'";

$params = [$fechaInicio, $fechaFin];

if (!empty($tipo)) {
    $query .= " AND o.Tipo = ?";
    $params[] = $tipo;
}

if (!empty($categoria)) {
    $query .= " AND p.CategoriaPlatilloID = ?";
    $params[] = $categoria;
}

$query .= " GROUP BY p.PlatilloID
            ORDER BY TotalVenta DESC";

$stmt = $pdo->prepare($query);
$stmt->execute($params);
$ventas = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Calcular totales
$totalVendido = 0;
$totalPlatillos = 0;
$totalCosto = 0;
foreach ($ventas as $venta) {
    $totalVendido += $venta['TotalVenta'];
    $totalPlatillos += $venta['TotalPlatillos'];
    $totalCosto += $venta['TotalCosto'];
}

// Calcular márgenes
$margenBruto = $totalVendido - $totalCosto;
$porcentajeMargen = ($totalVendido > 0) ? ($margenBruto / $totalVendido) * 100 : 0;
?>

<div class="container mt-4">
    <h1 class="mb-4"><?= $title ?></h1>
    
    <!-- Mostrar alerta si hay mensajes -->
    <?php if ($alertMessage): ?>
        <div class="alert alert-<?= $alertType ?> alert-dismissible fade show" role="alert">
            <?= $alertMessage ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    <?php endif; ?>
    
    <div class="card mb-4">
        <div class="card-header">
            <i class="fas fa-filter"></i> Filtros
        </div>
        <div class="card-body">
            <form method="get" class="row g-3">
                <div class="col-md-3">
                    <label for="fecha_inicio" class="form-label">Fecha Inicio</label>
                    <input type="date" class="form-control" id="fecha_inicio" name="fecha_inicio" 
                           value="<?= htmlspecialchars($fechaInicio) ?>">
                </div>
                
                <div class="col-md-3">
                    <label for="fecha_fin" class="form-label">Fecha Fin</label>
                    <input type="date" class="form-control" id="fecha_fin" name="fecha_fin" 
                           value="<?= htmlspecialchars($fechaFin) ?>">
                </div>
                
                <div class="col-md-3">
                    <label for="tipo" class="form-label">Tipo de Orden</label>
                    <select class="form-select" id="tipo" name="tipo">
                        <option value="">Todos</option>
                        <option value="Restaurante" <?= $tipo === 'Restaurante' ? 'selected' : '' ?>>Restaurante</option>
                        <option value="Habitacion" <?= $tipo === 'Habitacion' ? 'selected' : '' ?>>Habitación</option>
                        <option value="ParaLlevar" <?= $tipo === 'ParaLlevar' ? 'selected' : '' ?>>Para Llevar</option>
                    </select>
                </div>
                
                <div class="col-md-3">
                    <label for="categoria" class="form-label">Categoría</label>
                    <select class="form-select" id="categoria" name="categoria">
                        <option value="">Todas</option>
                        <?php foreach ($categorias as $cat): ?>
                            <option value="<?= $cat['CategoriaPlatilloID'] ?>" <?= $categoria == $cat['CategoriaPlatilloID'] ? 'selected' : '' ?>>
                                <?= htmlspecialchars($cat['Nombre']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                
                <div class="col-12">
                    <button type="submit" class="btn btn-primary me-2">
                        <i class="fas fa-search"></i> Generar Reporte
                    </button>
                    <a href="ventas.php" class="btn btn-secondary">
                        <i class="fas fa-sync-alt"></i> Limpiar
                    </a>
                    <button type="button" class="btn btn-success" onclick="exportToExcel()">
                        <i class="fas fa-file-excel"></i> Exportar a Excel
                    </button>
                </div>
            </form>
        </div>
    </div>
    
    <div class="card">
        <div class="card-header">
            <i class="fas fa-chart-bar"></i> Resultados del Reporte
            <span class="float-end">
                <?= date('d/m/Y', strtotime($fechaInicio)) ?> - <?= date('d/m/Y', strtotime($fechaFin)) ?>
            </span>
        </div>
        <div class="card-body">
            <div class="row mb-4">
                <div class="col-md-3">
                    <div class="card bg-light">
                        <div class="card-body text-center">
                            <h6 class="card-title">Total Vendido</h6>
                            <h4 class="card-text text-success">$<?= number_format($totalVendido, 2) ?></h4>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card bg-light">
                        <div class="card-body text-center">
                            <h6 class="card-title">Total Costos</h6>
                            <h4 class="card-text text-danger">$<?= number_format($totalCosto, 2) ?></h4>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card bg-light">
                        <div class="card-body text-center">
                            <h6 class="card-title">Margen Bruto</h6>
                            <h4 class="card-text text-primary">$<?= number_format($margenBruto, 2) ?></h4>
                            <small><?= number_format($porcentajeMargen, 2) ?>%</small>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card bg-light">
                        <div class="card-body text-center">
                            <h6 class="card-title">Platillos Vendidos</h6>
                            <h4 class="card-text text-info"><?= number_format($totalPlatillos) ?></h4>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="table-responsive">
                <table class="table table-striped table-hover" id="tablaReporte">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Platillo</th>
                            <th>Categoría</th>
                            <th>Precio Venta</th>
                            <th>Costo Unit.</th>
                            <th>Margen</th>
                            <th>Cantidad</th>
                            <th>Total Venta</th>
                            <th>Total Costo</th>
                            <th>Margen Total</th>
                            <th>% Margen</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($ventas as $index => $venta): 
                            $margenUnitario = $venta['PrecioVenta'] - $venta['CostoFabricacion'];
                            $margenTotal = $venta['TotalVenta'] - $venta['TotalCosto'];
                            $porcentajeMargen = ($venta['TotalVenta'] > 0) ? ($margenTotal / $venta['TotalVenta']) * 100 : 0;
                        ?>
                            <tr>
                                <td><?= $index + 1 ?></td>
                                <td><?= htmlspecialchars($venta['PlatilloNombre']) ?></td>
                                <td><?= htmlspecialchars($venta['Categoria']) ?: 'Sin categoría' ?></td>
                                <td>$<?= number_format($venta['PrecioVenta'], 2) ?></td>
                                <td>$<?= number_format($venta['CostoFabricacion'], 2) ?></td>
                                <td>$<?= number_format($margenUnitario, 2) ?></td>
                                <td><?= $venta['TotalPlatillos'] ?></td>
                                <td>$<?= number_format($venta['TotalVenta'], 2) ?></td>
                                <td>$<?= number_format($venta['TotalCosto'], 2) ?></td>
                                <td>$<?= number_format($margenTotal, 2) ?></td>
                                <td><?= number_format($porcentajeMargen, 2) ?>%</td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                    <tfoot>
                        <tr class="table-primary">
                            <th colspan="7">Totales</th>
                            <th>$<?= number_format($totalVendido, 2) ?></th>
                            <th>$<?= number_format($totalCosto, 2) ?></th>
                            <th>$<?= number_format($margenBruto, 2) ?></th>
                            <th><?= number_format($porcentajeMargen, 2) ?>%</th>
                        </tr>
                    </tfoot>
                </table>
            </div>
        </div>
    </div>
</div>

<?php require_once '../../../includes/footer.php'; ?>

<script>
$(document).ready(function() {
    $('#tablaReporte').DataTable({
        language: {
            url: '//cdn.datatables.net/plug-ins/1.10.25/i18n/Spanish.json'
        },
        dom: 'Bfrtip',
        buttons: [
            'copy', 'csv', 'excel', 'pdf', 'print'
        ],
        footerCallback: function(row, data, start, end, display) {
            var api = this.api();
            
            // Actualizar totales en el footer si hay paginación
            if (api.page.info().pages > 1) {
                // Puedes agregar lógica para actualizar los totales si es necesario
            }
        }
    });
});

function exportToExcel() {
    // Crear tabla HTML para exportar
    let html = '<table>';
    
    // Encabezados
    html += '<tr>';
    html += '<th>#</th>';
    html += '<th>Platillo</th>';
    html += '<th>Categoría</th>';
    html += '<th>Precio Venta</th>';
    html += '<th>Costo Unit.</th>';
    html += '<th>Margen</th>';
    html += '<th>Cantidad</th>';
    html += '<th>Total Venta</th>';
    html += '<th>Total Costo</th>';
    html += '<th>Margen Total</th>';
    html += '<th>% Margen</th>';
    html += '</tr>';
    
    // Datos
    <?php foreach ($ventas as $index => $venta): 
        $margenUnitario = $venta['PrecioVenta'] - $venta['CostoFabricacion'];
        $margenTotal = $venta['TotalVenta'] - $venta['TotalCosto'];
        $porcentajeMargen = ($venta['TotalVenta'] > 0) ? ($margenTotal / $venta['TotalVenta']) * 100 : 0;
    ?>
        html += '<tr>';
        html += '<td><?= $index + 1 ?></td>';
        html += '<td><?= htmlspecialchars($venta['PlatilloNombre']) ?></td>';
        html += '<td><?= htmlspecialchars($venta['Categoria']) ?: 'Sin categoría' ?></td>';
        html += '<td>$<?= number_format($venta['PrecioVenta'], 2) ?></td>';
        html += '<td>$<?= number_format($venta['CostoFabricacion'], 2) ?></td>';
        html += '<td>$<?= number_format($margenUnitario, 2) ?></td>';
        html += '<td><?= $venta['TotalPlatillos'] ?></td>';
        html += '<td>$<?= number_format($venta['TotalVenta'], 2) ?></td>';
        html += '<td>$<?= number_format($venta['TotalCosto'], 2) ?></td>';
        html += '<td>$<?= number_format($margenTotal, 2) ?></td>';
        html += '<td><?= number_format($porcentajeMargen, 2) ?>%</td>';
        html += '</tr>';
    <?php endforeach; ?>
    
    // Totales
    html += '<tr>';
    html += '<td colspan="7"><strong>Totales</strong></td>';
    html += '<td><strong>$<?= number_format($totalVendido, 2) ?></strong></td>';
    html += '<td><strong>$<?= number_format($totalCosto, 2) ?></strong></td>';
    html += '<td><strong>$<?= number_format($margenBruto, 2) ?></strong></td>';
    html += '<td><strong><?= number_format($porcentajeMargen, 2) ?>%</strong></td>';
    html += '</tr>';
    
    html += '</table>';
    
    // Crear archivo Excel
    let uri = 'data:application/vnd.ms-excel;base64,';
    let template = '<html xmlns:o="urn:schemas-microsoft-com:office:office" xmlns:x="urn:schemas-microsoft-com:office:excel" xmlns="http://www.w3.org/TR/REC-html40"><head><!--[if gte mso 9]><xml><x:ExcelWorkbook><x:ExcelWorksheets><x:ExcelWorksheet><x:Name>{worksheet}</x:Name><x:WorksheetOptions><x:DisplayGridlines/></x:WorksheetOptions></x:ExcelWorksheet></x:ExcelWorksheets></x:ExcelWorkbook></xml><![endif]--></head><body><table>{table}</table></body></html>';
    
    let base64 = function(s) {
        return window.btoa(unescape(encodeURIComponent(s)));
    };
    
    let format = function(s, c) {
        return s.replace(/{(\w+)}/g, function(m, p) { return c[p]; });
    };
    
    let ctx = {
        worksheet: 'Reporte de Ventas y Costos',
        table: html
    };
    
    let link = document.createElement('a');
    link.download = 'Reporte_Ventas_Costos_<?= date('Ymd') ?>.xls';
    link.href = uri + base64(format(template, ctx));
    link.click();
}
</script>