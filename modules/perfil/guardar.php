<?php
session_start();

require_once('../../config/database.php');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        // Obtener ID del usuario
        $usuario_id = $_SESSION['usuario_id'];
        
        // Verificar si el perfil pertenece a un admin
        $stmt = $conn->prepare("SELECT Rol FROM Usuarios WHERE UsuarioID = ?");
        $stmt->execute([$usuario_id]);
        $usuario = $stmt->fetch(PDO::FETCH_ASSOC);
        
        // Solo otro admin puede editar un perfil de admin
        if ($usuario['Rol'] == 'Admin' && $_SESSION['rol'] != 'Admin') {
            throw new Exception("Acceso denegado: Solo los administradores pueden editar este perfil");
        }
        
        // Recoger datos del formulario
        $nombre = trim($_POST['nombre']);
        $apellido = trim($_POST['apellido']);
        $telefono = trim($_POST['telefono'] ?? '');
        $email = trim($_POST['email'] ?? '');
        $direccion = trim($_POST['direccion'] ?? '');
        
        // Validaciones básicas
        if (empty($nombre) || empty($apellido)) {
            throw new Exception("Nombre y apellido son obligatorios");
        }
        
        if (!empty($email) && !filter_var($email, FILTER_VALIDATE_EMAIL)) {
            throw new Exception("El email no tiene un formato válido");
        }
        
        // Procesar foto de perfil si se subió
        $foto_nombre = null;
        if (isset($_FILES['foto']) && $_FILES['foto']['error'] === UPLOAD_ERR_OK) {
            $foto = $_FILES['foto'];
            
            // Validar tipo de archivo
            $permitidos = ['image/jpeg', 'image/png', 'image/gif'];
            if (!in_array($foto['type'], $permitidos)) {
                throw new Exception("Solo se permiten imágenes JPG, PNG o GIF");
            }
            
            // Validar tamaño (máx 2MB)
            if ($foto['size'] > 2097152) {
                throw new Exception("La imagen no debe superar los 2MB");
            }
            
            // Generar nombre único y mover al directorio de uploads
            $extension = pathinfo($foto['name'], PATHINFO_EXTENSION);
            $foto_nombre = 'profile_' . $usuario_id . '_' . time() . '.' . $extension;
            $destino = '../../uploads/profiles/' . $foto_nombre;
            
            if (!move_uploaded_file($foto['tmp_name'], $destino)) {
                throw new Exception("Error al subir la imagen");
            }
        }
        
        // Actualizar datos del empleado
        $query = "UPDATE Empleados SET 
                  Nombre = ?, Apellido = ?, Telefono = ?, 
                  Email = ?, Direccion = ?";
        
        // Si hay foto nueva, agregar al query
        $params = [$nombre, $apellido, $telefono, $email, $direccion];
        if ($foto_nombre) {
            $query .= ", Foto = ?";
            $params[] = $foto_nombre;
        }
        
        $query .= " WHERE EmpleadoID = (SELECT EmpleadoID FROM Usuarios WHERE UsuarioID = ?)";
        $params[] = $usuario_id;
        
        $stmt = $conn->prepare($query);
        $stmt->execute($params);
        
        // Actualizar nombre en sesión si cambió
        $_SESSION['nombre_completo'] = $nombre . ' ' . $apellido;
        
        $_SESSION['mensaje'] = [
            'tipo' => 'success',
            'titulo' => 'Éxito',
            'texto' => 'Perfil actualizado correctamente'
        ];
        
        header("Location: index.php");
        exit();
        
    } catch (Exception $e) {
        $_SESSION['mensaje'] = [
            'tipo' => 'danger',
            'titulo' => 'Error',
            'texto' => $e->getMessage()
        ];
        
        header("Location: editar.php");
        exit();
    }
} else {
    header("Location: index.php");
    exit();
}