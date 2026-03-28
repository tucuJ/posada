<?php
require_once('../../config/database.php');
include('../../includes/header.php');

// Verificar si el formulario fue enviado
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $nombre = $_POST['nombre'];
    $descripcion = $_POST['descripcion'];
    $precio = $_POST['precio'];
    $tipo = $_POST['tipo'];
    $costom = $_POST['costom'];
    $activo = isset($_POST['activo']) ? 1 : 0;

    // Insertar el servicio en la base de datos
    $stmt = $conn->prepare("INSERT INTO Servicios (Nombre, Descripcion, Precio, Tipo, costom, Activo) VALUES (?, ?, ?, ?, ?, ?)");
    $stmt->execute([$nombre, $descripcion, $precio, $tipo, $costom, $activo]);

    echo "<script>window.location.href='listar_servicio.php';</script>";
    exit();
}
?>

<div class="container">
    <h2>Añadir Servicio</h2>
    <form action="agregar_servicio.php" method="post">
        <div class="mb-3">
            <label for="nombre" class="form-label">Nombre</label>
            <input type="text" class="form-control" id="nombre" name="nombre" required>
        </div>
        <div class="mb-3">
            <label for="descripcion" class="form-label">Descripción</label>
            <textarea class="form-control" id="descripcion" name="descripcion" rows="3" required></textarea>
        </div>
        <div class="mb-3">
            <label for="precio" class="form-label">Precio</label>
            <input type="number" class="form-control" id="precio" name="precio" step="0.01" required>
        </div>
        <div class="mb-3">
            <label for="tipo" class="form-label">Tipo</label>
            <select class="form-select" id="tipo" name="tipo" required>
                <option value="Habitacion">Habitación</option>
                <option value="General">General</option>
            </select>
        </div>
                       <div class="mb-3">
                    <label for="costom" class="form-label">Costo de Mantenimiento por Uso</label>
                    <div class="input-group">
                        <span class="input-group-text">$</span>
                        <input type="number" min="0" step="0.01" class="form-control" 
                               id="costom" name="costom" value="0">
                    </div>
                </div>
        <div class="mb-3 form-check">
            <input type="checkbox" class="form-check-input" id="activo" name="activo" checked>
            <label class="form-check-label" for="activo">Activo</label>
        </div>
        <button type="submit" class="btn btn-success">Guardar</button>
    </form>
</div>

<?php include('../../includes/footer.php'); ?>
