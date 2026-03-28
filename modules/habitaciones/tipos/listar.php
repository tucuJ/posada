<?php 
define('BASE_URL', '/posada/');
require_once('../../../config/database.php');
include('../../../includes/header.php');

// Manejar mensajes
if (isset($_GET['success'])) {
    $messages = [
        '1' => 'Tipo de habitación agregado correctamente',
        '2' => 'Tipo de habitación actualizado correctamente'
    ];
    echo "<div class='alert alert-success'>{$messages[$_GET['success']]}</div>";
}

if (isset($_GET['error'])) {
    echo "<div class='alert alert-danger'>Error al procesar la solicitud</div>";
}
?>

<div class="container">
    <h2>Tipos de Habitación</h2>
    
    <div class="d-flex justify-content-between mb-4">
        <a href="agregar.php" class="btn btn-primary">
            <i class="fas fa-plus"></i> Agregar Tipo
        </a>
        <a href="../listar.php" class="btn btn-secondary">
            <i class="fas fa-arrow-left"></i> Volver a Habitaciones
        </a>
    </div>
    
    <div class="table-responsive">
        <table class="table table-striped table-hover">
            <thead class="table-dark">
                <tr>
                    <th>Nombre</th>
                    <th>Capacidad</th>
                    <th>Precio/Noche</th>
                    <th>Acciones</th>
                </tr>
            </thead>
            <tbody>
                <?php
                $query = "SELECT * FROM TiposHabitacion ORDER BY Nombre";
                $stmt = $conn->query($query);
                
                while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                    echo "<tr>
                            <td>{$row['Nombre']}</td>
                            <td>{$row['Capacidad']} personas</td>
                            <td>$".number_format($row['PrecioNoche'], 2)."</td>
                            <td>
                                <a href='editar.php?id={$row['TipoHabitacionID']}' class='btn btn-sm btn-warning' title='Editar'>
                                    <i class='fas fa-edit'></i>
                                </a>
                            </td>
                          </tr>";
                }
                ?>
            </tbody>
        </table>
    </div>
</div>

<?php include('../../../includes/footer.php'); ?>