<?php
require_once '../../../config/database.php';
require_once '../../../includes/header.php';

// Verificar permisos del usuario
if (!($_SESSION['rol'] === 'Admin' || $_SESSION['rol'] === 'Gerente' || $_SESSION['rol'] === 'Restaurante')) {
    header('Location: /dashboard.php');
    exit;
}

$title = 'Restaurante - Agregar Platillo';
$error = '';

// Obtener categorías para el select
$queryCategorias = "SELECT * FROM CategoriasPlatillos WHERE Activo = 1 ORDER BY Nombre";
$stmtCategorias = $pdo->query($queryCategorias);
$categorias = $stmtCategorias->fetchAll(PDO::FETCH_ASSOC);

// Procesar el formulario
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $codigo = trim($_POST['codigo']);
    $nombre = trim($_POST['nombre']);
    $descripcion = trim($_POST['descripcion']);
    $categoriaID = isset($_POST['categoria']) ? (int)$_POST['categoria'] : null;
    $precio = isset($_POST['precio']) ? (float)$_POST['precio'] : 0;
    $tiempo = isset($_POST['tiempo']) ? (int)$_POST['tiempo'] : null;
    $activo = isset($_POST['activo']) ? 1 : 0;

    // Validaciones
    if (empty($nombre)) {
        $error = 'El nombre del platillo es requerido.';
    } elseif (!is_numeric($precio) || $precio <= 0) {
        $error = 'El precio debe ser un número positivo.';
    } elseif (!empty($tiempo) && (!is_numeric($tiempo) || $tiempo <= 0)) {
        $error = 'El tiempo de preparación debe ser un número positivo.';
    } else {
        try {
            // Verificar si el código ya existe
            if (!empty($codigo)) {
                $stmt = $pdo->prepare("SELECT COUNT(*) FROM Platillos WHERE Codigo = ?");
                $stmt->execute([$codigo]);
                $existeCodigo = $stmt->fetchColumn();

                if ($existeCodigo) {
                    $error = 'Ya existe un platillo con ese código.';
                }
            }

            // Verificar si el nombre ya existe
            $stmt = $pdo->prepare("SELECT COUNT(*) FROM Platillos WHERE Nombre = ?");
            $stmt->execute([$nombre]);
            $existeNombre = $stmt->fetchColumn();

            if ($existeNombre) {
                $error = 'Ya existe un platillo con ese nombre.';
            }

            if (empty($error)) {
                // Insertar nuevo platillo
                $stmt = $pdo->prepare("INSERT INTO Platillos (Codigo, Nombre, Descripcion, CategoriaPlatilloID, PrecioVenta, TiempoPreparacion, Activo) 
                                      VALUES (?, ?, ?, ?, ?, ?, ?)");
                $stmt->execute([$codigo, $nombre, $descripcion, $categoriaID, $precio, $tiempo, $activo]);

                $platilloID = $pdo->lastInsertId();

                // Redirigir a editar receta si se creó correctamente
                    echo "<script>window.location.href='recetas.php?id=$platilloID&new=1';</script>";

                exit;
            }
        } catch (PDOException $e) {
            $error = 'Error al agregar el platillo: ' . $e->getMessage();
        }
    }
}
?>

<div class="container mt-4">
    <h1 class="mb-4"><?= $title ?></h1>
    
    <div class="card">
        <div class="card-header">
            <i class="fas fa-plus"></i> Nuevo Platillo
        </div>
        <div class="card-body">
            <?php if ($error): ?>
                <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
            <?php endif; ?>
            
            <form method="post" enctype="multipart/form-data">
                <div class="row mb-3">
                    <div class="col-md-6">
                        <label for="codigo" class="form-label">Código</label>
                        <input type="text" class="form-control" id="codigo" name="codigo" 
                               value="<?= isset($_POST['codigo']) ? htmlspecialchars($_POST['codigo']) : '' ?>">
                    </div>
                    
                    <div class="col-md-6">
                        <label for="nombre" class="form-label">Nombre *</label>
                        <input type="text" class="form-control" id="nombre" name="nombre" required 
                               value="<?= isset($_POST['nombre']) ? htmlspecialchars($_POST['nombre']) : '' ?>">
                    </div>
                </div>
                
                <div class="mb-3">
                    <label for="descripcion" class="form-label">Descripción</label>
                    <textarea class="form-control" id="descripcion" name="descripcion" rows="3"><?= 
                        isset($_POST['descripcion']) ? htmlspecialchars($_POST['descripcion']) : '' ?></textarea>
                </div>
                
                <div class="row mb-3">
                    <div class="col-md-4">
                        <label for="categoria" class="form-label">Categoría</label>
                        <select class="form-select" id="categoria" name="categoria">
                            <option value="">Sin categoría</option>
                            <?php foreach ($categorias as $cat): ?>
                                <option value="<?= $cat['CategoriaPlatilloID'] ?>" <?= isset($_POST['categoria']) && $_POST['categoria'] == $cat['CategoriaPlatilloID'] ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($cat['Nombre']) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    
                    <div class="col-md-4">
                        <label for="precio" class="form-label">Precio *</label>
                        <div class="input-group">
                            <span class="input-group-text">$</span>
                            <input type="number" step="0.01" min="0" class="form-control" id="precio" name="precio" required 
                                   value="<?= isset($_POST['precio']) ? htmlspecialchars($_POST['precio']) : '0' ?>">
                        </div>
                    </div>
                    
                    <div class="col-md-4">
                        <label for="tiempo" class="form-label">Tiempo Prep. (min)</label>
                        <input type="number" min="1" class="form-control" id="tiempo" name="tiempo" 
                               value="<?= isset($_POST['tiempo']) ? htmlspecialchars($_POST['tiempo']) : '' ?>">
                    </div>
                </div>
                
                <div class="mb-3">
                    <label for="imagen" class="form-label">Imagen</label>
                    <input class="form-control" type="file" id="imagen" name="imagen" accept="image/*">
                </div>
                
                <div class="mb-3 form-check">
                    <input type="checkbox" class="form-check-input" id="activo" name="activo" checked>
                    <label class="form-check-label" for="activo">Activo</label>
                </div>
                
                <div class="d-grid gap-2 d-md-flex justify-content-md-end">
                    <a href="listar.php" class="btn btn-secondary me-md-2">
                        <i class="fas fa-arrow-left"></i> Cancelar
                    </a>
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save"></i> Guardar Platillo
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<?php require_once '../../../includes/footer.php'; ?>