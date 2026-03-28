<?php 
define('BASE_URL', '/posada/');
require_once('../../config/database.php');
include('../../includes/header.php');

if (!isset($_GET['id'])) {
    header("Location: listar.php");
    exit();
}

$ventaId = $_GET['id'];

// Obtener datos de la venta
$queryVenta = "SELECT v.*, 
               CONCAT(c.Nombre, ' ', c.Apellido) as ClienteNombre,
               c.Direccion, c.Telefono, c.Email, c.numerodocumento,
               c.TipoDocumento as ClienteTipoDoc,
               c.NumeroDocumento as ClienteDoc,
               u.NombreUsuario as Vendedor
               FROM Ventas v
               LEFT JOIN Clientes c ON v.ClienteID = c.ClienteID
               JOIN Usuarios u ON v.UsuarioID = u.UsuarioID
               WHERE v.VentaID = ?";
$stmtVenta = $conn->prepare($queryVenta);
$stmtVenta->execute([$ventaId]);
$venta = $stmtVenta->fetch(PDO::FETCH_ASSOC);

if (!$venta) {
    header("Location: listar.php?error=1");
    exit();
}

// Obtener items de la venta
$items = [];

// Productos
$queryItems = "SELECT vd.*, p.Nombre 
               FROM VentaDetalles vd
               JOIN Productos p ON vd.ProductoID = p.ProductoID
               WHERE vd.VentaID = ?";
$stmtItems = $conn->prepare($queryItems);
$stmtItems->execute([$ventaId]);
$itemsProductos = $stmtItems->fetchAll(PDO::FETCH_ASSOC);

foreach ($itemsProductos as $item) {
    $items[] = [
        'tipo' => 'Producto',
        'nombre' => $item['Nombre'],
        'cantidad' => $item['Cantidad'],
        'precio' => $item['PrecioUnitario'],
        'descuento' => $item['Descuento'],
        'subtotal' => $item['Subtotal']
    ];
}

// Servicios y habitaciones
$queryServicios = "SELECT * FROM VentaServicios WHERE VentaID = ?";
$stmtServicios = $conn->prepare($queryServicios);
$stmtServicios->execute([$ventaId]);
$itemsServicios = $stmtServicios->fetchAll(PDO::FETCH_ASSOC);

foreach ($itemsServicios as $item) {
    if ($item['Tipo'] == 'Servicio') {
        $queryServicio = "SELECT Nombre FROM Servicios WHERE ServicioID = ?";
        $stmtServicio = $conn->prepare($queryServicio);
        $stmtServicio->execute([$item['ItemID']]);
        $servicio = $stmtServicio->fetch(PDO::FETCH_ASSOC);
        
        $items[] = [
            'tipo' => 'Servicio',
            'nombre' => $servicio['Nombre'],
            'cantidad' => $item['Cantidad'],
            'precio' => $item['PrecioUnitario'],
            'descuento' => $item['Descuento'],
            'subtotal' => $item['Subtotal']
        ];
    } elseif ($item['Tipo'] == 'Habitacion') {
        $queryHabitacion = "SELECT h.Numero, t.Nombre as Tipo 
                            FROM Habitaciones h
                            JOIN TiposHabitacion t ON h.TipoHabitacionID = t.TipoHabitacionID
                            WHERE h.HabitacionID = ?";
        $stmtHabitacion = $conn->prepare($queryHabitacion);
        $stmtHabitacion->execute([$item['ItemID']]);
        $habitacion = $stmtHabitacion->fetch(PDO::FETCH_ASSOC);
        
        $items[] = [
            'tipo' => 'Habitación',
            'nombre' => "Hab. {$habitacion['Numero']} ({$habitacion['Tipo']})",
            'cantidad' => $item['Cantidad'],
            'precio' => $item['PrecioUnitario'],
            'descuento' => $item['Descuento'],
            'subtotal' => $item['Subtotal'],
            'fechaInicio' => $item['FechaInicio'],
            'fechaFin' => $item['FechaFin']
        ];
    } elseif ($item['Tipo'] == 'Paquete') {
        $queryPaquete = "SELECT Nombre FROM Paquetes WHERE PaqueteID = ?";
        $stmtPaquete = $conn->prepare($queryPaquete);
        $stmtPaquete->execute([$item['ItemID']]);
        $paquete = $stmtPaquete->fetch(PDO::FETCH_ASSOC);
        
        $items[] = [
            'tipo' => 'Paquete',
            'nombre' => $paquete['Nombre'],
            'cantidad' => $item['Cantidad'],
            'precio' => $item['PrecioUnitario'],
            'descuento' => $item['Descuento'],
            'subtotal' => $item['Subtotal'],
            'fechaInicio' => $item['FechaInicio'],
            'fechaFin' => $item['FechaFin']
        ];
    }
}

// Platillos (dishes) from restaurant orders
$queryPlatillos = "SELECT vd.*, p.Nombre as PlatilloNombre 
                   FROM VentasRestaurante vr
                   JOIN OrdenDetalles vd ON vr.OrdenID = vd.OrdenID
                   JOIN Platillos p ON vd.PlatilloID = p.PlatilloID
                   WHERE vr.VentaID = ?";
$stmtPlatillos = $conn->prepare($queryPlatillos);
$stmtPlatillos->execute([$ventaId]);
$itemsPlatillos = $stmtPlatillos->fetchAll(PDO::FETCH_ASSOC);

foreach ($itemsPlatillos as $item) {
    $items[] = [
        'tipo' => 'Platillo',
        'nombre' => $item['PlatilloNombre'],
        'cantidad' => $item['Cantidad'],
        'precio' => $item['PrecioUnitario'],
        'descuento' => 0.00,
        'subtotal' => $item['PrecioUnitario'] * $item['Cantidad'],
        'notas' => $item['Notas']
    ];
}

$fecha = new DateTime($venta['FechaHora']);
?>

<div class="container">
    <div class="card mb-4">
        <div class="card-body">
            <!-- Encabezado de la factura -->
            <div class="row mb-4">
                <div class="col-md-6">
                    <img src="<?= BASE_URL ?>assets/img/logo.png" alt="Logo Posada del Mar" style="max-height: 100px;" class="mb-2">
                    <h3 class="mb-1">POSADA DEL MAR</h3>
                    <p class="mb-1">Dirección: Av. Costera #123, Acapulco, Gro.</p>
                    <p class="mb-1">Teléfono: 744 123 4567</p>
                    <p class="mb-1">numerodocumento: POS780101ABC</p>
                </div>
                <div class="col-md-6 text-end">
                    <h2 class="mb-2">FACTURA</h2>
                    <p class="mb-1"><strong>No. Factura:</strong> <?= str_pad($ventaId, 8, '0', STR_PAD_LEFT) ?></p>
                    <p class="mb-1"><strong>Fecha:</strong> <?= $fecha->format('d/m/Y H:i') ?></p>
                    <p class="mb-1"><strong>Método Pago:</strong> <?= $venta['MetodoPago'] ?></p>
                </div>
            </div>
            
            <!-- Datos del cliente -->
            <div class="row mb-4">
                <div class="col-md-6">
                    <div class="border p-3">
                        <h5 class="mb-3">Datos del Cliente</h5>
                        <p class="mb-1"><strong>Nombre:</strong> <?= $venta['ClienteNombre'] ?? 'Consumidor Final' ?></p>
                        <p class="mb-1"><strong>Documento:</strong> <?= $venta['ClienteTipoDoc'] ?? '' ?> <?= $venta['ClienteDoc'] ?? '' ?></p>
                        <p class="mb-1"><strong>Dirección:</strong> <?= $venta['Direccion'] ?? 'N/A' ?></p>
                        <p class="mb-1"><strong>Teléfono:</strong> <?= $venta['Telefono'] ?? 'N/A' ?></p>
                        <p class="mb-0"><strong>Email:</strong> <?= $venta['Email'] ?? 'N/A' ?></p>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="border p-3">
                        <h5 class="mb-3">Datos de la Factura</h5>
                        <p class="mb-1"><strong>Atendido por:</strong> <?= $venta['Vendedor'] ?></p>
                        <p class="mb-1"><strong>Estado:</strong> 
                            <span class="badge bg-<?= $venta['Estado'] == 'Completada' ? 'success' : ($venta['Estado'] == 'Pendiente' ? 'warning' : 'danger') ?>">
                                <?= $venta['Estado'] ?>
                            </span>
                        </p>
                        <p class="mb-0"><strong>Folio Fiscal:</strong> <?= $venta['FolioFiscal'] ?? 'PENDIENTE' ?></p>
                    </div>
                </div>
            </div>
            
            <!-- Detalle de items -->
            <div class="table-responsive mb-4">
                <table class="table table-bordered">
                    <thead class="table-dark">
                        <tr>
                            <th width="5%">Cant.</th>
                            <th width="45%">Descripción</th>
                            <th width="15%">P. Unitario</th>
                            <th width="15%">Descuento</th>
                            <th width="20%">Importe</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($items as $item): ?>
                        <tr>
                            <td><?= $item['cantidad'] ?></td>
                            <td>
                                <?= $item['nombre'] ?>
                                <?php if (isset($item['fechaInicio'])): ?>
                                    <br><small>
                                        <?= (new DateTime($item['fechaInicio']))->format('d/m/Y') ?> - 
                                        <?= (new DateTime($item['fechaFin']))->format('d/m/Y') ?>
                                    </small>
                                <?php endif; ?>
                                <?php if (isset($item['notas'])): ?>
                                    <br><small><?= $item['notas'] ?></small>
                                <?php endif; ?>
                            </td>
                            <td>$<?= number_format($item['precio'], 2) ?></td>
                            <td>$<?= number_format($item['descuento'], 2) ?></td>
                            <td>$<?= number_format($item['subtotal'], 2) ?></td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                    <tfoot>
                        <tr>
                            <th colspan="4" class="text-end">Subtotal:</th>
                            <th>$<?= number_format($venta['Subtotal'], 2) ?></th>
                        </tr>
                        <tr>
                            <th colspan="4" class="text-end">Descuento:</th>
                            <th>$<?= number_format($venta['Descuento'], 2) ?></th>
                        </tr>
                        <tr>
                            <th colspan="4" class="text-end">Impuestos (<?= number_format(($venta['Impuesto'] / $venta['Subtotal']) * 100, 2) ?>%):</th>
                            <th>$<?= number_format($venta['Impuesto'], 2) ?></th>
                        </tr>
                        <tr class="table-active">
                            <th colspan="4" class="text-end">Total:</th>
                            <th>$<?= number_format($venta['Total'], 2) ?></th>
                        </tr>
                    </tfoot>
                </table>
            </div>
            
            <!-- Información adicional -->
            <div class="row">
                <div class="col-md-12">
                    <div class="border p-3">
                        <h5 class="mb-3">Información Adicional</h5>
                        <p class="mb-1"><strong>Forma de Pago:</strong> <?= $venta['MetodoPago'] ?></p>
                        <p class="mb-1"><strong>Moneda:</strong> MXN - Peso Mexicano</p>
                        <p class="mb-0"><strong>Observaciones:</strong> <?= $venta['Observaciones'] ?? 'Ninguna' ?></p>
                    </div>
                </div>
            </div>
            
            <!-- Leyenda fiscal -->
            <div class="text-center mt-4">
                <p class="mb-1"><small>Este documento es una representación impresa de un CFDI</small></p>
                <p class="mb-1"><small>Folio Fiscal: <?= $venta['FolioFiscal'] ?? 'PENDIENTE DE TIMBRAR' ?></small></p>
                <p class="mb-0"><small>Fecha y hora de certificación: <?= $venta['FechaCertificacion'] ?? 'PENDIENTE' ?></small></p>
            </div>
        </div>
    </div>
    
    <div class="d-flex justify-content-between no-print">
        <a href="listar.php" class="btn btn-secondary">
            <i class="fas fa-arrow-left"></i> Volver al listado
        </a>
        <div>
            <button type="button" class="btn btn-primary me-2" onclick="printFactura()">
                <i class="fas fa-print"></i> Imprimir Factura
            </button>
            <a href="detalle.php?id=<?= $ventaId ?>" class="btn btn-info">
                <i class="fas fa-file-alt"></i> Ver Detalle
            </a>
        </div>
    </div>
</div>

<!-- Estilos para impresión -->
<style>
    @media print {
        body * {
            visibility: hidden;
            margin: 0;
            padding: 0;
        }
        
        .print-content, .print-content * {
            visibility: visible;
        }
        
        .print-content {
            position: absolute;
            left: 0;
            top: 0;
            width: 100%;
            padding: 20px;
        }
        
        .no-print, .no-print * {
            display: none !important;
        }
        
        table {
            width: 100%;
            border-collapse: collapse;
            font-size: 12px;
        }
        
        table, th, td {
            border: 1px solid #ddd;
        }
        
        th, td {
            padding: 6px;
            text-align: left;
        }
        
        th {
            background-color: #212529 !important;
            color: white !important;
            -webkit-print-color-adjust: exact;
            print-color-adjust: exact;
        }
        
        .table-active {
            background-color: #f8f9fa !important;
            -webkit-print-color-adjust: exact;
            print-color-adjust: exact;
        }
        
        .badge {
            border: 1px solid #000;
            color: #000;
            background-color: transparent !important;
        }
        
        h2, h3, h5 {
            margin-bottom: 10px;
        }
        
        .border {
            border: 1px solid #dee2e6 !important;
        }
        
        .text-end {
            text-align: right !important;
        }
        
        small {
            font-size: 80%;
        }
    }
    
    @page {
        size: auto;
        margin: 10mm;
    }
</style>

<script>
    function printFactura() {
        // Crear contenido para imprimir
        const printContent = document.createElement('div');
        printContent.className = 'print-content';
        
        // Clonar el contenido de la factura
        const facturaContent = document.querySelector('.card').cloneNode(true);
        printContent.appendChild(facturaContent);
        
        // Agregar pie de página
        const footer = document.createElement('div');
        footer.className = 'text-center mt-4 text-muted';
        footer.innerHTML = `
            <p class="mb-1">¡Gracias por su preferencia!</p>
            <p class="mb-0">Este documento es válido como factura fiscal. Consérvelo para cualquier aclaración.</p>
        `;
        printContent.appendChild(footer);
        
        // Abrir ventana de impresión
        const originalContent = document.body.innerHTML;
        document.body.innerHTML = printContent.outerHTML;
        window.print();
        document.body.innerHTML = originalContent;
    }
</script>

<?php include('../../includes/footer.php'); ?>