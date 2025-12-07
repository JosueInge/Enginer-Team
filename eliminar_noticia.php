<?php
session_start();
include 'conexion.php';

// Crear archivo de log en el proyecto
$log_file = __DIR__ . '/debug_eliminar.log';
function escribir_log($mensaje) {
    global $log_file;
    $timestamp = date('Y-m-d H:i:s');
    file_put_contents($log_file, "[$timestamp] $mensaje\n", FILE_APPEND);
}

if (!isset($_SESSION['usuario_id']) || $_SESSION['usuario_rol'] !== 'Administrador') {
    escribir_log("ERROR: Usuario no autenticado o no es administrador");
    header("Location: login.php");
    exit();
}

if (isset($_GET['id']) && isset($_GET['fuente'])) {
    $id_noticia = intval($_GET['id']);
    $fuente = trim($_GET['fuente']);
    
    // Log inicial para verificar que el script se ejecuta
    escribir_log("=== INICIANDO ELIMINACIÓN ===");
    escribir_log("ID: $id_noticia | Fuente: $fuente");

    try {
        // Eliminar comentarios relacionados a la noticia
        $stmt = $conexion->prepare("DELETE FROM comentarios WHERE propuestas_noticias_id = ?");
        if ($stmt) {
            $stmt->bind_param("i", $id_noticia);
            if ($stmt->execute()) {
                escribir_log("✓ Comentarios eliminados - Filas afectadas: " . $stmt->affected_rows);
            }
            $stmt->close();
        } else {
            escribir_log("✗ Error preparando DELETE comentarios: " . $conexion->error);
        }

        // Eliminar reportes relacionados a la noticia
        $stmt = $conexion->prepare("DELETE FROM reportes WHERE propuestas_noticias_id = ?");
        if ($stmt) {
            $stmt->bind_param("i", $id_noticia);
            if ($stmt->execute()) {
                escribir_log("✓ Reportes eliminados - Filas afectadas: " . $stmt->affected_rows);
            }
            $stmt->close();
        } else {
            escribir_log("✗ Error preparando DELETE reportes: " . $conexion->error);
        }

        // Eliminar la noticia de la tabla correcta según la fuente
        if ($fuente === 'propuestas') {
            $sql_delete = "DELETE FROM propuestas_noticias WHERE id = ?";
            $tabla = "propuestas_noticias";
        } else {
            $sql_delete = "DELETE FROM noticias WHERE id = ?";
            $tabla = "noticias";
        }
        
        escribir_log("Ejecutando DELETE en tabla: $tabla");
        $stmt = $conexion->prepare($sql_delete);
        
        if ($stmt) {
            $stmt->bind_param("i", $id_noticia);
            if ($stmt->execute()) {
                $filas_afectadas = $stmt->affected_rows;
                escribir_log("✓ NOTICIA ELIMINADA de $tabla - Filas afectadas: $filas_afectadas");
                if ($filas_afectadas == 0) {
                    escribir_log("⚠ ADVERTENCIA: Se ejecutó DELETE pero no se eliminó ninguna fila. ¿Existe el ID $id_noticia en $tabla?");
                }
            } else {
                escribir_log("✗ Error ejecutando DELETE: " . $stmt->error);
            }
            $stmt->close();
        } else {
            escribir_log("✗ Error preparando DELETE noticia: " . $conexion->error);
        }
    } catch (Exception $e) {
        escribir_log("✗ Excepción capturada: " . $e->getMessage());
    }
} else {
    escribir_log("✗ Parámetros GET faltantes - ID: " . (isset($_GET['id']) ? $_GET['id'] : 'NO') . " | Fuente: " . (isset($_GET['fuente']) ? $_GET['fuente'] : 'NO'));
}

escribir_log("=== FIN ELIMINACIÓN ===");

// Redirigir a noticias.php después de la eliminación
header("Location: noticias.php");
exit();
?>
