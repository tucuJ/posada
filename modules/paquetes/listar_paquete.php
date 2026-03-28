
<?php
require_once('../../config/database.php');
include('../../includes/header.php');

// Obtener todos los paquetes
$stmt = $conn->query("SELECT * FROM Paquetes");
$paquetes = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Eliminar paquete
if (isset($_GET['eliminar'])) {
    $paqueteID = $_GET['eliminar'];

    // Eliminar los componentes del paquete
    $stmt = $conn->prepare("DELETE FROM PaqueteComponentes WHERE PaqueteID = ?");
    $stmt->execute([$paqueteID]);

    // Eliminar el paquete
    $stmt = $conn->prepare("DELETE FROM Paquetes WHERE PaqueteID = ?");
    $stmt->execute([$paqueteID]);

    echo "<script>window.location.href='listar_paquete.php';</script>";
    exit;
}
?>

<div class="container mt-4">
    <h4>Lista de Paquetes</h4>
    <a href="crear_paquete.php" class="btn btn-primary mb-3">Nuevo Paquete</a>
    <a href="formulario_paquete.php" class="btn btn-primary mb-3">Procesar Venta</a>

    <?php if (isset($_GET['success'])): ?>
        <div class="alert alert-success">Paquete eliminado con éxito.</div>
    <?php endif; ?>

    <table class="table table-bordered">
        <thead>
            <tr>
                <th>Nombre</th>
                <th>Precio</th>
                <th>Duración (días)</th>
                <th>Activo</th>
                <th>Acciones</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($paquetes as $paquete): ?>
                <tr>
                    <td><?= htmlspecialchars($paquete['Nombre']) ?></td>
                    <td><?= number_format($paquete['Precio'], 2) ?></td>
                    <td><?= $paquete['DuracionDias'] ?></td>
                    <td><?= $paquete['Activo'] ? 'Sí' : 'No' ?></td>
                    <td>
                        <a href="editar_paquete.php?id=<?= $paquete['PaqueteID'] ?>" class="btn btn-warning btn-sm">Editar</a>
                        <a href="#" class="btn btn-danger btn-sm" onclick="if(confirm('¿Estás seguro de que deseas eliminar este paquete?')){ window.location='listar_paquete.php?eliminar=<?= $paquete['PaqueteID'] ?>'; }">Eliminar</a>
                    </td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>



<?php include('../../includes/footer.php'); ?>
