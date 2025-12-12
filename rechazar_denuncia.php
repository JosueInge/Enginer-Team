<?php
session_start();
include 'conexion.php';

if(!isset($_SESSION['usuario_id']) || $_SESSION['usuario_rol'] !== 'Administrador') {
    header("Location: login.php");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = $_POST['id'] ?? null;
    $tabla_origen = $_POST['tabla_origen'] ?? 'propuestas_denuncias';

    // Validar que la tabla sea una de las permitidas
    if (!in_array($tabla_origen, ['propuestas_denuncias', 'propuestas_denuncias_anonima'])) {
        $tabla_origen = 'propuestas_denuncias';
    }

    if ($id) {
        // Eliminar la denuncia de la tabla correspondiente
        $stmt = $conexion->prepare("DELETE FROM " . $tabla_origen . " WHERE id = ?");
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $stmt->close();
    }
}

header("Location: revision_denuncias.php");
exit();
?>
