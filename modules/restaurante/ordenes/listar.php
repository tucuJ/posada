<?php
require_once '../../../config/database.php';
require_once '../../../includes/header.php';

// Verificar permisos del usuario
if (!($_SESSION['rol'] === 'Admin' || $_SESSION['rol'] === 'Gerente' || $_SESSION['rol'] === 'Restaurante' || $_SESSION['rol'] === 'Recepcion')) {
    header('Location: /dashboard.php');
    exit;
}

$title = 'Restaurante - Órdenes';
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
$estado = isset($_GET['estado']) ? $_GET['estado'] : '';
$tipo = isset($_GET['tipo']) ? $_GET['tipo'] : '';
$fecha = isset($_GET['fecha']) ? $_GET['fecha'] : date('Y-m-d');

// Construir consulta con filtros
$query = "SELECT o.*, 
          CONCAT(c.Nombre, ' ', c.Apellido) AS ClienteNombre,
          h.Numero AS HabitacionNumero,
          u.NombreUsuario
          FROM OrdenesRestaurante o
          LEFT JOIN Clientes c ON o.ClienteID = c.ClienteID
          LEFT JOIN Habitaciones h ON o.HabitacionID = h.HabitacionID
          LEFT JOIN Usuarios u ON o.UsuarioID = u.UsuarioID
          WHERE DATE(o.FechaHora) = ?";
$params = [$fecha];

if (!empty($estado)) {
    $query .= " AND o.Estado = ?";
    $params[] = $estado;
}

if (!empty($tipo)) {
    $query .= " AND o.Tipo = ?";
    $params[] = $tipo;
}

$query .= " ORDER BY o.FechaHora DESC";

$stmt = $pdo->prepare($query);
$stmt->execute($params);
$ordenes = $stmt->fetchAll(PDO::FETCH_ASSOC);
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
                    <label for="fecha" class="form-label">Fecha</label>
                    <input type="date" class="form-control" id="fecha" name="fecha" value="<?= htmlspecialchars($fecha) ?>">
                </div>
                
                <div class="col-md-3">
                    <label for="estado" class="form-label">Estado</label>
                    <select class="form-select" id="estado" name="estado">
                        <option value="">Todos</option>
                        <option value="Pendiente" <?= $estado === 'Pendiente' ? 'selected' : '' ?>>Pendiente</option>
                        <option value="EnPreparacion" <?= $estado === 'EnPreparacion' ? 'selected' : '' ?>>En Preparación</option>
                        <option value="Listo" <?= $estado === 'Listo' ? 'selected' : '' ?>>Listo</option>
                        <option value="Entregado" <?= $estado === 'Entregado' ? 'selected' : '' ?>>Entregado</option>
                        <option value="Cancelado" <?= $estado === 'Cancelado' ? 'selected' : '' ?>>Cancelado</option>
                    </select>
                </div>
                
                <div class="col-md-3">
                    <label for="tipo" class="form-label">Tipo</label>
                    <select class="form-select" id="tipo" name="tipo">
                        <option value="">Todos</option>
                        <option value="Restaurante" <?= $tipo === 'Restaurante' ? 'selected' : '' ?>>Restaurante</option>
                        <option value="Habitacion" <?= $tipo === 'Habitacion' ? 'selected' : '' ?>>Habitación</option>
                        <option value="ParaLlevar" <?= $tipo === 'ParaLlevar' ? 'selected' : '' ?>>Para Llevar</option>
                    </select>
                </div>
                
                <div class="col-md-3 d-flex align-items-end">
                    <button type="submit" class="btn btn-primary me-2">
                        <i class="fas fa-search"></i> Filtrar
                    </button>
                    <a href="listar.php" class="btn btn-secondary">
                        <i class="fas fa-sync-alt"></i> Limpiar
                    </a>
                </div>
            </form>
        </div>
    </div>
    
    <div class="card">
        <div class="card-header d-flex justify-content-between align-items-center">
            <span><i class="fas fa-list"></i> Lista de Órdenes</span>
            <a href="nueva.php" class="btn btn-success btn-sm">
                <i class="fas fa-plus"></i> Nueva Orden
            </a>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-striped table-hover" id="tablaOrdenes">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Fecha/Hora</th>
                            <th>Cliente</th>
                            <th>Habitación</th>
                            <th>Tipo</th>
                            <th>Estado</th>
                            <th>Total</th>
                            <th>Usuario</th>
                            <th>Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($ordenes as $orden): ?>
                            <tr>
                                <td><?= htmlspecialchars($orden['OrdenID']) ?></td>
                                <td><?= date('d/m/Y H:i', strtotime($orden['FechaHora'])) ?></td>
                                <td><?= htmlspecialchars($orden['ClienteNombre']) ?: 'N/A' ?></td>
                                <td><?= htmlspecialchars($orden['HabitacionNumero']) ?: 'N/A' ?></td>
                                <td>
                                    <?php 
                                        $tipoText = [
                                            'Restaurante' => '<span class="badge bg-primary">Restaurante</span>',
                                            'Habitacion' => '<span class="badge bg-info">Habitación</span>',
                                            'ParaLlevar' => '<span class="badge bg-warning">Para Llevar</span>'
                                        ];
                                        echo $tipoText[$orden['Tipo'] ?? $orden['Tipo']];
                                    ?>
                                </td>
                                <td>
                                    <?php 
                                        $estadoClass = [
                                            'Pendiente' => 'warning',
                                            'EnPreparacion' => 'info',
                                            'Listo' => 'success',
                                            'Entregado' => 'primary',
                                            'Cancelado' => 'danger'
                                        ];
                                    ?>
                                    <span class="badge bg-<?= $estadoClass[$orden['Estado']] ?>">
                                        <?= $orden['Estado'] ?>
                                    </span>
                                </td>
                                <td>$<?= number_format($orden['Total'], 2) ?></td>
                                <td><?= htmlspecialchars($orden['NombreUsuario']) ?></td>
                                <td>
                                    <div class="btn-group" role="group">
                                        <a href="detalle.php?id=<?= $orden['OrdenID'] ?>" class="btn btn-info btn-sm" title="Ver Detalle">
                                            <i class="fas fa-eye"></i>
                                        </a>
                                        <?php if ($orden['Estado'] === 'Pendiente' || $orden['Estado'] === 'EnPreparacion'): ?>
                                            <a href="editar.php?id=<?= $orden['OrdenID'] ?>" class="btn btn-warning btn-sm" title="Editar">
                                                <i class="fas fa-edit"></i>
                                            </a>
                                        <?php endif; ?>
                                        <?php if ($orden['Estado'] !== 'Cancelado' && $orden['Estado'] !== 'Entregado'): ?>
                                            <a href="cancelar.php?id=<?= $orden['OrdenID'] ?>" class="btn btn-danger btn-sm" title="Cancelar">
                                                <i class="fas fa-times"></i>
                                            </a>
                                        <?php endif; ?>
                                    </div>
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
    $('#tablaOrdenes').DataTable({
        language: {
            url: '//cdn.datatables.net/plug-ins/1.10.25/i18n/Spanish.json'
        },
        order: [[1, 'desc']],
        columnDefs: [
            { orderable: false, targets: [8] }
        ]
    });
});
</script>