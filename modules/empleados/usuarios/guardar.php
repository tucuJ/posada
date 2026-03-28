<?php
session_start();
require_once('../../../config/database.php');

if ($_SESSION['rol'] != 'Admin') {
    header("Location: ../dashboard.php");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        $empleado_id = $_POST['empleado_id'];
        $NombreUsuario = trim($_POST['NombreUsuario']);
        $contrasena = $_POST['contrasena'];
        $rol = $_POST['rol'];
        $activo = $_POST['activo'];

        if (empty($NombreUsuario) || empty($contrasena)) {
            throw new Exception("Todos los campos son obligatorios");
        }

        $stmt_check = $conn->prepare("SELECT UsuarioID FROM Usuarios WHERE NombreUsuario = ?");
        $stmt_check->execute([$NombreUsuario]);
        
        if ($stmt_check->fetch()) {
            throw new Exception("El nombre de usuario ya está en uso");
        }

        $query = "INSERT INTO Usuarios (
                    NombreUsuario, 
                    contrasena, 
                    Rol, 
                    Activo,
                    EmpleadoID
                  ) VALUES (?, ?, ?, ?, ?)";
        
        $stmt = $conn->prepare($query);
        $success = $stmt->execute([
            $NombreUsuario,
            $contrasena, // Sin encriptación
            $rol,
            $activo,
            $empleado_id
        ]);

        if (!$success) {
            throw new Exception("Error al crear el usuario");
        }

        $_SESSION['mensaje'] = [
            'tipo' => 'success',
            'texto' => 'Usuario creado correctamente'
        ];

        header("Location: listar.php?empleado_id=" . $empleado_id);
        exit();

    } catch (Exception $e) {
        $_SESSION['mensaje'] = [
            'tipo' => 'danger',
            'texto' => $e->getMessage()
        ];

        header("Location: agregar.php?empleado_id=" . $empleado_id);
        exit();
    }
} else {
    header("Location: ../listar.php");
    exit();
}