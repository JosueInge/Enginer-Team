<?php
session_start();
include 'conexion.php';

// Verificar que el usuario haya iniciado sesión con Outlook
if (!isset($_SESSION['usuario_id']) || $_SESSION['oauth_provider'] !== 'outlook') {
    $_SESSION['comentario'] = "Para comentar una noticia, debes iniciar sesión primero con Outlook.";
    header("Location: login.php");
    exit();
}

// Verificar que se recibió el ID de la noticia
if (!isset($_GET['id'])) {
    header("Location: noticias.php");
    exit();
}

$noticia_id = intval($_GET['id']);
$usuario_id = $_SESSION['usuario_id'];

// Procesar el comentario
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!empty(trim($_POST['comentario']))) {
        $comentario = trim($_POST['comentario']);

        // Preparar la inserción
        $stmt = $conexion->prepare("INSERT INTO comentarios (usuario_id, noticia_id, comentario, fecha) VALUES (?, ?, ?, NOW())");
        $stmt->bind_param("iis", $usuario_id, $noticia_id, $comentario);

        if ($stmt->execute()) {
            $_SESSION['comentario_exito'] = "Tu comentario se ha publicado correctamente.";
        } else {
            $_SESSION['comentario_error'] = "Ocurrió un error al publicar tu comentario.";
        }

        $stmt->close();
    } else {
        $_SESSION['comentario_error'] = "El comentario no puede estar vacío.";
    }

    // Redirigir de vuelta a la noticia
    header("Location: noticia_detalle.php?id=" . $noticia_id);
    exit();
}
?>

<!-- Formulario para comentar -->
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Comentar Noticia</title>
</head>
<body>
    <h2>Comentar noticia</h2>
    <?php
    if (isset($_SESSION['comentario_error'])) {
        echo "<p style='color:red'>" . $_SESSION['comentario_error'] . "</p>";
        unset($_SESSION['comentario_error']);
    }
    if (isset($_SESSION['comentario_exito'])) {
        echo "<p style='color:green'>" . $_SESSION['comentario_exito'] . "</p>";
        unset($_SESSION['comentario_exito']);
    }
    ?>
    <form action="" method="POST">
        <textarea name="comentario" rows="5" cols="50" placeholder="Escribe tu comentario aquí..."></textarea><br>
        <button type="submit">Publicar comentario</button>
    </form>
</body>
</html>
