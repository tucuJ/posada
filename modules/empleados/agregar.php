<?php
require_once('../../config/database.php');
include('../../includes/header.php');


// Verificar si el usuario es administrador
if ($_SESSION['rol'] != 'Admin') {
    header("Location: /posada_del_mar/dashboard.php");
    exit();
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Agregar Empleado - Posada del Mar</title>
  
    <style>
        .form-section {
            background-color: #f8f9fa;
            border-radius: 10px;
            padding: 20px;
            margin-bottom: 20px;
        }
    </style>
</head>
<body>
    
    <div class="container mt-4">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h2><i class="fas fa-user-plus me-2"></i>Agregar Empleado</h2>
            <a href="listar.php" class="btn btn-outline-secondary">
                <i class="fas fa-arrow-left me-1"></i> Volver
            </a>
        </div>
            <?php if (isset($_SESSION['mensaje'])): ?>
            <div class="alert alert-<?= $_SESSION['mensaje']['tipo'] ?> alert-dismissible fade show">
                <?= $_SESSION['mensaje']['texto'] ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
            <?php unset($_SESSION['mensaje']); ?>
        <?php endif; ?>
        
        <div class="card shadow-sm">
            <div class="card-body">
                <form action="guardar.php" method="post">
                    <div class="form-section">
                        <h5 class="mb-4"><i class="fas fa-id-card me-2"></i>Información Personal</h5>
                        
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label for="nombre" class="form-label">Nombre*</label>
                                <input type="text" class="form-control" id="nombre" name="nombre" required>
                            </div>
                            <div class="col-md-6">
                                <label for="apellido" class="form-label">Apellido*</label>
                                <input type="text" class="form-control" id="apellido" name="apellido" required>
                            </div>
                            
                            <div class="col-md-4">
                                <label for="tipo_documento" class="form-label">Tipo de Documento*</label>
                                <select class="form-select" id="tipo_documento" name="tipo_documento" required>
                                    <option value="CED">Cédula</option>
                                    <option value="PAS">Pasaporte</option>
                                </select>
                            </div>
                            <div class="col-md-8">
                                <label for="numero_documento" class="form-label">Número de Documento*</label>
                                <input type="text" class="form-control" id="numero_documento" name="numero_documento" required>
                            </div>
                        </div>
                    </div>
                    
                    <div class="form-section">
                        <h5 class="mb-4"><i class="fas fa-address-book me-2"></i>Información de Contacto</h5>
                        
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label for="telefono" class="form-label">Teléfono</label>
                                <input type="tel" class="form-control" id="telefono" name="telefono">
                            </div>
                            <div class="col-md-6">
                                <label for="email" class="form-label">Email</label>
                                <input type="email" class="form-control" id="email" name="email">
                            </div>
                            
                            <div class="col-12">
                                <label for="direccion" class="form-label">Dirección</label>
                                <textarea class="form-control" id="direccion" name="direccion" rows="2"></textarea>
                            </div>
                        </div>
                    </div>
                    
                    <div class="form-section">
                        <h5 class="mb-4"><i class="fas fa-briefcase me-2"></i>Información Laboral</h5>
                        
                        <div class="row g-3">
                             <div class="col-md-4">
                                <label for="cargo" class="form-label">Tipo Cargo*</label>
                                <select class="form-select" id="cargo" name="cargo" required>
                                    <option value="Habitaciones">Habitaciones</option>
                                                                        <option value="Recepcion">Recepcion</option>
<option value="Restaurante">Restaurante</option>
<option value="Cosina">Cosina</option>
                                    <option value="Barra">Barra</option>
                                    <option value="Limpieza">Limpieza</option>
                                    <option value="Deposito">Deposito o Almacen</option>
                                    <option value="Servicios">Servicios</option>
                                    <option value="Gerente">Gerente</option>
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label for="fecha_contratacion" class="form-label">Fecha de Contratación</label>
                                <input type="date" class="form-control" id="fecha_contratacion" name="fecha_contratacion">
                            </div>
                            
                            <div class="col-12 mt-3">
                                <div class="form-check form-switch">
                                    <input class="form-check-input" type="checkbox" id="activo" name="activo" checked>
                                    <label class="form-check-label" for="activo">Empleado Activo</label>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="d-flex justify-content-between mt-4">
                        <a href="listar.php" class="btn btn-outline-secondary">
                            <i class="fas fa-arrow-left me-1"></i> Cancelar
                        </a>
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-save me-1"></i> Guardar Empleado
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script>
        // Validación básica del formulario
        document.querySelector('form').addEventListener('submit', function(e) {
            const nombre = document.getElementById('nombre').value.trim();
            const apellido = document.getElementById('apellido').value.trim();
            const documento = document.getElementById('numero_documento').value.trim();
            const cargo = document.getElementById('cargo').value.trim();
            
            if (!nombre || !apellido || !documento || !cargo) {
                e.preventDefault();
                alert('Por favor complete todos los campos obligatorios');
                return false;
            }
        });
    </script>
</body>
</html>
    <?php include('../../includes/footer.php'); ?>
