<?php 

define('BASE_URL', '/posada/');
require_once('../../../config/database.php');
include('../../../includes/header.php');

// Iniciar sesión para mensajes flash

// Obtener todas las mesas
$query = "SELECT * FROM MesasRestaurante ORDER BY Numero";
$stmt = $conn->query($query);
$mesas = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Mostrar mensajes de sesión
$successMsg = $_SESSION['success'] ?? null;
$errorMsg = $_SESSION['error'] ?? null;

// Limpiar mensajes después de mostrarlos
unset($_SESSION['success']);
unset($_SESSION['error']);
?>

<div class="container">
    <h2>Gestión de Mesas del Restaurante</h2>
    
    <!-- Mostrar mensajes de éxito/error -->
    <?php if ($successMsg): ?>
        <div class="alert alert-success alert-dismissible fade show">
            <?= htmlspecialchars($successMsg) ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    <?php endif; ?>
    
    <?php if ($errorMsg): ?>
        <div class="alert alert-danger alert-dismissible fade show">
            <?= htmlspecialchars($errorMsg) ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    <?php endif; ?>
    
    <div class="d-flex justify-content-between mb-4">
        <a href="agregar.php" class="btn btn-primary">
            <i class="fas fa-plus"></i> Agregar Mesa
        </a>
    </div>
    
    <div class="card">
        <div class="card-header bg-primary text-white">
            <h5 class="mb-0">Listado de Mesas</h5>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-striped">
                    <thead>
                        <tr>
                            <th>Número</th>
                            <th>Capacidad</th>
                            <th>Ubicación</th>
                            <th>Estado</th>
                            <th>Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($mesas as $mesa): ?>
                        <tr>
                            <td><?= htmlspecialchars($mesa['Numero']) ?></td>
                            <td><?= htmlspecialchars($mesa['Capacidad']) ?></td>
                            <td><?= htmlspecialchars($mesa['Ubicacion']) ?></td>
                            <td>
                                <span class="badge bg-<?= 
                                    $mesa['Estado'] == 'Disponible' ? 'success' : 
                                    ($mesa['Estado'] == 'Ocupada' ? 'danger' : 
                                    ($mesa['Estado'] == 'Reservada' ? 'warning' : 
                                    ($mesa['Estado'] == 'Mantenimiento' ? 'secondary' : 'light'))) 
                                ?>">
                                    <?= htmlspecialchars($mesa['Estado']) ?>
                                </span>
                            </td>
                            <td>
                                <a href="editar.php?id=<?= $mesa['MesaID'] ?>" class="btn btn-sm btn-warning">
                                    <i class="fas fa-edit"></i> Editar
                                </a>
                                <?php if ($mesa['Estado'] != 'Mantenimiento'): ?>
                                    <a href="suspender.php?id=<?= $mesa['MesaID'] ?>" class="btn btn-sm btn-secondary" 
                                       onclick="return confirm('¿Está seguro que desea suspender esta mesa?');">
                                        <i class="fas fa-pause"></i> Suspender
                                    </a>
                                <?php else: ?>
                                    <a href="reactivar.php?id=<?= $mesa['MesaID'] ?>" class="btn btn-sm btn-success"
                                       onclick="return confirm('¿Está seguro que desea reactivar esta mesa?');">
                                        <i class="fas fa-play"></i> Reactivar
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

<?php include('../../../includes/footer.php'); ?>