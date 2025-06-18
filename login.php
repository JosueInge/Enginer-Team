<?php 
session_start();
include 'conexion.php';

$error = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $correo = $_POST['correo'] ?? '';
  $contraseña = $_POST['contraseña'] ?? '';

  // Buscar solo por correo
  $stmt = $conexion->prepare("SELECT id, contraseña, nombre, email_verificacion, rol FROM usuarios WHERE correo = ?");
  $stmt->bind_param("s", $correo);
  $stmt->execute();
  $resultado = $stmt->get_result();

  if ($resultado->num_rows === 1) {
    $usuario = $resultado->fetch_assoc();

    // Verificar si el correo está verificado
    if ($usuario['email_verificacion'] != 1) {
      $VerificarCorreo = "Debes verificar tu correo electrónico antes de iniciar sesión.";
    }
    // Verificar contraseña
    elseif (password_verify($contraseña, $usuario['contraseña'])) {
      $_SESSION['usuario_id'] = $usuario['id'];
      $_SESSION['usuario_correo'] = $correo;
      $_SESSION['usuario_nombre'] = $usuario['nombre'];
      $_SESSION['usuario_rol'] = $usuario['rol'];
      header("Location: inicio.php");
      exit();
    } else {
      $DatosIncorrectos = "Correo o contraseña incorrectas.";
    }
  } else {
    $DatosIncorrectos = "Correo o contraseña incorrectas.";
  }
}

?>

<!DOCTYPE html>
<html lang="es">
<head>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Inicio de Sesión</title>
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
      display: flex;
      align-items: center;
    }
    .logo img {
      height: 50px; 
      margin-right: 10px;
    }

    .informacion a {
      color: white;
      margin-left: 20px;
      text-decoration: none;
      font-size: 14px;
      font-family: 'Open Sans Regular';
    }

    main {
      display: flex;
      flex-direction: column;
      align-items: center;
    }

    h2 {
      margin-bottom: 10px;
      font-family: 'Open Sans Bold';
    }

    p {
      font-family: 'Open Sans Regular';
    }

    .login {
      border: 2px solid black;
      padding: 30px;
      border-radius: 10px;
      max-width: 400px;
      width: 100%;
      text-align: left;
    }

    .login label {
      font-weight: bold;
      display: block;
      margin: 15px 0 5px;
      font-family: 'Open Sans Regular';
    }

    .login input[type="email"],
    .login input[type="password"] {
      width: 100%;
      padding: 10px;
      border: 1px solid #ccc;
      border-radius: 6px;
      background-color: #d9d9d9;
    }

    .password {
      font-size: 13px;
      color: black;
      margin-top: 5px;
      display: inline-block;
      font-family: 'Open Sans Regular';
    }
    .login input[type="text"].password-visible {
      width: 100%;
      padding: 10px;
      border: 1px solid #ccc;
      border-radius: 6px;
      background-color: #d9d9d9;
    }

    .login button {
      margin-top: 50px;
      width: 100%;
      background-color: #0d5c9b;
      color: white;
      border: none;
      padding: 10px;
      border-radius: 6px;
      font-weight: bold;
      cursor: pointer;
      font-family: 'Open Sans Regular';
    }

    .registro {
      text-align: center;
      margin-top: 15px;
      font-size: 14px;
      font-family: 'Open Sans Regular';
    }

    .registro a {
      text-decoration: none;
      font-weight: bold;
      color: black;
    }
    .menu-desplegable {
      display: none;
      position: absolute;
      right: 0;
      background-color: white;
      min-width: 160px;
      box-shadow: 0 8px 16px rgba(0,0,0,0.2);
      z-index: 1001;
      border-radius: 4px;
    }
    
    .menu-desplegable a {
      color: #333;
      padding: 12px 16px;
      text-decoration: none;
      display: block;
      transition: background-color 0.3s;
    }
    
    .menu-desplegable a:hover {
      background-color: #f1f1f1;
    }
    
    .menu-configuracion:hover .menu-desplegable {
      display: block;
    }
  </style>
</head>
<body>
  <header>
    <div class="logo">
      <img src="imagenes/logo.png" alt="logo">
    </div>
    <div class="informacion">
      <a href="#">Contacto</a>
      <a href="sobrenosotros.php">Sobre Nosotros</a>
      <a href="login.php">Iniciar Sesión</a>
    </div>
  </header>
  <main>
    <h2>Inicio de Sesión</h2>
    <p>Ingresa tus credenciales para acceder</p>

    <?php if ($error): ?>
      <div style="color: red; text-align: center; margin: 15px 0;">
        <?php echo htmlspecialchars($error); ?>
      </div>
    <?php endif; ?>

    <div class="login">
      <form method="POST" action="login.php">
        <label for="correo">Correo</label>
        <input type="email" id="correo" name="correo" required>

        <label for="contraseña">Contraseña</label>
        <div style="position: relative;">
          <input type="password" id="contraseña" name="contraseña" required>
          <img src="imagenes/ojoAbierto.webp" id="togglePassword" alt="Mostrar/Ocultar" 
            style="position: absolute; top: 50%; right: 10px; transform: translateY(-50%); cursor: pointer; width: 24px;">
        </div>

        <a href="recuperar.php" class="password">Olvidé mi contraseña</a>

        <button type="submit">Ingresar</button>

       
      </form>

      <div class="registro">
        ¿No tienes una cuenta? <a href="registro.php">Regístrate</a>
      </div>
    </div>
  </main>
  <!-- Mensaje de envio de correo -->
  <?php if (isset($_SESSION['correo'])): ?>
  <div class="position-fixed bottom-0 end-0 p-3" style="z-index: 1055;">
    <div class="toast align-items-center text-bg-success border-0 show" role="alert" aria-live="assertive" aria-atomic="true">
        <div class="d-flex">
            <div class="toast-body">
                <?= htmlspecialchars($_SESSION['correo']) ?>
            </div>
            <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast" aria-label="Cerrar"></button>
        </div>
    </div>
  </div>
  <?php unset($_SESSION['correo']); ?>
  <?php endif; ?>

<!-- Mensaje a usuario no logueado que desee comentar -->
  <?php if (isset($_SESSION['comentario'])): ?>
  <div class="position-fixed bottom-0 end-0 p-3" style="z-index: 1055;">
  <div class="toast align-items-center text-bg-primary border-0 show" role="alert" aria-live="assertive" aria-atomic="true">
      <div class="d-flex">
          <div class="toast-body">
              <?= htmlspecialchars($_SESSION['comentario']) ?>
          </div>
          <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast" aria-label="Cerrar"></button>
      </div>
    </div>
  </div>
  <?php unset($_SESSION['comentario']); ?>
  <?php endif; ?>

<!-- Mensaje a usuario no logueado que desee reportar -->
  <?php if (isset($_SESSION['reportar'])): ?>
  <div class="position-fixed bottom-0 end-0 p-3" style="z-index: 1055;">
  <div class="toast align-items-center text-bg-primary border-0 show" role="alert" aria-live="assertive" aria-atomic="true">
      <div class="d-flex">
          <div class="toast-body">
              <?= htmlspecialchars($_SESSION['reportar']) ?>
          </div>
          <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast" aria-label="Cerrar"></button>
      </div>
    </div>
  </div>
  <?php unset($_SESSION['reportar']); ?>
  <?php endif; ?>

<!-- Mensaje para verificacion del registro -->
  <?php if (isset($VerificarCorreo)): ?> 
  <div class="position-fixed bottom-0 end-0 p-3" style="z-index: 1055;">
    <div class="toast align-items-center text-bg-primary border-0 show" role="alert" aria-live="assertive" aria-atomic="true">
        <div class="d-flex">
            <div class="toast-body">
                <?= htmlspecialchars($VerificarCorreo) ?>
            </div>
            <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast" aria-label="Cerrar"></button>
        </div>
    </div>
  </div>
  <?php unset($VerificarCorreo); ?>
  <?php endif; ?>
  
<!-- Mensaje de datos incorrectos -->
  <?php if (isset($DatosIncorrectos)): ?>
  <div class="position-fixed bottom-0 end-0 p-3" style="z-index: 1055;">
    <div class="toast align-items-center text-bg-danger border-0 show" role="alert" aria-live="assertive" aria-atomic="true">
        <div class="d-flex">
            <div class="toast-body">
                <?= htmlspecialchars($DatosIncorrectos) ?>
            </div>
            <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast" aria-label="Cerrar"></button>
        </div>
    </div>
  </div>
  <?php unset($DatosIncorrectos); ?>
  <?php endif; ?>

  <script>
    document.querySelector('form').addEventListener('submit', function(e) {
      if (!this.checkValidity()) {
        e.preventDefault();
        alert('Completa todos los campos');
      }
    });
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
    // Codigo que hace aparecer y desaparecer la contraseña.
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
</body>
</html>