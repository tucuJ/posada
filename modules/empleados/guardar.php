<?php 
session_start();
require_once('../../config/database.php');

// Verificar si el usuario es administrador
if ($_SESSION['rol'] != 'Admin') {
    header("Location: /posada_del_mar/dashboard.php");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        // Recoger y validar datos
        $empleado_id = $_POST['empleado_id'] ?? null;
        $nombre = trim($_POST['nombre'] ?? '');
        $apellido = trim($_POST['apellido'] ?? '');
        $tipo_documento = $_POST['tipo_documento'] ?? '';
        $numero_documento = trim($_POST['numero_documento'] ?? '');
        $telefono = trim($_POST['telefono'] ?? '');
        $email = trim($_POST['email'] ?? '');
        $direccion = trim($_POST['direccion'] ?? '');
        $cargo = trim($_POST['cargo'] ?? '');
        $fecha_contratacion = !empty($_POST['fecha_contratacion']) ? $_POST['fecha_contratacion'] : null;
        $activo = isset($_POST['activo']) ? 1 : 0;

        // Validaciones básicas
        if (empty($nombre) || empty($apellido) || empty($cargo) || empty($numero_documento)) {
            throw new Exception("Todos los campos obligatorios deben ser completados");
        }

        // Verificar si el documento ya existe
        $query_check = "SELECT EmpleadoID FROM Empleados WHERE NumeroDocumento = ?";
        $params_check = [$numero_documento];
        
        if ($empleado_id) {
            $query_check .= " AND EmpleadoID != ?";
            $params_check[] = $empleado_id;
        }
        
        $stmt_check = $conn->prepare($query_check);
        $stmt_check->execute($params_check);
        
        if ($stmt_check->fetch()) {
            throw new Exception("Ya existe un empleado con este número de documento");
        }

        // Procesar la operación
        if ($empleado_id) {
            // Verificar si es admin para no desactivar
            $stmt_admin = $conn->prepare("SELECT UsuarioID FROM Usuarios WHERE EmpleadoID = ? AND Rol = 'Admin'");
            $stmt_admin->execute([$empleado_id]);
            
            if ($stmt_admin->fetch()) {
                $activo = 1; // Forzar activo para admin
            }

            // Actualizar empleado existente
            $query = "UPDATE Empleados SET 
                      Nombre = ?, 
                      Apellido = ?, 
                      TipoDocumento = ?, 
                      NumeroDocumento = ?,
                      Telefono = ?, 
                      Email = ?, 
                      Direccion = ?, 
                      Cargo = ?, 
                      FechaContratacion = ?, 
                      Activo = ? 
                      WHERE EmpleadoID = ?";
            
            $stmt = $conn->prepare($query);
            $result = $stmt->execute([
                $nombre, 
                $apellido, 
                $tipo_documento, 
                $numero_documento,
                $telefono, 
                $email, 
                $direccion, 
                $cargo, 
                $fecha_contratacion, 
                $activo,
                $empleado_id
            ]);
            
            if (!$result) {
                $errorInfo = $stmt->errorInfo();
                throw new Exception("Error al actualizar empleado: " . $errorInfo[2]);
            }
            
            $mensaje = "Empleado actualizado correctamente";
        } else {
            // Insertar nuevo empleado (sin FechaCreacion)
            $query = "INSERT INTO Empleados (
                Nombre, 
                Apellido, 
                TipoDocumento, 
                NumeroDocumento,
                Telefono, 
                Email, 
                Direccion, 
                Cargo, 
                FechaContratacion, 
                Activo
            ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
            
            $stmt = $conn->prepare($query);
            $result = $stmt->execute([
                $nombre, 
                $apellido, 
                $tipo_documento, 
                $numero_documento,
                $telefono, 
                $email, 
                $direccion, 
                $cargo, 
                $fecha_contratacion, 
                $activo
            ]);
            
            if (!$result) {
                $errorInfo = $stmt->errorInfo();
                throw new Exception("Error al registrar empleado: " . $errorInfo[2]);
            }
            
            $empleado_id = $conn->lastInsertId();
            $mensaje = "Empleado registrado correctamente";
        }

        $_SESSION['mensaje'] = [
            'tipo' => 'success',
            'titulo' => 'Éxito',
            'texto' => $mensaje
        ];

        header("Location: editar.php?id=" . $empleado_id);
        exit();

    } catch (Exception $e) {
        $_SESSION['mensaje'] = [
            'tipo' => 'danger',
            'titulo' => 'Error',
            'texto' => $e->getMessage()
        ];

        $redirect_url = isset($_POST['empleado_id']) ? 'editar.php?id='.$_POST['empleado_id'] : 'agregar.php';
        header("Location: " . $redirect_url);
        exit();
    }
} else {
    header("Location: listar.php");
    exit();
}