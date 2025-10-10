<?php 
session_start();
include 'conexion.php';

function guardarLog($mensaje) {
    $rutaLog = __DIR__ . '/logs/errores.log';
    $fecha = date('Y-m-d H:i:s');
    $mensajeCompleto = "[$fecha] $mensaje" . PHP_EOL;
    file_put_contents($rutaLog, $mensajeCompleto, FILE_APPEND);
}

$errorCorreo = $errorContraseña = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $correo = trim($_POST['correo'] ?? '');
  $contraseña = $_POST['contraseña'] ?? '';

  if (empty($correo)) {
      $errorCorreo = "El correo electrónico es obligatorio.";
  } elseif (!filter_var($correo, FILTER_VALIDATE_EMAIL)) {
      $errorCorreo = "Formato de correo incorrecto.";
  } elseif (empty($contraseña)) {
      $errorContraseña = "La contraseña es obligatoria.";
  } else {
      $stmt = $conexion->prepare("SELECT id, contraseña, nombre, email_verificacion, rol FROM usuarios WHERE correo = ?");
      $stmt->bind_param("s", $correo);
      $stmt->execute();
      $resultado = $stmt->get_result();

      if ($resultado->num_rows === 1) {
          $usuario = $resultado->fetch_assoc();

          if ($usuario['email_verificacion'] != 1) {
              $errorCorreo = "Debes verificar tu correo electrónico antes de iniciar sesión.";
          } elseif (password_verify($contraseña, $usuario['contraseña'])) {
              $_SESSION['usuario_id'] = $usuario['id'];
              $_SESSION['usuario_correo'] = $correo;
              $_SESSION['usuario_nombre'] = $usuario['nombre'];
              $_SESSION['usuario_rol'] = $usuario['rol'];
              header("Location: inicio.php");
              exit();
          } else {
              $errorContraseña = "Correo electrónico o contraseña incorrectos.";
              guardarLog("Intento fallido de inicio de sesión: $correo");
          }
      } else {
          $errorCorreo = "Correo electrónico o contraseña incorrectos.";
      }
  }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Inicio de Sesión</title>
  <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600&family=Inter&display=swap" rel="stylesheet">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css" rel="stylesheet">
  <style>
    body {
      margin:0; padding:0;
      font-family:'Inter', sans-serif;
      background:#f9f9f9;
      display:flex; justify-content:center; align-items:center;
      height:100vh;
    }
    .formulario {
      width:100%; max-width:600px;
      background:#fff; padding:40px 35px;
      border-radius:10px;
      box-shadow:0 2px 10px rgba(0,0,0,0.1);
      text-align:center;
    }
    .formulario h2 {
      font-family:'Poppins',sans-serif;
      font-size:22px; font-weight:600;
      color:#1661AC; margin-bottom:10px;
    }
    .formulario p.sub {
      font-size:14px; color:#666; margin-bottom:25px;
    }
    /* === CAMPOS DE CORREO Y CONTRASEÑA === */
    .campo {
      position: relative;
      margin: 0 auto 20px auto;
      width: 100%;
      max-width: 550px;
    }
    .campo input {
      width: 100%;
      padding: 12px 16px 12px 48px;
      border: 1px solid #B1B1B1;
      border-radius: 8px;
      font-family: 'Inter', sans-serif;
      font-size: 16px;
      color: #403F48;
      box-sizing: border-box;
    }
    .campo input::placeholder {
      color: #B1B1B1;
    }
    .campo .icon {
      position: absolute;
      top: 50%;
      left: 12px;
      transform: translateY(-50%);
      font-size: 24px;
      color: #B1B1B1;
      pointer-events: none;
    }
    .campo .toggle {
      position: absolute;
      top: 50%;
      right: 12px;
      transform: translateY(-50%);
      font-size: 24px;
      color: #B1B1B1;
      cursor: pointer;
    }
    /* === MENSAJES DE ERROR === */
    .error-msg {
      font-size:13px; color:#c00;
      text-align:left; margin: -10px auto 10px auto;
      max-width:550px;
    }
    .forgot {
      text-align:left;
      margin:10px auto 20px auto;
      max-width:550px;
    }
    .forgot a {
      font-size:13px; color:#1661AC; text-decoration:none;
    }
    /* === BOTÓN INGRESAR === */
    .btn-ingresar {
      display:inline-block;
      background:#61C9A8;
      color:#1B314B;
      border:none;
      border-radius:16px;
      padding:0.5rem 1.5rem;
      font-family:'Poppins', sans-serif;
      font-size:20px;
      font-weight:700;
      cursor:pointer;
      transition:.3s;
      margin-top:10px;
    }
    .btn-ingresar:hover { background:#4da78b; }
    /* === DIVISOR Y REDES === */
    .divider {
      display:flex; 
      align-items:center; 
      text-align:center;
      margin:20px 0;
      font-size: 25px;
    }
    .divider::before, .divider::after {
      content:""; flex:1;
      border-bottom:1px solid #ddd;
    }
    .divider:not(:empty)::before { margin-right:.75em; }
    .divider:not(:empty)::after { margin-left:.75em; }
    .social-container {
      display:flex; justify-content:space-between; gap:10px;
      max-width:550px; margin:0 auto;
    }
    .btn-social {
      flex:1;
      display:inline-flex; align-items:center; justify-content:center;
      border:1px solid #1661AC;
      border-radius:6px; padding:8px;
      font-size:18px; text-decoration:none;
      color:#333; transition:.3s;
      background:#fff; 
    }
    .btn-social img {
      width:30px; height:30px; margin-right:6px; padding: 5px;
    }
    .btn-social:hover { background:#f5f5f5; }
    .registro-text {
      margin-top:20px; font-size:13px;
    }
    .registro-text a {
      color:#1661AC; font-weight:600;
      text-decoration:none;
    }

    /* Boton para volver a home */
    .btn-volver-home {
      position: absolute;
      top: 20px;
      left: 20px;
      background: #1661AC;
      color: white;
      border-radius: 50%;
      width: 50px;
      height: 50px;
      display: flex;
      align-items: center;
      justify-content: center;
      cursor: pointer;
      text-decoration: none;
      font-size: 24px;
      box-shadow: 0 2px 10px rgba(0,0,0,0.1);
      transition: all 0.3s ease;
      z-index: 10000;
    }

    .btn-volver-home:hover {
      background: #2D8EFF;
      transform: translateY(-2px);
      box-shadow: 0 4px 15px rgba(0,0,0,0.3);
    }

    .btn-volver-home:active {
      transform: translateY(0);
    }

   
  </style>
</head>
<body>

  <!-- Boton volver a home -->
   <a href="home.php" class="btn-volver-home" title="Volver al inicio">
    <i class="bi bi-arrow-left"></i>
  </a>

  <form method="POST" action="login.php" class="formulario">
    <h2>Inicia Sesión</h2>
    <p class="sub">Ingresa tus credenciales para acceder</p>

    <!-- CAMPO CORREO -->
    <div class="campo">
      <i class="bi bi-envelope icon"></i>
      <input type="email" name="correo" placeholder="Correo electrónico" value="<?= htmlspecialchars($_POST['correo'] ?? '') ?>">
    </div>
    <?php if ($errorCorreo): ?><div class="error-msg"><?= $errorCorreo ?></div><?php endif; ?>

    <!-- CAMPO CONTRASEÑA -->
    <div class="campo">
      <i class="bi bi-lock icon"></i>
      <input type="password" id="contraseña" name="contraseña" placeholder="Contraseña">
      <i class="bi bi-eye-slash toggle" id="togglePassword"></i>
    </div>
    <?php if ($errorContraseña): ?><div class="error-msg"><?= $errorContraseña ?></div><?php endif; ?>

    <!-- OLVIDÉ CONTRASEÑA -->
    <div class="forgot">
      <a href="recuperar.php">Olvidé mi contraseña</a>
    </div>

    <!-- BOTÓN INGRESAR -->
    <button type="submit" class="btn-ingresar">Ingresar</button>

    <!-- DIVISOR -->
    <div class="divider">o</div>

    <!-- BOTONES SOCIALES -->
    <div class="social-container">
      <a href="google_login.php" class="btn-social">
        <img src="imagenes/google.png" alt="Google"> Ingresa con Google
      </a>
      <a href="outlook_login.php" class="btn-social">
        <img src="imagenes/outlook.png" alt="Outlook"> Ingresa con Outlook
      </a>
    </div>

    <!-- REGISTRO -->
    <div class="registro-text">
      ¿No tienes una cuenta? <a href="registro.php">Regístrate</a>
    </div>


  </form>

  <script>
    const togglePassword = document.getElementById('togglePassword');
    const passwordInput = document.getElementById('contraseña');
    togglePassword.addEventListener('click', () => {
      const type = passwordInput.type === 'password' ? 'text' : 'password';
      passwordInput.type = type;
      togglePassword.classList.toggle('bi-eye');
      togglePassword.classList.toggle('bi-eye-slash');
    });
  </script>

</body>
</html>
