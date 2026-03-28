<?php
require_once '../../../config/database.php';
require_once '../../../includes/header.php';

// Verificar permisos del usuario
if (!($_SESSION['rol'] === 'Admin' || $_SESSION['rol'] === 'Gerente' || $_SESSION['rol'] === 'Restaurante')) {
    header('Location: /dashboard.php');
    exit;
}

$title = 'Restaurante - Platillos';
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

// Obtener todos los platillos con información de categoría
$query = "SELECT p.*, c.Nombre AS Categoria 
          FROM Platillos p
          LEFT JOIN CategoriasPlatillos c ON p.CategoriaPlatilloID = c.CategoriaPlatilloID
          ORDER BY p.Nombre";
$stmt = $pdo->query($query);
$platillos = $stmt->fetchAll(PDO::FETCH_ASSOC);
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
        <div class="card-header d-flex justify-content-between align-items-center">
            <span><i class="fas fa-utensils"></i> Lista de Platillos</span>
            <a href="agregar.php" class="btn btn-success btn-sm">
                <i class="fas fa-plus"></i> Nuevo Platillo
            </a>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-striped table-hover" id="tablaPlatillos">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Código</th>
                            <th>Nombre</th>
                            <th>Categoría</th>
                            <th>Precio</th>
                            <th>Tiempo</th>
                            <th>Estado</th>
                            <th>Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($platillos as $platillo): ?>
                            <tr>
                                <td><?= htmlspecialchars($platillo['PlatilloID']) ?></td>
                                <td><?= htmlspecialchars($platillo['Codigo']) ?></td>
                                <td><?= htmlspecialchars($platillo['Nombre']) ?></td>
                                <td><?= htmlspecialchars($platillo['Categoria']) ?: 'Sin categoría' ?></td>
                                <td>$<?= number_format($platillo['PrecioVenta'], 2) ?></td>
                                <td><?= htmlspecialchars($platillo['TiempoPreparacion']) ?> min</td>
                                <td>
                                    <span class="badge bg-<?= $platillo['Activo'] ? 'success' : 'secondary' ?>">
                                        <?= $platillo['Activo'] ? 'Activo' : 'Inactivo' ?>
                                    </span>
                                </td>
                                <td>
                                    <div class="btn-group" role="group">
                                        <a href="editar.php?id=<?= $platillo['PlatilloID'] ?>" class="btn btn-warning btn-sm" title="Editar">
                                            <i class="fas fa-edit"></i>
                                        </a>
                                        <a href="recetas.php?id=<?= $platillo['PlatilloID'] ?>" class="btn btn-info btn-sm" title="Receta">
                                            <i class="fas fa-list"></i>
                                        </a>
                                        <?php if ($platillo['Activo']): ?>
                                            <a href="eliminar.php?id=<?= $platillo['PlatilloID'] ?>" class="btn btn-danger btn-sm" title="Desactivar">
                                                <i class="fas fa-times"></i>
                                            </a>
                                        <?php else: ?>
                                            <a href="activar.php?id=<?= $platillo['PlatilloID'] ?>" class="btn btn-success btn-sm" title="Activar">
                                                <i class="fas fa-check"></i>
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
    $('#tablaPlatillos').DataTable({
        language: {
            url: '//cdn.datatables.net/plug-ins/1.10.25/i18n/Spanish.json'
        },
        columnDefs: [
            { orderable: false, targets: [7] }
        ]
    });
});
</script>