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
  
  <!-- Google Fonts -->
  <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600;700&family=Inter:wght@400;500&display=swap" rel="stylesheet">
  <!-- Bootstrap Icons -->
  <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css" rel="stylesheet">

  <style>
    :root {
      --color-primary: #1661AC;
      --color-secondary: #61C9A8;
      --color-text: #403F48;
      --color-gray: #B1B1B1;
      --color-dark-gray: #555555;
    }

    * { margin: 0; padding: 0; box-sizing: border-box; }

    body {
      font-family: 'Inter', sans-serif;
      background: #fff;
      min-height: 100vh;
      display: flex;
      align-items: center;
      justify-content: center;
    }

    .formulario {
      width: 100%;
      max-width: 400px;
      text-align: center;
    }

    .form-title {
      font-family: 'Poppins';
      font-size: 20px;
      font-weight: 700;
      color: #1661ac;
      margin-bottom: 8px;
      font-weight: bold;
    }

    .form-subtitle {
      font-size: 14px;
      color: #555555;
      margin-bottom: 25px;
      font-family: 'Inter';
    }

    .campo {
      position: relative;
      margin-bottom: 15px;
    }

    .campo input {
      width: 100%;
      padding: 12px 16px 12px 45px;
      border: 1px solid #ddd;
      border-radius: 8px;
      font-size: 15px;
      font-family: 'Inter', sans-serif;
      height: 48px;
    }

    .campo input:focus {
      border-color: var(--color-primary);
      outline: none;
    }

    .campo .icon {
      position: absolute;
      top: 50%;
      left: 14px;
      transform: translateY(-50%);
      font-size: 18px;
      color: var(--color-gray);
    }

    .campo .toggle {
      position: absolute;
      top: 50%;
      right: 14px;
      transform: translateY(-50%);
      cursor: pointer;
      font-size: 18px;
      color: var(--color-gray);
    }

    .forgot-password {
      text-align: left;
      margin-bottom: 20px;
    }

    .forgot-password a {
      font-size: 14px;
      color: var(--color-primary);
      text-decoration: none;
    }

    .btn-ingresar {
      display: block;
      width: 50%;
      background: #61c9a8;
      color: #1b314b;
      border: none;
      border-radius: 16px;
      padding: 8px;
      font-size: 20px;
      font-weight: 600;
      cursor: pointer;
      margin-bottom: 20px;
      transition: all 0.3s ease;
      font-family: 'Poppins';
      font-weight: bold;
    }

    .btn-ingresar:hover {
      background: #61c9a8;
    }

    .divider {
      text-align: center;
      font-size: 14px;
      color: var(--color-dark-gray);
      margin: 20px 0;
      position: relative;
    }

    .divider::before,
    .divider::after {
      content: '';
      position: absolute;
      top: 50%;
      width: 40%;
      height: 1px;
      background-color: #ddd;
    }

    .divider::before { left: 0; }
    .divider::after { right: 0; }

    .social-buttons {
      display: flex;
      gap: 12px;
      justify-content: center;
      margin-bottom: 20px;
    }

    .btn-social {
      flex: 1;
      font-size: 14px;
      font-weight: 500;
      display: flex;
      align-items: center;
      justify-content: center;
      gap: 10px;
      padding: 10px;
      border-radius: 6px;
      border: 1px solid #ccc;
      background: #fff;
      color: var(--color-text);
      text-decoration: none;
      transition: all 0.3s ease;
    }

    .btn-social img {
      width: 20px;
      height: 20px;
    }

    .btn-social:hover {
      background: #f5f5f5;
    }

    .register-text {
      font-size: 14px;
      margin-top: 10px;
    }

    .register-text a {
      color: var(--color-primary);
      font-weight: 600;
      text-decoration: none;
    }

    .register-text a:hover {
      text-decoration: underline;
    }
  </style>
</head>
<body>
  <form method="POST" action="login.php" class="formulario">
    <h2 class="form-title">Inicia Sesión</h2>
    <p class="form-subtitle">Ingresa tus credenciales para acceder</p>

    <div class="campo">
      <i class="bi bi-envelope icon"></i>
      <input type="email" name="correo" placeholder="Correo electrónico">
    </div>

    <div class="campo">
      <i class="bi bi-lock icon"></i>
      <input type="password" id="contraseña" name="contraseña" placeholder="Contraseña">
      <i class="bi bi-eye-slash toggle" id="togglePassword"></i>
    </div>

    <div class="forgot-password">
      <a href="#">Olvidé mi contraseña</a>
    </div>

      <button type="submit" class="btn-ingresar">Ingresar</button>

    <div class="divider">O</div>

    <div class="social-buttons">
      <a href="#" class="btn-social">
        <img src="imagenes/google.png" alt="Google"> Ingresar con Google
      </a>
      <a href="#" class="btn-social">
        <img src="imagenes/outlook.png" alt="Outlook"> Ingresar con Outlook
      </a>
    </div>

    <div class="register-text">
      ¿No tienes una cuenta? <a href="#">Regístrate</a>
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
