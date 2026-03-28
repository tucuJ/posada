<?php
require_once '../../../config/database.php';
require_once '../../../includes/header.php';

// Verificar permisos del usuario
if (!($_SESSION['rol'] === 'Admin' || $_SESSION['rol'] === 'Gerente' || $_SESSION['rol'] === 'Restaurante' || $_SESSION['rol'] === 'Bodega')) {
    header('Location: /dashboard.php');
    exit;
}

$title = 'Restaurante - Ingredientes';
$alertType = '';
$alertMessage = '';

// Manejar mensajes de éxito/error
if (isset($_GET['success'])) {
    $alertType = 'success';
    $alertMessage = 'Operación realizada con éxito.';
} elseif (isset($_GET['error'])) {
    $alertType = 'danger';
    $alertMessage = 'Ocurrió un error: ' . htmlspecialchars($_GET['error']);
}

// Obtener parámetros de filtrado
$nombre = isset($_GET['nombre']) ? trim($_GET['nombre']) : '';
$estado = isset($_GET['estado']) ? $_GET['estado'] : '';
$stock_minimo = isset($_GET['stock_minimo']) ? $_GET['stock_minimo'] : '';

// Construir consulta base con filtros
$query = "SELECT * FROM Ingredientes WHERE 1=1";
$params = [];

if (!empty($nombre)) {
    $query .= " AND Nombre LIKE :nombre";
    $params[':nombre'] = "%$nombre%";
}

if ($estado === 'activo') {
    $query .= " AND Activo = 1";
} elseif ($estado === 'inactivo') {
    $query .= " AND Activo = 0";
}

if ($stock_minimo === 'bajo') {
    $query .= " AND Stock < StockMinimo";
} elseif ($stock_minimo === 'ok') {
    $query .= " AND Stock >= StockMinimo";
}

$query .= " ORDER BY Nombre";

$stmt = $pdo->prepare($query);
foreach ($params as $key => $value) {
    $stmt->bindValue($key, $value);
}
$stmt->execute();
$ingredientes = $stmt->fetchAll(PDO::FETCH_ASSOC);
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
    
    <!-- Panel de Filtros -->
    <div class="card mb-4">
        <div class="card-header bg-primary text-white">
            <i class="fas fa-filter"></i> Filtros
        </div>
        <div class="card-body">
            <form method="get" class="row g-3">
                <div class="col-md-4">
                    <label for="nombre" class="form-label">Nombre</label>
                    <input type="text" class="form-control" id="nombre" name="nombre" 
                           value="<?= htmlspecialchars($nombre) ?>" placeholder="Buscar por nombre...">
                </div>
                
                <div class="col-md-3">
                    <label for="estado" class="form-label">Estado</label>
                    <select class="form-select" id="estado" name="estado">
                        <option value="">Todos</option>
                        <option value="activo" <?= $estado === 'activo' ? 'selected' : '' ?>>Activos</option>
                        <option value="inactivo" <?= $estado === 'inactivo' ? 'selected' : '' ?>>Inactivos</option>
                    </select>
                </div>
                
                <div class="col-md-3">
                    <label for="stock_minimo" class="form-label">Stock</label>
                    <select class="form-select" id="stock_minimo" name="stock_minimo">
                        <option value="">Todos</option>
                        <option value="bajo" <?= $stock_minimo === 'bajo' ? 'selected' : '' ?>>Bajo mínimo</option>
                        <option value="ok" <?= $stock_minimo === 'ok' ? 'selected' : '' ?>>Sobre mínimo</option>
                    </select>
                </div>
                
                <div class="col-md-2 d-flex align-items-end">
                    <button type="submit" class="btn btn-primary me-2">
                        <i class="fas fa-search"></i> Filtrar
                    </button>
                    <a href="listar.php" class="btn btn-secondary">
                        <i class="fas fa-sync-alt"></i>
                    </a>
                </div>
            </form>
        </div>
    </div>
    
    <div class="card mb-4">
        <div class="card-header d-flex justify-content-between align-items-center">
            <span><i class="fas fa-utensils"></i> Lista de Ingredientes</span>
            <div>
                <a href="agregar.php" class="btn btn-success btn-sm me-2">
                    <i class="fas fa-plus"></i> Nuevo Ingrediente
                </a>
                <a href="movimientos.php" class="btn btn-info btn-sm">
                    <i class="fas fa-exchange-alt"></i> Movimientos
                </a>
            </div>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-striped table-hover" id="tablaIngredientes">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Nombre</th>
                            <th>Unidad</th>
                            <th>Stock</th>
                            <th>Mínimo</th>
                            <th>Estado</th>
                            <th>Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($ingredientes as $ing): ?>
                            <tr class="<?= $ing['Stock'] < $ing['StockMinimo'] ? 'table-warning' : '' ?>">
                                <td><?= htmlspecialchars($ing['IngredienteID']) ?></td>
                                <td><?= htmlspecialchars($ing['Nombre']) ?></td>
                                <td><?= htmlspecialchars($ing['UnidadMedida']) ?></td>
                                <td><?= number_format($ing['Stock'], 0) ?></td>
                                <td><?= number_format($ing['StockMinimo'], 0) ?></td>
                                <td>
                                    <span class="badge bg-<?= $ing['Activo'] ? 'success' : 'secondary' ?>">
                                        <?= $ing['Activo'] ? 'Activo' : 'Inactivo' ?>
                                    </span>
                                </td>
                                <td>
                                    <a href="editar.php?id=<?= $ing['IngredienteID'] ?>" class="btn btn-warning btn-sm" title="Editar">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                    <?php if ($ing['Activo']): ?>
                                        <a href="eliminar.php?id=<?= $ing['IngredienteID'] ?>" class="btn btn-danger btn-sm" title="Desactivar">
                                            <i class="fas fa-times"></i>
                                        </a>
                                    <?php else: ?>
                                        <a href="activar.php?id=<?= $ing['IngredienteID'] ?>" class="btn btn-success btn-sm" title="Activar">
                                            <i class="fas fa-check"></i>
                                        </a>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<?php require_once '../../../includes/footer.php'; ?>

<script>
$(document).ready(function() {
    $('#tablaIngredientes').DataTable({
        language: {
            url: '//cdn.datatables.net/plug-ins/1.10.25/i18n/Spanish.json'
        },
        dom: '<"top"f>rt<"bottom"lip><"clear">',
        columnDefs: [
            { orderable: false, targets: [6] }
        ],
        initComplete: function() {
            // Mantener los parámetros de filtro en la paginación
            $('input[name="nombre"]').val('<?= htmlspecialchars($nombre) ?>');
            $('select[name="estado"]').val('<?= htmlspecialchars($estado) ?>');
            $('select[name="stock_minimo"]').val('<?= htmlspecialchars($stock_minimo) ?>');
        }
    });
});
</script>