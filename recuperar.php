<?php
// librerias PHPMailer
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require 'PHPMailer/PHPMailer.php';
require 'PHPMailer/SMTP.php';
require 'PHPMailer/Exception.php';

$conexion = new mysqli("localhost", "root", "", "comunicado_digital");
if ($conexion->connect_error) {
    die("Error de conexión: " . $conexion->connect_error);
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $correo = $conexion->real_escape_string($_POST['correo']);

    $consulta = "SELECT id FROM usuarios WHERE correo='$correo'";
    $resultado = $conexion->query($consulta);

    if ($resultado->num_rows > 0) {
        $fila = $resultado->fetch_assoc();
        $id_usuario = $fila['id'];
        $token = bin2hex(random_bytes(32)); // Token seguro
        $expiracion = date("Y-m-d H:i:s", strtotime("+1 hour"));

        // Guardar token en tabla tokens_recuperacion
        $conexion->query("INSERT INTO tokens_recuperacion (id_usuario, token, expiracion) 
                          VALUES ('$id_usuario', '$token', '$expiracion')");

        // Enviar el link de recuperación
        $mail = new PHPMailer(true);
        $mail->CharSet = 'UTF-8';

        try {
            $mail->isSMTP();
            $mail->Host       = 'smtp.gmail.com';
            $mail->SMTPAuth   = true;
            $mail->Username   = 'd4660140@gmail.com'; // Tu correo
            $mail->Password   = 'emar lypn ivdw bcwn';   // Contraseña de aplicación de Gmail
            $mail->SMTPSecure = 'tls';
            $mail->Port       = 587;

            $mail->setFrom('TUCORREO@gmail.com', 'Comunicado Digital');
            $mail->addAddress($correo);

            $link = "http://192.168.1.9:8080/Engine-Team/restablecer.php?token=$token"; // Ajusta tu URL

            $mail->isHTML(true);
            $mail->Subject = 'Recuperación de contraseña';
            $mail->Body    = "Hola, haz clic en el siguiente enlace para restablecer tu contraseña:<br><br>
                             <a href='$link'>$link</a><br><br>
                             Este enlace expirará en 1 hora.";

            $mail->send();
            $recuperar = "Te hemos enviado un correo con instrucciones para restablecer tu contraseña.";
        } catch (Exception $e) { 
            $recuperar = "Error al enviar el correo: {$mail->ErrorInfo}";
        }
    } else {
        $correonoencontrado = "Correo no encontrado. Verifica e intenta de nuevo.";
    }
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
  <style>
    body {
      margin: 0;
      font-family: Arial, sans-serif;
      background-color: #fff;
    }

    header {
      background-color: #0d5c9b;
      color: white;
      padding: 15px 30px;
      display: flex;
      justify-content: space-between;
      align-items: center;
    }

    .logo {
      font-size: 20px;
      font-weight: bold;
    }
    .logo img {
      height: 50px;
      margin-right: 10px;
    }

    .redes {
      margin-right: 250px;
    }
    a {
        text-decoration: none;
    }
    .contenedor {
      height: 75vh;
      display: flex;
      justify-content: center;
      align-items: center;
    }
    .formulario-recuperar {
      border: 3px solid black;
      padding: 40px;
      border-radius: 10px;
      text-align: center;
      max-width: 500px;
      width: 100%;
    }

    .formulario-recuperar h2 {
      color: #0056a1;
      margin-bottom: 10px;
      font-family: 'Open Sans Bold';
    }

    .formulario-recuperar p {
      font-size: 14px;
      color: #555;
      margin-bottom: 20px;
      font-family: 'Open Sans Regular';
    }

    .formulario-recuperar label {
      display: block;
      font-weight: bold;
      text-align: left;
      margin-bottom: 5px;
      font-family: 'Open Sans Bold';
    }

    .formulario-recuperar input[type="email"] {
      width: 100%;
      padding: 10px;
      margin-bottom: 20px;
      border: 1px solid #ccc;
      border-radius: 5px;
      font-size: 14px;
      font-family: 'Open Sans Regular';
    }

    .formulario-recuperar button {
      width: 100%;
      padding: 10px;
      background-color: #0056a1;
      color: white;
      border: none;
      border-radius: 5px;
      font-size: 16px;
      cursor: pointer;
      font-family: 'Open Sans Regular';
    }

    .formulario-recuperar button:hover {
      background-color: #004080;
    }

    .formulario-recuperar .enlace-login {
      display: block;
      margin-top: 15px;
      font-size: 14px;
      color: #0056a1;
      text-decoration: none;
      font-family: 'Open Sans Regular';
    }
  </style>
</head>
<body>
<header>
<div class="logo">
        <img src="imagenes/logo.png" alt="logo">
      </div>
      <div class="redes">
        <a target="_blank" href="https://www.instagram.com/"><img src="imagenes/instagram.png" height="35"/></a>
        <a target="_blank" href="https://www.facebook.com/"><img src="imagenes/facebook.png" height="35" style="margin-left: 12px;"/></a>
        <a target="_blank" href="https://x.com/?lang=es"><img src="imagenes/X.png" height="35" style="margin-left: 10px;"/></a>
     </div>
</header>

<div class="contenedor">
    <div class="formulario-recuperar">
        <h2>Recupera tu contraseña</h2>
        <p>Ingresa el correo del usuario registrado para recuperar la contraseña</p>
        <form method="POST" action="">
            <label for="correo">Correo Electrónico</label>
            <input type="email" name="correo" placeholder="tu@correo.com" required>
            <button type="submit">Enviar</button>
            <a class="enlace-login" href="login.php">¿Recordaste tu contraseña? Inicia Sesión</a>
        </form>
    </div>
</div>
    <?php if (isset($recuperar)): ?>
  <div class="position-fixed bottom-0 end-0 p-3" style="z-index: 1055;">
    <div class="toast align-items-center text-bg-primary border-0 show" role="alert" aria-live="assertive" aria-atomic="true">
        <div class="d-flex">
            <div class="toast-body">
                <?= htmlspecialchars($recuperar) ?>
            </div>
            <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast" aria-label="Cerrar"></button>
        </div>
    </div>
  </div>
  <?php unset($recuperar); ?>
  <?php endif; ?>

  <?php if (isset($correonoencontrado)): ?>
  <div class="position-fixed bottom-0 end-0 p-3" style="z-index: 1055;">
    <div class="toast align-items-center text-bg-danger border-0 show" role="alert" aria-live="assertive" aria-atomic="true">
        <div class="d-flex">
            <div class="toast-body">
                <?= htmlspecialchars($correonoencontrado) ?>
            </div>
            <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast" aria-label="Cerrar"></button>
        </div>
    </div>
  </div>
  <?php unset($correonoencontrado); ?>
  <?php endif; ?>

   <script>
    document.addEventListener('DOMContentLoaded', function () {
        const toastEl = document.querySelector('.toast');
        if (toastEl) {
            const bsToast = new bootstrap.Toast(toastEl, {
                autohide: true,
                delay: 7000
            });
            bsToast.show();
        }
    });
  </script>
</body>
</html>
