<?php
require_once '../../../config/database.php';
require_once '../../../includes/header.php';

// Verificar permisos del usuario
$rolesPermitidos = ['Admin', 'Gerente', 'Restaurante', 'Bodega'];
if (!in_array($_SESSION['rol'], $rolesPermitidos)) {
    header('Location: /dashboard.php');
    exit;
}

$title = 'Restaurante - Reporte de Consumo y Costos de Ingredientes';
$alertType = '';
$alertMessage = '';

// Manejar mensajes de éxito/error
if (isset($_GET['success'])) {
    $alertType = 'success';
    $alertMessage = 'Reporte generado con éxito.';
} elseif (isset($_GET['error'])) {
    $alertType = 'danger';
    $alertMessage = htmlspecialchars($_GET['error']);
}

// Obtener y validar parámetros de filtrado
$fechaInicio = filter_input(INPUT_GET, 'fecha_inicio', FILTER_SANITIZE_STRING) ?: date('Y-m-01');
$fechaFin = filter_input(INPUT_GET, 'fecha_fin', FILTER_SANITIZE_STRING) ?: date('Y-m-d');
$categoria = filter_input(INPUT_GET, 'categoria', FILTER_VALIDATE_INT) ?: '';

// Validar fechas
if ($fechaInicio > $fechaFin) {
    $fechaInicio = $fechaFin;
}

// Obtener categorías de platillos para el select
try {
    $stmtCategorias = $pdo->prepare("SELECT * FROM CategoriasPlatillos WHERE Activo = 1 ORDER BY Nombre");
    $stmtCategorias->execute();
    $categorias = $stmtCategorias->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $alertType = 'danger';
    $alertMessage = 'Error al cargar categorías: ' . $e->getMessage();
    $categorias = [];
}

// Construir y ejecutar consulta para el reporte con información de costos
$consumos = [];
$totalIngredientes = 0;
$totalConsumido = 0;
$totalCosto = 0;

try {
    $query = "SELECT 
                i.IngredienteID,
                i.Nombre AS Ingrediente,
                i.UnidadMedida,
                i.Precio,
                i.Cantidad AS CantidadUnidad,
                SUM(ci.CantidadConsumida) AS TotalConsumido,
                COUNT(DISTINCT od.PlatilloID) AS PlatillosDiferentes,
                COUNT(DISTINCT od.OrdenID) AS Ordenes,
                (i.Precio / i.Cantidad) AS CostoUnitario,
                SUM(ci.CantidadConsumida) * (i.Precio / i.Cantidad) AS TotalCosto
              FROM ConsumoIngredientes ci
              JOIN Ingredientes i ON ci.IngredienteID = i.IngredienteID
              JOIN OrdenDetalles od ON ci.OrdenDetalleID = od.DetalleID
              JOIN OrdenesRestaurante o ON od.OrdenID = o.OrdenID
              JOIN Platillos p ON od.PlatilloID = p.PlatilloID
              WHERE DATE(ci.FechaHora) BETWEEN :fechaInicio AND :fechaFin
              AND o.Estado = 'Entregado'";

    $params = [
        ':fechaInicio' => $fechaInicio,
        ':fechaFin' => $fechaFin
    ];

    if (!empty($categoria)) {
        $query .= " AND p.CategoriaPlatilloID = :categoria";
        $params[':categoria'] = $categoria;
    }

    $query .= " GROUP BY i.IngredienteID
                ORDER BY TotalCosto DESC";

    $stmt = $pdo->prepare($query);
    $stmt->execute($params);
    $consumos = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Calcular totales
    $totalIngredientes = count($consumos);
    $totalConsumido = array_sum(array_column($consumos, 'TotalConsumido'));
    $totalCosto = array_sum(array_column($consumos, 'TotalCosto'));

} catch (PDOException $e) {
    $alertType = 'danger';
    $alertMessage = 'Error al generar el reporte: ' . $e->getMessage();
}

// Obtener total de ventas para el mismo período para calcular márgenes
$totalVentas = 0;
try {
    $queryVentas = "SELECT SUM(od.PrecioUnitario * od.Cantidad) AS TotalVentas
                   FROM OrdenDetalles od
                   JOIN OrdenesRestaurante o ON od.OrdenID = o.OrdenID
                   WHERE DATE(o.FechaHora) BETWEEN :fechaInicio AND :fechaFin
                   AND o.Estado = 'Entregado'";
    
    if (!empty($categoria)) {
        $queryVentas .= " AND od.PlatilloID IN (
                         SELECT PlatilloID FROM Platillos 
                         WHERE CategoriaPlatilloID = :categoria)";
    }
    
    $stmtVentas = $pdo->prepare($queryVentas);
    $stmtVentas->execute($params);
    $result = $stmtVentas->fetch(PDO::FETCH_ASSOC);
    $totalVentas = $result['TotalVentas'] ?? 0;
} catch (PDOException $e) {
    // Si hay error, continuamos sin el dato de ventas
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($title) ?></title>
    <link rel="stylesheet" type="text/css" href="https://cdn.datatables.net/1.11.5/css/jquery.dataTables.min.css">
    <link rel="stylesheet" type="text/css" href="https://cdn.datatables.net/buttons/2.2.2/css/buttons.dataTables.min.css">
    <style>
        .costo-cell { background-color: #fff8f8; }
        .margen-cell { background-color: #f8fff8; }
        .table-responsive { overflow-x: auto; }
        .card-summary { height: 100%; }
        .text-small { font-size: 0.85rem; }
    </style>
</head>
<body>
    
    <div class="container mt-4">
        <h1 class="mb-4"><?= htmlspecialchars($title) ?></h1>
        
        <!-- Mostrar alerta si hay mensajes -->
        <?php if ($alertMessage): ?>
            <div class="alert alert-<?= htmlspecialchars($alertType) ?> alert-dismissible fade show" role="alert">
                <?= $alertMessage ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        <?php endif; ?>
        
        <!-- Panel de Filtros -->
        <div class="card mb-4">
            <div class="card-header bg-primary text-white">
                <i class="fas fa-filter"></i> Filtros del Reporte
            </div>
            <div class="card-body">
                <form method="get" class="row g-3">
                    <div class="col-md-4">
                        <label for="fecha_inicio" class="form-label">Fecha Inicio</label>
                        <input type="date" class="form-control" id="fecha_inicio" name="fecha_inicio" 
                               value="<?= htmlspecialchars($fechaInicio) ?>" required>
                    </div>
                    
                    <div class="col-md-4">
                        <label for="fecha_fin" class="form-label">Fecha Fin</label>
                        <input type="date" class="form-control" id="fecha_fin" name="fecha_fin" 
                               value="<?= htmlspecialchars($fechaFin) ?>" required>
                    </div>
                    
                    <div class="col-md-4">
                        <label for="categoria" class="form-label">Categoría Platillo</label>
                        <select class="form-select" id="categoria" name="categoria">
                            <option value="">Todas las categorías</option>
                            <?php foreach ($categorias as $cat): ?>
                                <option value="<?= htmlspecialchars($cat['CategoriaPlatilloID']) ?>" 
                                    <?= $categoria == $cat['CategoriaPlatilloID'] ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($cat['Nombre']) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    
                    <div class="col-12">
                        <div class="d-flex justify-content-between">
                            <div>
                                <button type="submit" class="btn btn-primary me-2">
                                    <i class="fas fa-search"></i> Generar Reporte
                                </button>
                                <a href="consumo_ingredientes.php" class="btn btn-outline-secondary">
                                    <i class="fas fa-sync-alt"></i> Limpiar Filtros
                                </a>
                            </div>
                            <button type="button" class="btn btn-success" onclick="exportToExcel()">
                                <i class="fas fa-file-excel"></i> Exportar a Excel
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
        
        <!-- Resultados del Reporte -->
        <div class="card">
            <div class="card-header bg-primary text-white">
                <i class="fas fa-chart-pie"></i> Resultados del Reporte
                <span class="float-end">
                    <?= date('d/m/Y', strtotime($fechaInicio)) ?> - <?= date('d/m/Y', strtotime($fechaFin)) ?>
                </span>
            </div>
            
            <div class="card-body">
                <!-- Resumen Estadístico -->
                <div class="row mb-4">
                    <div class="col-md-3 mb-3">
                        <div class="card card-summary border-primary">
                            <div class="card-body text-center">
                                <h6 class="card-title text-muted">Ingredientes Diferentes</h6>
                                <h2 class="card-text text-primary"><?= number_format($totalIngredientes) ?></h2>
                            </div>
                        </div>
                    </div>
                    
                    <div class="col-md-3 mb-3">
                        <div class="card card-summary border-success">
                            <div class="card-body text-center">
                                <h6 class="card-title text-muted">Total Consumido</h6>
                                <h2 class="card-text text-success"><?= number_format($totalConsumido, 3) ?></h2>
                            </div>
                        </div>
                    </div>
                    
                    <div class="col-md-3 mb-3">
                        <div class="card card-summary border-danger">
                            <div class="card-body text-center">
                                <h6 class="card-title text-muted">Total Costos</h6>
                                <h2 class="card-text text-danger">$<?= number_format($totalCosto, 2) ?></h2>
                                <?php if ($totalVentas > 0): ?>
                                    <small class="text-small">
                                        <?= number_format(($totalCosto / $totalVentas) * 100, 2) ?>% de ventas
                                    </small>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                    
                    <div class="col-md-3 mb-3">
                        <div class="card card-summary border-info">
                            <div class="card-body text-center">
                                <h6 class="card-title text-muted">Órdenes Procesadas</h6>
                                <h2 class="card-text text-info"><?= number_format(array_sum(array_column($consumos, 'Ordenes'))) ?></h2>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Tabla de Resultados -->
                <div class="table-responsive">
                    <table class="table table-striped table-hover table-bordered" id="tablaConsumo">
                        <thead class="table-dark">
                            <tr>
                                <th>#</th>
                                <th>Ingrediente</th>
                                <th>Unidad</th>
                                <th>Precio Compra</th>
                                <th>Cant. Compra</th>
                                <th>Costo Unitario</th>
                                <th>Cant. Consumida</th>
                                <th>Costo Total</th>
                                <th>Platillos</th>
                                <th>Órdenes</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($consumos as $index => $consumo): ?>
                                <tr>
                                    <td><?= $index + 1 ?></td>
                                    <td><?= htmlspecialchars($consumo['Ingrediente']) ?></td>
                                    <td><?= htmlspecialchars($consumo['UnidadMedida']) ?></td>
                                    <td class="costo-cell">$<?= number_format($consumo['Precio'], 2) ?></td>
                                    <td><?= number_format($consumo['CantidadUnidad'], 2) ?> <?= htmlspecialchars($consumo['UnidadMedida']) ?></td>
                                    <td class="costo-cell">$<?= number_format($consumo['CostoUnitario'], 4) ?></td>
                                    <td class="text-end"><?= number_format($consumo['TotalConsumido'], 3) ?></td>
                                    <td class="costo-cell">$<?= number_format($consumo['TotalCosto'], 2) ?></td>
                                    <td class="text-center"><?= $consumo['PlatillosDiferentes'] ?></td>
                                    <td class="text-center"><?= $consumo['Ordenes'] ?></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                        <tfoot>
                            <tr class="table-active">
                                <th colspan="6">Totales</th>
                                <th class="text-end"><?= number_format($totalConsumido, 3) ?></th>
                                <th class="costo-cell">$<?= number_format($totalCosto, 2) ?></th>
                                <th class="text-center"><?= array_sum(array_column($consumos, 'PlatillosDiferentes')) ?></th>
                                <th class="text-center"><?= array_sum(array_column($consumos, 'Ordenes')) ?></th>
                            </tr>
                            <?php if ($totalVentas > 0): ?>
                            <tr class="table-active">
                                <th colspan="6">Relación con Ventas</th>
                                <th colspan="2" class="text-center">
                                    Costos representan el <?= number_format(($totalCosto / $totalVentas) * 100, 2) ?>% de las ventas ($<?= number_format($totalVentas, 2) ?>)
                                </th>
                                <th colspan="2"></th>
                            </tr>
                            <?php endif; ?>
                        </tfoot>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <?php include '../../../includes/footer.php'; ?>

    <!-- Scripts para DataTables y exportación -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.datatables.net/1.11.5/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/buttons/2.2.2/js/dataTables.buttons.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jszip/3.1.3/jszip.min.js"></script>
    <script src="https://cdn.datatables.net/buttons/2.2.2/js/buttons.html5.min.js"></script>
    <script src="https://cdn.datatables.net/buttons/2.2.2/js/buttons.print.min.js"></script>
    
    <script>
    $(document).ready(function() {
        // Configuración de DataTable
        $('#tablaConsumo').DataTable({
            language: {
                url: '//cdn.datatables.net/plug-ins/1.11.5/i18n/es-ES.json'
            },
            dom: '<"top"Bf>rt<"bottom"lip><"clear">',
            buttons: [
                {
                    extend: 'excel',
                    text: '<i class="fas fa-file-excel"></i> Excel',
                    className: 'btn btn-success',
                    title: 'Reporte de Consumo y Costos de Ingredientes',
                    messageTop: 'Período: <?= date("d/m/Y", strtotime($fechaInicio)) ?> - <?= date("d/m/Y", strtotime($fechaFin)) ?>',
                    exportOptions: {
                        columns: [0, 1, 2, 3, 4, 5, 6, 7, 8, 9]
                    }
                },
                {
                    extend: 'pdf',
                    text: '<i class="fas fa-file-pdf"></i> PDF',
                    className: 'btn btn-danger',
                    title: 'Reporte de Consumo y Costos de Ingredientes',
                    messageTop: 'Período: <?= date("d/m/Y", strtotime($fechaInicio)) ?> - <?= date("d/m/Y", strtotime($fechaFin)) ?>'
                },
                {
                    extend: 'print',
                    text: '<i class="fas fa-print"></i> Imprimir',
                    className: 'btn btn-info',
                    title: 'Reporte de Consumo y Costos de Ingredientes',
                    messageTop: 'Período: <?= date("d/m/Y", strtotime($fechaInicio)) ?> - <?= date("d/m/Y", strtotime($fechaFin)) ?>'
                }
            ],
            pageLength: 25,
            order: [[7, 'desc']], // Ordenar por Costo Total descendente
            columnDefs: [
                { type: 'currency', targets: [3,5,7] } // Especificar que estas columnas son moneda
            ]
        });
    });

    // Función para exportar a Excel (compatible con versiones antiguas)
    function exportToExcel() {
        // Crear tabla HTML para exportar
        let html = '<table border="1">';
        
        // Encabezados
        html += '<tr style="background-color: #f8f9fa; font-weight: bold;">';
        html += '<th>#</th>';
        html += '<th>Ingrediente</th>';
        html += '<th>Unidad</th>';
        html += '<th>Precio Compra</th>';
        html += '<th>Cant. Compra</th>';
        html += '<th>Costo Unitario</th>';
        html += '<th>Cant. Consumida</th>';
        html += '<th>Costo Total</th>';
        html += '<th>Platillos</th>';
        html += '<th>Órdenes</th>';
        html += '</tr>';
        
        // Datos
        <?php foreach ($consumos as $index => $consumo): ?>
            html += '<tr>';
            html += '<td><?= $index + 1 ?></td>';
            html += '<td><?= htmlspecialchars($consumo['Ingrediente']) ?></td>';
            html += '<td><?= htmlspecialchars($consumo['UnidadMedida']) ?></td>';
            html += '<td>$<?= number_format($consumo['Precio'], 2) ?></td>';
            html += '<td><?= number_format($consumo['CantidadUnidad'], 2) ?> <?= htmlspecialchars($consumo['UnidadMedida']) ?></td>';
            html += '<td>$<?= $consumo['CostoUnitario']  ?></td>';
            html += '<td><?= number_format($consumo['TotalConsumido'],2 ) ?></td>';
            html += '<td>$<?= number_format($consumo['TotalCosto'], 2) ?></td>';
            html += '<td><?= $consumo['PlatillosDiferentes'] ?></td>';
            html += '<td><?= $consumo['Ordenes'] ?></td>';
            html += '</tr>';
        <?php endforeach; ?>
        
        // Totales
        html += '<tr style="background-color: #f8f9fa; font-weight: bold;">';
        html += '<td colspan="6">Totales</td>';
        html += '<td><?= number_format($totalConsumido, 3) ?></td>';
        html += '<td>$<?= number_format($totalCosto, 2) ?></td>';
        html += '<td><?= array_sum(array_column($consumos, 'PlatillosDiferentes')) ?></td>';
        html += '<td><?= array_sum(array_column($consumos, 'Ordenes')) ?></td>';
        html += '</tr>';
        
        <?php if ($totalVentas > 0): ?>
        html += '<tr style="background-color: #f8f9fa; font-weight: bold;">';
        html += '<td colspan="6">Relación con Ventas</td>';
        html += '<td colspan="2" style="text-align: center;">';
        html += 'Costos representan el <?= number_format(($totalCosto / $totalVentas) * 100, 2) ?>% de las ventas ($<?= number_format($totalVentas, 2) ?>)';
        html += '</td>';
        html += '<td colspan="2"></td>';
        html += '</tr>';
        <?php endif; ?>
        
        html += '</table>';
        
        // Crear archivo Excel
        let uri = 'data:application/vnd.ms-excel;base64,';
        let template = '<html xmlns:o="urn:schemas-microsoft-com:office:office" xmlns:x="urn:schemas-microsoft-com:office:excel" xmlns="http://www.w3.org/TR/REC-html40"><head><!--[if gte mso 9]><xml><x:ExcelWorkbook><x:ExcelWorksheets><x:ExcelWorksheet><x:Name>{worksheet}</x:Name><x:WorksheetOptions><x:DisplayGridlines/></x:WorksheetOptions></x:ExcelWorksheet></x:ExcelWorksheets></x:ExcelWorkbook></xml><![endif]--><meta charset="UTF-8"></head><body>{table}</body></html>';
        
        let base64 = function(s) {
            return window.btoa(unescape(encodeURIComponent(s)));
        };
        
        let format = function(s, c) {
            return s.replace(/{(\w+)}/g, function(m, p) { return c[p]; });
        };
        
        let ctx = {
            worksheet: 'Reporte de Consumo y Costos',
            table: html
        };
        
        let link = document.createElement('a');
        link.download = 'Reporte_Consumo_Costos_<?= date('Y-m-d') ?>.xls';
        link.href = uri + base64(format(template, ctx));
        link.click();
    }
    </script>
</body>
</html>