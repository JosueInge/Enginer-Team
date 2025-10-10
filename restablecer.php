<?php
$conexion = new mysqli("localhost", "root", "", "comunicado_digital");
if ($conexion->connect_error) {
    die("Error de conexión: " . $conexion->connect_error);
}

$mensaje = "";
$token = $_GET['token'] ?? '';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $token = $_POST['token'];
    $nuevaContrasena = $_POST['nueva_contrasena'];

    $stmt = $conexion->prepare("SELECT id_usuario, expiracion FROM tokens_recuperacion WHERE token = ?");
    $stmt->bind_param("s", $token);
    $stmt->execute();
    $resultado = $stmt->get_result();

    if ($resultado->num_rows > 0) {
        $fila = $resultado->fetch_assoc();
        $id_usuario = $fila['id_usuario'];
        $expiracion = $fila['expiracion'];

        if (strtotime($expiracion) > time()) {
            // Token válido y no expirado → actualizar contraseña
            $hash = password_hash($nuevaContrasena, PASSWORD_DEFAULT);

            $conexion->query("UPDATE usuarios SET contraseña = '$hash' WHERE id = '$id_usuario'");
            $conexion->query("DELETE FROM tokens_recuperacion WHERE token = '$token'");

            $mensaje = "¡Tu contraseña ha sido actualizada exitosamente! <a href='login.php'>Inicia sesión</a>";
        } else {
            $mensaje = "Este enlace ha expirado.";
        }
    } else {
        $mensaje = "Token inválido o ya usado.";
    }
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Restablecer Contraseña</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background: #f2f2f2;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 90vh;
        }

        .form-box {
            background: white;
            padding: 30px;
            border-radius: 10px;
            box-shadow: 0px 0px 10px rgba(0,0,0,0.1);
            width: 350px;
        }

        .form-box h2 {
            margin-bottom: 20px;
            color: #333;
        }

        .form-box input[type="password"] {
            width: 100%;
            padding: 10px;
            margin: 15px 0;
            border: 1px solid #ccc;
            border-radius: 5px;
            margin-left: -12px;
        }

        .form-box button {
            width: 100%;
            padding: 10px;
            background-color: #0056a1;
            color: white;
            border: none;
            border-radius: 5px;
            cursor: pointer;
        }

        .form-box button:hover {
            background-color: #004080;
        }

        .mensaje {
            margin-top: 15px;
            color: red;
        }
        .form-box input[type="text"].password-visible {
            width: 100%;
            padding: 10px;
            margin: 15px 0;
            border: 1px solid #ccc;
            border-radius: 5px;
            margin-left: -12px;
        }
    </style>
</head>
<body>
<div class="form-box">
    <?php if ($token && !$mensaje): ?>
        <h2>Escribe tu nueva contraseña</h2> 
        <form method="POST" action="">
        <input type="hidden" name="token" value="<?php echo htmlspecialchars($token); ?>">
        <div style="position: relative;">
          <input type="password" id="contraseña" name="nueva_contrasena" placeholder="Nueva contraseña" required>
            <img src="imagenes/ojoAbierto.webp" id="togglePassword" alt="Mostrar/Ocultar" 
            style="position: absolute; top: 50%; right: 1px; transform: translateY(-50%); cursor: pointer; width: 24px;">
        </div>
            <button type="submit">Actualizar contraseña</button>
        </form>
    <?php else: ?>
        <p class="mensaje"><?php echo $mensaje; ?></p>
    <?php endif; ?>

    <script>
        const togglePassword = document.getElementById('togglePassword');
    const passwordInput = document.getElementById('contraseña');

    togglePassword.addEventListener('click', function () {
      const isPassword = passwordInput.type === 'password';
      passwordInput.type = isPassword ? 'text' : 'password';

      if (isPassword) {
        passwordInput.classList.add('password-visible');
          this.src = 'imagenes/ojoCerrado.webp';
          this.alt = 'Ocultar contraseña';
      } else {
        passwordInput.classList.remove('password-visible');
        this.src = 'imagenes/ojoAbierto.webp';
        this.alt = 'Mostrar contraseña';
      }
    });
    </script>
</div>
</body>
</html>
