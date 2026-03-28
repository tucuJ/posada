<?php
require_once('../../config/database.php');
include('../../includes/header.php');

// Obtener el paquete a editar
$paqueteID = $_GET['id'];
$stmt = $conn->prepare("SELECT * FROM Paquetes WHERE PaqueteID = ?");
$stmt->execute([$paqueteID]);
$paquete = $stmt->fetch(PDO::FETCH_ASSOC);

// Obtener componentes del paquete
$stmt = $conn->prepare("SELECT * FROM PaqueteComponentes WHERE PaqueteID = ?");
$stmt->execute([$paqueteID]);
$componentes = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Obtener productos, servicios y habitaciones para mostrar como opciones
$productos = $conn->query("SELECT ProductoID, Nombre FROM Productos WHERE Activo = 1")->fetchAll(PDO::FETCH_ASSOC);
$servicios = $conn->query("SELECT ServicioID, Nombre FROM Servicios WHERE Activo = 1")->fetchAll(PDO::FETCH_ASSOC);
$habitaciones = $conn->query("SELECT HabitacionID, CONCAT('Hab. ', HabitacionID, ' - ', Numero) AS Nombre FROM Habitaciones WHERE Estado != 'Mantenimiento'")->fetchAll(PDO::FETCH_ASSOC);

// Guardar cambios al paquete
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    try {
        $conn->beginTransaction();

        // Actualizar paquete
        $stmt = $conn->prepare("UPDATE Paquetes SET Nombre = ?, Descripcion = ?, Precio = ?, DuracionDias = ?, Activo = ? WHERE PaqueteID = ?");
        $stmt->execute([
            $_POST['nombre'],
            $_POST['descripcion'],
            $_POST['precio'],
            $_POST['duracion'],
            isset($_POST['activo']) ? 1 : 0,
            $paqueteID
        ]);

        // Eliminar componentes anteriores
        $stmt = $conn->prepare("DELETE FROM PaqueteComponentes WHERE PaqueteID = ?");
        $stmt->execute([$paqueteID]);

        // Insertar nuevos componentes
        foreach ($_POST['componentes'] as $componente) {
            $tipo = $componente['tipo'];
            $itemID = $componente['item'];
            $cantidad = $componente['cantidad'];

            $stmt = $conn->prepare("INSERT INTO PaqueteComponentes (PaqueteID, Tipo, ItemID, Cantidad) VALUES (?, ?, ?, ?)");
            $stmt->execute([$paqueteID, $tipo, $itemID, $cantidad]);
        }

        $conn->commit();
    echo "<script>window.location.href='listar_paquete.php?sussec';</script>";
        exit;
    } catch (Exception $e) {
        $conn->rollBack();
    echo "<script>window.location.href='listar_paquete.php?error';</script>";
        exit;
    }
}
?>

<div class="container mt-4">
    <h4>Editar Paquete</h4>
    <form method="POST" class="mt-3">
        <div class="row">
            <div class="col-md-6 mb-3">
                <label>Nombre del paquete</label>
                <input type="text" name="nombre" class="form-control" value="<?= htmlspecialchars($paquete['Nombre']) ?>" required>
            </div>
            <div class="col-md-3 mb-3">
                <label>Precio</label>
                <input type="number" step="0.01" name="precio" class="form-control" value="<?= $paquete['Precio'] ?>" required>
            </div>
            <div class="col-md-3 mb-3">
                <label>Duración (días)</label>
                <input type="number" name="duracion" class="form-control" value="<?= $paquete['DuracionDias'] ?>" required>
            </div>
            <div class="col-md-12 mb-3">
                <label>Descripción</label>
                <textarea name="descripcion" class="form-control"><?= htmlspecialchars($paquete['Descripcion']) ?></textarea>
            </div>
            <div class="col-md-12 mb-3">
                <label><input type="checkbox" name="activo" <?= $paquete['Activo'] ? 'checked' : '' ?>> Paquete Activo</label>
            </div>
        </div>

        <h5 class="mt-4">Componentes del paquete</h5>
        <div id="componentes-container" class="border p-3 rounded bg-light mb-3">
            <?php foreach ($componentes as $index => $componente): ?>
                <div class="row mb-2 align-items-end">
                    <div class="col-md-3">
                        <label>Tipo</label>
                        <select name="componentes[<?= $index ?>][tipo]" class="form-control tipo-selector" onchange="cargarOpciones(this, <?= $index ?>)" required>
                            <option value="">Seleccione</option>
                            <option value="Producto" <?= $componente['Tipo'] == 'Producto' ? 'selected' : '' ?>>Producto</option>
                            <option value="Servicio" <?= $componente['Tipo'] == 'Servicio' ? 'selected' : '' ?>>Servicio</option>
                            <option value="Habitacion" <?= $componente['Tipo'] == 'Habitacion' ? 'selected' : '' ?>>Habitación</option>
                        </select>
                    </div>
                    <div class="col-md-5">
                        <label>Item</label>
                        <select name="componentes[<?= $index ?>][item]" class="form-control opciones-item" required>
                            <option value="">Seleccione un tipo primero</option>
                            <?php
                            $items = [];
                            if ($componente['Tipo'] == 'Producto') {
                                $items = $productos;
                            } elseif ($componente['Tipo'] == 'Servicio') {
                                $items = $servicios;
                            } elseif ($componente['Tipo'] == 'Habitacion') {
                                $items = $habitaciones;
                            }
                            foreach ($items as $item):
                            ?>
                                <option value="<?= $item['ProductoID'] ?? $item['ServicioID'] ?? $item['HabitacionID'] ?>" <?= $componente['ItemID'] == ($item['ProductoID'] ?? $item['ServicioID'] ?? $item['HabitacionID']) ? 'selected' : '' ?>>
                                    <?= $item['Nombre'] ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-md-2">
                        <label>Cantidad</label>
                        <input type="number" name="componentes[<?= $index ?>][cantidad]" class="form-control" value="<?= $componente['Cantidad'] ?>" required>
                    </div>
                    <div class="col-md-2">
                        <button type="button" class="btn btn-danger" onclick="this.closest('.row').remove()">Quitar</button>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
        <button type="button" class="btn btn-outline-secondary mb-3" onclick="agregarComponente()">+ Agregar Componente</button>

        <div>
            <button type="submit" class="btn btn-success">Guardar Cambios</button>
            <a href="listar_paquete.php" class="btn btn-secondary">Cancelar</a>
        </div>
    </form>
</div>

<script>
const productos = <?= json_encode($productos) ?>;
const servicios = <?= json_encode($servicios) ?>;
const habitaciones = <?= json_encode($habitaciones) ?>;

function agregarComponente() {
    const container = document.getElementById('componentes-container');
    const index = container.children.length;

    const div = document.createElement('div');
    div.classList.add('row', 'mb-2', 'align-items-end');
    div.innerHTML = `
        <div class="col-md-3">
            <label>Tipo</label>
            <select name="componentes[${index}][tipo]" class="form-control tipo-selector" onchange="cargarOpciones(this, ${index})" required>
                <option value="">Seleccione</option>
                <option value="Producto">Producto</option>
                <option value="Servicio">Servicio</option>
                <option value="Habitacion">Habitación</option>
            </select>
        </div>
        <div class="col-md-5">
            <label>Item</label>
            <select name="componentes[${index}][item]" class="form-control opciones-item" required>
                <option value="">Seleccione un tipo primero</option>
            </select>
        </div>
        <div class="col-md-2">
            <label>Cantidad</label>
            <input type="number" name="componentes[${index}][cantidad]" class="form-control" value="1" required>
        </div>
        <div class="col-md-2">
            <button type="button" class="btn btn-danger" onclick="this.closest('.row').remove()">Quitar</button>
        </div>
    `;
    container.appendChild(div);
}

function cargarOpciones(select, index) {
    const tipo = select.value;
    const itemSelect = select.closest('.row').querySelector('.opciones-item');
    let data = [];

    if (tipo === 'Producto') data = productos;
    else if (tipo === 'Servicio') data = servicios;
    else if (tipo === 'Habitacion') data = habitaciones;

    itemSelect.innerHTML = '<option value="">Seleccione</option>';
    data.forEach(item => {
        const option = document.createElement('option');
        option.value = item[Object.keys(item)[0]];
        option.text = item.Nombre;
        itemSelect.appendChild(option);
    });
}
</script>

<?php include('../../includes/footer.php'); ?>
