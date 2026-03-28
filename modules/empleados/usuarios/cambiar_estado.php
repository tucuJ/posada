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
        $nuevo_estado = $_POST['nuevo_estado'];

        if ($_SESSION['usuario_id'] == $usuario_id && $nuevo_estado == 0) {
            throw new Exception("No puedes desactivar tu propio usuario");
        }

        $query = "UPDATE Usuarios SET Activo = ? WHERE UsuarioID = ?";
        $stmt = $conn->prepare($query);
        $success = $stmt->execute([$nuevo_estado, $usuario_id]);

        if (!$success) {
            throw new Exception("Error al cambiar el estado del usuario");
        }

        $_SESSION['mensaje'] = [
            'tipo' => 'success',
            'texto' => 'Estado del usuario actualizado correctamente'
        ];

    } catch (Exception $e) {
        $_SESSION['mensaje'] = [
            'tipo' => 'danger',
            'texto' => $e->getMessage()
        ];
    }
}

header("Location: listar.php?empleado_id=" . $_POST['empleado_id']);
exit();