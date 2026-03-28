<?php session_start();

require_once('../../config/database.php');

// Verificar si el usuario es administrador
if ($_SESSION['rol'] != 'Admin') {
    header("Location: /posada_del_mar/dashboard.php");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        $empleado_id = $_POST['empleado_id'] ?? null;
        $accion = $_POST['accion'] ?? null;

        if (!$empleado_id || !$accion) {
            throw new Exception("Datos incompletos para la acción");
        }

        // Verificar si el empleado es admin principal
        $stmt = $conn->prepare("SELECT u.UsuarioID 
                               FROM Usuarios u
                               JOIN Empleados e ON u.EmpleadoID = e.EmpleadoID
                               WHERE e.EmpleadoID = ? AND u.Rol = 'Admin'");
        $stmt->execute([$empleado_id]);
        
        if ($stmt->fetch() && $accion == 'desactivar') {
            throw new Exception("No se puede desactivar al administrador principal");
        }

        // Procesar la acción solicitada
        switch ($accion) {
            case 'activar':
                $query = "UPDATE Empleados SET Activo = TRUE WHERE EmpleadoID = ?";
                $mensaje = "Empleado activado correctamente";
                break;
            
            case 'desactivar':
                $query = "UPDATE Empleados SET Activo = FALSE WHERE EmpleadoID = ?";
                $mensaje = "Empleado desactivado correctamente";
                break;
            
            default:
                throw new Exception("Acción no válida");
        }

        // Ejecutar la acción
        $stmt = $conn->prepare($query);
        $stmt->execute([$empleado_id]);

        $_SESSION['mensaje'] = [
            'tipo' => 'success',
            'titulo' => 'Éxito',
            'texto' => $mensaje
        ];

    } catch (Exception $e) {
        $_SESSION['mensaje'] = [
            'tipo' => 'danger',
            'titulo' => 'Error',
            'texto' => $e->getMessage()
        ];
    }
}

header("Location: listar.php");
exit();
?>