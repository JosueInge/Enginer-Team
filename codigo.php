<?php
session_start();
include 'conexion.php';

if (!isset($_SESSION['usuario_id']) || $_SESSION['usuario_rol'] !== 'Administrador') {
    header("Location: noticias.php");
    exit();
}

if (isset($_GET['id'])) {
    $id_noticia = intval($_GET['id']);

    // Eliminar comentarios relacionados
    $stmt = $conexion->prepare("DELETE FROM comentarios WHERE noticia_id = ?");
    $stmt->bind_param("i", $id_noticia);
    $stmt->execute();
    $stmt->close();

    // Luego eliminar la noticia
    $stmt = $conexion->prepare("DELETE FROM noticias WHERE id = ?");
    $stmt->bind_param("i", $id_noticia);
    $stmt->execute();
    $stmt->close();
}

header("Location: noticias.php");
exit();
