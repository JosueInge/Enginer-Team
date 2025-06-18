<?php
session_start();
include 'conexion.php';

if (!isset($_SESSION['usuario_id']) || $_SESSION['usuario_rol'] !== 'Administrador') {
    header("Location: login.php");
    exit();
}

if (isset($_GET['id'])) {
    $id_noticia = intval($_GET['id']);

    // Eliminar comentarios relacionados a la noticia
    $stmt = $conexion->prepare("DELETE FROM comentarios WHERE propuestas_noticias_id = ?");
    $stmt->bind_param("i", $id_noticia);
    $stmt->execute();
    $stmt->close();

    // Eliminar reportes relacionados a la noticia
    $stmt = $conexion->prepare("DELETE FROM reportes WHERE propuestas_noticias_id = ?");
    $stmt->bind_param("i", $id_noticia);
    $stmt->execute();
    $stmt->close();

    // Eliminar la noticia
    $stmt = $conexion->prepare("DELETE FROM propuestas_noticias WHERE id = ?");
    $stmt->bind_param("i", $id_noticia);
    $stmt->execute();
    $stmt->close();
}

header("Location: noticias.php");
exit();
?>
