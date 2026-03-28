<?php 
define('BASE_URL', '/posada/');
require_once('../../../config/database.php');
include('../../../includes/header.php');

// Obtener parámetros de fechas
$fechaInicio = $_GET['fechaInicio'] ?? date('Y-m-d', strtotime('-7 days'));
$fechaFin = $_GET['fechaFin'] ?? date('Y-m-d');

// Validar fechas
if ($fechaInicio > $fechaFin) {
    $temp = $fechaInicio;
    $fechaInicio = $fechaFin;
    $fechaFin = $temp;
}

// Obtener productos más vendidos
$query = "SELECT 
          p.ProductoID,
          p.Nombre,
          c.Nombre as Categoria,
          SUM(vd.Cantidad) as TotalVendido,
          SUM(vd.Subtotal) as TotalIngresos
          FROM VentaDetalles vd
          JOIN Productos p ON vd.ProductoID = p.ProductoID
          JOIN CategoriasProductos c ON p.CategoriaID = c.CategoriaID
          JOIN Ventas v ON vd.VentaID = v.VentaID
          WHERE v.FechaHora BETWEEN ? AND ?
          AND v.Estado = 'Completada'
          GROUP BY p.ProductoID
          ORDER BY TotalVendido DESC";
$stmt = $conn->prepare($query);
$stmt->execute([$fechaInicio . ' 00:00:00', $fechaFin . ' 23:59:59']);
$productos = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<div class="container">
    <h2>Reporte de Productos Vendidos</h2>
    
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
            <h5 class="mb-0">Productos Más Vendidos</h5>
        </div>
        <div class="card-body">
            <?php if (count($productos) > 0): ?>
                <div class="table-responsive">
                    <table class="table table-striped">
                        <thead>
                            <tr>
                                <th>Producto</th>
                                <th>Categoría</th>
                                <th>Unidades Vendidas</th>
                                <th>Total Generado</th>
                                <th>Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($productos as $producto): ?>
                            <tr>
                                <td><?= $producto['Nombre'] ?></td>
                                <td><?= $producto['Categoria'] ?></td>
                                <td><?= $producto['TotalVendido'] ?></td>
                                <td>$<?= number_format($producto['TotalIngresos'], 2) ?></td>
                                <td>
                                    <a href="../../productos/editar.php?id=<?= $producto['ProductoID'] ?>" 
                                       class="btn btn-sm btn-warning" title="Editar producto">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php else: ?>
                <div class="alert alert-info">No hay ventas de productos en el período seleccionado</div>
            <?php endif; ?>
        </div>
    </div>
</div>

<?php include('../../../includes/footer.php'); ?>