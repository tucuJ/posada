<?php
require_once('../../config/database.php');
include('../../includes/header.php');

// Verificar si se recibe el ID del servicio
if (isset($_GET['id'])) {
    $servicio_id = $_GET['id'];

    // Obtener los datos del servicio
    $stmt = $conn->prepare("SELECT * FROM Servicios WHERE ServicioID = ?");
    $stmt->execute([$servicio_id]);
    $servicio = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$servicio) {
        echo "Servicio no encontrado.";
        exit();
    }

    // Verificar si el formulario fue enviado
    if ($_SERVER['REQUEST_METHOD'] == 'POST') {
        $nombre = $_POST['nombre'];
        $descripcion = $_POST['descripcion'];
        $precio = $_POST['precio'];
        $tipo = $_POST['tipo'];
        $activo = isset($_POST['activo']) ? 1 : 0;
    $costom = $_POST['costom'];

        // Actualizar los datos del servicio
        $stmt = $conn->prepare("UPDATE Servicios SET Nombre = ?, Descripcion = ?, Precio = ?, Tipo = ?, costom = ?, Activo = ? WHERE ServicioID = ?");
        $stmt->execute([$nombre, $descripcion, $precio, $tipo, $costom, $activo, $servicio_id]);

    echo "<script>window.location.href='listar_servicio.php';</script>";
        exit();
    }
} else {
    echo "ID de servicio no proporcionado.";
    exit();
}
?>

<div class="container">
    <h2>Editar Servicio</h2>
    <form action="editar_servicio.php?id=<?= $servicio['ServicioID'] ?>" method="post">
        <div class="mb-3">
            <label for="nombre" class="form-label">Nombre</label>
            <input type="text" class="form-control" id="nombre" name="nombre" value="<?= htmlspecialchars($servicio['Nombre']) ?>" required>
        </div>
        <div class="mb-3">
            <label for="descripcion" class="form-label">Descripción</label>
            <textarea class="form-control" id="descripcion" name="descripcion" rows="3" required><?= htmlspecialchars($servicio['Descripcion']) ?></textarea>
        </div>
        <div class="mb-3">
            <label for="precio" class="form-label">Precio</label>
            <input type="number" class="form-control" id="precio" name="precio" step="0.01" value="<?= number_format($servicio['Precio'], 2) ?>" required>
        </div>
        <div class="mb-3">
            <label for="tipo" class="form-label">Tipo</label>
            <select class="form-select" id="tipo" name="tipo" required>
                <option value="Habitacion" <?= $servicio['Tipo'] == 'Habitacion' ? 'selected' : '' ?>>Habitación</option>
                <option value="General" <?= $servicio['Tipo'] == 'General' ? 'selected' : '' ?>>General</option>
            </select>
        </div>
        <div class="mb-3">
                    <label for="costom" class="form-label">Costo de Mantenimiento por Uso</label>
                    <div class="input-group">
                        <span class="input-group-text">$</span>
                        <input type="number" min="0" class="form-control" 
                               id="costom" name="costom" value="<?= htmlspecialchars($servicio['costom']) ?>">
                    </div>
                </div>
        <div class="mb-3 form-check">
            <input type="checkbox" class="form-check-input" id="activo" name="activo" <?= $servicio['Activo'] ? 'checked' : '' ?>>
            <label class="form-check-label" for="activo">Activo</label>
        </div>
        <button type="submit" class="btn btn-warning">Actualizar</button>
    </form>
</div>

<?php include('../../includes/footer.php'); ?>
