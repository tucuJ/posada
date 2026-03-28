<?php 
define('BASE_URL', '/posada/');
require_once('../../config/database.php');
include('../../includes/header.php');

// Manejar mensajes
if (isset($_GET['success'])) {
    $messages = [
        '1' => 'Habitación agregada correctamente',
        '2' => 'Habitación actualizada correctamente',
        '3' => 'Estado de habitación cambiado correctamente'
    ];
    echo "<div class='alert alert-success'>{$messages[$_GET['success']]}</div>";
}

if (isset($_GET['error'])) {
    echo "<div class='alert alert-danger'>Error al procesar la solicitud</div>";
}
?>

<div class="container">
    <h2>Gestión de Habitaciones</h2>
    
    <div class="d-flex justify-content-between mb-4">
        <a href="agregar.php" class="btn btn-primary">
            <i class="fas fa-plus"></i> Agregar Habitación
        </a>
        <a href="tipos/listar.php" class="btn btn-info">
            <i class="fas fa-list"></i> Tipos de Habitación
        </a>
    </div>
    
    <div class="table-responsive">
        <table class="table table-striped table-hover">
            <thead class="table-dark">
                <tr>
                    <th>Número</th>
                    <th>Tipo</th>
                    <th>Capacidad</th>
                    <th>Precio/Noche</th>
                    <th>Estado</th>
                    <th>Acciones</th>
                </tr>
            </thead>
            <tbody>
                <?php
                $query = "SELECT h.HabitacionID, h.Numero, h.Estado, h.Notas, 
                          t.TipoHabitacionID, t.Nombre as TipoNombre, t.Capacidad, t.PrecioNoche
                          FROM Habitaciones h
                          JOIN TiposHabitacion t ON h.TipoHabitacionID = t.TipoHabitacionID
                          ORDER BY h.Numero";
                $stmt = $conn->query($query);
                
                while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                    $estadoClass = [
                        'Disponible' => 'success',
                        'Ocupada' => 'danger',
                        'Reservada' => 'warning',
                        'Mantenimiento' => 'secondary'
                    ][$row['Estado']];
                    
                    echo "<tr>
                            <td>{$row['Numero']}</td>
                            <td>{$row['TipoNombre']}</td>
                            <td>{$row['Capacidad']} personas</td>
                            <td>$".number_format($row['PrecioNoche'], 2)."</td>
                            <td><span class='badge bg-$estadoClass'>{$row['Estado']}</span></td>
                            <td>
                                <a href='editar.php?id={$row['HabitacionID']}' class='btn btn-sm btn-warning' title='Editar'>
                                    <i class='fas fa-edit'></i>
                                </a>
                                <button onclick='cambiarEstado({$row['HabitacionID']}, \"{$row['Numero']}\")' 
                                        class='btn btn-sm btn-info' title='Cambiar Estado'>
                                    <i class='fas fa-sync-alt'></i>
                                </button>
                            </td>
                          </tr>";
                }
                ?>
            </tbody>
        </table>
    </div>
</div>

<script>
function cambiarEstado(id, numero) {
    const nuevoEstado = prompt(`Cambiar estado de habitación ${numero}:\n(Disponible, Ocupada, Reservada, Mantenimiento)`);
    
    if (nuevoEstado && ['Disponible', 'Ocupada', 'Reservada', 'Mantenimiento'].includes(nuevoEstado)) {
        window.location.href = `cambiar_estado.php?id=${id}&estado=${nuevoEstado}`;
    } else if (nuevoEstado) {
        alert('Estado no válido. Use: Disponible, Ocupada, Reservada o Mantenimiento');
    }
}
</script>

<?php include('../../includes/footer.php'); ?>