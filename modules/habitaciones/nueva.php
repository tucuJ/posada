<?php include('../../includes/header.php'); ?>
<?php include('../../config/database.php'); ?>

<div class="container">
    <h2>Nueva Venta</h2>
    
    <div class="row">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header">
                    <h5>Productos y Servicios</h5>
                </div>
                <div class="card-body">
                    <ul class="nav nav-tabs" id="myTab" role="tablist">
                        <li class="nav-item">
                            <a class="nav-link active" id="productos-tab" data-toggle="tab" href="#productos" role="tab">Productos</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" id="servicios-tab" data-toggle="tab" href="#servicios" role="tab">Servicios</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" id="habitaciones-tab" data-toggle="tab" href="#habitaciones" role="tab">Habitaciones</a>
                        </li>
                    </ul>
                    
                    <div class="tab-content mt-3" id="myTabContent">
                        <div class="tab-pane fade show active" id="productos" role="tabpanel">
                            <div class="form-group">
                                <input type="text" class="form-control" id="buscarProducto" placeholder="Buscar producto...">
                            </div>
                            <div class="row" id="listaProductos">
                                <?php
                                $query = "SELECT * FROM Productos WHERE Activo = 1 ORDER BY Nombre";
                                $stmt = $conn->query($query);
                                
                                while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                                    echo "<div class='col-md-3 mb-3 producto-item' data-id='{$row['ProductoID']}' data-nombre='{$row['Nombre']}' data-precio='{$row['PrecioVenta']}'>
                                            <div class='card h-100'>
                                                <div class='card-body text-center'>
                                                    <h6 class='card-title'>{$row['Nombre']}</h6>
                                                    <p class='card-text'>$".number_format($row['PrecioVenta'], 2)."</p>
                                                    <button class='btn btn-sm btn-primary agregar-producto'>Agregar</button>
                                                </div>
                                            </div>
                                          </div>";
                                }
                                ?>
                            </div>
                        </div>
                        
                        <div class="tab-pane fade" id="servicios" role="tabpanel">
                            <div class="row">
                                <?php
                                $query = "SELECT * FROM Servicios WHERE Activo = 1 AND Tipo = 'General' ORDER BY Nombre";
                                $stmt = $conn->query($query);
                                
                                while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                                    echo "<div class='col-md-4 mb-3'>
                                            <div class='card h-100'>
                                                <div class='card-body'>
                                                    <h5 class='card-title'>{$row['Nombre']}</h5>
                                                    <p class='card-text'>{$row['Descripcion']}</p>
                                                    <p class='card-text'><strong>$".number_format($row['Precio'], 2)."</strong></p>
                                                    <button class='btn btn-primary agregar-servicio' data-id='{$row['ServicioID']}'>Agregar</button>
                                                </div>
                                            </div>
                                          </div>";
                                }
                                ?>
                            </div>
                        </div>
                        
                        <div class="tab-pane fade" id="habitaciones" role="tabpanel">
                            <div class="row">
                                <?php
                                $query = "SELECT h.HabitacionID, h.Numero, t.Nombre as Tipo, t.PrecioNoche 
                                          FROM Habitaciones h
                                          JOIN TiposHabitacion t ON h.TipoHabitacionID = t.TipoHabitacionID
                                          WHERE h.Estado = 'Disponible'";
                                $stmt = $conn->query($query);
                                
                                while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                                    echo "<div class='col-md-4 mb-3'>
                                            <div class='card h-100'>
                                                <div class='card-body'>
                                                    <h5 class='card-title'>Habitación {$row['Numero']}</h5>
                                                    <p class='card-text'>Tipo: {$row['Tipo']}</p>
                                                    <p class='card-text'><strong>$".number_format($row['PrecioNoche'], 2)." por noche</strong></p>
                                                    <button class='btn btn-primary agregar-habitacion' data-id='{$row['HabitacionID']}'>Reservar</button>
                                                </div>
                                            </div>
                                          </div>";
                                }
                                ?>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="col-md-4">
            <div class="card">
                <div class="card-header">
                    <h5>Resumen de Venta</h5>
                </div>
                <div class="card-body">
                    <form id="formVenta" action="procesar_venta.php" method="post">
                        <div class="form-group">
                            <label for="cliente">Cliente</label>
                            <select class="form-control" id="cliente" name="cliente" required>
                                <option value="">Seleccione un cliente</option>
                                <?php
                                $query = "SELECT * FROM Clientes ORDER BY Nombre, Apellido";
                                $stmt = $conn->query($query);
                                
                                while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                                    echo "<option value='{$row['ClienteID']}'>{$row['Nombre']} {$row['Apellido']}</option>";
                                }
                                ?>
                            </select>
                        </div>
                        
                        <table class="table table-sm" id="tablaItems">
                            <thead>
                                <tr>
                                    <th>Item</th>
                                    <th>Cant.</th>
                                    <th>Precio</th>
                                    <th>Total</th>
                                    <th></th>
                                </tr>
                            </thead>
                            <tbody id="itemsVenta">
                                <!-- Aquí se agregarán los items dinámicamente -->
                            </tbody>
                            <tfoot>
                                <tr>
                                    <th colspan="3">Subtotal</th>
                                    <th id="subtotal">$0.00</th>
                                    <th></th>
                                </tr>
                                <tr>
                                    <th colspan="3">Impuesto</th>
                                    <th id="impuesto">$0.00</th>
                                    <th></th>
                                </tr>
                                <tr>
                                    <th colspan="3">Total</th>
                                    <th id="total">$0.00</th>
                                    <th></th>
                                </tr>
                            </tfoot>
                        </table>
                        
                        <div class="form-group">
                            <label for="metodoPago">Método de Pago</label>
                            <select class="form-control" id="metodoPago" name="metodoPago" required>
                                <option value="Efectivo">Efectivo</option>
                                <option value="Tarjeta">Tarjeta</option>
                                <option value="Transferencia">Transferencia</option>
                            </select>
                        </div>
                        <button type="submit" class="btn btn-success btn-block" id="finalizarVenta">Finalizar Venta</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
// JavaScript para manejar la venta (agregar items, calcular totales, etc.)
$(document).ready(function() {
    // Buscar productos
    $('#buscarProducto').keyup(function() {
        var search = $(this).val().toLowerCase();
        $('.producto-item').each(function() {
            var nombre = $(this).data('nombre').toLowerCase();
            if (nombre.includes(search)) {
                $(this).show();
            } else {
                $(this).hide();
            }
        });
    });
    
    // Agregar producto al carrito
    $('.agregar-producto').click(function() {
        var card = $(this).closest('.producto-item');
        var id = card.data('id');
        var nombre = card.data('nombre');
        var precio = card.data('precio');
        
        agregarItem(id, 'producto', nombre, precio, 1);
    });
    
    // Función para agregar items al resumen
    function agregarItem(id, tipo, nombre, precio, cantidad) {
        // Lógica para agregar items a la tabla
        // ...
    }
    
    // Calcular totales
    function calcularTotales() {
        // Lógica para calcular subtotal, impuesto y total
        // ...
    }
});
</script>

<?php include('../../includes/footer.php'); ?>