<?php

session_start();
require_once('../../config/database.php');

// Verificar permisos
if (!($_SESSION['rol'] === 'Admin' || $_SESSION['rol'] === 'Recepcion')) {
    header('Location: /dashboard.php');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        // Validar y sanitizar datos
        $numero = trim($_POST['numero'] ?? '');
        $tipo = $_POST['tipo'] ?? '';
        $estado = $_POST['estado'] ?? 'Disponible';
        $notas = trim($_POST['notas'] ?? '');
        $costom = floatval($_POST['costom'] ?? 0);
        
        // Validación básica
        if (empty($numero) || empty($tipo)) {
            header("Location: agregar.php?error=3");
            exit();
        }

              
        if (isset($_POST['agregar'])) {
            // Proceso para agregar nueva habitación
            // Validar datos requeridos
            if (empty($numero) || empty($tipo)) {
                throw new Exception('Datos incompletos');
            }
            
            // Verificar si el número ya existe
            $query = "SELECT COUNT(*) FROM Habitaciones WHERE Numero = ?";
            $stmt = $conn->prepare($query);
            $stmt->execute([$numero]);
            
            if ($stmt->fetchColumn() > 0) {
                header("Location: agregar.php?error=1");
                exit();
            }
            
            // Insertar nueva habitación
            $query = "INSERT INTO Habitaciones 
                     (Numero, TipoHabitacionID, Estado, Notas, costom) 
                     VALUES (?, ?, ?, ?, ?)";
            $stmt = $conn->prepare($query);
            $stmt->execute([$numero, $tipo, $estado, $notas, $costom]);
            
            header("Location: listar.php?success=1");
         
            if ($success) {
                $conn->commit();
                header("Location: listar.php?success=1");
                exit();
            } else {
                $conn->rollBack();
            header("Location: listar.php?success=1");
                exit();
            }
        }
        
        if (isset($_POST['editar'])) {
            $id = intval($_POST['id'] ?? 0);
            
            if ($id <= 0) {
                header("Location: listar.php?error=4");
                exit();
            }
            
            // Verificar si el número ya existe (excluyendo la actual)
            $query = "SELECT COUNT(*) FROM Habitaciones WHERE Numero = ? AND HabitacionID != ?";
            $stmt = $conn->prepare($query);
            $stmt->execute([$numero, $id]);
            
            if ($stmt->fetchColumn() > 0) {
                header("Location: editar.php?id=$id&error=1");
                exit();
            }
            
            // Actualizar habitación con transacción
            $conn->beginTransaction();
            
            $query = "UPDATE Habitaciones SET 
                      Numero = ?, TipoHabitacionID = ?, Estado = ?, 
                      Notas = ?, costom = ?
                      WHERE HabitacionID = ?";
            $stmt = $conn->prepare($query);
            $success = $stmt->execute([$numero, $tipo, $estado, $notas, $costom, $id]);
            
            if ($success) {
                $conn->commit();
                header("Location: listar.php?success=2");
                exit();
            } else {
                $conn->rollBack();
                header("Location: editar.php?id=$id&error=2");
                exit();
            }
        }
        
    } catch(PDOException $e) {
        if (isset($conn) && $conn->inTransaction()) {
            $conn->rollBack();
        }
        error_log('Error en procesar.php: ' . $e->getMessage());
        header("Location: " . (isset($_POST['agregar']) ? 'listar.php' : 'editar.php?id='.($_POST['id'] ?? '').'&error=2'));
        exit();
    } catch(Exception $e) {
        if (isset($conn) && $conn->inTransaction()) {
            $conn->rollBack();
        }
        error_log('Error general en procesar.php: ' . $e->getMessage());
        header("Location: " . (isset($_POST['agregar']) ? 'agregar.php?error=4' : 'editar.php?id='.($_POST['id'] ?? '').'&error=4'));
        exit();
    }
}

// Si no es POST, redirigir
header("Location: listar.php?error=5");