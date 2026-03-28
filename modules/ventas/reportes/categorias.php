<?php 
define('BASE_URL', '/posada/');
require_once('../../../config/database.php');
include('../../../includes/header.php');

// Obtener parámetros de fechas
$fechaInicio = $_GET['fechaInicio'] ?? date('Y-m-d', strtotime('-1 month'));
$fechaFin = $_GET['fechaFin'] ?? date('Y-m-d');

// Validar fechas
if ($fechaInicio > $fechaFin) {
    $temp = $fechaInicio;
    $fechaInicio = $fechaFin;
    $fechaFin = $temp;
}

// Obtener ventas por categoría
$query = "SELECT 
          c.CategoriaID,
          c.Nombre as Categoria,
          COUNT(DISTINCT v.VentaID) as Ventas,
          SUM(vd.Cantidad) as Unidades,
          SUM(vd.Subtotal) as Subtotal,
          SUM(vd.Subtotal * 0.16) as Impuestos,
          SUM(vd.Subtotal * 1.16) as Total
          FROM VentaDetalles vd
          JOIN Productos p ON vd.ProductoID = p.ProductoID
          JOIN CategoriasProductos c ON p.CategoriaID = c.CategoriaID
          JOIN Ventas v ON vd.VentaID = v.VentaID
          WHERE v.FechaHora BETWEEN ? AND ?
          AND v.Estado = 'Completada'
          GROUP BY c.CategoriaID
          ORDER BY Total DESC";
$stmt = $conn->prepare($query);
$stmt->execute([$fechaInicio . ' 00:00:00', $fechaFin . ' 23:59:59']);
$categorias = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Calcular totales
$totales = [
    'Ventas' => array_sum(array_column($categorias, 'Ventas')),
    'Unidades' => array_sum(array_column($categorias, 'Unidades')),
    'Subtotal' => array_sum(array_column($categorias, 'Subtotal')),
    'Impuestos' => array_sum(array_column($categorias, 'Impuestos')),
    'Total' => array_sum(array_column($categorias, 'Total'))
];
?>

<div class="container">
    <h2>Reporte de Ventas por Categoría</h2>
    
    <div class="card mb-4">
        <div class="card-body">
            <form method="get" class="row g-3">
                <div class="col-md-4">
                    <label for="fechaInicio" class="form-label">Fecha Inicio</label>
                    <input type="date" class="form-control" id="fechaInicio" name="fechaInicio" 
                           value="<?= $fechaInicio ?>" max="<?= $fechaFin ?>">
                </div>
                <div class="col-md-4">
                    <label for="fechaFin" class="form-label">Fecha Fin</label>
                    <input type="date" class="form-control" id="fechaFin" name="fechaFin" 
                           value="<?= $fechaFin ?>" min="<?= $fechaInicio ?>">
                </div>
                <div class="col-md-4 d-flex align-items-end">
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-filter"></i> Generar Reporte
                    </button>
                </div>
            </form>
        </div>
    </div>
    
    <div class="card">
        <div class="card-header bg-primary text-white">
            <h5 class="mb-0">Ventas por Categoría</h5>
        </div>
        <div class="card-body">
            <?php if (count($categorias) > 0): ?>
                <div class="table-responsive">
                    <table class="table table-striped">
                        <thead>
                            <tr>
                                <th>Categoría</th>
                                <th>Ventas</th>
                                <th>Unidades</th>
                                <th>Subtotal</th>
                                <th>Impuestos</th>
                                <th>Total</th>
                                <th>Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($categorias as $categoria): ?>
                            <tr>
                                <td><?= $categoria['Categoria'] ?></td>
                                <td><?= $categoria['Ventas'] ?></td>
                                <td><?= $categoria['Unidades'] ?></td>
                                <td>$<?= number_format($categoria['Subtotal'], 2) ?></td>
                                <td>$<?= number_format($categoria['Impuestos'], 2) ?></td>
                                <td>$<?= number_format($categoria['Total'], 2) ?></td>
                                <td>
                                    <a href="../../productos/listar.php?categoria=<?= $categoria['CategoriaID'] ?>" 
                                       class="btn btn-sm btn-info" title="Ver productos">
                                        <i class="fas fa-list"></i>
                                    </a>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                            <tr class="table-success fw-bold">
                                <td>Total General</td>
                                <td><?= $totales['Ventas'] ?></td>
                                <td><?= $totales['Unidades'] ?></td>
                                <td>$<?= number_format($totales['Subtotal'], 2) ?></td>
                                <td>$<?= number_format($totales['Impuestos'], 2) ?></td>
                                <td>$<?= number_format($totales['Total'], 2) ?></td>
                                <td></td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            <?php else: ?>
                <div class="alert alert-info">No hay ventas en el período seleccionado</div>
            <?php endif; ?>
        </div>
    </div>
</div>

<?php include('../../../includes/footer.php'); ?>