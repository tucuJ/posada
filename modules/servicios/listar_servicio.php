<?php
require_once('../../config/database.php');
include('../../includes/header.php');

// Obtener todos los servicios
$servicios = $conn->query("SELECT * FROM Servicios ORDER BY Nombre")->fetchAll(PDO::FETCH_ASSOC);
?>

<div class="container">
    <h2>Lista de Servicios</h2>
    <a href="agregar_servicio.php" class="btn btn-primary mb-3">Añadir Servicio</a>
    <table class="table table-bordered">
        <thead>
            <tr>
                <th>Nombre</th>
                <th>Descripción</th>
                <th>Precio</th>
                <th>Tipo</th>
                <th>Activo</th>
                <th>Acciones</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($servicios as $servicio): ?>
                <tr>
                    <td><?= htmlspecialchars($servicio['Nombre']) ?></td>
                    <td><?= htmlspecialchars($servicio['Descripcion']) ?></td>
                    <td>$<?= number_format($servicio['Precio'], 2) ?></td>
                    <td><?= htmlspecialchars($servicio['Tipo']) ?></td>
                    <td><?= $servicio['Activo'] ? 'Sí' : 'No' ?></td>
                    <td>
                        <a href="editar_servicio.php?id=<?= $servicio['ServicioID'] ?>" class="btn btn-warning btn-sm">Editar</a>
                        <a href="eliminar_servicio.php?id=<?= $servicio['ServicioID'] ?>" class="btn btn-danger btn-sm" onclick="return confirm('¿Estás seguro de que quieres eliminar este servicio?')">Eliminar</a>
                    </td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>

<?php include('../../includes/footer.php'); ?>
