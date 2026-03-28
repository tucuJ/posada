<?php 
define('BASE_URL', '/posada/');
require_once('../../config/database.php');
include('../../includes/header.php');

// Manejar mensajes
if (isset($_GET['success'])) {
    $messages = [
        '1' => 'Venta registrada correctamente',
        '2' => 'Venta anulada correctamente'
    ];
    echo "<div class='alert alert-success'>{$messages[$_GET['success']]}</div>";
}

if (isset($_GET['error'])) {
    echo "<div class='alert alert-danger'>Error al procesar la solicitud</div>";
}

// Obtener parámetros de filtrado
$fechaInicio = $_GET['fechaInicio'] ?? date('Y-m-d', strtotime('-7 days'));
$fechaFin = $_GET['fechaFin'] ?? date('Y-m-d');
$estado = $_GET['estado'] ?? 'Completada';
$busqueda = $_GET['busqueda'] ?? '';

// Validar fechas
if ($fechaInicio > $fechaFin) {
    $temp = $fechaInicio;
    $fechaInicio = $fechaFin;
    $fechaFin = $temp;
}

// Construir consulta SQL
$query = "SELECT v.*, 
          CONCAT(c.Nombre, ' ', c.Apellido) as Cliente,
          u.NombreUsuario as Vendedor
          FROM Ventas v
          LEFT JOIN Clientes c ON v.ClienteID = c.ClienteID
          JOIN Usuarios u ON v.UsuarioID = u.UsuarioID
          WHERE v.FechaHora BETWEEN ? AND ?";

$params = [$fechaInicio . ' 00:00:00', $fechaFin . ' 23:59:59'];

// Agregar condición de estado si no es vacío
if (!empty($estado)) {
    $query .= " AND v.Estado = ?";
    $params[] = $estado;
}

// Agregar condición de búsqueda si existe
if (!empty($busqueda)) {
    $query .= " AND (v.VentaID LIKE ? OR c.Nombre LIKE ? OR c.Apellido LIKE ? OR u.NombreUsuario LIKE ? OR v.Tipo LIKE ?)";
    $searchTerm = "%$busqueda%";
    $params = array_merge($params, [$searchTerm, $searchTerm, $searchTerm, $searchTerm, $searchTerm]);
}

$query .= " ORDER BY v.FechaHora DESC";

$stmt = $conn->prepare($query);
$stmt->execute($params);
$ventas = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<div class="container">
    <h2>Historial de Ventas</h2>
    
    <div class="card mb-4">
        <div class="card-body">
            <form method="get" class="row g-3">
                <div class="col-md-2">
                    <label for="fechaInicio" class="form-label">Fecha Inicio</label>
                    <input type="date" class="form-control" id="fechaInicio" name="fechaInicio" 
                           value="<?= $fechaInicio ?>" max="<?= $fechaFin ?>">
                </div>
                <div class="col-md-2">
                    <label for="fechaFin" class="form-label">Fecha Fin</label>
                    <input type="date" class="form-control" id="fechaFin" name="fechaFin" 
                           value="<?= $fechaFin ?>" min="<?= $fechaInicio ?>">
                </div>
                <div class="col-md-2">
                    <label for="estado" class="form-label">Estado</label>
                    <select class="form-select" id="estado" name="estado">
                        <option value="Completada" <?= $estado == 'Completada' ? 'selected' : '' ?>>Completadas</option>
                        <option value="Pendiente" <?= $estado == 'Pendiente' ? 'selected' : '' ?>>Pendientes</option>
                        <option value="Cancelada" <?= $estado == 'Cancelada' ? 'selected' : '' ?>>Canceladas</option>
                        <option value="">Todas</option>
                    </select>
                </div>
                <div class="col-md-3">
                    <label for="busqueda" class="form-label">Buscar</label>
                    <input type="text" class="form-control" id="busqueda" name="busqueda" 
                           value="<?= htmlspecialchars($busqueda) ?>" placeholder="ID, Cliente, Vendedor o Tipo">
                </div>
                <div class="col-md-3 d-flex align-items-end">
                    <button type="submit" class="btn btn-primary me-2">
                        <i class="fas fa-filter"></i> Filtrar
                    </button>
                    <button type="button" class="btn btn-secondary me-2" onclick="printReport()">
                        <i class="fas fa-print"></i> Imprimir
                    </button>
                    <a href="nueva.php" class="btn btn-success">
                        <i class="fas fa-plus"></i> Nueva
                    </a>
                </div>
            </form>
        </div>
    </div>
    
    <div class="table-responsive">
        <table class="table table-striped table-hover">
            <thead class="">
                <tr>
                    <th>ID</th>
                    <th>Fecha/Hora</th>
                    <th>Cliente</th>
                    <th>Tipo</th>
                    <th>Vendedor</th>
                    <th>Total</th>
                    <th>Estado</th>
                    <th class="no-print">Acciones</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($ventas as $venta): 
                    $estadoClass = [
                        'Completada' => 'success',
                        'Pendiente' => 'warning',
                        'Cancelada' => 'danger'
                    ][$venta['Estado']];
                    
                    $fecha = new DateTime($venta['FechaHora']);
                ?>
                <tr>
                    <td>#<?= $venta['VentaID'] ?></td>
                    <td><?= $fecha->format('d/m/Y H:i') ?></td>
                    <td><?= $venta['Cliente'] ?? 'Consumidor Final' ?></td>
                    <td><?= $venta['Tipo'] ?></td>
                    <td><?= $venta['Vendedor'] ?></td>
                    <td>$<?= number_format($venta['Total'], 2) ?></td>
                    <td>
                        <span class="badge bg-<?= $estadoClass ?>">
                            <?= $venta['Estado'] ?>
                        </span>
                    </td>
                    <td class="no-print">
                        <a href="detalle.php?id=<?= $venta['VentaID'] ?>" class="btn btn-sm btn-info" title="Ver detalle">
                            <i class="fas fa-eye"></i>
                        </a>
                        <a href="factura.php?id=<?= $venta['VentaID'] ?>" class="btn btn-sm btn-secondary" title="Factura">
                            <i class="fas fa-file-invoice"></i>
                        </a>
                        <?php if ($venta['Estado'] == 'Completada' || $venta['Estado'] == 'Pendiente'): ?>
                            <a href="anular.php?id=<?= $venta['VentaID'] ?>" class="btn btn-sm btn-danger" title="Anular"
                               onclick="return confirm('¿Anular esta venta?')">
                                <i class="fas fa-ban"></i>
                            </a>
                        <?php endif; ?>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
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
            background-color: #f2f2f2 !important;
            -webkit-print-color-adjust: exact;
            print-color-adjust: exact;
        }
        
        .badge {
            border: 1px solid #000;
            color: #000;
            background-color: transparent !important;
        }
        
        h2 {
            margin-bottom: 15px;
            text-align: center;
            font-size: 20px;
        }
        
        @page {
            size: auto;
            margin: 10mm;
        }
    }
</style>

<script>
    function printReport() {
        // Crear contenido para imprimir
        const printContent = document.createElement('div');
        printContent.className = 'print-content';
        
        // Agregar título
        const title = document.createElement('h2');
        title.textContent = 'Historial de Ventas';
        printContent.appendChild(title);
        
        // Agregar rango de fechas
        const fechaInicio = document.getElementById('fechaInicio').value;
        const fechaFin = document.getElementById('fechaFin').value;
        const subtitle = document.createElement('p');
        subtitle.textContent = `Desde: ${fechaInicio} - Hasta: ${fechaFin}`;
        subtitle.style.textAlign = 'center';
        subtitle.style.marginBottom = '20px';
        printContent.appendChild(subtitle);
        
        // Crear tabla para imprimir (sin columna de acciones)
        const originalTable = document.querySelector('table');
        const printTable = document.createElement('table');
        printTable.className = 'table table-striped';
        
        // Crear encabezados (excluyendo la columna de acciones)
        const thead = document.createElement('thead');
        const headerRow = document.createElement('tr');
        
        // Obtener todos los encabezados excepto el último (Acciones)
        const headers = originalTable.querySelectorAll('th:not(:last-child)');
        headers.forEach(header => {
            const th = document.createElement('th');
            th.textContent = header.textContent;
            headerRow.appendChild(th);
        });
        
        thead.appendChild(headerRow);
        printTable.appendChild(thead);
        
        // Crear cuerpo de la tabla (excluyendo la columna de acciones)
        const tbody = document.createElement('tbody');
        const rows = originalTable.querySelectorAll('tbody tr');
        
        rows.forEach(row => {
            const tr = document.createElement('tr');
            // Obtener todas las celdas excepto la última (Acciones)
            const cells = row.querySelectorAll('td:not(:last-child)');
            
            cells.forEach(cell => {
                const td = document.createElement('td');
                td.innerHTML = cell.innerHTML;
                tr.appendChild(td);
            });
            
            tbody.appendChild(tr);
        });
        
        printTable.appendChild(tbody);
        printContent.appendChild(printTable);
        
        // Agregar pie de página
        const footer = document.createElement('div');
        footer.className = 'text-end mt-3';
        footer.innerHTML = `<small>Impreso el ${new Date().toLocaleString()}</small>`;
        printContent.appendChild(footer);
        
        // Abrir ventana de impresión
        const originalContent = document.body.innerHTML;
        document.body.innerHTML = printContent.outerHTML;
        window.print();
        document.body.innerHTML = originalContent;
    }
</script>

<?php include('../../includes/footer.php'); ?>