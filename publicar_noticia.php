<?php
session_start();
error_reporting(E_ALL);
ini_set('display_errors', 1);

include 'conexion.php';

if (!isset($_SESSION['usuario_id'])) {
    header("Location: login.php");
    exit();
}

if ($_SESSION['usuario_rol'] !== 'Administrador') {
    header("Location: noticias.php");
    exit();
}

$error = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $categoria = $_POST['categoria'] ?? '';
    $titulo = $_POST['titulo'] ?? '';
    $descripcion = $_POST['descripcion'] ?? '';
    $autor = $_SESSION['usuario_nombre'];
    $fecha = date('Y-m-d H:i:s');
    $usuario_id = $_SESSION['usuario_id'] ?? null;

    $imagenes = [null, null, null]; // espacio para 3 imágenes

if (isset($_FILES['imagen']) && isset($_FILES['imagen']['name'])) {
    foreach ($_FILES['imagen']['name'] as $index => $nombreOriginal) {
        if ($_FILES['imagen']['error'][$index] === UPLOAD_ERR_OK) {
            $extension = pathinfo($nombreOriginal, PATHINFO_EXTENSION);
            $nombreFinal = uniqid() . '.' . $extension;
            $ruta_destino = 'imagenes/noticias/' . $nombreFinal;

            if (!file_exists('imagenes/noticias')) {
                mkdir('imagenes/noticias', 0777, true);
            }

            if (move_uploaded_file($_FILES['imagen']['tmp_name'][$index], $ruta_destino)) {
                $imagenes[$index] = $nombreFinal; // Guardamos en la posición (0,1,2)
            }
        }
    }
}

// Asignar cada imagen a las variables que usas en el INSERT
$imagen1 = $imagenes[0];
$imagen2 = $imagenes[1];
$imagen3 = $imagenes[2];

$stmt = $conexion->prepare("INSERT INTO propuestas_noticias 
    (categoria, titulo, descripcion, imagen, autor, fecha, usuario_id, estado, bloquear_comentarios, imagen2, imagen3)
    VALUES (?, ?, ?, ?, ?, ?, ?, 'aprobada', ?, ?, ?)");

$bloquear_comentarios = isset($_POST['bloquear_comentarios']) ? 1 : 0;

$stmt->bind_param(
    "sssssssiss",
    $categoria,
    $titulo,
    $descripcion,
    $imagen1,
    $autor,
    $fecha,
    $usuario_id,
    $bloquear_comentarios,
    $imagen2,
    $imagen3
);

    if ($stmt->execute()) {
        $envioexitoso = "Denuncia enviada correctamente. Será revisada por un administrador.";
    } else {
        $errorenviar = "Error al enviar la denuncia: " . $conexion->error;
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Publicar Noticia - Comunicado Digital</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@600;700&family=Inter&display=swap" rel="stylesheet">
  <style>
    * { box-sizing: border-box; margin: 0; padding: 0; }
    body { font-family: 'Inter', sans-serif; background: #fff; }

    header {
      background-color: #061F3E;
      color: white;
      padding: 15px 20px;
      display: flex;
      justify-content: space-between;
      align-items: center;
    }
    .logo img { height: 50px; }
    nav a {
      font-family: 'Poppins', sans-serif;
      font-size: 24px;
      font-weight: 600;
      color: #fff;
      text-decoration: none;
      margin-left: 20px;
    }
    nav a:hover {
      color: #1661AC;
      text-decoration: underline;
    }

    .contenedor-principal {
      max-width: 800px;
      margin: 30px auto;
      padding: 20px;
    }
    h1 {
      font-family: 'Poppins', sans-serif;
      font-size: 32px;
      font-weight: 700;
      color: #1661AC;
      text-align: center;
      margin-bottom: 30px;
    }
    .campo { margin-bottom: 20px; }
    .campo label {
      font-family: 'Poppins', sans-serif;
      font-size: 16px;
      font-weight: 600;
      color: #403F48;
      display: block;
      margin-bottom: 8px;
    }
    .campo select,
    .campo input[type="text"],
    .campo input[type="date"],
    .campo textarea {
      width: 100%;
      padding: 10px;
      font-family: 'Inter', sans-serif;
      font-size: 16px;
      border: 1px solid #B1B1B1;
      border-radius: 12px;
      color: #061F3E;
      outline: none;
      transition: 0.3s;
    }
    .campo textarea { min-height: 120px; border-radius: 6px; }

    .campo input::placeholder,
    .campo textarea::placeholder { color: #B1B1B1; }

    .campo input:hover, .campo textarea:hover, .campo select:hover {
      border-color: #2D8EFF;
      transform: scale(1.01);
      box-shadow: 0 2px 5px rgba(0,0,0,0.1);
    }
    .campo input:focus, .campo textarea:focus, .campo select:focus {
      border-color: #2D8EFF;
      transform: scale(1.01);
      box-shadow: 0 2px 5px rgba(0,0,0,0.1);
    }

    .contador {
      font-size: 14px;
      font-family: 'Inter', sans-serif;
      text-align: right;
      margin-top: 4px;
      color: #B1B1B1;
      display: none;
    }
    .contador.error { color: #E33639; }

    .campo input[type="file"] {
      border-radius: 12px;
      padding: 6px;
      border: 1px solid #B1B1B1;
      transition: 0.3s;
    }

    /* Contenedor de previews */
    #preview-imagen {
      display: flex;
      gap: 20px;
      flex-wrap: wrap;
      align-items: flex-start;
      margin-top: 10px;
    }

    /* Caja de cada preview */
    .imagen-preview {
      display: flex;
      flex-direction: column;
      align-items: center;
      width: 320px;
      margin-left: 3px;
    }

    /* Imagen cargada */
    .imagen-preview img {
      width: 360px;
      height: 203px;
      object-fit: cover;
      border: 1px solid #B1B1B1;
      border-radius: 12px;
    }

    /* Contenedor inferior (nombre + eliminar) */
    .imagen-preview .info {
      display: flex;
      justify-content: space-between;
      width: 100%;
      margin-top: 5px;
      font-family: 'Inter', sans-serif;
    }

    /* Nombre de archivo */
    .imagen-preview .nombre {
      font-size: 0.8rem;
      overflow: hidden;
      text-overflow: ellipsis;
      white-space: nowrap;
      max-width: 260px;
      color: #403F48;
    }

    /* Botón eliminar */
    .imagen-preview .eliminar {
      background: #EB7373;
      color: #fff;
      border: none;
      border-radius: 50%;
      cursor: pointer;
      width: 22px;
      height: 22px;
      line-height: 20px;
      text-align: center;
      font-weight: bold;
      font-size: 14px;
      transition: background 0.2s ease;
    }
    .imagen-preview .eliminar:hover {
      background: #d33;
    }

    .campo-checkbox {
      display: flex;
      align-items: center;
      gap: 8px;
      font-family: 'Inter', sans-serif;
      color: #403F48;
      margin: 20px 0;
    }

    .boton-publicar {
      width: 200px;
      height: 50px;
      background: #61C9A8;
      border: none;
      border-radius: 12px;
      font-size: 20px;
      font-weight: bold;
      font-family: 'Poppins', sans-serif;
      color: #1B314B;
      cursor: pointer;
      transition: 0.3s;
      display: block;
      margin: 0 auto;
    }
    .boton-publicar:hover { background: #4CA88C; }

    .error {
      color: #E33629;
      font-size: 16px;
      font-family: 'Inter', sans-serif;
      text-align: center;
      margin-top: 8px;
    }

    /* Modal base */
    .modal {
      display: none;
      position: fixed;
      top:0; left:0; right:0; bottom:0;
      background: rgba(0,0,0,0.5);
      justify-content: center;
      align-items: center;
      z-index: 1000;
    }
    .modal-contenido {
      background: #fff;
      padding: 20px;
      border-radius: 12px;
      width: 400px;
      text-align: center;
    }
    .modal h3 {
      font-family: 'Poppins', sans-serif;
      font-size: 16px;
      font-weight: bold;
      color: #403F48;
      margin-bottom: 10px;
    }
    .modal p {
      font-family: 'Inter', sans-serif;
      font-size: 16px;
      color: #403F48;
      margin-bottom: 20px;
    }
    .modal button {
      padding: 8px 16px;
      border-radius: 8px;
      font-size: 16px;
      font-family: 'Inter', sans-serif;
      cursor: pointer;
      border: none;
      margin: 0 5px;
    }
    .btn-confirmar { background: #61C9A8; color: #fff; }
    .btn-cancelar { background: #EB7373; color: #061F3E; }

    .file-input-wrapper {
      position: relative;
      display: flex;
      align-items: center;
      border: 1px solid #B1B1B1;
      border-radius: 12px;
      padding: 4px 8px;
      background: #fff;
      max-width: 100%;
    }

    .file-input-wrapper input[type="file"] {
      position: absolute;
      left: 0;
      top: 0;
      opacity: 0;
      width: 100%;
      height: 100%;
      cursor: pointer;
    }

    .file-input-button {
      background: #ADEBFFD9;
      border: 1px solid #9ecce6;
      color:  #061F3E;
      padding: 6px 12px;
      border-radius: 4px;
      font-size: 14px;
      cursor: pointer;
      white-space: nowrap;
      margin-right: 10px;
    }

    .file-input-text {
      flex-grow: 1;
      color: #777;
      font-size: 14px;
    }

    .campo .file-input-wrapper:hover,
    .campo .file-input-wrapper:focus-within {
      border-color: #2D8EFF;
      transform: scale(1.01);
      box-shadow: 0 2px 5px rgba(0,0,0,0.1);
    }

  </style>
</head>
<script>
  document.addEventListener("DOMContentLoaded", function () {
    const fileInputs = document.querySelectorAll('.file-input-wrapper input[type="file"]');

    fileInputs.forEach(input => {
      const textElement = input.parentElement.querySelector('.file-input-text');

      input.addEventListener("change", function () {
        if (this.files.length > 0) {
          // Si hay archivos seleccionados, muestra los nombres
          const fileNames = Array.from(this.files).map(file => file.name).join(', ');
          textElement.textContent = fileNames;
        } else {
          // Si no hay archivos, muestra el placeholder original
          textElement.textContent = "No se ha seleccionado ningún archivo";
        }
      });
    });
  });
</script>
<body>
<header>
  <div class="logo">
    <img src="imagenes/logo.png" alt="logo">
  </div>
  <nav>
    <a href="#" id="volverNoticias">Volver a Noticias</a>
  </nav>
</header>

<div class="contenedor-principal">
  <h1>Publicar Nueva Noticia</h1>

  <form id="formNoticia" action="publicar_noticia.php" method="POST" enctype="multipart/form-data">
    <div class="campo">
      <label for="categoria">Categoría:</label>
      <select id="categoria" name="categoria" required>
        <option value="">Seleccione una categoría</option>
        <option value="Deportes">Deportes</option>
        <option value="Clima">Clima</option>
        <option value="Educacion">Educación</option>
        <option value="Turismo">Turismo</option>
      </select>
    </div>

    <div class="campo">
      <label for="titulo">Título:</label>
      <input type="text" id="titulo" name="titulo" maxlength="150" placeholder="Escribe el título de la noticia">
      <div id="contadorTitulo" class="contador"></div>
      <div id="errorTitulo" class="error"></div>
    </div>

    <div class="campo">
      <label for="descripcion">Descripción:</label>
      <textarea id="descripcion" name="descripcion" maxlength="3000" placeholder="Escribe la descripción de tu noticia"></textarea>
      <div id="contadorDescripcion" class="contador"></div>
      <div id="errorDescripcion" class="error"></div>
    </div>

    <div class="campo">
  <div class="d-flex justify-content-between align-items-center mb-1">
    <label for="imagen" class="fw-semibold">Carga una imagen (Opcional)</label>
    <span class="text-muted">Puedes cargar hasta 3 imágenes en formato (.jpeg).</span>
  </div>

  <div class="file-input-wrapper">
      <span class="file-input-button">Elegir archivo</span>
      <span id="file-text" class="file-input-text">No se ha seleccionado ningún archivo</span>
      <input type="file" id="imagen" name="imagen[]" accept="image/jpeg" multiple>
    </div>

    <div id="preview-imagen" style="display:flex; gap:50px; margin-top:10px;"></div>
  </div>

    <div class="campo-checkbox">
      <input type="checkbox" id="bloquear_comentarios" name="bloquear_comentarios">
      <label for="bloquear_comentarios">Bloquear comentarios</label>
    </div>

    <button class="boton-publicar" type="button" id="btnPublicar">Publicar Noticia</button>
  </form>
</div>

<!-- Modal volver -->
<div class="modal" id="modalVolver">
  <div class="modal-contenido">
    <h3>¿Estás seguro de volver a la vista de noticias?</h3>
    <p><b>Esta acción cancelará los cambios hechos en el formulario</b></p>
    <button class="btn-confirmar" onclick="window.location='noticias.php'">Confirmar</button>
    <button class="btn-cancelar" onclick="cerrarModal('modalVolver')">Cancelar</button>
  </div>
</div>

<!-- Modal publicar -->
<div class="modal" id="modalPublicar">
  <div class="modal-contenido">
    <h3>¿Estás seguro de publicar tu noticia?</h3>
    <p><b>Estás a punto de publicar tu noticia. Una vez publicada, estará disponible para todos los lectores.</b></p>
    <button class="btn-cancelar" onclick="cerrarModal('modalPublicar')">Cancelar</button>
    <button class="btn-confirmar" onclick="document.getElementById('formNoticia').submit()">Confirmar</button>
  </div>
</div>

<script>
  const titulo = document.getElementById('titulo');
  const descripcion = document.getElementById('descripcion');
  const contadorTitulo = document.getElementById('contadorTitulo');
  const contadorDescripcion = document.getElementById('contadorDescripcion');
  const errorTitulo = document.getElementById('errorTitulo');
  const errorDescripcion = document.getElementById('errorDescripcion');
  const errorFecha = document.getElementById('errorFecha');

  // Contador título
  titulo.addEventListener('input', () => {
    const length = titulo.value.length;
    contadorTitulo.style.display = 'block';
    contadorTitulo.textContent = `${length}/150`;
    if (length < 10) {
      contadorTitulo.classList.add('error');
      errorTitulo.textContent = "El título debe tener al menos 10 caracteres.";
    } else if (length === 150) {
      errorTitulo.textContent = "Has alcanzado el límite de 150 caracteres.";
      contadorTitulo.classList.add('error');
    } else {
      errorTitulo.textContent = "";
      contadorTitulo.classList.remove('error');
    }
  });

  // Contador descripción
  descripcion.addEventListener('input', () => {
    const length = descripcion.value.length;
    contadorDescripcion.style.display = 'block';
    contadorDescripcion.textContent = `${length}/3000`;
    if (length < 300) {
      contadorDescripcion.classList.add('error');
      errorDescripcion.textContent = "La descripción debe tener al menos 300 caracteres.";
    } else if (length === 3000) {
      errorDescripcion.textContent = "Has alcanzado el límite de 3000 caracteres.";
      contadorDescripcion.classList.add('error');
    } else {
      errorDescripcion.textContent = "";
      contadorDescripcion.classList.remove('error');
    }
  });

  // Imagen preview
  document.addEventListener('DOMContentLoaded', function () {
  const input = document.getElementById('imagen');
  const previewContainer = document.getElementById('preview-imagen');
  const form = document.querySelector('form');

  let archivosSeleccionados = [];

  input.addEventListener('change', function () {
    const nuevosArchivos = Array.from(this.files);

    // Validar tipo y extensión
    const validos = nuevosArchivos.filter(file => {
      const name = file.name.toLowerCase();
      return file.type === 'image/jpeg' && (name.endsWith('.jpg') || name.endsWith('.jpeg'));
    });

    if (validos.length < nuevosArchivos.length) {
      mostrarToast?.('Solo se permiten imágenes en formato JPEG.', 'danger');
    }

    archivosSeleccionados = archivosSeleccionados.concat(validos);

    // Limitar a 3
    if (archivosSeleccionados.length > 3) {
      mostrarToast?.('Solo puedes seleccionar hasta 3 imágenes.', 'danger');
      archivosSeleccionados = archivosSeleccionados.slice(0, 3);
    }

    // Refrescar preview
    actualizarPreview();

    // Limpiar input para poder volver a elegir
    input.value = '';
  });

  function actualizarPreview() {
    previewContainer.innerHTML = '';

    archivosSeleccionados.forEach((file, index) => {
      const reader = new FileReader();
      reader.onload = function (e) {
        const wrapper = document.createElement('div');
        wrapper.className = 'imagen-preview';

        // Imagen
        const img = document.createElement('img');
        img.src = e.target.result;
        img.alt = file.name;
        wrapper.appendChild(img);

        // Info (nombre + eliminar)
        const info = document.createElement('div');
        info.className = 'info';

        const nombreSpan = document.createElement('span');
        nombreSpan.className = 'nombre';
        nombreSpan.textContent = file.name;
        info.appendChild(nombreSpan);

        const btnX = document.createElement('button');
        btnX.type = 'button';
        btnX.className = 'eliminar';
        btnX.textContent = '×';
        btnX.addEventListener('click', function () {
          archivosSeleccionados.splice(index, 1);
          actualizarPreview();
        });
        info.appendChild(btnX);

        wrapper.appendChild(info);
        previewContainer.appendChild(wrapper);
      };
      reader.readAsDataURL(file);
    });

    // Sincronizar input.files
    const dt = new DataTransfer();
    archivosSeleccionados.forEach(f => dt.items.add(f));
    input.files = dt.files;
  }

  // Antes de enviar, aseguramos que se mantenga la selección
  form?.addEventListener('submit', function () {
    const dt = new DataTransfer();
    archivosSeleccionados.forEach(f => dt.items.add(f));
    input.files = dt.files;
  });
});


  // Validación antes de modal publicar
  document.getElementById('btnPublicar').addEventListener('click', () => {
    let valido = true;
    if (titulo.value.trim() === "") {
      errorTitulo.textContent = "El título es obligatorio.";
      valido = false;
    }
    if (descripcion.value.trim() === "") {
      errorDescripcion.textContent = "La descripción es obligatoria.";
      valido = false;
    }
    if (valido) {
      abrirModal('modalPublicar');
    }
  });

  // Modal funciones
  function abrirModal(id) { document.getElementById(id).style.display = 'flex'; }
  function cerrarModal(id) { document.getElementById(id).style.display = 'none'; }

  document.getElementById('volverNoticias').addEventListener('click', (e) => {
    e.preventDefault();
    abrirModal('modalVolver');
  });
</script>
</body>
</html>