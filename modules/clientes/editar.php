<?php 
define('BASE_URL', '/posada/');
require_once('../../config/database.php');
include('../../includes/header.php');

if (!isset($_GET['id'])) {
    header("Location: listar.php");
    exit();
}

$clienteId = $_GET['id'];

// Obtener datos del cliente
$query = "SELECT * FROM Clientes WHERE ClienteID = ?";
$stmt = $conn->prepare($query);
$stmt->execute([$clienteId]);
$cliente = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$cliente) {
    header("Location: listar.php?error=1");
    exit();
}

// Manejar errores
if (isset($_GET['error'])) {
    $errorMsg = [
        '1' => 'Ya existe un cliente con este documento',
        '2' => 'Error al actualizar el cliente'
    ];
    echo "<div class='alert alert-danger'>{$errorMsg[$_GET['error']]}</div>";
}
?>

<div class="container">
    <h2>Editar Cliente: <?= "{$cliente['Nombre']} {$cliente['Apellido']}" ?></h2>
    
    <form action="procesar.php" method="post">
        <input type="hidden" name="id" value="<?= $clienteId ?>">
        
        <div class="row">
            <div class="col-md-6">
                <div class="form-group mb-3">
                    <label for="nombre" class="form-label">Nombre</label>
                    <input type="text" class="form-control" id="nombre" name="nombre" 
                           value="<?= htmlspecialchars($cliente['Nombre']) ?>" required>
                </div>
                
                <div class="form-group mb-3">
                    <label for="apellido" class="form-label">Apellido</label>
                    <input type="text" class="form-control" id="apellido" name="apellido" 
                           value="<?= htmlspecialchars($cliente['Apellido']) ?>" required>
                </div>
                
                <div class="form-group mb-3">
                    <label for="tipoDocumento" class="form-label">Tipo de Documento</label>
                    <select class="form-select" id="tipoDocumento" name="tipoDocumento" required>
                        <option value="CED" <?= $cliente['TipoDocumento'] == 'CED' ? 'selected' : '' ?>>Cédula</option>
                        <option value="PAS" <?= $cliente['TipoDocumento'] == 'PAS' ? 'selected' : '' ?>>Pasaporte</option>
                        <option value="RUC" <?= $cliente['TipoDocumento'] == 'RUC' ? 'selected' : '' ?>>RUC</option>
                    </select>
                </div>
                
                <div class="form-group mb-3">
                    <label for="numeroDocumento" class="form-label">Número de Documento</label>
                    <input type="text" class="form-control" id="numeroDocumento" name="numeroDocumento" 
                           value="<?= htmlspecialchars($cliente['NumeroDocumento']) ?>" required>
                </div>
            </div>
            
            <div class="col-md-6">
                <div class="form-group mb-3">
                    <label for="telefono" class="form-label">Teléfono</label>
                    <input type="tel" class="form-control" id="telefono" name="telefono" 
                           value="<?= htmlspecialchars($cliente['Telefono']) ?>">
                </div>
                
                <div class="form-group mb-3">
                    <label for="email" class="form-label">Email</label>
                    <input type="email" class="form-control" id="email" name="email" 
                           value="<?= htmlspecialchars($cliente['Email']) ?>">
                </div>
                
                <div class="form-group mb-3">
                    <label for="direccion" class="form-label">Dirección</label>
                    <textarea class="form-control" id="direccion" name="direccion" rows="2"><?= htmlspecialchars($cliente['Direccion']) ?></textarea>
                </div>
            </div>
        </div>
        
        <div class="d-flex justify-content-between mt-3">
            <button type="submit" class="btn btn-primary" name="editar">
                <i class="fas fa-save"></i> Guardar Cambios
            </button>
            <a href="perfil.php?id=<?= $clienteId ?>" class="btn btn-secondary">
                <i class="fas fa-times"></i> Cancelar
            </a>
        </div>
    </form>
</div>

<?php include('../../includes/footer.php'); ?>