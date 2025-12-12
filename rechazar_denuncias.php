<?php
session_start();
include 'conexion.php';

if(!isset($_SESSION['usuario_id']) || $_SESSION['usuario_rol'] !== 'Administrador') {
    header("Location: login.php");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = $_POST['id'] ?? null;

    if ($id) {
        // Eliminar la noticia de propuestas_noticias
        $stmt = $conexion->prepare("DELETE FROM propuestas_noticias WHERE id = ?");
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $stmt->close();
    }
}

header("Location: revision_noticias.php");
exit();
?>
