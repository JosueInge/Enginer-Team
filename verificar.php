<?php
session_start();
$conexion = new mysqli("localhost", "root", "", "comunicado_digital");

if ($conexion->connect_error) {
    die("Error de conexión: " . $conexion->connect_error);
}

$mensaje = "";

if (isset($_GET['token'])) {
    $token = $_GET['token'];

    // Buscar usuario con ese token.
    $stmt = $conexion->prepare("SELECT * FROM usuarios WHERE token_verificacion = ?");
    $stmt->bind_param("s", $token);
    $stmt->execute();
    $resultado = $stmt->get_result();

    if ($resultado->num_rows > 0) {
        // Actualiza verificación del correo a 1.
        $stmt = $conexion->prepare("UPDATE usuarios SET email_verificacion = 1, token_verificacion = NULL WHERE token_verificacion = ?");
        $stmt->bind_param("s", $token);

        if ($stmt->execute()) {
            $mensaje = "¡Correo verificado exitosamente! Ahora puedes iniciar sesión.";
        } else {
            $mensaje = "Error al actualizar la verificación.";
        }
    } else {
        $mensaje = "Token no válido o ya verificado.";
    }
} else {
    $mensaje = "Token no proporcionado.";
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Verificación de Correo</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #eef2f7;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
        }
        .mensaje {
            background: white;
            padding: 30px;
            border-radius: 10px;
            box-shadow: 0 0 10px rgba(0,0,0,0.1);
            text-align: center;
        }
        .mensaje h2 {
            color: #0d5c9b;
        }
        .mensaje a {
            display: inline-block;
            margin-top: 20px;
            background: #0d5c9b;
            color: white;
            padding: 10px 20px;
            border-radius: 5px;
            text-decoration: none;
        }
        .mensaje a:hover {
            background: #004080;
        }
    </style>
</head>
<body>
    <div class="mensaje">
        <h2><?php echo $mensaje; ?></h2>
        <a href="login.php">Iniciar sesión</a>
    </div>
</body>
</html>
