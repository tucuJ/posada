<?php
session_start();
require_once('../../../config/database.php');

if ($_SESSION['rol'] != 'Admin') {
    header("Location: ../dashboard.php");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        $usuario_id = $_POST['usuario_id'];
        $empleado_id = $_POST['empleado_id'];
        $NombreUsuario = trim($_POST['NombreUsuario']);
        $contrasena = $_POST['contrasena'] ?? null;
        $rol = $_POST['rol'];
        $activo = $_POST['activo'];

        if (empty($NombreUsuario)) {
            throw new Exception("El nombre de usuario es obligatorio");
        }

        $stmt_check = $conn->prepare("SELECT UsuarioID FROM Usuarios WHERE NombreUsuario = ? AND UsuarioID != ?");
        $stmt_check->execute([$NombreUsuario, $usuario_id]);
        
        if ($stmt_check->fetch()) {
            throw new Exception("El nombre de usuario ya está en uso");
        }

        if (!empty($contrasena)) {
            $query = "UPDATE Usuarios SET 
                      NombreUsuario = ?, 
                      contrasena = ?, 
                      Rol = ?, 
                      Activo = ?
                      WHERE UsuarioID = ?";
            
            $params = [$NombreUsuario, $contrasena, $rol, $activo, $usuario_id];
        } else {
            $query = "UPDATE Usuarios SET 
                      NombreUsuario = ?, 
                      Rol = ?, 
                      Activo = ?
                      WHERE UsuarioID = ?";
            
            $params = [$NombreUsuario, $rol, $activo, $usuario_id];
        }
        
        $stmt = $conn->prepare($query);
        $success = $stmt->execute($params);

        if (!$success) {
            throw new Exception("Error al actualizar el usuario");
        }

        $_SESSION['mensaje'] = [
            'tipo' => 'success',
            'texto' => 'Usuario actualizado correctamente'
        ];

        header("Location: listar.php?empleado_id=" . $empleado_id);
        exit();

    } catch (Exception $e) {
        $_SESSION['mensaje'] = [
            'tipo' => 'danger',
            'texto' => $e->getMessage()
        ];

        header("Location: editar.php?id=" . $usuario_id . "&empleado_id=" . $empleado_id);
        exit();
    }
} else {
    header("Location: ../listar.php");
    exit();
}