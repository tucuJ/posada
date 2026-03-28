<?php include('../../../includes/header.php');?>

<?php
require_once '../../../config/database.php'; // Asume que la conexión está en este archivo

// Verificar si el usuario tiene permisos (deberías implementar tu sistema de autenticación)


// Funciones para categorías
function obtenerCategorias($conn) {
    $stmt = $conn->prepare("SELECT * FROM CategoriasProductos ORDER BY Nombre");
    $stmt->execute();
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

function agregarCategoria($conn, $nombre, $descripcion) {
    $stmt = $conn->prepare("INSERT INTO CategoriasProductos (Nombre, Descripcion) VALUES (?, ?)");
    return $stmt->execute([$nombre, $descripcion]);
}

function actualizarCategoria($conn, $id, $nombre, $descripcion, $activo) {
    $stmt = $conn->prepare("UPDATE CategoriasProductos SET Nombre = ?, Descripcion = ?, Activo = ? WHERE CategoriaID = ?");
    return $stmt->execute([$nombre, $descripcion, $activo, $id]);
}

function eliminarCategoria($conn, $id) {
    // Verificar si hay productos asociados
    $stmt = $conn->prepare("SELECT COUNT(*) FROM Productos WHERE CategoriaID = ?");
    $stmt->execute([$id]);
    $count = $stmt->fetchColumn();
    
    if ($count > 0) {
        return false; // No se puede eliminar si hay productos asociados
    }
    
    $stmt = $conn->prepare("DELETE FROM CategoriasProductos WHERE CategoriaID = ?");
    return $stmt->execute([$id]);
}

// Funciones para proveedores
function obtenerProveedores($conn) {
    $stmt = $conn->prepare("SELECT * FROM Proveedores ORDER BY Nombre");
    $stmt->execute();
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

function agregarProveedor($conn, $datos) {
    $stmt = $conn->prepare("INSERT INTO Proveedores (Nombre, RUC, Telefono, Email, Direccion) 
                           VALUES (?, ?, ?, ?, ?)");
    return $stmt->execute([
        $datos['nombre'], 
        $datos['ruc'], 
        $datos['telefono'], 
        $datos['email'], 
        $datos['direccion']
    ]);
}

function actualizarProveedor($conn, $id, $datos) {
    $stmt = $conn->prepare("UPDATE Proveedores 
                           SET Nombre = ?, RUC = ?, Telefono = ?, Email = ?, Direccion = ?, Activo = ?
                           WHERE ProveedorID = ?");
    return $stmt->execute([
        $datos['nombre'], 
        $datos['ruc'], 
        $datos['telefono'], 
        $datos['email'], 
        $datos['direccion'],
        $datos['activo'],
        $id
    ]);
}

function eliminarProveedor($conn, $id) {
    // Verificar si hay productos asociados
    $stmt = $conn->prepare("SELECT COUNT(*) FROM Productos WHERE ProveedorID = ?");
    $stmt->execute([$id]);
    $count = $stmt->fetchColumn();
    
    if ($count > 0) {
        return false; // No se puede eliminar si hay productos asociados
    }
    
    $stmt = $conn->prepare("DELETE FROM Proveedores WHERE ProveedorID = ?");
    return $stmt->execute([$id]);
}

// Procesar formularios
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (isset($_POST['accion'])) {
        switch ($_POST['accion']) {
            case 'agregar_categoria':
                if (agregarCategoria($conn, $_POST['nombre'], $_POST['descripcion'])) {
                    $mensaje = "Categoría agregada correctamente";
                } else {
                    $error = "Error al agregar categoría";
                }
                break;
                
            case 'editar_categoria':
                if (actualizarCategoria($conn, $_POST['id'], $_POST['nombre'], $_POST['descripcion'], $_POST['activo'])) {
                    $mensaje = "Categoría actualizada correctamente";
                } else {
                    $error = "Error al actualizar categoría";
                }
                break;
                
            case 'eliminar_categoria':
                if (eliminarCategoria($conn, $_POST['id'])) {
                    $mensaje = "Categoría eliminada correctamente";
                } else {
                    $error = "No se puede eliminar la categoría porque tiene productos asociados";
                }
                break;
                
            case 'agregar_proveedor':
                $datosProveedor = [
                    'nombre' => $_POST['nombre'],
                    'ruc' => $_POST['ruc'],
                    'telefono' => $_POST['telefono'],
                    'email' => $_POST['email'],
                    'direccion' => $_POST['direccion']
                ];
                if (agregarProveedor($conn, $datosProveedor)) {
                    $mensaje = "Proveedor agregado correctamente";
                } else {
                    $error = "Error al agregar proveedor";
                }
                break;
                
            case 'editar_proveedor':
                $datosProveedor = [
                    'nombre' => $_POST['nombre'],
                    'ruc' => $_POST['ruc'],
                    'telefono' => $_POST['telefono'],
                    'email' => $_POST['email'],
                    'direccion' => $_POST['direccion'],
                    'activo' => isset($_POST['activo']) ? 1 : 0
                ];
                if (actualizarProveedor($conn, $_POST['id'], $datosProveedor)) {
                    $mensaje = "Proveedor actualizado correctamente";
                } else {
                    $error = "Error al actualizar proveedor";
                }
                break;
                
            case 'eliminar_proveedor':
                if (eliminarProveedor($conn, $_POST['id'])) {
                    $mensaje = "Proveedor eliminado correctamente";
                } else {
                    $error = "No se puede eliminar el proveedor porque tiene productos asociados";
                }
                break;
        }
    }
}

// Obtener datos actuales
$categorias = obtenerCategorias($conn);
$proveedores = obtenerProveedores($conn);
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestión de Categorías O Proveedores</title>
    <style>
        body {
            font-family: 'Arial', sans-serif;
            line-height: 1.6;
            margin: 0;
            padding: 20px;
            background-color: #f4f4f4;
            color: #333;
        }

        h1, h2 {
            color: #2c3e50;
            border-bottom: 2px solid #3498db;
            padding-bottom: 10px;
        }
        .tabla-container {
            overflow-x: auto;
            margin-bottom: 30px;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin: 20px 0;
        }
        th, td {
            padding: 12px;
            text-align: left;
            border-bottom: 1px solid #ddd;
        }
        th {
            background-color: #3498db;
            color: white;
        }
        tr:hover {
            background-color: #f5f5f5;
        }
        .btn {
            display: inline-block;
            padding: 8px 15px;
            margin: 5px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            text-decoration: none;
            font-size: 14px;
        }
        .btn-editar {
            background-color: #f39c12;
            color: white;
        }
        .btn-eliminar {
            background-color: #e74c3c;
            color: white;
        }
        .btn-agregar {
            background-color: #2ecc71;
            color: white;
            margin-bottom: 20px;
        }
        .btn:hover {
            opacity: 0.8;
        }
        .modal {
            display: none;
            position: fixed;
            z-index: 1;
            left: 0;
            top: 0;
            width: 100%;
            height: 100%;
            overflow: auto;
            background-color: rgba(0,0,0,0.4);
        }
        .modal-content {
            background-color: #fefefe;
            margin: 10% auto;
            padding: 20px;
            border: 1px solid #888;
            width: 80%;
            max-width: 600px;
            border-radius: 5px;
        }
        .cerrar {
            color: #aaa;
            float: right;
            font-size: 28px;
            font-weight: bold;
            cursor: pointer;
        }
        .cerrar:hover {
            color: black;
        }
        .form-group {
            margin-bottom: 15px;
        }
        label {
            display: block;
            margin-bottom: 5px;
            font-weight: bold;
        }
        input[type="text"], input[type="email"], input[type="tel"], textarea, select {
            width: 100%;
            padding: 8px;
            border: 1px solid #ddd;
            border-radius: 4px;
            box-sizing: border-box;
        }
        .checkbox-group {
            display: flex;
            align-items: center;
        }
        .checkbox-group input {
            margin-right: 10px;
        }
        .mensaje {
            padding: 10px;
            margin-bottom: 20px;
            border-radius: 4px;
        }
        .exito {
            background-color: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
        }
        .error {
            background-color: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
        }
        .tabs {
            display: flex;
            margin-bottom: 20px;
            border-bottom: 1px solid #ddd;
        }
        .tab {
            padding: 10px 20px;
            cursor: pointer;
            background-color: #f1f1f1;
            border: 1px solid #ddd;
            border-bottom: none;
            margin-right: 5px;
            border-radius: 5px 5px 0 0;
        }
        .tab.active {
            background-color: #fff;
            border-bottom: 1px solid #fff;
            margin-bottom: -1px;
        }
        .tab-content {
            display: none;
        }
        .tab-content.active {
            display: block;
        }
    </style>
</head>
<body>
<div class="container">
        <h1>Gestión de Inventario</h1>
        
        <?php if (isset($mensaje)): ?>
            <div class="mensaje exito"><?php echo $mensaje; ?></div>
        <?php endif; ?>
        
        <?php if (isset($error)): ?>
            <div class="mensaje error"><?php echo $error; ?></div>
        <?php endif; ?>
        
        <div class="tabs">
            <div class="tab active" onclick="abrirTab(event, 'categorias')">Categorías</div>
            <div class="tab" onclick="abrirTab(event, 'proveedores')">Proveedores</div>
        </div>
        
        <!-- Pestaña de Categorías -->
        <div id="categorias" class="tab-content active">
            <h2>Categorías de Productos</h2>
            <button class="btn btn-agregar" onclick="abrirModal('modalAgregarCategoria')">Agregar Categoría</button>
            
            <div class="tabla-container">
                <table>
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Nombre</th>
                            <th>Descripción</th>
                            <th>Estado</th>
                            <th>Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($categorias as $categoria): ?>
                        <tr>
                            <td><?php echo $categoria['CategoriaID']; ?></td>
                            <td><?php echo htmlspecialchars($categoria['Nombre']); ?></td>
                            <td><?php echo htmlspecialchars($categoria['Descripcion']); ?></td>
                            <td><?php echo $categoria['Activo'] ? 'Activo' : 'Inactivo'; ?></td>
                            <td>
                                <button class="btn btn-editar" onclick="editarCategoria(
                                    <?php echo $categoria['CategoriaID']; ?>,
                                    '<?php echo htmlspecialchars($categoria['Nombre'], ENT_QUOTES); ?>',
                                    '<?php echo htmlspecialchars($categoria['Descripcion'], ENT_QUOTES); ?>',
                                    <?php echo $categoria['Activo']; ?>
                                )">Editar</button>
                                <button class="btn btn-eliminar" onclick="confirmarEliminarCategoria(<?php echo $categoria['CategoriaID']; ?>)">Eliminar</button>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
        
        <!-- Pestaña de Proveedores -->
        <div id="proveedores" class="tab-content">
            <h2>Proveedores</h2>
            <button class="btn btn-agregar" onclick="abrirModal('modalAgregarProveedor')">Agregar Proveedor</button>
            
            <div class="tabla-container">
                <table>
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Nombre</th>
                            <th>RUC</th>
                            <th>Teléfono</th>
                            <th>Email</th>
                            <th>Estado</th>
                            <th>Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($proveedores as $proveedor): ?>
                        <tr>
                            <td><?php echo $proveedor['ProveedorID']; ?></td>
                            <td><?php echo htmlspecialchars($proveedor['Nombre']); ?></td>
                            <td><?php echo htmlspecialchars($proveedor['RUC']); ?></td>
                            <td><?php echo htmlspecialchars($proveedor['Telefono']); ?></td>
                            <td><?php echo htmlspecialchars($proveedor['Email']); ?></td>
                            <td><?php echo $proveedor['Activo'] ? 'Activo' : 'Inactivo'; ?></td>
                            <td>
                                <button class="btn btn-editar" onclick="editarProveedor(
                                    <?php echo $proveedor['ProveedorID']; ?>,
                                    '<?php echo htmlspecialchars($proveedor['Nombre'], ENT_QUOTES); ?>',
                                    '<?php echo htmlspecialchars($proveedor['RUC'], ENT_QUOTES); ?>',
                                    '<?php echo htmlspecialchars($proveedor['Telefono'], ENT_QUOTES); ?>',
                                    '<?php echo htmlspecialchars($proveedor['Email'], ENT_QUOTES); ?>',
                                    '<?php echo htmlspecialchars($proveedor['Direccion'], ENT_QUOTES); ?>',
                                    <?php echo $proveedor['Activo']; ?>
                                )">Editar</button>
                                <button class="btn btn-eliminar" onclick="confirmarEliminarProveedor(<?php echo $proveedor['ProveedorID']; ?>)">Eliminar</button>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    
    <!-- Modales para Categorías -->
    <div id="modalAgregarCategoria" class="modal">
        <div class="modal-content">
            <span class="cerrar" onclick="cerrarModal('modalAgregarCategoria')">&times;</span>
            <h2>Agregar Nueva Categoría</h2>
            <form action="" method="POST">
                <input type="hidden" name="accion" value="agregar_categoria">
                <div class="form-group">
                    <label for="nombre">Nombre:</label>
                    <input type="text" id="nombre" name="nombre" required>
                </div>
                <div class="form-group">
                    <label for="descripcion">Descripción:</label>
                    <textarea id="descripcion" name="descripcion" rows="3"></textarea>
                </div>
                <button type="submit" class="btn btn-agregar">Guardar</button>
            </form>
        </div>
    </div>
    
    <div id="modalEditarCategoria" class="modal">
        <div class="modal-content">
            <span class="cerrar" onclick="cerrarModal('modalEditarCategoria')">&times;</span>
            <h2>Editar Categoría</h2>
            <form action="" method="POST">
                <input type="hidden" name="accion" value="editar_categoria">
                <input type="hidden" id="editarCategoriaId" name="id">
                <div class="form-group">
                    <label for="editarNombre">Nombre:</label>
                    <input type="text" id="editarNombre" name="nombre" required>
                </div>
                <div class="form-group">
                    <label for="editarDescripcion">Descripción:</label>
                    <textarea id="editarDescripcion" name="descripcion" rows="3"></textarea>
                </div>
                <div class="form-group checkbox-group">
                    <input type="checkbox" id="editarActivo" name="activo" value="1">
                    <label for="editarActivo">Activo</label>
                </div>
                <button type="submit" class="btn btn-agregar">Actualizar</button>
            </form>
        </div>
    </div>
    
    <div id="modalEliminarCategoria" class="modal">
        <div class="modal-content">
            <span class="cerrar" onclick="cerrarModal('modalEliminarCategoria')">&times;</span>
            <h2>Confirmar Eliminación</h2>
            <p>¿Está seguro que desea eliminar esta categoría?</p>
            <form action="" method="POST">
                <input type="hidden" name="accion" value="eliminar_categoria">
                <input type="hidden" id="eliminarCategoriaId" name="id">
                <button type="submit" class="btn btn-eliminar">Eliminar</button>
                <button type="button" class="btn" onclick="cerrarModal('modalEliminarCategoria')">Cancelar</button>
            </form>
        </div>
    </div>
    
    <!-- Modales para Proveedores -->
    <div id="modalAgregarProveedor" class="modal">
        <div class="modal-content">
            <span class="cerrar" onclick="cerrarModal('modalAgregarProveedor')">&times;</span>
            <h2>Agregar Nuevo Proveedor</h2>
            <form action="" method="POST">
                <input type="hidden" name="accion" value="agregar_proveedor">
                <div class="form-group">
                    <label for="proveedorNombre">Nombre:</label>
                    <input type="text" id="proveedorNombre" name="nombre" required>
                </div>
                <div class="form-group">
                    <label for="proveedorRuc">RUC:</label>
                    <input type="text" id="proveedorRuc" name="ruc">
                </div>
                <div class="form-group">
                    <label for="proveedorTelefono">Teléfono:</label>
                    <input type="tel" id="proveedorTelefono" name="telefono">
                </div>
                <div class="form-group">
                    <label for="proveedorEmail">Email:</label>
                    <input type="email" id="proveedorEmail" name="email">
                </div>
                <div class="form-group">
                    <label for="proveedorDireccion">Dirección:</label>
                    <textarea id="proveedorDireccion" name="direccion" rows="3"></textarea>
                </div>
                <button type="submit" class="btn btn-agregar">Guardar</button>
            </form>
        </div>
    </div>
    
    <div id="modalEditarProveedor" class="modal">
        <div class="modal-content">
            <span class="cerrar" onclick="cerrarModal('modalEditarProveedor')">&times;</span>
            <h2>Editar Proveedor</h2>
            <form action="" method="POST">
                <input type="hidden" name="accion" value="editar_proveedor">
                <input type="hidden" id="editarProveedorId" name="id">
                <div class="form-group">
                    <label for="editarProveedorNombre">Nombre:</label>
                    <input type="text" id="editarProveedorNombre" name="nombre" required>
                </div>
                <div class="form-group">
                    <label for="editarProveedorRuc">RUC:</label>
                    <input type="text" id="editarProveedorRuc" name="ruc">
                </div>
                <div class="form-group">
                    <label for="editarProveedorTelefono">Teléfono:</label>
                    <input type="tel" id="editarProveedorTelefono" name="telefono">
                </div>
                <div class="form-group">
                    <label for="editarProveedorEmail">Email:</label>
                    <input type="email" id="editarProveedorEmail" name="email">
                </div>
                <div class="form-group">
                    <label for="editarProveedorDireccion">Dirección:</label>
                    <textarea id="editarProveedorDireccion" name="direccion" rows="3"></textarea>
                </div>
                <div class="form-group checkbox-group">
                    <input type="checkbox" id="editarProveedorActivo" name="activo" value="1">
                    <label for="editarProveedorActivo">Activo</label>
                </div>
                <button type="submit" class="btn btn-agregar">Actualizar</button>
            </form>
        </div>
    </div>
    
    <div id="modalEliminarProveedor" class="modal">
        <div class="modal-content">
            <span class="cerrar" onclick="cerrarModal('modalEliminarProveedor')">&times;</span>
            <h2>Confirmar Eliminación</h2>
            <p>¿Está seguro que desea eliminar este proveedor?</p>
            <form action="" method="POST">
                <input type="hidden" name="accion" value="eliminar_proveedor">
                <input type="hidden" id="eliminarProveedorId" name="id">
                <button type="submit" class="btn btn-eliminar">Eliminar</button>
                <button type="button" class="btn" onclick="cerrarModal('modalEliminarProveedor')">Cancelar</button>
            </form>
        </div>
    </div>
    
    <script>
        // Funciones para manejar pestañas
        function abrirTab(evt, tabName) {
            var i, tabcontent, tablinks;
            
            tabcontent = document.getElementsByClassName("tab-content");
            for (i = 0; i < tabcontent.length; i++) {
                tabcontent[i].classList.remove("active");
            }
            
            tablinks = document.getElementsByClassName("tab");
            for (i = 0; i < tablinks.length; i++) {
                tablinks[i].classList.remove("active");
            }
            
            document.getElementById(tabName).classList.add("active");
            evt.currentTarget.classList.add("active");
        }
        
        // Funciones para manejar modales
        function abrirModal(modalId) {
            document.getElementById(modalId).style.display = "block";
        }
        
        function cerrarModal(modalId) {
            document.getElementById(modalId).style.display = "none";
        }
        
        // Cerrar modal al hacer clic fuera del contenido
        window.onclick = function(event) {
            if (event.target.className === "modal") {
                event.target.style.display = "none";
            }
        }
        
        // Funciones para categorías
        function editarCategoria(id, nombre, descripcion, activo) {
            document.getElementById('editarCategoriaId').value = id;
            document.getElementById('editarNombre').value = nombre;
            document.getElementById('editarDescripcion').value = descripcion;
            document.getElementById('editarActivo').checked = activo == 1;
            abrirModal('modalEditarCategoria');
        }
        
        function confirmarEliminarCategoria(id) {
            document.getElementById('eliminarCategoriaId').value = id;
            abrirModal('modalEliminarCategoria');
        }
        
        // Funciones para proveedores
        function editarProveedor(id, nombre, ruc, telefono, email, direccion, activo) {
            document.getElementById('editarProveedorId').value = id;
            document.getElementById('editarProveedorNombre').value = nombre;
            document.getElementById('editarProveedorRuc').value = ruc;
            document.getElementById('editarProveedorTelefono').value = telefono;
            document.getElementById('editarProveedorEmail').value = email;
            document.getElementById('editarProveedorDireccion').value = direccion;
            document.getElementById('editarProveedorActivo').checked = activo == 1;
            abrirModal('modalEditarProveedor');
        }
        
        function confirmarEliminarProveedor(id) {
            document.getElementById('eliminarProveedorId').value = id;
            abrirModal('modalEliminarProveedor');
        }
    </script>
</body>
</html>
<?php include('../../../includes/footer.php'); ?>