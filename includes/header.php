<?php
session_start();

// Verificar autenticación
if (!isset($_SESSION['usuario_id'])) {
    header("Location: ../login.php");
    exit();
}

// Configuración básica
if (!defined('BASE_URL')) {
    define('BASE_URL', '/posada/');
}?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Posada del Mar - <?= $pageTitle ?? 'Sistema' ?></title>
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <!-- Estilos personalizados -->
    <link href="<?= BASE_URL ?>assets/css/style.css" rel="stylesheet">
</head>
<body>
    <nav class="navbar navbar-expand-lg navbar-dark bg-primary">
        <div class="container">
            <a class="navbar-brand" href="<?= BASE_URL ?>dashboard.php">
                <i class="fas fa-umbrella-beach"></i> Posada del Mar
            </a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav me-auto">
                    <!-- Menú para Recepción -->
                    <?php if (in_array($_SESSION['rol'], ['Admin', 'Gerente', 'Recepcion'])): ?>
                        <li class="nav-item dropdown">
                            <a class="nav-link dropdown-toggle" href="#" id="reservasDropdown" role="button" data-bs-toggle="dropdown">
                                <i class="fas fa-calendar-check"></i> Reservas
                            </a>
                            <ul class="dropdown-menu">
                                <li><a class="dropdown-item" href="<?= BASE_URL ?>modules/reservas/listar.php">Listar Reservas</a></li>
                                <li><a class="dropdown-item" href="<?= BASE_URL ?>modules/reservas/nueva.php">Nueva Reserva</a></li>
                                <li><hr class="dropdown-divider"></li>
                                <li><a class="dropdown-item" href="<?= BASE_URL ?>modules/habitaciones/listar.php">Habitaciones</a></li>
                                <li><a class="dropdown-item" href="<?= BASE_URL ?>modules/clientes/listar.php">Clientes</a></li>
                            </ul>
                        </li>
                    <?php endif; ?>

                    <!-- Menú para Ventas -->
                    <?php if (in_array($_SESSION['rol'], ['Admin', 'Gerente', 'Cajero'])): ?>
                        <li class="nav-item dropdown">
                            <a class="nav-link dropdown-toggle" href="#" id="ventasDropdown" role="button" data-bs-toggle="dropdown">
                                <i class="fas fa-cash-register"></i> Ventas
                            </a>
                            <ul class="dropdown-menu">
                                <li><a class="dropdown-item" href="<?= BASE_URL ?>modules/ventas/nueva.php">Nueva Venta</a></li>
                                <li><a class="dropdown-item" href="<?= BASE_URL ?>modules/ventas/listar.php">Historial</a></li>
                                <li><hr class="dropdown-divider"></li>
                                <li><a class="dropdown-item" href="<?= BASE_URL ?>modules/ventas/reportes/diario.php">Reportes</a></li>
                            </ul>
                        </li>
                    <?php endif; ?>

                    <!-- Menú para Inventario -->
                    <?php if (in_array($_SESSION['rol'], ['Admin', 'Gerente', 'Bodega'])): ?>
                        <li class="nav-item dropdown">
                            <a class="nav-link dropdown-toggle" href="#" id="inventarioDropdown" role="button" data-bs-toggle="dropdown">
                                <i class="fas fa-boxes"></i> Almacen
                            </a>
                            <ul class="dropdown-menu">
                                <li><a class="dropdown-item" href="<?= BASE_URL ?>modules/productos/listar.php">Productos</a></li>
                                <li><a class="dropdown-item" href="<?= BASE_URL ?>modules/productos/agregar.php">Agregar Producto</a></li>
                                <li><a class="dropdown-item" href="<?= BASE_URL ?>modules/productos/categorias/listar.php">Categorías o Proveedores</a></li>
                                                                <li><hr class="dropdown-divider"></li>
                                <li><a class="dropdown-item" href="<?= BASE_URL ?>modules/servicios/listar_servicio.php">Servicios</a></li>
                                <li><a class="dropdown-item" href="<?= BASE_URL ?>modules/paquetes/listar_paquete.php">Paquetes</a></li>

                            </ul>
                        </li>
                    <?php endif; ?>           
                    <?php if (in_array($_SESSION['rol'], ['Admin', 'Gerente', 'Bodega'])): ?>
                        <li class="nav-item dropdown">
                            <a class="nav-link dropdown-toggle" href="#" id="inventarioDropdown" role="button" data-bs-toggle="dropdown">
                                <i class="fas fa-boxes"></i> Restaurante
                            </a>
                            <ul class="dropdown-menu">
                                <li><a class="dropdown-item" href="<?= BASE_URL ?>modules/restaurante/ingredientes/listar.php">Ingredientes</a></li>
                                <li><a class="dropdown-item" href="<?= BASE_URL ?>modules/restaurante/categorias/listar.php">Categorias</a></li>
                               <li><a class="dropdown-item" href="<?= BASE_URL ?>modules/restaurante/platillos/listar.php">Platillos</a></li>
                               <li><a class="dropdown-item" href="<?= BASE_URL ?>modules/restaurante/mesas/listar.php">Mesas</a></li>

                                <li><a class="dropdown-item" href="<?= BASE_URL ?>modules/restaurante/ordenes/listar.php">Ordenes</a></li>
                               
                                   <li><hr class="dropdown-divider"></li>

                                <li><a class="dropdown-item" href="<?= BASE_URL ?>modules/restaurante/reportes/consumo_ingredientes.php">Consumo de Ingredientes</a></li>
                                <li><a class="dropdown-item" href="<?= BASE_URL ?>modules/restaurante/reportes/ventas.php">Reportes de venta</a></li>

                            </ul>
                        </li>
                    <?php endif; ?>

                    <!-- Menú para Administración -->
                    <?php if (in_array($_SESSION['rol'], ['Admin', 'Gerente'])): ?>
                        <li class="nav-item dropdown">
                            <a class="nav-link dropdown-toggle" href="#" id="adminDropdown" role="button" data-bs-toggle="dropdown">
                                <i class="fas fa-cog"></i> Administración
                            </a>
                            <ul class="dropdown-menu">
                                <li><a class="dropdown-item" href="<?= BASE_URL ?>modules/empleados/listar.php">Empleados</a></li>
                                <li><hr class="dropdown-divider"></li>
                                <li><a class="dropdown-item" href="<?= BASE_URL ?>modules/reportes/ingresos.php">Reportes Financieros</a></li>
                                <li><a class="dropdown-item" href="<?= BASE_URL ?>modules/configuracion/general.php">Configuración</a></li>
                            </ul>
                        </li>
                    <?php endif; ?>
                </ul>
                
                <ul class="navbar-nav ms-auto">
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle" href="#" id="userDropdown" role="button" data-bs-toggle="dropdown">
                            <i class="fas fa-user-circle"></i> <?= htmlspecialchars($_SESSION['nombre_completo']) ?>
                        </a>
                        <ul class="dropdown-menu dropdown-menu-end">
                            <li><a class="dropdown-item" href="<?= BASE_URL ?>modules/perfil/"><i class="fas fa-user-edit"></i> Mi Perfil</a></li>
                            <li><hr class="dropdown-divider"></li>
                            <li><a class="dropdown-item" href="<?= BASE_URL ?>logout.php"><i class="fas fa-sign-out-alt"></i> Cerrar Sesión</a></li>
                        </ul>
                    </li>
                </ul>
            </div>
        </div>
    </nav>

    <div class=" mt-3">
        <?php if (isset($_SESSION['success_message'])): ?>
            <div class="alert alert-success alert-dismissible fade show">
                <?= $_SESSION['success_message'] ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
            <?php unset($_SESSION['success_message']); ?>
        <?php endif; ?>

        <?php if (isset($_SESSION['error_message'])): ?>
            <div class="alert alert-danger alert-dismissible fade show">
                <?= $_SESSION['error_message'] ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
            <?php unset($_SESSION['error_message']); ?>
        <?php endif; ?>
    </div>