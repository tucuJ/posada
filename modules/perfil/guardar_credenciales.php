<?php
session_start();


require_once('../../config/database.php');


// Verificar que el usuario es administrador
if ($_SESSION['rol'] != 'Admin') {
    header("Location: /posada_del_mar/dashboard.php");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        // Obtener datos del formulario
        $nuevo_usuario = trim($_POST['nuevo_usuario'] ?? '');
        $contrasena_actual = $_POST['contrasena_actual'] ?? '';
        $nueva_contrasena = $_POST['nueva_contrasena'] ?? '';
        
        // Validaciones básicas
        if (empty($contrasena_actual)) {
            throw new Exception("Debes ingresar tu contraseña actual para confirmar los cambios");
        }
        
        // Obtener usuario actual
        $query = "SELECT * FROM Usuarios WHERE UsuarioID = ?";
        $stmt = $conn->prepare($query);
        $stmt->execute([$_SESSION['usuario_id']]);
        $usuario = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if (!$usuario) {
            throw new Exception("Usuario no encontrado");
        }
        
        // Verificar contraseña actual (comparación directa sin password_verify)
        if ($contrasena_actual !== $usuario['Contrasena']) {
            throw new Exception("La contraseña actual es incorrecta");
        }
        
        // Preparar actualización
        $updates = [];
        $params = [];
        
        // Actualizar nombre de usuario si cambió
        if (!empty($nuevo_usuario) && $nuevo_usuario != $usuario['NombreUsuario']) {
            // Verificar si el nuevo usuario ya existe
            $stmt = $conn->prepare("SELECT UsuarioID FROM Usuarios WHERE NombreUsuario = ? AND UsuarioID != ?");
            $stmt->execute([$nuevo_usuario, $_SESSION['usuario_id']]);
            
            if ($stmt->fetch()) {
                throw new Exception("El nombre de usuario ya está en uso");
            }
            
            $updates[] = "NombreUsuario = ?";
            $params[] = $nuevo_usuario;
            
            // Actualizar en sesión
            $_SESSION['usuario_nombre'] = $nuevo_usuario;
        }
        
        // Actualizar contraseña si se proporcionó una nueva
        if (!empty($nueva_contrasena)) {
            if (strlen($nueva_contrasena) < 8) {
                throw new Exception("La nueva contraseña debe tener al menos 8 caracteres");
            }
            
            // Guardar contraseña en texto plano (o aplicar tu método de hashing alternativo)
            $updates[] = "Contrasena = ?";
            $params[] = $nueva_contrasena;
        }
        
        // Si hay cambios que hacer
        if (!empty($updates)) {
            $query = "UPDATE Usuarios SET " . implode(", ", $updates) . " WHERE UsuarioID = ?";
            $params[] = $_SESSION['usuario_id'];
            
            $stmt = $conn->prepare($query);
            $stmt->execute($params);
            
            $_SESSION['mensaje_credenciales'] = [
                'tipo' => 'success',
                'titulo' => 'Éxito',
                'texto' => 'Credenciales actualizadas correctamente'
            ];
        } else {
            $_SESSION['mensaje_credenciales'] = [
                'tipo' => 'info',
                'titulo' => 'Información',
                'texto' => 'No se realizaron cambios en las credenciales'
            ];
        }
        
        header("Location: credenciales.php");
        exit();
        
    } catch (Exception $e) {
        $_SESSION['mensaje_credenciales'] = [
            'tipo' => 'danger',
            'titulo' => 'Error',
            'texto' => $e->getMessage()
        ];
        
        header("Location: credenciales.php");
        exit();
    }
} else {
    header("Location: credenciales.php");
    exit();
}