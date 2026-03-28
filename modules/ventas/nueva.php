<?php 
define('BASE_URL', '/posada/');
require_once('../../config/database.php');
include('../../includes/header.php');

// Verificar permisos
if (!($_SESSION['rol'] === 'Admin' || $_SESSION['rol'] === 'Ventas')) {
    header('Location: /dashboard.php');
    exit;
}

// Obtener clientes activos
$clientes = $conn->query("SELECT ClienteID, CONCAT(Nombre, ' ', Apellido) AS NombreCompleto 
                         FROM Clientes 
                    
                         ORDER BY Nombre, Apellido")->fetchAll(PDO::FETCH_ASSOC);

// Obtener productos activos con stock
$productos = $conn->query("SELECT ProductoID, Nombre, PrecioVenta, Stock, 
                                  CONCAT('$', FORMAT(PrecioVenta, 2)) AS PrecioFormateado
                           FROM Productos 
                           WHERE Stock > 0 AND Activo = 1 
                           ORDER BY Nombre")->fetchAll(PDO::FETCH_ASSOC);

// Obtener servicios activos
$servicios = $conn->query("SELECT ServicioID, Nombre, Descripcion, Precio,
                                  CONCAT('$', FORMAT(Precio, 2)) AS PrecioFormateado
                           FROM Servicios 
                           WHERE Activo = 1 
                           ORDER BY Nombre")->fetchAll(PDO::FETCH_ASSOC);
?>

<div class="container">
    <h2 class="mb-4"><i class="fas fa-cash-register"></i> Nueva Venta</h2>
    
    <form id="formVenta" action="procesar.php" method="post" class="needs-validation" novalidate>
        <div class="row">
            <div class="col-md-8">
                <div class="card mb-4 shadow-sm">
                    <div class="card-header bg-primary text-white d-flex justify-content-between align-items-center">
                        <h5 class="mb-0"><i class="fas fa-boxes"></i> Productos y Servicios</h5>
                        <div class="badge bg-light text-dark">
                            <span id="contadorProductos"><?= count($productos) ?></span> productos disponibles
                        </div>
                    </div>
                    <div class="card-body">
                        <ul class="nav nav-tabs" id="ventaTabs" role="tablist">
                            <li class="nav-item" role="presentation">
                                <button class="nav-link active" id="productos-tab" data-bs-toggle="tab" data-bs-target="#productos" type="button">
                                    <i class="fas fa-box-open"></i> Productos
                                </button>
                            </li>
                            <li class="nav-item" role="presentation">
                                <button class="nav-link" id="servicios-tab" data-bs-toggle="tab" data-bs-target="#servicios" type="button">
                                    <i class="fas fa-concierge-bell"></i> Servicios
                                </button>
                            </li>
                        </ul>
                        
                        <div class="tab-content mt-3" id="ventaTabsContent">
                            <!-- Productos -->
                            <div class="tab-pane fade show active" id="productos" role="tabpanel">
                                <div class="input-group mb-3">
                                    <span class="input-group-text"><i class="fas fa-search"></i></span>
                                    <input type="text" id="buscarProducto" class="form-control" placeholder="Buscar producto por nombre..." autocomplete="off">
                                    <button class="btn btn-outline-secondary" type="button" id="limpiarBusqueda">
                                        <i class="fas fa-times"></i>
                                    </button>
                                </div>
                                <div class="row row-cols-2 row-cols-md-3 row-cols-lg-4 g-3" id="listaProductos">
                                    <?php foreach ($productos as $producto): ?>
                                    <div class="col producto-item" 
                                         data-id="<?= $producto['ProductoID'] ?>"
                                         data-nombre="<?= htmlspecialchars($producto['Nombre']) ?>"
                                         data-precio="<?= $producto['PrecioVenta'] ?>"
                                         data-stock="<?= $producto['Stock'] ?>">
                                        <div class="card h-100 shadow-sm">
                                            <div class="card-body text-center d-flex flex-column">
                                                <h6 class="card-title"><?= htmlspecialchars($producto['Nombre']) ?></h6>
                                                <div class="mt-auto">
                                                    <p class="mb-1 text-success fw-bold"><?= $producto['PrecioFormateado'] ?></p>
                                                    <p class="mb-2 text-muted small">Disponible: <?= $producto['Stock'] ?></p>
                                                    <button type="button" class="btn btn-sm btn-primary agregar-item w-100">
                                                        <i class="fas fa-plus"></i> Agregar
                                                    </button>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <?php endforeach; ?>
                                </div>
                            </div>

                            <!-- Servicios -->
                            <div class="tab-pane fade" id="servicios" role="tabpanel">
                                <div class="row row-cols-1 row-cols-md-2 g-3">
                                    <?php foreach ($servicios as $servicio): ?>
                                    <div class="col servicio-item"
                                         data-id="<?= $servicio['ServicioID'] ?>"
                                         data-nombre="<?= htmlspecialchars($servicio['Nombre']) ?>"
                                         data-precio="<?= $servicio['Precio'] ?>">
                                        <div class="card h-100 shadow-sm">
                                            <div class="card-body d-flex flex-column">
                                                <h5 class="card-title"><?= htmlspecialchars($servicio['Nombre']) ?></h5>
                                                <p class="card-text text-muted small"><?= htmlspecialchars($servicio['Descripcion']) ?></p>
                                                <div class="mt-auto d-flex justify-content-between align-items-center">
                                                    <span class="text-success fw-bold"><?= $servicio['PrecioFormateado'] ?></span>
                                                    <button type="button" class="btn btn-sm btn-primary agregar-item">
                                                        <i class="fas fa-plus"></i> Agregar
                                                    </button>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <?php endforeach; ?>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Resumen -->
            <div class="col-md-4">
                <div class="card mb-4 shadow-sm">
                    <div class="card-header bg-primary text-white">
                        <h5 class="mb-0"><i class="fas fa-receipt"></i> Resumen de Venta</h5>
                    </div>
                    <div class="card-body">
                        <div class="mb-3">
                            <label for="cliente" class="form-label">Cliente *</label>
                            <select class="form-select" id="cliente" name="cliente" required>
                                <option value="">-- Selecciona un cliente --</option>
                                <?php foreach ($clientes as $cliente): ?>
                                    <option value="<?= $cliente['ClienteID'] ?>">
                                        <?= htmlspecialchars($cliente['NombreCompleto']) ?>
                                    </option>
                                <?php endforeach; ?>
                                <option value="0">Cliente Genérico</option>
                            </select>
                            <div class="invalid-feedback">Por favor selecciona un cliente</div>
                        </div>

                        <div class="mb-3">
                            <label for="metodoPago" class="form-label">Método de Pago *</label>
                            <select class="form-select" id="metodoPago" name="metodoPago" required>
                                <option value="Efectivo">Efectivo</option>
                                <option value="Tarjeta">Tarjeta</option>
                                <option value="Transferencia">Transferencia</option>
                                <option value="Mixto">Mixto</option>
                            </select>
                        </div>

                        <div class="table-responsive mb-3">
                            <table class="table table-sm table-hover" id="tablaItems">
                                <thead class="table-light">
                                    <tr>
                                        <th>Item</th>
                                        <th width="80">Cant.</th>
                                        <th width="100">Precio</th>
                                        <th width="100">Total</th>
                                        <th width="40"></th>
                                    </tr>
                                </thead>
                                <tbody id="itemsVenta"></tbody>
                                <tfoot class="table-group-divider">
                                    <tr>
                                        <th colspan="3">Subtotal</th>
                                        <th id="subtotal">$0.00</th>
                                        <th></th>
                                    </tr>
                                    <tr>
                                        <th colspan="3">Impuesto (16%)</th>
                                        <th id="impuesto">$0.00</th>
                                        <th></th>
                                    </tr>
                                    <tr class="table-active">
                                        <th colspan="3">Total</th>
                                        <th id="total">$0.00</th>
                                        <th></th>
                                    </tr>
                                </tfoot>
                            </table>
                        </div>

                        <div class="mb-3">
                            <label for="notas" class="form-label">Notas</label>
                            <textarea class="form-control" id="notas" name="notas" rows="2" placeholder="Observaciones sobre la venta..."></textarea>
                        </div>

                        <button type="submit" class="btn btn-success w-100 py-2 fw-bold" id="finalizarVenta">
                            <i class="fas fa-check-circle"></i> Finalizar Venta
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </form>
</div>

<?php include('../../includes/footer.php'); ?>

<!-- Scripts JS para gestión de items -->
<script>
$(function () {
    const itemsVenta = [];
    const IVA = 0.16; // 16% de IVA

    // Función para agregar items
    $('.agregar-item').click(function () {
        const item = $(this).closest('.producto-item, .servicio-item');
        const tipo = item.hasClass('producto-item') ? 'producto' : 'servicio';
        const id = item.data('id');
        const nombre = item.data('nombre');
        const precio = parseFloat(item.data('precio'));
        const stock = item.data('stock') ?? Infinity;

        const existente = itemsVenta.find(i => i.id === id && i.tipo === tipo);
        
        if (existente) {
            if (tipo === 'producto' && existente.cantidad >= stock) {
                return Swal.fire('Stock insuficiente', 'No hay suficiente stock disponible', 'warning');
            }
            existente.cantidad++;
            existente.total = existente.precio * existente.cantidad;
        } else {
            itemsVenta.push({ 
                tipo, 
                id, 
                nombre, 
                precio, 
                cantidad: 1, 
                total: precio 
            });
        }

        renderItems();
        actualizarContadorItems();
    });

    // Renderizar items en la tabla
    function renderItems() {
        const tbody = $('#itemsVenta').empty();
        let subtotal = 0;

        itemsVenta.forEach((item, i) => {
            subtotal += item.total;
            tbody.append(`
                <tr>
                    <td>${item.nombre}</td>
                    <td>
                        <input type="number" min="1" ${item.tipo === 'servicio' ? 'readonly' : ''}
                               class="form-control form-control-sm cantidad-item" 
                               data-index="${i}" value="${item.cantidad}">
                    </td>
                    <td>$${item.precio.toFixed(2)}</td>
                    <td>$${item.total.toFixed(2)}</td>
                    <td>
                        <button class="btn btn-sm btn-outline-danger eliminar-item" data-index="${i}">
                            <i class="fas fa-trash-alt"></i>
                        </button>
                    </td>
                </tr>
            `);
        });

        const impuesto = subtotal * IVA;
        const total = subtotal + impuesto;
        
        $('#subtotal').text(`$${subtotal.toFixed(2)}`);
        $('#impuesto').text(`$${impuesto.toFixed(2)}`);
        $('#total').text(`$${total.toFixed(2)}`);
    }

    // Actualizar contador de items
    function actualizarContadorItems() {
        $('#contadorItems').text(itemsVenta.length);
    }

    // Cambiar cantidad de items
    $(document).on('change', '.cantidad-item', function () {
        const i = $(this).data('index');
        const cantidad = Math.max(1, parseInt($(this).val()) || 1);
        const item = itemsVenta[i];

        if (item.tipo === 'producto') {
            const stock = $(`.producto-item[data-id="${item.id}"]`).data('stock');
            if (cantidad > stock) {
                $(this).val(item.cantidad);
                return Swal.fire('Stock insuficiente', 'No hay suficiente stock disponible', 'warning');
            }
        }

        item.cantidad = cantidad;
        item.total = cantidad * item.precio;
        renderItems();
    });

    // Eliminar item
    $(document).on('click', '.eliminar-item', function () {
        const index = $(this).data('index');
        itemsVenta.splice(index, 1);
        renderItems();
        actualizarContadorItems();
    });

    // Buscar productos
    $('#buscarProducto').on('input', function() {
        const searchTerm = $(this).val().toLowerCase();
        $('.producto-item').each(function() {
            const nombre = $(this).data('nombre').toLowerCase();
            $(this).toggle(nombre.includes(searchTerm));
        });
        $('#contadorProductos').text($('.producto-item:visible').length);
    });

    // Limpiar búsqueda
    $('#limpiarBusqueda').click(function() {
        $('#buscarProducto').val('').trigger('input');
    });

    // Validación del formulario
    $('#formVenta').submit(function (e) {
        e.preventDefault();
        
        if ($('#cliente').val() === "") {
            Swal.fire('Cliente requerido', 'Debes seleccionar un cliente antes de finalizar la venta.', 'warning');
            $('#cliente').focus();
            return;
        }

        if (itemsVenta.length === 0) {
            Swal.fire('Items requeridos', 'Agrega al menos un ítem a la venta.', 'warning');
            return;
        }

        // Agregar items al formulario
        itemsVenta.forEach((item, i) => {
            $(this).append(`
                <input type="hidden" name="items[${i}][tipo]" value="${item.tipo}">
                <input type="hidden" name="items[${i}][id]" value="${item.id}">
                <input type="hidden" name="items[${i}][cantidad]" value="${item.cantidad}">
                <input type="hidden" name="items[${i}][precio]" value="${item.precio}">
            `);
        });

        // Confirmar antes de enviar
        Swal.fire({
            title: 'Confirmar Venta',
            text: '¿Estás seguro de que deseas registrar esta venta?',
            icon: 'question',
            showCancelButton: true,
            confirmButtonText: 'Sí, registrar',
            cancelButtonText: 'Cancelar'
        }).then((result) => {
            if (result.isConfirmed) {
                this.submit();
            }
        });
    });

    // Inicializar contador
    actualizarContadorItems();
});
</script>