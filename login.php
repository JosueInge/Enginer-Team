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
  <!-- Fuentes -->
  <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600&family=Inter&display=swap" rel="stylesheet">
  <!-- Bootstrap + Icons -->
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css" rel="stylesheet">
  <style>
    body { margin:0; background:#fff; font-family:'Inter', sans-serif; }
    header {
      background:#1B314B;
      padding:15px 30px;
      display:flex;
      justify-content:space-between;
      align-items:center;
    }
    header img { height:50px; }
    nav a {
      color:#fff; font-family:'Poppins',sans-serif;
      font-size:14px; margin-left:15px;
      text-decoration:none; transition:.3s;
    }
    nav a:hover { color:#DDDDDD; text-decoration:underline; }
    .menu-toggle { display:none; font-size:24px; color:#fff; cursor:pointer; }
    @media(max-width:768px){
      nav { display:none; flex-direction:column; background:#1B314B; position:absolute; right:0; top:70px; padding:10px; border-radius:6px; }
      nav.show { display:flex; }
      .menu-toggle { display:block; }
    }
    .formulario {
      max-width:550px; margin:40px auto; padding:30px;
      border-radius:12px; box-shadow:0 4px 12px rgba(0,0,0,.1);
      background:#fff;
    }
    .formulario h2 {
      font-family:'Poppins',sans-serif; font-size:20px;
      font-weight:600; color:#1661AC; text-align:center;
    }
    .formulario p.sub {
      font-family:'Inter',sans-serif; font-size:14px;
      color:#555; text-align:center; margin-bottom:25px;
    }
    .campo { position:relative; margin-bottom:20px; }
    .campo input {
      width:100%; padding:12px 16px 12px 45px;
      border:1px solid #B1B1B1; border-radius:8px;
      font-size:16px; color:#403F48; font-family:'Inter',sans-serif;
      transition:.3s;
    }
    .campo input:focus {
      border-color:#1661AC; transform:scale(1.01);
      box-shadow:0 0 6px rgba(22,97,172,.3);
    }
    .campo .icon {
      position:absolute; top:50%; left:12px;
      transform:translateY(-50%); color:#B1B1B1; font-size:20px;
    }
    .campo .toggle {
      position:absolute; top:50%; right:12px;
      transform:translateY(-50%); cursor:pointer;
      color:#B1B1B1; font-size:20px;
    }
    .error-msg {
      font-family:'Poppins',sans-serif; font-size:16px;
      color:#1B314B; text-align:center; margin-top:5px;
      animation:shake .3s;
    }
    @keyframes shake {
      0%,100%{transform:translateX(0);}
      25%{transform:translateX(-5px);}
      75%{transform:translateX(5px);}
    }
    .btn-ingresar {
      display:block; width:100%; background:#61C9A8; color:#1B314B;
      border:none; border-radius:16px; padding:.5rem 1.5rem;
      font-size:20px; font-weight:600; font-family:'Poppins',sans-serif;
      cursor:pointer; transition:.3s; position:relative; overflow:hidden;
    }
    .btn-ingresar:hover { box-shadow:0 4px 10px rgba(0,0,0,.2); }
    .divider { 
      text-align:center; font-family:'Inter',sans-serif; 
      font-size:18px; font-weight:600; color:#403F48; margin:20px 0; 
    }
    .registro-text {
      font-size:18px; font-weight:600; font-family:'Poppins',sans-serif;
    }
    /* BOTONES SOCIALES */
    .btn-social {
      font-family:'Inter', sans-serif;
      font-size:20px;
      display:flex;
      align-items:center;
      gap:10px;
      justify-content:flex-start;
      width:100%;
      padding:12px 16px;
      border-radius:8px;
      border:1px solid #1661AC;
      background:#fff;
      color:#403F48;
      text-decoration:none;
      transition:.3s;
      margin-bottom:10px;
      position:relative;
      overflow:hidden;
    }
    .btn-social img {
      width:24px; height:24px;
    }
    .btn-social:hover {
      box-shadow:0 4px 10px rgba(0,0,0,0.1);
      transform:translateY(-1px);
    }
    .btn-social:active::after {
      content:"";
      position:absolute;
      width:100%; height:100%;
      background:rgba(22,97,172,0.15);
      top:0; left:0;
      animation:ripple .4s linear;
    }
    @keyframes ripple {
      from { opacity:1; transform:scale(0); }
      to { opacity:0; transform:scale(2.5); }
    }
  </style>
</head>
<body>
  
  <header>
    <img src="imagenes/logo.png" alt="logo">
    <div class="menu-toggle" onclick="document.querySelector('nav').classList.toggle('show')"><i class="bi bi-list"></i></div>
    <nav>
      <a href="#">Contacto</a>
      <a href="sobrenosotros.php">Sobre nosotros</a>
      <a href="login.php">Inicio de sesión</a>
    </nav>
  </header>

  <form method="POST" action="login.php" class="formulario">
    <h2>Inicia Sesión</h2>
    <p class="sub">Ingresa tus credenciales para acceder</p>

    <div class="campo">
      <i class="bi bi-envelope icon"></i>
      <input type="email" name="correo" placeholder="Correo electrónico" value="<?= htmlspecialchars($_POST['correo'] ?? '') ?>">
    </div>
    <?php if ($errorCorreo): ?><div class="error-msg"><?= $errorCorreo ?></div><?php endif; ?>

    <div class="campo">
      <i class="bi bi-lock icon"></i>
      <input type="password" id="contraseña" name="contraseña" placeholder="Contraseña">
      <i class="bi bi-eye-slash toggle" id="togglePassword"></i>
    </div>
    <?php if ($errorContraseña): ?><div class="error-msg"><?= $errorContraseña ?></div><?php endif; ?>

    <div style="text-align:right; margin-bottom:15px;">
      <a href="recuperar.php" style="font-size:13px; color:#1661AC; text-decoration:none;">Olvidé mi contraseña</a>
    </div>

    <button type="submit" class="btn-ingresar">Ingresar</button>

    <div class="divider">O</div>

    <!-- Botón Google -->
    <a href="google_login.php" class="btn-social">
      <img src="imagenes/google.png" alt="Google"> Ingresar con Google
    </a>

    <!-- Botón Outlook -->
    <a href="outlook_login.php" class="btn-social">
      <img src="imagenes/outlook.png" alt="Outlook"> Ingresar con Outlook
    </a>

    <div style="text-align:center; margin-top:20px;" class="registro-text">
      ¿No tienes una cuenta? <a href="registro.php" style="color:#1661AC; font-weight:700;">Regístrate</a>
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