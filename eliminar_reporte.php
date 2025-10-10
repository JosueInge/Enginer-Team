<?php
session_start();
include 'conexion.php';

if (!isset($_SESSION['usuario_id']) || $_SESSION['usuario_rol'] !== 'Administrador') {
    header("Location: login.php");
    exit();
}

if (isset($_GET['id'])) {
    $id_reporte = intval($_GET['id']);

    $stmt = $conexion->prepare("SELECT propuestas_noticias_id FROM reportes WHERE id = ?");
    $stmt->bind_param("i", $id_reporte);
    $stmt->execute();
    $stmt->bind_result($id_noticia);
    if ($stmt->fetch()) {
        $stmt->close();

        $stmt = $conexion->prepare("DELETE FROM comentarios WHERE propuestas_noticias_id = ?");
        $stmt->bind_param("i", $id_noticia);
        $stmt->execute();
        $stmt->close();

        $stmt = $conexion->prepare("DELETE FROM reportes WHERE propuestas_noticias_id = ?");
        $stmt->bind_param("i", $id_noticia);
        $stmt->execute();
        $stmt->close();

        $stmt = $conexion->prepare("DELETE FROM propuestas_noticias WHERE id = ?");
        $stmt->bind_param("i", $id_noticia);
        $stmt->execute();
        $stmt->close();
    } else {
        
        $stmt->close();
    }
}

header("Location: revision_reportes.php");
exit();