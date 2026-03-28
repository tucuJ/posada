<?php
require_once '../../../config/database.php';
require_once '../../../includes/header.php';

// Verificar permisos del usuario
if (!($_SESSION['rol'] === 'Admin' || $_SESSION['rol'] === 'Gerente' || $_SESSION['rol'] === 'Restaurante')) {
    header('Location: /dashboard.php');
    exit;
}

$title = 'Restaurante - Categorías de Platillos';
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

// Obtener todas las categorías
$query = "SELECT * FROM CategoriasPlatillos ORDER BY Nombre";
$stmt = $pdo->query($query);
$categorias = $stmt->fetchAll(PDO::FETCH_ASSOC);
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
            <span><i class="fas fa-list"></i> Lista de Categorías</span>
            <a href="agregar.php" class="btn btn-success btn-sm">
                <i class="fas fa-plus"></i> Nueva Categoría
            </a>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-striped table-hover" id="tablaCategorias">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Nombre</th>
                            <th>Descripción</th>
                            <th>Estado</th>
                            <th>Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($categorias as $categoria): ?>
                            <tr>
                                <td><?= htmlspecialchars($categoria['CategoriaPlatilloID']) ?></td>
                                <td><?= htmlspecialchars($categoria['Nombre']) ?></td>
                                <td><?= htmlspecialchars($categoria['Descripcion']) ?: 'N/A' ?></td>
                                <td>
                                    <span class="badge bg-<?= $categoria['Activo'] ? 'success' : 'secondary' ?>">
                                        <?= $categoria['Activo'] ? 'Activo' : 'Inactivo' ?>
                                    </span>
                                </td>
                                <td>
                                    <a href="editar.php?id=<?= $categoria['CategoriaPlatilloID'] ?>" class="btn btn-warning btn-sm" title="Editar">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                    <?php if ($categoria['Activo']): ?>
                                        <a href="eliminar.php?id=<?= $categoria['CategoriaPlatilloID'] ?>" class="btn btn-danger btn-sm" title="Desactivar">
                                            <i class="fas fa-times"></i>
                                        </a>
                                    <?php else: ?>
                                        <a href="activar.php?id=<?= $categoria['CategoriaPlatilloID'] ?>" class="btn btn-success btn-sm" title="Activar">
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
    $('#tablaCategorias').DataTable({
        language: {
            url: '//cdn.datatables.net/plug-ins/1.10.25/i18n/Spanish.json'
        },
        columnDefs: [
            { orderable: false, targets: [4] }
        ]
    });
});
</script>