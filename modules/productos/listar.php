<?php 
define('BASE_URL', '/posada/');
require_once('../../config/database.php');
include('../../includes/header.php');

// Manejar mensajes de éxito/error
$alertMessages = [
    'success' => [
        '1' => 'Producto agregado correctamente',
        '2' => 'Producto actualizado correctamente',
        '3' => 'Producto desactivado correctamente',
        '4' => 'Producto reactivado correctamente'
    ],
    'error' => [
        '1' => 'Error en la base de datos',
        '2' => 'Producto no encontrado',
        '3' => 'ID de producto inválido',
        '4' => 'No se puede modificar un producto con stock'
    ]
];

if (isset($_GET['success']) && isset($alertMessages['success'][$_GET['success']])) {
    echo "<div class='alert alert-success alert-dismissible fade show'>
            {$alertMessages['success'][$_GET['success']]}
            <button type='button' class='btn-close' data-bs-dismiss='alert' aria-label='Close'></button>
          </div>";
}

if (isset($_GET['error']) && isset($alertMessages['error'][$_GET['error']])) {
    echo "<div class='alert alert-danger alert-dismissible fade show'>
            {$alertMessages['error'][$_GET['error']]}
            <button type='button' class='btn-close' data-bs-dismiss='alert' aria-label='Close'></button>
          </div>";
}
?>

<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2><i class="fas fa-boxes me-2"></i>Inventario de Productos</h2>
        <div>
            <a href="agregar.php" class="btn btn-primary me-2">
                <i class="fas fa-plus me-1"></i> Nuevo Producto
            </a>
            <div class="btn-group">
                <button type="button" class="btn btn-outline-secondary dropdown-toggle" data-bs-toggle="dropdown">
                    <i class="fas fa-filter me-1"></i> Filtros
                </button>
                <ul class="dropdown-menu">
                    <li><a class="dropdown-item filter-option" href="#" data-filter="all">Todos</a></li>
                    <li><a class="dropdown-item filter-option" href="#" data-filter="stock-min">Stock mínimo</a></li>
                    <li><a class="dropdown-item filter-option" href="#" data-filter="stock-zero">Sin stock</a></li>
                    <li><a class="dropdown-item filter-option" href="#" data-filter="active">Activos</a></li>
                    <li><a class="dropdown-item filter-option" href="#" data-filter="inactive">Inactivos</a></li>
                </ul>
            </div>
        </div>
    </div>

    <div class="card shadow mb-4">
        <div class="card-header py-3 d-flex justify-content-between align-items-center">
            <h6 class="m-0 font-weight-bold text-primary">Listado de Productos</h6>
            <div class="col-md-4">
                <div class="input-group">
                    <input type="text" id="buscadorProductos" class="form-control" placeholder="Buscar por nombre o código...">
                    <button class="btn btn-outline-secondary" type="button" id="btnLimpiarBusqueda">
                        <i class="fas fa-times"></i>
                    </button>
                </div>
            </div>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-striped table-hover table-bordered" id="tablaProductos">
                    <thead class="">
                        <tr>
                            <th width="120">Código</th>
                            <th>Nombre</th>
                            <th>Categoría</th>
                            <th width="120">P. Compra</th>
                            <th width="120">P. Venta</th>
                            <th width="100">Stock</th>
                            <th width="100">Mínimo</th>
                            <th width="100">Estado</th>
                            <th width="120">Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        $query = "SELECT p.*, c.Nombre as CategoriaNombre 
                                FROM Productos p 
                                LEFT JOIN CategoriasProductos c ON p.CategoriaID = c.CategoriaID
                                ORDER BY p.Nombre";
                        $stmt = $conn->query($query);
                        
                        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                            $stockClass = '';
                            if ($row['Stock'] == 0) {
                                $stockClass = 'bg-danger text-white';
                            } elseif ($row['Stock'] <= $row['StockMinimo']) {
                                $stockClass = 'bg-warning text-dark';
                            }
                            
                            $estado = $row['Activo'] ? 
                                      "<span class='badge bg-success'>Activo</span>" : 
                                      "<span class='badge bg-secondary'>Inactivo</span>";
                            
                            echo "<tr class='producto-row' data-nombre='".htmlspecialchars($row['Nombre'])."' 
                                    data-codigo='".htmlspecialchars($row['CodigoBarras'])."'
                                    data-status='".($row['Activo'] ? 'active' : 'inactive')."'
                                    data-stock='".($row['Stock'] <= $row['StockMinimo'] ? 'stock-min' : '')."'
                                    data-zero='".($row['Stock'] == 0 ? 'stock-zero' : '')."'>
                                    <td>".htmlspecialchars($row['CodigoBarras'])."</td>
                                    <td>".htmlspecialchars($row['Nombre'])."</td>
                                    <td>".htmlspecialchars($row['CategoriaNombre'])."</td>
                                    <td class='text-end'>$".number_format($row['PrecioCompra'], 2)."</td>
                                    <td class='text-end'>$".number_format($row['PrecioVenta'], 2)."</td>
                                    <td class='text-center $stockClass'>{$row['Stock']}</td>
                                    <td class='text-center'>{$row['StockMinimo']}</td>
                                    <td class='text-center'>$estado</td>
                                    <td class='text-center'>
                                        <a href='editar.php?id={$row['ProductoID']}' class='btn btn-sm btn-warning me-1' title='Editar'>
                                            <i class='fas fa-edit'></i>
                                        </a>
                                        <button class='btn btn-sm btn-danger btn-eliminar' 
                                                data-id='{$row['ProductoID']}' 
                                                data-nombre='".htmlspecialchars($row['Nombre'])."' 
                                                data-active='{$row['Activo']}'
                                                title='".($row['Activo'] ? 'Desactivar' : 'Activar')."'>
                                            <i class='".($row['Activo'] ? 'fas fa-eye-slash' : 'fas fa-eye')."'></i>
                                        </button>
                                    </td>
                                </tr>";
                        }
                        ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<!-- Modal de confirmación -->
<div class="modal fade" id="confirmModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Confirmar acción</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <p id="modalMessage">¿Está seguro que desea realizar esta acción?</p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                <a id="btnConfirmAction" href="#" class="btn btn-primary">Confirmar</a>
            </div>
        </div>
    </div>
</div>

<?php include('../../includes/footer.php'); ?>

<script>
$(document).ready(function() {
    // Búsqueda en tiempo real
    $('#buscadorProductos').keyup(function() {
        const searchText = $(this).val().toLowerCase();
        $('.producto-row').each(function() {
            const nombre = $(this).data('nombre').toLowerCase();
            const codigo = $(this).data('codigo').toLowerCase();
            if (nombre.includes(searchText) || codigo.includes(searchText)) {
                $(this).show();
            } else {
                $(this).hide();
            }
        });
    });

    // Limpiar búsqueda
    $('#btnLimpiarBusqueda').click(function() {
        $('#buscadorProductos').val('').keyup();
    });

    // Filtros
    $('.filter-option').click(function(e) {
        e.preventDefault();
        const filter = $(this).data('filter');
        
        $('.producto-row').each(function() {
            const show = 
                filter === 'all' ||
                (filter === 'stock-min' && $(this).data('stock') === 'stock-min') ||
                (filter === 'stock-zero' && $(this).data('zero') === 'stock-zero') ||
                (filter === 'active' && $(this).data('status') === 'active') ||
                (filter === 'inactive' && $(this).data('status') === 'inactive');
            
            $(this).toggle(show);
        });
    });

    // Confirmación para activar/desactivar
    $('.btn-eliminar').click(function() {
        const id = $(this).data('id');
        const nombre = $(this).data('nombre');
        const isActive = $(this).data('active');
        
        const action = isActive ? 'desactivar' : 'activar';
        $('#modalMessage').html(`¿Está seguro que desea ${action} el producto <strong>${nombre}</strong>?`);
        
        $('#btnConfirmAction').attr('href', `eliminar.php?id=${id}`);
        $('#confirmModal').modal('show');
    });
});
</script>