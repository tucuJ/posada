<?php
require_once '../../../config/database.php';
require_once '../../../includes/header.php';

// Verificar permisos del usuario
if (!($_SESSION['rol'] === 'Admin' || $_SESSION['rol'] === 'Gerente' || $_SESSION['rol'] === 'Restaurante')) {
    header('Location: /dashboard.php');
    exit;
}

$title = 'Restaurante - Editar Categoría';
$error = '';

// Obtener ID de la categoría
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    header('Location: listar.php?error=ID no válido');
    exit;
}

$id = (int)$_GET['id'];

// Obtener datos de la categoría
try {
    $stmt = $pdo->prepare("SELECT * FROM CategoriasPlatillos WHERE CategoriaPlatilloID = ?");
    $stmt->execute([$id]);
    $categoria = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$categoria) {
        header('Location: listar.php?error=Categoría no encontrada');
        exit;
    }
} catch (PDOException $e) {
    header('Location: listar.php?error=Error al obtener la categoría');
    exit;
}

// Procesar el formulario
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nombre = trim($_POST['nombre']);
    $descripcion = trim($_POST['descripcion']);
    $activo = isset($_POST['activo']) ? 1 : 0;

    // Validaciones
    if (empty($nombre)) {
        $error = 'El nombre de la categoría es requerido.';
    } else {
        try {
            // Verificar si el nombre ya existe en otra categoría
            $stmt = $pdo->prepare("SELECT COUNT(*) FROM CategoriasPlatillos WHERE Nombre = ? AND CategoriaPlatilloID != ?");
            $stmt->execute([$nombre, $id]);
            $existe = $stmt->fetchColumn();

            if ($existe) {
                $error = 'Ya existe otra categoría con ese nombre.';
            } else {
                // Actualizar la categoría
                $stmt = $pdo->prepare("UPDATE CategoriasPlatillos SET Nombre = ?, Descripcion = ?, Activo = ? WHERE CategoriaPlatilloID = ?");
                $stmt->execute([$nombre, $descripcion, $activo, $id]);
    echo "<script>window.location.href='listar.php?success=1';</script>";

                exit;
            }
        } catch (PDOException $e) {
            $error = 'Error al actualizar la categoría: ' . $e->getMessage();
        }
    }
}
?>

<div class="container mt-4">
    <h1 class="mb-4"><?= $title ?></h1>
    
    <div class="card">
        <div class="card-header">
            <i class="fas fa-edit"></i> Editar Categoría de Platillo
        </div>
        <div class="card-body">
            <?php if ($error): ?>
                <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
            <?php endif; ?>
            
            <form method="post">
                <div class="mb-3">
                    <label for="nombre" class="form-label">Nombre *</label>
                    <input type="text" class="form-control" id="nombre" name="nombre" required 
                           value="<?= htmlspecialchars($categoria['Nombre']) ?>">
                </div>
                
                <div class="mb-3">
                    <label for="descripcion" class="form-label">Descripción</label>
                    <textarea class="form-control" id="descripcion" name="descripcion" rows="3"><?= 
                        htmlspecialchars($categoria['Descripcion']) ?></textarea>
                </div>
                
                <div class="mb-3 form-check">
                    <input type="checkbox" class="form-check-input" id="activo" name="activo" 
                        <?= $categoria['Activo'] ? 'checked' : '' ?>>
                    <label class="form-check-label" for="activo">Activo</label>
                </div>
                
                <div class="d-grid gap-2 d-md-flex justify-content-md-end">
                    <a href="listar.php" class="btn btn-secondary me-md-2">
                        <i class="fas fa-arrow-left"></i> Cancelar
                    </a>
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save"></i> Guardar Cambios
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<?php require_once '../../../includes/footer.php'; ?>