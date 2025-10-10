<?php
session_start();
include 'conexion.php';

if (!isset($_SESSION['usuario_id'])) {
    header("Location: login.php");
    exit();
}

$stmt = $conexion->prepare("SELECT * FROM usuarios WHERE id = ?");
$stmt->bind_param("i", $_SESSION['usuario_id']);
$stmt->execute();
$usuario = $stmt->get_result()->fetch_assoc();
$stmt->close();

$mensaje = '';
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['username'])) {
    $nombre = trim($_POST['username']);
    $password_actual = trim($_POST['password_actual']);
    $nueva_password = trim($_POST['nueva_password']);
    
    if (empty($nombre)) {
        $error = "El nombre de usuario no puede estar vacío";
    } else {
        try {
            if (!empty($nueva_password)) {
                if (!password_verify($password_actual, $usuario['contraseña'])) {
                    $error = "La contraseña actual es incorrecta";
                } else {
                    $hash_password = password_hash($nueva_password, PASSWORD_DEFAULT);
                    $stmt = $conexion->prepare("UPDATE usuarios SET nombre = ?, contraseña = ? WHERE id = ?");
                    $stmt->bind_param("ssi", $nombre, $hash_password, $_SESSION['usuario_id']);
                }
            } else {
                $stmt = $conexion->prepare("UPDATE usuarios SET nombre = ? WHERE id = ?");
                $stmt->bind_param("si", $nombre, $_SESSION['usuario_id']);
            }
            
            if (empty($error) && $stmt->execute()) {
                $mensaje = "Perfil actualizado correctamente";
                $_SESSION['usuario_nombre'] = $nombre;
                $stmt = $conexion->prepare("SELECT * FROM usuarios WHERE id = ?");
                $stmt->bind_param("i", $_SESSION['usuario_id']);
                $stmt->execute();
                $usuario = $stmt->get_result()->fetch_assoc();
            }
        } catch (Exception $e) {
            $error = "Error al actualizar el perfil: " . $e->getMessage();
        }
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['avatar'])) {
    $nombre_archivo = uniqid() . '_' . basename($_FILES['avatar']['name']);
    $ruta_destino = "imagenes/avatars/" . $nombre_archivo;
    
    if (!file_exists('imagenes/avatars/')) {
        mkdir('imagenes/avatars/', 0777, true);
    }
    
    if (move_uploaded_file($_FILES['avatar']['tmp_name'], $ruta_destino)) {
        $stmt = $conexion->prepare("UPDATE usuarios SET avatar = ? WHERE id = ?");
        $stmt->bind_param("si", $ruta_destino, $_SESSION['usuario_id']);
        if ($stmt->execute()) {
            $usuario['avatar'] = $ruta_destino;
            $mensaje = "Foto de perfil actualizada correctamente";
        }
    } else {
        $error = "Error al subir la imagen";
    }
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Configurar Perfil - Comunicado Digital</title>
    <style>
        body {
      font-family: Arial, sans-serif;
      margin: 0;
      background-color: white;
    }

    header {
      background-color: #0d5c9b;
      color: white;
      padding: 15px 30px;
      display: flex;
      justify-content: space-between;
      align-items: center;
    }

    header .logo img {
      height: 40px;
    }

    header nav a {
      color: white;
      text-decoration: none;
      margin-left: 20px;
      font-size: 14px;
    }

    .container {
      padding: 40px;
    }

    .container h2 {
      margin-bottom: 30px;
      text-align: center;
      color: #333;
    }

    .profile-box {
      border: 1px solid #ddd;
      border-radius: 10px;
      padding: 30px;
      display: flex;
      justify-content: space-between;
      flex-wrap: wrap;
      gap: 20px;
      max-width: 800px;
      margin: 0 auto;
      box-shadow: 0 2px 10px rgba(0,0,0,0.1);
    }

    .form-left {
      flex: 1 1 300px;
    }

    .form-left label {
      display: block;
      margin-bottom: 5px;
      font-weight: bold;
      color: #555;
    }

    .form-left input {
      width: 100%;
      padding: 10px;
      margin-bottom: 15px;
      border: 1px solid #ddd;
      border-radius: 4px;
    }

    .form-left .btn-group {
      display: flex;
      gap: 10px;
      margin-bottom: 20px;
    }

    .form-left .btn-group button {
      flex: 1;
      background-color: #f0f0f0;
      border: 1px solid #ddd;
      padding: 10px;
      font-weight: bold;
      cursor: pointer;
      border-radius: 4px;
      transition: background-color 0.3s;
    }

    .form-left .btn-group button:hover {
      background-color: #e0e0e0;
    }

    .form-left .btn-submit {
      background-color: #0d5c9b;
      color: white;
      padding: 12px;
      width: 100%;
      border: none;
      font-weight: bold;
      cursor: pointer;
      border-radius: 4px;
      transition: background-color 0.3s;
    }

    .form-left .btn-submit:hover {
      background-color: #0a4a7a;
    }

    .form-right {
      flex: 1 1 200px;
      text-align: center;
    }

    .form-right img {
      width: 120px;
      height: 120px;
      background-color: #f0f0f0;
      border-radius: 50%;
      margin-bottom: 15px;
      object-fit: cover;
    }

    .form-right p {
      margin-bottom: 15px;
      color: #555;
    }

    .form-right input[type="file"] {
      display: none;
    }

    .form-right label.upload-button {
      background-color: #f0f0f0;
      padding: 10px 15px;
      display: inline-block;
      font-weight: bold;
      border-radius: 4px;
      cursor: pointer;
      border: 1px solid #ddd;
      transition: background-color 0.3s;
    }

    .form-right label.upload-button:hover {
      background-color: #e0e0e0;
    }

    .mensaje {
      padding: 15px;
      margin-bottom: 20px;
      border-radius: 4px;
      text-align: center;
    }

    .mensaje.exito {
      background-color: #dff0d8;
      color: #3c763d;
      border: 1px solid #d6e9c6;
    }

    .mensaje.error {
      background-color: #f2dede;
      color: #a94442;
      border: 1px solid #ebccd1;
    }
    #toggleActual, #toggleNueva {
      position: absolute;
      top: 40%;
      right: -5px;
      transform: translateY(-60%);
      cursor: pointer;
      width: 24px;
    }
    </style>
</head>
<body>
    <header>
        <div class="logo">
            <img src="imagenes/logo.png" alt="Logo">
        </div>
        <nav>
            <a href="inicio.php">Volver a Noticias</a>
            <a href="logout.php">Cerrar Sesión</a>
        </nav>
    </header>

    <div class="container">
        <h2>Configuración del perfil</h2>
        
        <?php if ($mensaje): ?>
            <div class="mensaje exito"><?= htmlspecialchars($mensaje) ?></div>
        <?php endif; ?>
        
        <?php if ($error): ?>
            <div class="mensaje error"><?= htmlspecialchars($error) ?></div>
        <?php endif; ?>
        
        <form class="profile-box" method="POST" enctype="multipart/form-data">
            <div class="form-left">
                <label for="username">Nombre de Usuario</label>
                <input type="text" id="username" name="username" value="<?= htmlspecialchars($usuario['nombre']) ?>" required>

                <label for="password_actual">Contraseña Actual (para cambios)</label>
                <div style="position: relative;">
                  <input type="password" id="password_actual" name="password_actual">
                  <img src="imagenes/ojoAbierto.webp" id="toggleActual" onclick="togglePasswordVisibility('password_actual', this)">
                </div>

                <label for="nueva_password">Nueva Contraseña</label>
                <div style="position: relative;">
                  <input type="password" id="nueva_password" name="nueva_password" placeholder="Dejar en blanco para no cambiar">
                  <img src="imagenes/ojoAbierto.webp" id="toggleNueva" onclick="togglePasswordVisibility('nueva_password', this)">
                </div>

                <button type="submit" class="btn-submit">Guardar Cambios</button>
            </div>

            <div class="form-right">
                <img src="<?= !empty($usuario['avatar']) ? htmlspecialchars($usuario['avatar']) : 'imagenes/avatar-default.png' ?>" alt="Foto de perfil">
                <p>Foto de perfil</p>
                <label class="upload-button" for="upload">Cambiar imagen</label>
                <input type="file" id="upload" name="avatar" accept="image/*">
            </div>
        </form>
    </div>

    <script>
        document.getElementById('upload').addEventListener('change', function(e) {
            const file = e.target.files[0];
            if (file) {
                const reader = new FileReader();
                reader.onload = function(event) {
                    document.querySelector('.form-right img').src = event.target.result;
                }
                reader.readAsDataURL(file);
            }
        });
        // Codigo para mostrar la contraseña.
        function togglePasswordVisibility(inputId, icon) {
          const input = document.getElementById(inputId);
          if (input.type === "password") {
            input.type = "text";
            icon.src = "imagenes/ojoCerrado.webp";
          } else {
            input.type = "password";
            icon.src = "imagenes/ojoAbierto.webp";
          }
        }
    </script>
</body>
</html>