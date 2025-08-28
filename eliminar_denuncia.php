<?php
session_start();
include 'conexion.php';

if (!isset($_SESSION['usuario_id']) || $_SESSION['usuario_rol'] !== 'Administrador') {
    header("Location: login.php");
    exit();
}

if (isset($_GET['id'])) {
    $id_noticia = intval($_GET['id']);

    // Eliminar comentarios relacionados a la denuncia
    $stmt = $conexion->prepare("DELETE FROM comentariosdenuncias WHERE propuestas_denuncias_id = ?");
    $stmt->bind_param("i", $id_noticia);
    $stmt->execute();
    $stmt->close();

    // Eliminar reportes relacionados a la denuncia
    $stmt = $conexion->prepare("DELETE FROM reportesdenuncias WHERE propuestas_denuncias_id = ?");
    $stmt->bind_param("i", $id_noticia);
    $stmt->execute();
    $stmt->close();

    // Eliminar la denuncia
    $stmt = $conexion->prepare("DELETE FROM propuestas_denuncias WHERE id = ?");
    $stmt->bind_param("i", $id_noticia);
    $stmt->execute();
    $stmt->close();
}

header("Location: denuncia.php");
exit();
?>
