<?php
include 'conexion.php';
session_start();

function guardarLog($mensaje) {
    $rutaLog = __DIR__ . '/logs/errores.log';
    $fecha = date('Y-m-d H:i:s');
    $mensajeCompleto = "[$fecha] $mensaje" . PHP_EOL;
    file_put_contents($rutaLog, $mensajeCompleto, FILE_APPEND);
}

$mensajeToast = null;
$tipoToast = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $titulo = trim($_POST['titulo'] ?? '');
    $descripcion = trim($_POST['descripcion'] ?? '');
    $usuario_id = $_SESSION['usuario_id'] ?? null;

    if ($titulo === '' || $descripcion === '') {
        guardarLog("Error: campos vacíos al enviar los datos.");
        $mensajeToast = "Debes completar todos los campos requeridos.";
        $tipoToast = "danger";
    } else {
        $imagen_nombre = null;
        if (isset($_FILES['imagen']) && $_FILES['imagen']['error'] === UPLOAD_ERR_OK) {
            $mime = mime_content_type($_FILES['imagen']['tmp_name']);
            $extension = strtolower(pathinfo($_FILES['imagen']['name'], PATHINFO_EXTENSION));
            $info = getimagesize($_FILES['imagen']['tmp_name']);

            if ($mime !== 'image/jpeg' || ($extension !== 'jpg' && $extension !== 'jpeg') || $info === false || $info['mime'] !== 'image/jpeg') {
                $mensajeToast = "Solo se permiten imágenes en formato JPEG.";
                $tipoToast = "danger";
                guardarLog("Error de validación de imagen: formato no permitido.");
                $imagen_nombre = null;
            } else {
                $imagen_nombre = uniqid() . '.jpg';
                $ruta_destino = 'imagenes/denuncias/' . $imagen_nombre;

                if (!file_exists('imagenes/denuncias')) {
                    mkdir('imagenes/denuncias', 0777, true);
                }

                if (!move_uploaded_file($_FILES['imagen']['tmp_name'], $ruta_destino)) {
                    $mensajeToast = "Error al subir la imagen.";
                    $tipoToast = "danger";
                    guardarLog("Error al mover la imagen a la carpeta destino: $ruta_destino");
                    $imagen_nombre = null;
                }
            }
        }

        if (!$mensajeToast) {
            $stmt = $conexion->prepare("INSERT INTO propuestas_denuncias_anonima (titulo, descripcion, imagen, usuario_id, estado)
                                        VALUES (?, ?, ?, ?, 'pendiente')");
            $stmt->bind_param("sssi", $titulo, $descripcion, $imagen_nombre, $usuario_id);

            if ($stmt->execute()) {
                $mensajeToast = "Denuncia enviada correctamente.";
                $tipoToast = "primary";
            } else {
                $mensajeToast = "Error al enviar la denuncia: " . $conexion->error;
                $tipoToast = "danger";
                guardarLog("Error en BD al insertar denuncia: " . $conexion->error);
            }
        }
    }
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Enviar Denuncia - Comunicado Digital</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <style>
        body { 
            font-family: Arial, sans-serif; 
            background-color: #fff; 
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
        .campo textarea {
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
        .requerido:after { 
            content: " *"; 
            color: red; 
        }
        a {
            text-decoration: none; 
        }
        .volver { 
            color: white; 
            position: relative; 
            top: -5px; 
        }
    </style>
</head>
<body>
<header>
    <div class="logo"><img src="imagenes/logo.png" alt="logo"></div>
    <nav><a href="denuncia_anonima.php" class="volver">Volver a Denuncias</a></nav>
</header>

<div class="contenedor-principal">
    <h1>Denuncia Anónima</h1>

    <form action="enviar_denuncia_anonima.php" method="POST" enctype="multipart/form-data">
        <div class="campo">
            <label for="titulo" class="requerido">Título:</label>
            <input type="text" id="titulo" name="titulo" required placeholder="Escribe el título de la noticia">
        </div> 

        <div class="campo">
            <label for="descripcion" class="requerido">Descripción:</label>
            <textarea id="descripcion" name="descripcion" required placeholder="Escribe la descripción de la noticia"></textarea>
        </div>

        <input type="hidden" name="fecha" value="<?php echo date('Y-m-d'); ?>">

        <div class="campo">
            <label for="imagen">Imagen (opcional):</label>               
            <input type="file" id="imagen" name="imagen" accept="image/jpeg">
            <img id="preview-imagen" src="#" alt="Vista previa de la imagen">
        </div>

        <button class="boton-publicar" type="submit">Enviar Denuncia</button>
    </form>
</div>

<!-- Toast dinámico desde PHP -->
<?php if ($mensajeToast): ?>
<script>
document.addEventListener('DOMContentLoaded', function () {
    mostrarToast("<?php echo htmlspecialchars($mensajeToast); ?>", "<?php echo $tipoToast; ?>");
});
</script>
<?php endif; ?>

<script>
// Función para mostrar un toast dinámico
function mostrarToast(mensaje, tipo = 'danger') {
    const toastContainer = document.createElement('div');
    toastContainer.className = 'toast align-items-center text-bg-' + tipo + ' border-0 show position-fixed bottom-0 end-0 m-3';
    toastContainer.setAttribute('role', 'alert');
    toastContainer.style.zIndex = '9999';

    toastContainer.innerHTML = `
        <div class="d-flex">
            <div class="toast-body">${mensaje}</div>
            <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast"></button>
        </div>
    `;

    document.body.appendChild(toastContainer);

    setTimeout(() => {
        toastContainer.remove();
    }, 4000);
}

document.addEventListener('DOMContentLoaded', function () {
    const fileInput = document.getElementById('imagen');
    const preview = document.getElementById('preview-imagen');

    fileInput.addEventListener('change', function () {
        const file = this.files[0];
        if (file) {
            const allowedTypes = ['image/jpeg'];
            const allowedExtensions = ['.jpg', '.jpeg'];
            const fileName = file.name.toLowerCase();

            if (!allowedTypes.includes(file.type) || !allowedExtensions.some(ext => fileName.endsWith(ext))) {
                mostrarToast('Solo se permiten imágenes en formato JPEG.', 'danger');
                this.value = '';
                preview.style.display = 'none';
                return;
            }

            const reader = new FileReader();
            reader.onload = function (event) {
                preview.src = event.target.result;
                preview.style.display = 'block';
            }
            reader.readAsDataURL(file);
        } else {
            preview.style.display = 'none';
        }
    });
});
</script>
</body>
</html>
