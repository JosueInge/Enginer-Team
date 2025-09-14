<?php 
session_start();
include 'conexion.php';

if (!isset($_SESSION['usuario_id'])) {
    header("Location: login.php");
    exit();
}

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

    if ($titulo === '' || $descripcion === '') {
        guardarLog("Error Denuncia: campos vacíos al enviar los datos.");
        $mensajeToast = "Debes completar todos los campos requeridos.";
        $tipoToast = "danger";
    } elseif (strlen($titulo) < 4 || strlen($descripcion) < 4) {
        guardarLog("Error Denuncia: campos con menos de 4 caracteres.");
        $mensajeToast = "Título y descripción deben tener al menos 4 caracteres.";
        $tipoToast = "danger";
    } elseif (strlen($titulo) > 255 || strlen($descripcion) > 255) {
        guardarLog("Error Denuncia: campos exceden longitud permitida.");
        $mensajeToast = "Título y descripción no deben exceder 255 caracteres.";
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
                guardarLog("Error Denuncia: de validación de imagen: formato no permitido.");
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
                    guardarLog("Error Denuncia: al mover la imagen a la carpeta destino: $ruta_destino");
                    $imagen_nombre = null;
                }
            }
        }

        if (!$mensajeToast) {
            $stmt = $conexion->prepare("INSERT INTO propuestas_denuncias (titulo, descripcion, imagen, estado)
                                        VALUES ( ?, ?, ?, 'pendiente')");
            $stmt->bind_param("sss", $titulo, $descripcion, $imagen_nombre);

            if ($stmt->execute()) {
                $mensajeToast = "Denuncia enviada correctamente.";
                $tipoToast = "primary";
            } else {
                $mensajeToast = "Error al enviar la denuncia: " . $conexion->error;
                $tipoToast = "danger";
                guardarLog("Error Denuncia: En BD al insertar denuncia: " . $conexion->error);
            }
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
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Enviar Denuncia - Comunicado Digital</title>
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
    .mensaje-error {
        background-color: #f8d7da;
        color: #842029;
        border: 1px solid #f5c2c7;
        padding: 8px 12px;
        border-radius: 4px;
        font-size: 14px;
        margin-top: 5px;
        animation: aparecer 0.3s ease-in-out;
    }

    /* Mensaje centrado */
    .toast-personalizado {
        position: fixed;
        top: 50%;
        left: 50%;
        transform: translate(-50%, -50%);
        background: #0d6efd;
        color: #fff;
        padding: 15px 20px;
        border-radius: 8px;
        font-size: 16px;
        font-weight: bold;
        box-shadow: 0 4px 12px rgba(0,0,0,0.3);
        z-index: 9999;
        text-align: center;
        min-width: 280px;
        animation: fadeIn 0.3s ease-in-out;
    }

    /* Colores */
    .toast-personalizado.text-bg-danger {
        background: #dc3545;
    }
    .toast-personalizado.text-bg-primary {
        background: #0d6efd;
    }

    /* Animaciones */
    @keyframes fadeIn {
        from { opacity: 0; transform: translate(-50%, -60%); }
        to { opacity: 1; transform: translate(-50%, -50%); }
    }
    .btn-seleccionar {
        background-color: #0d5c9b;
        color: #fff;
        border: none;
        border-radius: 4px;
        cursor: pointer;
        padding: 5px 5px;
    }

    .btn-seleccionar:hover {
        background-color: #0a4a7a;
    }

    .btn-descartar {
        background-color: #eb0909ff;
        color: #fff;
        border: none;
        border-radius: 4px;
        cursor: pointer;
        padding: 5px 5px;
    }
    .btn-descartar:hover {
        background-color: #970000ff;
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
        <h1>Envia tu Denuncia</h1>

        <form action="enviar_denuncia.php" method="POST" enctype="multipart/form-data">
            <div class="campo">
                <label for="titulo" class="requerido">Titulo:</label>
                <input type="text" id="titulo" name="titulo" required placeholder="Escribe el titulo de la denuncia">
            </div> 

            <div class="campo">
                <label for="descripcion" class="requerido">Descripcion:</label>
                <textarea id="descripcion" name="descripcion" required placeholder="Escribe la descripcion de la denuncia"></textarea>
            </div>

                <input type="hidden" name="fecha" value="<?php echo date('Y-m-d'); ?>">

            <div class="campo">
                <label for="imagen">Imagen (opcional, maximo 1 imagen, formato jpg):</label>
                <input type="file" id="imagen" name="imagen" accept="image/jpeg" hidden>
                <button type="button" id="btn-imagen" class="btn-seleccionar">Seleccionar archivo</button>
                <span id="estado-archivo" class="ms-2 text-muted">Ningún archivo seleccionado</span>
                <img id="preview-imagen" src="#" alt="Vista previa de la imagen" style="display:none; max-width:200px; margin-top:10px;">
            </div>
        
            <button class="boton-publicar" type="submit">Enviar Denuncia</button>
        </form>
    </div>
<?php if ($mensajeToast): ?>
<script>
document.addEventListener('DOMContentLoaded', function () {
    mostrarToast("<?php echo htmlspecialchars($mensajeToast); ?>", "<?php echo $tipoToast; ?>");
});
</script>
<?php endif; ?>

<script>
document.addEventListener('DOMContentLoaded', function () {
    const inputs = document.querySelectorAll('#titulo, #descripcion');

    inputs.forEach(input => {
        input.addEventListener('blur', function () {
            const valor = this.value;
            const valorTrim = valor.trim();
            let errorMsg = this.parentNode.querySelector('.mensaje-error');

            if (errorMsg) errorMsg.remove();

            if (valorTrim === '') {
                mostrarError(this, 'Complete este campo');
            } else if (valorTrim.length < 4) {
                mostrarError(this, 'El campo debe contener al menos 4 caracteres.');
            } else if (valorTrim.length > 255) {
                mostrarError(this, 'Has excedido el máximo de caracteres (255).');
            }
        });
    });

    function mostrarError(input, mensaje) {
        let errorMsg = document.createElement('div');
        errorMsg.className = 'mensaje-error';
        errorMsg.textContent = mensaje;
        input.parentNode.appendChild(errorMsg);

        setTimeout(() => {
            if (errorMsg) errorMsg.remove();
        }, 3000);
    }

    // Validación de imagen
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

    // Validación al enviar el formulario
    const form = document.querySelector('form');
    const titulo = document.getElementById('titulo');
    const descripcion = document.getElementById('descripcion');

    form.addEventListener('submit', function (e) {
        const tituloVal = titulo.value.trim();
        const descripcionVal = descripcion.value.trim();

        if (tituloVal === '' || descripcionVal === '') {
            e.preventDefault();
            mostrarToast('Debes completar todos los campos requeridos.', 'danger');
            return;
        }

        if (tituloVal.length < 4 || descripcionVal.length < 4) {
            e.preventDefault();
            mostrarToast('Título y descripción deben tener al menos 4 caracteres.', 'danger');
            return;
        }

        if (tituloVal.length > 255 || descripcionVal.length > 255) {
            e.preventDefault();
            mostrarToast('Título y descripción no deben exceder 255 caracteres.', 'danger');
            return;
        }
    });
});

// Toast centrado
function mostrarToast(mensaje, tipo = 'danger') {
    const toastContainer = document.createElement('div');
    toastContainer.className = 'toast-personalizado text-bg-' + tipo;
    toastContainer.setAttribute('role', 'alert');

    toastContainer.innerHTML = `
        <div class="contenido-toast">${mensaje}</div>
    `;

    document.body.appendChild(toastContainer);

    setTimeout(() => {
        toastContainer.remove();
    }, 4000);
}

document.addEventListener('DOMContentLoaded', function () {
    const input = document.getElementById('imagen');
    const boton = document.getElementById('btn-imagen');
    const estado = document.getElementById('estado-archivo');
    const preview = document.getElementById('preview-imagen');

    // Acción del botón
    boton.addEventListener('click', function () {
        if (boton.dataset.modo === "descartar") {
            input.value = "";
            estado.textContent = "Ningún archivo seleccionado";
            preview.style.display = "none";

            boton.textContent = "Seleccionar archivo";
            boton.classList.remove("btn-descartar");
            boton.classList.add("btn-seleccionar");
            boton.dataset.modo = "seleccionar";
        } else {
            input.click();
        }
    });

    input.addEventListener('change', function () {
        const archivo = this.files[0];
        if (archivo) {
            // Cambiar a modo descartar
            estado.textContent = archivo.name;
            boton.textContent = "Descartar archivo";
            boton.classList.remove("btn-seleccionar");
            boton.classList.add("btn-descartar");
            boton.dataset.modo = "descartar";

            // Mostrar vista previa
            const reader = new FileReader();
            reader.onload = function (e) {
                preview.src = e.target.result;
                preview.style.display = "block";
            };
            reader.readAsDataURL(archivo);
        }
    });
});
function mostrarToast(mensaje, tipo = 'danger') {
    const toastContainer = document.createElement('div');
    toastContainer.className = 'toast-personalizado text-bg-' + tipo;
    toastContainer.setAttribute('role', 'alert');

    toastContainer.innerHTML = `
        <div class="contenido-toast">${mensaje}</div>
    `;

    document.body.appendChild(toastContainer);

    setTimeout(() => {
        toastContainer.remove();
        if (tipo === 'primary') {
            window.location.href = 'denuncia.php';
        }
    }, 3000);
}
</script>
</body>
</html>