<?php
session_start();
include 'conexion.php';

if (!isset($_SESSION['usuario_id'])) {
    header("Location: login.php");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['eliminar_cuenta'])) {
    $stmt = $conexion->prepare("SELECT contraseña FROM usuarios WHERE id = ?");
    $stmt->bind_param("i", $_SESSION['usuario_id']);
    $stmt->execute();
    $resultado = $stmt->get_result();
    $usuario = $resultado->fetch_assoc();
    
    if ($usuario && password_verify($_POST['password_eliminar'], $usuario['contraseña'])) {
        $stmt = $conexion->prepare("DELETE FROM usuarios WHERE id = ?");
        $stmt->bind_param("i", $_SESSION['usuario_id']);
        
        if ($stmt->execute()) {
            session_destroy();
            header("Location: login.php?cuenta_eliminada=1");
            exit();
        }
    }
    
    header("Location: configurar_perfil.php?error=1");
    exit();
}

header("Location: configurar_perfil.php");
exit();
?>