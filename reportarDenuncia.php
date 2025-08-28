<?php
session_start();
include 'conexion.php';

if (!isset($_SESSION['usuario_id'])) {
  $_SESSION['reportar'] = "Para poder reportar una denuncia, debes iniciar sesión primero.";
    header("Location: login.php");
    exit();
}

if (!isset($_GET['id'])) {
    header("Location: noticias.php");
    exit();
}

$id_noticia = intval($_GET['id']);

$stmt = $conexion->prepare("SELECT titulo, fecha FROM propuestas_denuncias WHERE id = ?");
$stmt->bind_param("i", $id_noticia);
$stmt->execute();
$resultado = $stmt->get_result();
$noticia = $resultado->fetch_assoc();
$stmt->close();

$errores = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (empty($_POST['motivo'])) {
        $errores[] = "Debes seleccionar un motivo";
    }

    if (empty($_POST['comentario'])) {
        $errores[] = "Debes agregar un comentario";
    } elseif (strlen($_POST['comentario']) < 10) {
        $errores3 = "El comentario debe tener al menos 10 caracteres";
    }

    if (empty($errores)) {
        // Verificar si ya existe un reporte del mismo usuario para esta noticia
        $verificar_stmt = $conexion->prepare("SELECT COUNT(*) AS total FROM reportesdenuncias WHERE usuario_id = ? AND propuestas_denuncias_id = ?");
        $verificar_stmt->bind_param("ii", $_SESSION['usuario_id'], $id_noticia);
        $verificar_stmt->execute();
        $verificar_result = $verificar_stmt->get_result();
        $verificar_dato = $verificar_result->fetch_assoc();
        $verificar_stmt->close();

        if ($verificar_dato['total'] > 0) {
            $errores4 = "Ya has enviado un reporte para esta denuncia.";
        } else {
            // Proceder a insertar el reporte
            $stmt = $conexion->prepare("INSERT INTO reportesdenuncias 
                                       (propuestas_denuncias_id, usuario_id, motivo, comentario, fecha_reporte) 
                                       VALUES (?, ?, ?, ?, NOW())");
            $stmt->bind_param("iiss", 
                $id_noticia,
                $_SESSION['usuario_id'],
                $_POST['motivo'],
                $_POST['comentario']
            );

            if ($stmt->execute()) {
                header("Location: reportarDenuncia.php?id=" . $id_noticia);
                exit();
            } else {
                $errores[] = "Error al guardar el reporte: " . $conexion->error;
            }
            $stmt->close();
        }
    }
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
  <meta charset="UTF-8">
  <title>Reportar Denuncia</title>
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
      height: 50px;
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
      margin-bottom: 10px;
      margin-left: 150px;
    }

    hr {
      margin-bottom: 30px;
      border: none;
      height: 2px;
      background-color: black;
      margin-left: 150px;
      margin-right: 150px;
    }

    .report-box {
      border: 2px solid black;
      border-radius: 10px;
      padding: 30px;
      display: grid;
      grid-template-columns: 1fr 1fr;
      gap: 20px;
      max-width: 900px;
      margin: 0 auto;
    }

    .report-box label {
      display: block;
      margin-bottom: 5px;
      font-weight: bold;
    }

    .report-box input,
    .report-box select,
    .report-box textarea {
      width: 100%;
      padding: 10px;
      margin-bottom: 15px;
      background-color: #e5e5e5;
      border: none;
      border-radius: 4px;
    }

    .report-box textarea {
      height: 100px;
      resize: none;
    }

    .full-width {
      grid-column: span 2;
    }

    .btn-submit {
      background-color: #0d5c9b;
      color: white;
      padding: 10px 20px;
      border: none;
      font-weight: bold;
      cursor: pointer;
      border-radius: 4px;
      justify-self: end;
      transition: background-color 0.3s;
    }

    .btn-submit:hover {
      background-color: #0a4a7a;
    }

    .error {
      color: red;
      margin-bottom: 15px;
      padding: 10px;
      background-color: #ffeeee;
      border: 1px solid #ffcccc;
      border-radius: 4px;
      grid-column: span 2;
    }
  </style>
</head>
<body>

  <header>
    <div class="logo">
      <img src="imagenes/logo.png" alt="Logo">
    </div>
    <nav>
      <a href="denuncia.php">Volver a Denuncia</a>
      <a href="logout.php">Cerrar Sesión</a>
    </nav>
  </header>

  <div class="container">
    <h2>Reportar denuncia</h2>
    <hr>
    
    <form class="report-box" method="POST" action="reportarDenuncia.php?id=<?= $id_noticia ?>">
      <?php if (!empty($errores)): ?>
        <div class="error">
          <?php foreach ($errores as $error): ?>
            <p><?= htmlspecialchars($error) ?></p>
          <?php endforeach; ?>
        </div>
      <?php endif; ?>
      
      <div>
        <label for="titulo">Título de la denuncia</label>
        <input type="text" id="titulo" value="<?= htmlspecialchars($noticia['titulo']) ?>" readonly>
      </div>

      <div>
        <label for="fecha">Fecha de publicación</label>
        <input type="text" id="fecha" value="<?= htmlspecialchars($noticia['fecha']) ?>" readonly>
      </div>

      <div>
        <label for="motivo">Motivo*</label>
        <select id="motivo" name="motivo" required>
          <option value="" disabled selected>Selecciona un motivo</option>
          <option value="Contenido falso" <?= isset($_POST['motivo']) && $_POST['motivo'] == 'Contenido falso' ? 'selected' : '' ?>>Contenido falso</option>
          <option value="Lenguaje ofensivo" <?= isset($_POST['motivo']) && $_POST['motivo'] == 'Lenguaje ofensivo' ? 'selected' : '' ?>>Lenguaje ofensivo</option>
          <option value="Plagio" <?= isset($_POST['motivo']) && $_POST['motivo'] == 'Plagio' ? 'selected' : '' ?>>Plagio</option>
          <option value="Información personal" <?= isset($_POST['motivo']) && $_POST['motivo'] == 'Información personal' ? 'selected' : '' ?>>Información personal expuesta</option>
        </select>
      </div>

      <div>
        <label for="usuario">Tu nombre</label>
        <input type="text" id="usuario" value="<?= htmlspecialchars($_SESSION['usuario_nombre'] ?? '') ?>" readonly>
      </div>

      <div class="full-width">
        <label for="comentario">Comentario*</label>
        <textarea id="comentario" name="comentario" required><?= htmlspecialchars($_POST['comentario'] ?? '') ?></textarea>
      </div>

      <div class="full-width" style="text-align: right;">
        <button type="submit" class="btn-submit">Enviar Reporte</button>
      </div>
    </form>
  </div>

  <?php if (isset($errores4)): ?>
        <div class="position-fixed bottom-0 end-0 p-3" style="z-index: 1055;">
            <div class="toast align-items-center text-bg-danger border-0 show" role="alert" aria-live="assertive" aria-atomic="true">
                <div class="d-flex">
                    <div class="toast-body">
                        <?= htmlspecialchars($errores4) ?>
                    </div>
                    <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast" aria-label="Cerrar"></button>
                </div>
            </div>
        </div>
        <?php unset($errores4); ?>
        <?php endif; ?>

        <?php if (isset($errores3)): ?>
        <div class="position-fixed bottom-0 end-0 p-3" style="z-index: 1055;">
            <div class="toast align-items-center text-bg-danger border-0 show" role="alert" aria-live="assertive" aria-atomic="true">
                <div class="d-flex">
                    <div class="toast-body">
                        <?= htmlspecialchars($errores3) ?>
                    </div>
                    <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast" aria-label="Cerrar"></button>
                </div>
            </div>
        </div>
        <?php unset($errores3); ?>
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