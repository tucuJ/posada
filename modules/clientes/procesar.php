<?php
require_once('../../config/database.php');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        if (isset($_POST['agregar'])) {
            // Proceso para agregar nuevo cliente
            $nombre = $_POST['nombre'];
            $apellido = $_POST['apellido'];
            $tipoDocumento = $_POST['tipoDocumento'];
            $numeroDocumento = $_POST['numeroDocumento'];
            $telefono = $_POST['telefono'] ?? null;
            $email = $_POST['email'] ?? null;
            $direccion = $_POST['direccion'] ?? null;
            
            // Verificar si el documento ya existe
            $query = "SELECT COUNT(*) FROM Clientes 
                      WHERE TipoDocumento = ? AND NumeroDocumento = ?";
            $stmt = $conn->prepare($query);
            $stmt->execute([$tipoDocumento, $numeroDocumento]);
            
            if ($stmt->fetchColumn() > 0) {
                header("Location: agregar.php?error=1");
                exit();
            }
            
            // Insertar nuevo cliente
            $query = "INSERT INTO Clientes (
                Nombre, Apellido, TipoDocumento, NumeroDocumento, 
                Telefono, Email, Direccion
            ) VALUES (?, ?, ?, ?, ?, ?, ?)";
            
            $stmt = $conn->prepare($query);
            $stmt->execute([
                $nombre, $apellido, $tipoDocumento, $numeroDocumento,
                $telefono, $email, $direccion
            ]);
            
            header("Location: listar.php?success=1");
            exit();
        }
        
        if (isset($_POST['editar'])) {
            // Proceso para editar cliente
            $id = $_POST['id'];
            $nombre = $_POST['nombre'];
            $apellido = $_POST['apellido'];
            $tipoDocumento = $_POST['tipoDocumento'];
            $numeroDocumento = $_POST['numeroDocumento'];
            $telefono = $_POST['telefono'] ?? null;
            $email = $_POST['email'] ?? null;
            $direccion = $_POST['direccion'] ?? null;
            
            // Verificar si el documento ya existe (excluyendo el actual)
            $query = "SELECT COUNT(*) FROM Clientes 
                      WHERE TipoDocumento = ? AND NumeroDocumento = ? AND ClienteID != ?";
            $stmt = $conn->prepare($query);
            $stmt->execute([$tipoDocumento, $numeroDocumento, $id]);
            
            if ($stmt->fetchColumn() > 0) {
                header("Location: editar.php?id=$id&error=1");
                exit();
            }
            
            // Actualizar cliente
            $query = "UPDATE Clientes SET 
                      Nombre = ?, Apellido = ?, TipoDocumento = ?, NumeroDocumento = ?,
                      Telefono = ?, Email = ?, Direccion = ?
                      WHERE ClienteID = ?";
            
            $stmt = $conn->prepare($query);
            $stmt->execute([
                $nombre, $apellido, $tipoDocumento, $numeroDocumento,
                $telefono, $email, $direccion, $id
            ]);
            
            header("Location: listar.php?success=2");
            exit();
        }
    } catch(PDOException $e) {
        header("Location: " . (isset($_POST['agregar']) ? 'agregar.php' : 'editar.php?id='.$_POST['id']) . "&error=2");
        exit();
    }
} elseif (isset($_GET['action']) && $_GET['action'] == 'delete' && isset($_GET['id'])) {
    // Proceso para eliminar cliente (solo si no tiene reservas)
    $id = $_GET['id'];
    
    try {
        // Verificar si tiene reservas
        $query = "SELECT COUNT(*) FROM Reservaciones WHERE ClienteID = ?"; 
        $stmt = $conn->prepare($query);
        $stmt->execute([$id]);

        if ($stmt->fetchColumn() > 0) {
            header("Location: listar.php?error=1");
            exit();
        }
    
        // Eliminar cliente
        $query = "DELETE FROM Clientes WHERE ClienteID = ?";
        $conn->prepare($query)->execute([$id]);
        
        header("Location: listar.php?success=3");
        exit();
    } catch(PDOException $e) {
        header("Location: listar.php?error=2");
        exit();
    }
}

header("Location: listar.php");
exit();
?>