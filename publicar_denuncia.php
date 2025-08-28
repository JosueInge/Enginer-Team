<?php
session_start();
include 'conexion.php';

if (!isset($_SESSION['usuario_id'])) {
    header("Location: login.php");
    exit();
}

$mensaje = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $titulo = $_POST['titulo'] ?? '';
    $descripcion = $_POST['descripcion'] ?? '';
    $fecha = date('Y-m-d H:i:s');
    $usuario_id = $_SESSION['usuario_id'] ?? null;

    $imagen_nombre = null;
    if (isset($_FILES['imagen']) && $_FILES['imagen']['error'] === UPLOAD_ERR_OK) {
        $extension = pathinfo($_FILES['imagen']['name'], PATHINFO_EXTENSION);
        $imagen_nombre = uniqid() . '.' . $extension;
        $ruta_destino = 'imagenes/denuncias/' . $imagen_nombre;

        // Crear la carpeta si no existe
        if (!file_exists('imagenes/denuncias')) {
            mkdir('imagenes/denuncias', 0777, true);
        }

        // Mover la imagen al destino
        if (!move_uploaded_file($_FILES['imagen']['tmp_name'], $ruta_destino)) {
            $errorimagen = "Error al subir la imagen.";
            $imagen_nombre = null;
        }
    }

    $stmt = $conexion->prepare("INSERT INTO propuestas_denuncias ( titulo, descripcion, imagen, fecha, usuario_id, estado)
                                VALUES (?, ?, ?, ?, ?, 'aprobada')");
    $stmt->bind_param("ssssi", $titulo, $descripcion, $imagen_nombre, $fecha, $usuario_id);

    if ($stmt->execute()) {
        $envioexitoso = "Denuncia enviada correctamente.";
    } else {
        $errorenviar = "Error al enviar la denuncia: " . $conexion->error;
    }
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Publicar Denuncia - Comunicado Digital</title>
    <style>
        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }
        body {
            font-family: Arial, sans-serif;
            background-color: #fff;
            margin: 0;
        }
        header {
            background-color: #0d5c9b;
            color: white;
            padding: 10px 20px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        .logo img {
            height: 50px;
        }
        .contenedor-principal {
            max-width: 800px;
            margin: 30px auto;
            padding: 0 20px;
        }
        h1 {
            color: #0d5c9b;
            margin-bottom: 20px;
        }
        .campo {
            margin-bottom: 20px;
        }
        .campo label {
            display: block;
            margin-bottom: 5px;
            font-weight: bold;
        }
        .campo input[type="text"],
        .campo input[type="date"],
        .campo textarea,
        .campo select {
            width: 100%;
            padding: 10px;
            border: 1px solid #ccc;
            border-radius: 4px;
        }
        .campo textarea {
            min-height: 150px;
        }
        .boton-publicar {
            background-color: #0d5c9b;
            color: white;
            border: none;
            padding: 12px 20px;
            border-radius: 4px;
            cursor: pointer;
            font-size: 16px;
            width: 100%;
        }
        .boton-publicar:hover {
            background-color: #0a4a7a;
        }
        #preview-imagen {
            max-width: 100%;
            max-height: 200px;
            margin-top: 10px;
            display: none;
        }
        .error {
            color: red;
            margin-bottom: 15px;
            padding: 10px;
            background-color: #ffeeee;
            border: 1px solid #ffcccc;
            border-radius: 4px;
        }
        .requerido:after {
            content: " *";
            color: red;
        }
        .menu-configuracion { 
      position: relative;
      display: inline-block;
      margin-left: 15px;
    }
    
    .icono-configuracion {
      width: 30px;
      height: 30px;
      cursor: pointer;
      transition: transform 0.3s;
    }
    
    .icono-configuracion:hover {
      transform: rotate(30deg);
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
    a {
        text-decoration: none;
    }
    .volver {
        color: white;
        position: relative;
        top: -1px;
    }
    </style>
</head>
<body>
<header>
        <div class="logo">
            <img src="imagenes/logo.png" alt="logo">
        </div>
        <nav class="informacion">
            <a href="denuncia.php" class="volver" >Volver a Denuncias</a>
            <div class="menu-configuracion">
                <img src="imagenes/configurar.png" class="icono-configuracion" alt="Configuración">
                <div class="menu-desplegable">
                    <a href="actualizar_perfil.php">Configurar Perfil</a>
                    <a href="logout.php">Cerrar Sesión</a>
                </div>
            </div>
        </nav>
    </header>

    <div class="contenedor-principal">
        <h1>Publicar Denuncia</h1>

        <?php if ($mensaje): ?>
            <div class="error"><?php echo htmlspecialchars($mensaje); ?></div>
        <?php endif; ?>

        <form action="publicar_denuncia.php" method="POST" enctype="multipart/form-data">
            <div class="campo">
                <label for="titulo" class="requerido">Titulo:</label>
                <input type="text" id="titulo" name="titulo" required placeholder="Escribe el titulo de la noticia">
            </div> 

            <div class="campo">
                <label for="descripcion" class="requerido">Descripcion:</label>
                <textarea id="descripcion" name="descripcion" required placeholder="Escribe la descripcion de la noticia"></textarea>
            </div>

            <div class="campo">
                <label for="fecha" class="requerido">Fecha:</label>
                <input type="date" id="fecha" name="fecha" required value="<?php echo date('Y-m-d'); ?>">
            </div> 

            <div class="campo">
                <label for="imagen">Imagen (opcional):</label>               
                <input type="file" id="imagen" name="imagen" accept="image/*">
                <img id="preview-imagen" src="#" alt="Vista previa de la imagen">
            </div>
        
            <button class="boton-publicar" type="submit">Enviar Denuncia</button>
        </form>
    </div>
       <!-- Mensaje de exito al enviar denuncia -->
    <?php if (isset($envioexitoso)): ?> 
  <div class="position-fixed bottom-0 end-0 p-3" style="z-index: 1055;">
    <div class="toast align-items-center text-bg-primary border-0 show" role="alert" aria-live="assertive" aria-atomic="true">
        <div class="d-flex">
            <div class="toast-body">
                <?= htmlspecialchars($envioexitoso) ?>
            </div>
            <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast" aria-label="Cerrar"></button>
        </div>
    </div>
  </div>
  <?php unset($envioexitoso); ?>
  <?php endif; ?>
        <!-- error al subir la imagen -->
  <?php if (isset($errorimagen)): ?> 
  <div class="position-fixed bottom-0 end-0 p-3" style="z-index: 1055;">
    <div class="toast align-items-center text-bg-primary border-0 show" role="alert" aria-live="assertive" aria-atomic="true">
        <div class="d-flex">
            <div class="toast-body">
                <?= htmlspecialchars($errorimagen) ?>
            </div>
            <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast" aria-label="Cerrar"></button>
        </div>
    </div>
  </div>
  <?php unset($errorimagen); ?>
  <?php endif; ?>

          <!--error al enviar-->
  <?php if (isset($errorenviar)): ?> 
  <div class="position-fixed bottom-0 end-0 p-3" style="z-index: 1055;">
    <div class="toast align-items-center text-bg-primary border-0 show" role="alert" aria-live="assertive" aria-atomic="true">
        <div class="d-flex">
            <div class="toast-body">
                <?= htmlspecialchars($errorenviar) ?>
            </div>
            <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast" aria-label="Cerrar"></button>
        </div>
    </div>
  </div>
  <?php unset($errorenviar); ?>
  <?php endif; ?>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const today = new Date().toISOString().split('T')[0];
            document.getElementById('fecha').value = today;
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

        document.getElementById('imagen').addEventListener('change', function(e) {
            const file = e.target.files[0];
            if (file) {
                const reader = new FileReader();
                reader.onload = function(event) {
                    const preview = document.getElementById('preview-imagen');
                    preview.src = event.target.result;
                    preview.style.display = 'block';
                }
                reader.readAsDataURL(file);
            }
        });
    });
    </script>  
</body>
</html>