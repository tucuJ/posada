<?php
require_once('../../../config/database.php');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        if (isset($_POST['agregar'])) {
            // Proceso para agregar nuevo tipo
            $nombre = $_POST['nombre'];
            $capacidad = $_POST['capacidad'];
            $precio = $_POST['precio'];
            $descripcion = $_POST['descripcion'];
            
            // Verificar si el nombre ya existe
            $query = "SELECT COUNT(*) FROM TiposHabitacion WHERE Nombre = ?";
            $stmt = $conn->prepare($query);
            $stmt->execute([$nombre]);
            
            if ($stmt->fetchColumn() > 0) {
                header("Location: agregar.php?error=1");
                exit();
            }
            
            // Insertar nuevo tipo
            $query = "INSERT INTO TiposHabitacion (Nombre, Capacidad, PrecioNoche, Descripcion) 
                      VALUES (?, ?, ?, ?)";
            $stmt = $conn->prepare($query);
            $stmt->execute([$nombre, $capacidad, $precio, $descripcion]);
            
            header("Location: listar.php?success=1");
        }
        
        if (isset($_POST['editar'])) {
            // Proceso para editar tipo
            $id = $_POST['id'];
            $nombre = $_POST['nombre'];
            $capacidad = $_POST['capacidad'];
            $precio = $_POST['precio'];
            $descripcion = $_POST['descripcion'];
            
            // Verificar si el nombre ya existe (excluyendo el actual)
            $query = "SELECT COUNT(*) FROM TiposHabitacion WHERE Nombre = ? AND TipoHabitacionID != ?";
            $stmt = $conn->prepare($query);
            $stmt->execute([$nombre, $id]);
            
            if ($stmt->fetchColumn() > 0) {
                header("Location: editar.php?id=$id&error=1");
                exit();
            }
            
            // Actualizar tipo
            $query = "UPDATE TiposHabitacion SET 
                      Nombre = ?, Capacidad = ?, PrecioNoche = ?, Descripcion = ?
                      WHERE TipoHabitacionID = ?";
            $stmt = $conn->prepare($query);
            $stmt->execute([$nombre, $capacidad, $precio, $descripcion, $id]);
            
            header("Location: listar.php?success=2");
        }
    } catch(PDOException $e) {
        header("Location: " . (isset($_POST['agregar']) ? 'agregar.php' : 'editar.php?id='.$_POST['id']) . "&error=2");
    }
}
?>