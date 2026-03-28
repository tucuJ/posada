<?php require_once('../../config/database.php');require_once('../../includes/header.php');
 ?>
<?php
if (isset($_SESSION['error_paquete'])) {
    echo "<div class='error'>" . $_SESSION['error_paquete'] . "</div>";
    unset($_SESSION['error_paquete']);
}
?>
<!DOCTYPE html>
<html lang="es">
<head>

    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Procesar Paquete</title>
</head>
<body>
    <div class="container mt-5">
        <h2>Procesar Paquete</h2>
        
        <?php if (isset($error)): ?>
            <div class="alert alert-danger"><?php echo htmlspecialchars($error); ?></div>
        <?php endif; ?>
        
        <form action="procesar_paquete.php" method="post">
            <div class="mb-3">
                <label for="paquete_id" class="form-label">Paquete</label>
                <select class="form-select" id="paquete_id" name="paquete_id" required>
                    <option value="">Seleccione un paquete</option>
                    <?php
                    $stmt = $conn->query("SELECT * FROM Paquetes WHERE Activo = TRUE");
                    while ($paquete = $stmt->fetch(PDO::FETCH_ASSOC)) {
                        echo "<option value='{$paquete['PaqueteID']}'>{$paquete['Nombre']} - {$paquete['Precio']}</option>";
                    }
                    ?>
                </select>
            </div>
            
            <div class="mb-3">
                <label for="cliente_id" class="form-label">Cliente</label>
                <select class="form-select" id="cliente_id" name="cliente_id" required>
                    <option value="">Seleccione un cliente</option>
                    <?php
                    $stmt = $conn->query("SELECT * FROM Clientes ORDER BY Nombre, Apellido");
                    while ($cliente = $stmt->fetch(PDO::FETCH_ASSOC)) {
                        echo "<option value='{$cliente['ClienteID']}'>{$cliente['Nombre']} {$cliente['Apellido']} - {$cliente['NumeroDocumento']}</option>";
                    }
                    ?>
                </select>
            </div>
            
            <div class="mb-3">
                <label for="fecha_inicio" class="form-label">Fecha de Inicio</label>
                <input type="date" class="form-control" id="fecha_inicio" name="fecha_inicio" required min="<?php echo date('Y-m-d'); ?>">
            </div>
            
            <div class="mb-3">
                <label for="metodo_pago" class="form-label">Método de Pago</label>
                <select class="form-select" id="metodo_pago" name="metodo_pago" required>
                    <option value="Efectivo">Efectivo</option>
                    <option value="Tarjeta">Tarjeta</option>
                    <option value="Transferencia">Transferencia</option>
                    <option value="Mixto">Mixto</option>
                </select>
            </div>
            
            <button type="submit" class="btn btn-primary">Procesar Paquete</button>
        </form>
    </div>
<?php
require_once('../../includes/footer.php');
?>
