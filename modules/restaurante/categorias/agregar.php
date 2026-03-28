<?php
require_once '../../../config/database.php';
require_once '../../../includes/header.php';

// Verificar permisos del usuario
if (!($_SESSION['rol'] === 'Admin' || $_SESSION['rol'] === 'Gerente' || $_SESSION['rol'] === 'Restaurante')) {
    header('Location: /dashboard.php');
    exit;
}

$title = 'Restaurante - Agregar Categoría';
$error = '';

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
            // Verificar si la categoría ya existe
            $stmt = $pdo->prepare("SELECT COUNT(*) FROM CategoriasPlatillos WHERE Nombre = ?");
            $stmt->execute([$nombre]);
            $existe = $stmt->fetchColumn();

            if ($existe) {
                $error = 'Ya existe una categoría con ese nombre.';
            } else {
                // Insertar nueva categoría
                $stmt = $pdo->prepare("INSERT INTO CategoriasPlatillos (Nombre, Descripcion, Activo) VALUES (?, ?, ?)");
                $stmt->execute([$nombre, $descripcion, $activo]);

                header('Location: listar.php?success=1');
                exit;
            }
        } catch (PDOException $e) {
            $error = 'Error al agregar la categoría: ' . $e->getMessage();
        }
    }
}
?>

<div class="container mt-4">
    <h1 class="mb-4"><?= $title ?></h1>
    
    <div class="card">
        <div class="card-header">
            <i class="fas fa-plus"></i> Nueva Categoría de Platillo
        </div>
        <div class="card-body">
            <?php if ($error): ?>
                <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
            <?php endif; ?>
            
            <form method="post">
                <div class="mb-3">
                    <label for="nombre" class="form-label">Nombre *</label>
                    <input type="text" class="form-control" id="nombre" name="nombre" required 
                           value="<?= isset($_POST['nombre']) ? htmlspecialchars($_POST['nombre']) : '' ?>">
                </div>
                
                <div class="mb-3">
                    <label for="descripcion" class="form-label">Descripción</label>
                    <textarea class="form-control" id="descripcion" name="descripcion" rows="3"><?= 
                        isset($_POST['descripcion']) ? htmlspecialchars($_POST['descripcion']) : '' ?></textarea>
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
                        <i class="fas fa-save"></i> Guardar
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<?php require_once '../../../includes/footer.php'; ?>