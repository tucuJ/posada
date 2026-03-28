<?php
require_once '../../../config/database.php';
require_once '../../../includes/header.php';

// Verificar permisos del usuario
if (!($_SESSION['rol'] === 'Admin' || $_SESSION['rol'] === 'Gerente' || $_SESSION['rol'] === 'Restaurante' || $_SESSION['rol'] === 'Bodega')) {
    header('Location: /dashboard.php');
    exit;
}

$title = 'Restaurante - Movimientos de Ingredientes';
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

// Obtener todos los ingredientes para el select
$queryIngredientes = "SELECT IngredienteID, Nombre FROM Ingredientes WHERE Activo = 1 ORDER BY Nombre";
$stmtIngredientes = $pdo->query($queryIngredientes);
$ingredientes = $stmtIngredientes->fetchAll(PDO::FETCH_ASSOC);

// Obtener parámetros de filtrado
$ingredienteID = isset($_GET['ingrediente']) ? (int)$_GET['ingrediente'] : null;
$tipo = isset($_GET['tipo']) ? $_GET['tipo'] : null;
$fechaInicio = isset($_GET['fecha_inicio']) ? $_GET['fecha_inicio'] : null;
$fechaFin = isset($_GET['fecha_fin']) ? $_GET['fecha_fin'] : null;

// Determinar si hay filtros activos (excepto ingrediente y tipo)
$filtrosActivos = ($fechaInicio !== null || $fechaFin !== null);

// Construir consulta base
$query = "SELECT m.*, i.Nombre AS IngredienteNombre, u.NombreUsuario 
          FROM IngredientesMovimientos m
          JOIN Ingredientes i ON m.ingredientesID = i.IngredienteID
          LEFT JOIN Usuarios u ON m.UsuarioID = u.UsuarioID
          WHERE m.Tipo IN ('Entrada', 'Salida')";

$params = [];

// Aplicar filtros
if ($ingredienteID) {
    $query .= " AND m.ingredientesID = ?";
    $params[] = $ingredienteID;
}

if ($tipo) {
    $query .= " AND m.Tipo = ?";
    $params[] = $tipo;
}

if ($fechaInicio) {
    $query .= " AND DATE(m.FechaHora) >= ?";
    $params[] = $fechaInicio;
}

if ($fechaFin) {
    $query .= " AND DATE(m.FechaHora) <= ?";
    $params[] = $fechaFin;
}

// Ordenar por fecha descendente
$query .= " ORDER BY m.FechaHora DESC";

// Limitar a 10 registros si no hay filtros de fecha
if (!$filtrosActivos) {
    $query .= " LIMIT 10";
}

$stmt = $pdo->prepare($query);
$stmt->execute($params);
$movimientos = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Contar total de movimientos sin límite (para el mensaje)
$totalMovimientos = 0;
if (!$filtrosActivos) {
    $queryTotal = "SELECT COUNT(*) FROM IngredientesMovimientos WHERE Tipo IN ('Entrada', 'Salida')";
    $totalMovimientos = $pdo->query($queryTotal)->fetchColumn();
}
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
                    <label for="ingrediente" class="form-label">Ingrediente</label>
                    <select class="form-select" id="ingrediente" name="ingrediente">
                        <option value="">Todos</option>
                        <?php foreach ($ingredientes as $ing): ?>
                            <option value="<?= $ing['IngredienteID'] ?>" <?= $ingredienteID == $ing['IngredienteID'] ? 'selected' : '' ?>>
                                <?= htmlspecialchars($ing['Nombre']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                
                <div class="col-md-3">
                    <label for="tipo" class="form-label">Tipo</label>
                    <select class="form-select" id="tipo" name="tipo">
                        <option value="">Todos</option>
                        <option value="Entrada" <?= $tipo === 'Entrada' ? 'selected' : '' ?>>Entrada</option>
                        <option value="Salida" <?= $tipo === 'Salida' ? 'selected' : '' ?>>Salida</option>
                    </select>
                </div>
                
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
                
                <div class="col-12">
                    <button type="submit" class="btn btn-primary me-2">
                        <i class="fas fa-search"></i> Filtrar
                    </button>
                    <a href="movimientos.php" class="btn btn-secondary">
                        <i class="fas fa-times"></i> Limpiar
                    </a>
                </div>
            </form>
        </div>
    </div>
    
    <div class="card">
        <div class="card-header d-flex justify-content-between align-items-center">
            <span><i class="fas fa-exchange-alt"></i> Movimientos</span>
            <div>
                <?php if (!$filtrosActivos && $totalMovimientos > 10): ?>
                    <span class="badge bg-info me-2">
                        Mostrando últimos 10 de <?= $totalMovimientos ?> movimientos
                    </span>
                <?php endif; ?>
                <a href="agregar-movimiento.php" class="btn btn-success btn-sm">
                    <i class="fas fa-plus"></i> Nuevo Movimiento
                </a>
            </div>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-striped table-hover" id="tablaMovimientos">
                    <thead>
                        <tr>
                            <th>Fecha</th>
                            <th>Ingrediente</th>
                            <th>Tipo</th>
                            <th>Cantidad</th>
                            <th>Referencia</th>
                            <th>Usuario</th>
                            <th>Notas</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($movimientos as $mov): ?>
                            <tr>
                                <td><?= date('d/m/Y H:i', strtotime($mov['FechaHora'])) ?></td>
                                <td><?= htmlspecialchars($mov['IngredienteNombre']) ?></td>
                                <td>
                                    <span class="badge bg-<?= $mov['Tipo'] === 'Entrada' ? 'success' : 'danger' ?>">
                                        <?= $mov['Tipo'] ?>
                                    </span>
                                </td>
                                <td><?= number_format($mov['Cantidad'], 0) ?></td>
                                <td><?= htmlspecialchars($mov['Referencia']) ?: 'N/A' ?></td>
                                <td><?= htmlspecialchars($mov['NombreUsuario']) ?: 'Sistema' ?></td>
                                <td><?= htmlspecialchars($mov['Notas']) ?: 'N/A' ?></td>
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
    $('#tablaMovimientos').DataTable({
        language: {
            url: '//cdn.datatables.net/plug-ins/1.10.25/i18n/Spanish.json'
        },
        order: [[0, 'desc']],
        dom: 'Bfrtip',
        buttons: [
            {
                extend: 'excel',
                text: '<i class="fas fa-file-excel"></i> Exportar',
                className: 'btn btn-success btn-sm',
                title: 'Movimientos de Ingredientes',
                messageTop: '<?= $filtrosActivos ? "Filtrado por fechas: " . htmlspecialchars($fechaInicio) . " - " . htmlspecialchars($fechaFin) : "Últimos 10 movimientos" ?>'
            }
        ]
    });
});
</script>