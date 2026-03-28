<?php 
define('BASE_URL', '/posada/');
require_once('../../config/database.php');
include('../../includes/header.php');

// Manejar mensajes
if (isset($_GET['success'])) {
    $messages = [
        '1' => 'Cliente agregado correctamente',
        '2' => 'Cliente actualizado correctamente',
        '3' => 'Cliente marcado como inactivo'
    ];
    echo "<div class='alert alert-success'>{$messages[$_GET['success']]}</div>";
}

if (isset($_GET['error'])) {
    echo "<div class='alert alert-danger'>Error al procesar la solicitud</div>";
}

// Obtener parámetro de búsqueda
$busqueda = isset($_GET['busqueda']) ? trim($_GET['busqueda']) : '';

// Construir consulta SQL
$query = "SELECT * FROM Clientes WHERE 1=1";
$params = [];

if (!empty($busqueda)) {
    $query .= " AND (CONCAT(Nombre, ' ', Apellido) LIKE :busqueda OR 
                    NumeroDocumento LIKE :busqueda OR
                    Telefono LIKE :busqueda OR
                    Email LIKE :busqueda)";
    $params[':busqueda'] = "%$busqueda%";
}

$query .= " ORDER BY Nombre, Apellido";

// Preparar y ejecutar consulta
$stmt = $conn->prepare($query);
foreach ($params as $key => $value) {
    $stmt->bindValue($key, $value);
}
$stmt->execute();
$clientes = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<div class="container">
    <h2>Gestión de Clientes</h2>
    
    <div class="d-flex justify-content-between mb-4">
        <a href="agregar.php" class="btn btn-primary">
            <i class="fas fa-plus"></i> Nuevo Cliente
        </a>
        <form method="GET" action="" class="input-group" style="width: 300px;">
            <input type="text" name="busqueda" class="form-control" 
                   placeholder="Buscar cliente..." value="<?= htmlspecialchars($busqueda) ?>">
            <button class="btn btn-outline-secondary" type="submit">
                <i class="fas fa-search"></i>
            </button>
            <?php if (!empty($busqueda)): ?>
                <a href="?" class="btn btn-outline-danger">
                    <i class="fas fa-times"></i>
                </a>
            <?php endif; ?>
        </form>
    </div>
    
    <div class="table-responsive">
        <?php if (empty($clientes)): ?>
            <div class="alert alert-warning">No se encontraron clientes</div>
        <?php else: ?>
            <table class="table table-striped table-hover">
                <thead class="table-dark">
                    <tr>
                        <th>Nombre</th>
                        <th>Documento</th>
                        <th>Teléfono</th>
                        <th>Email</th>
                        <th>Reservas</th>
                        <th>Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($clientes as $cliente): 
                        // Obtener número de reservas del cliente
                        $queryReservas = "SELECT COUNT(*) FROM Reservaciones WHERE ClienteID = ?";
                        $stmtReservas = $conn->prepare($queryReservas);
                        $stmtReservas->execute([$cliente['ClienteID']]);
                        $numReservas = $stmtReservas->fetchColumn();
                    ?>
                    <tr>
                        <td><?= htmlspecialchars("{$cliente['Nombre']} {$cliente['Apellido']}") ?></td>
                        <td><?= htmlspecialchars("{$cliente['TipoDocumento']}-{$cliente['NumeroDocumento']}") ?></td>
                        <td><?= htmlspecialchars($cliente['Telefono'] ?? 'N/A') ?></td>
                        <td><?= htmlspecialchars($cliente['Email'] ?? 'N/A') ?></td>
                        <td><?= $numReservas ?></td>
                        <td>
                            <a href="perfil.php?id=<?= $cliente['ClienteID'] ?>" class="btn btn-sm btn-info" title="Ver perfil">
                                <i class="fas fa-user"></i>
                            </a>
                            <a href="editar.php?id=<?= $cliente['ClienteID'] ?>" class="btn btn-sm btn-warning" title="Editar">
                                <i class="fas fa-edit"></i>
                            </a>
                            <?php if ($numReservas == 0): ?>
                                <a href="procesar.php?action=delete&id=<?= $cliente['ClienteID'] ?>" 
                                   class="btn btn-sm btn-danger" title="Eliminar"
                                   onclick="return confirm('¿Eliminar este cliente?')">
                                    <i class="fas fa-trash"></i>
                                </a>
                            <?php endif; ?>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php endif; ?>
    </div>
</div>

<?php include('../../includes/footer.php'); ?>