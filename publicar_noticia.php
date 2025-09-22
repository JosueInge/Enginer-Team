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

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $categoria = trim($_POST['categoria'] ?? '');
    $titulo = trim($_POST['titulo'] ?? '');
    $descripcion = trim($_POST['descripcion'] ?? '');
    $fecha_hecho = trim($_POST['fecha'] ?? '');
    $autor = $_SESSION['usuario_nombre'] ?? '';
    $fecha = date('Y-m-d H:i:s');
    $usuario_id = isset($_SESSION['usuario_id']) ? (int)$_SESSION['usuario_id'] : 0;
    $bloquear_comentarios = isset($_POST['bloquear_comentarios']) ? 1 : 0;

    // Manejo de hasta 3 imágenes JPEG
    $imagenes = [null, null, null];
    if (isset($_FILES['imagen']) && isset($_FILES['imagen']['name'])) {
        // Normalize arrays
        $names = $_FILES['imagen']['name'];
        for ($i = 0, $c = count($names); $i < $c && $i < 3; $i++) {
            if ($_FILES['imagen']['error'][$i] === UPLOAD_ERR_OK) {
                $tmp = $_FILES['imagen']['tmp_name'][$i];
                $orig = $_FILES['imagen']['name'][$i];
                $mime = mime_content_type($tmp);
                $ext = strtolower(pathinfo($orig, PATHINFO_EXTENSION));
                if (($mime === 'image/jpeg' || $ext === 'jpg' || $ext === 'jpeg') && ($ext === 'jpg' || $ext === 'jpeg')) {
                    $nombreFinal = uniqid() . '.' . $ext;
                    $ruta_destino = __DIR__ . '/imagenes/noticias/' . $nombreFinal;
                    if (!file_exists(__DIR__ . '/imagenes/noticias')) {
                        mkdir(__DIR__ . '/imagenes/noticias', 0755, true);
                    }
                    if (move_uploaded_file($tmp, $ruta_destino)) {
                        $imagenes[$i] = $nombreFinal;
                    }
                }
            }
        }
    }

    // Insert en DB
    $stmt = $conexion->prepare("INSERT INTO propuestas_noticias 
        (categoria, titulo, descripcion, imagen, imagen2, imagen3, fecha_hecho, autor, fecha, usuario_id, estado, bloquear_comentarios)
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 'aprobada', ?)");
    if ($stmt) {
        $stmt->bind_param(
            "sssssssssii",
            $categoria,
            $titulo,
            $descripcion,
            $imagenes[0],
            $imagenes[1],
            $imagenes[2],
            $fecha_hecho,
            $autor,
            $fecha,
            $usuario_id,
            $bloquear_comentarios
        );
        if ($stmt->execute()) {
            header("Location: noticias.php?publicada=1");
            exit();
        } else {
            $errorenviar = "Error al publicar: " . $stmt->error;
        }
    } else {
        $errorenviar = "Error en la preparación de la consulta: " . $conexion->error;
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="utf-8" />
  <title>Publicar Noticia</title>
  <meta name="viewport" content="width=device-width,initial-scale=1" />
  <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@600;700&family=Inter&display=swap" rel="stylesheet">
  <style>
    /* ---------- BASE ---------- */
    * { 
      box-sizing: border-box; 
    }
    body { 
      margin:0; 
      font-family: 'Inter', sans-serif; 
      background:#fff; 
      color:#061F3E; 
    }
    header { 
      background:#061F3E; 
      color:#fff; 
      padding:12px 18px; 
      display:flex; 
      justify-content:space-between; 
      align-items:center; 
    }
    .logo img { 
      height:40px; 
    }
    nav a { 
      color:#fff; 
      text-decoration:none; 
      font-weight:600; 
    }

    .contenedor-principal { 
      max-width:820px; 
      margin:28px auto; 
      padding:18px; 
    }
    h1 { 
      font-family:'Poppins',sans-serif; 
      color:#1661AC; 
      text-align:center; 
      margin-bottom:18px; 
      font-size:26px; 
    }

    .campo { 
      margin-bottom:18px; 
      position:relative; 
    }
    .campo label { 
      display:block; 
      margin-bottom:6px; 
      font-weight:600; 
      color:#403F48; 
    }
    .campo input[type="text"], 
    .campo input[type="date"], 
    .campo select, 
    .campo textarea {
      width:100%; 
      padding:10px 12px; 
      border:1px solid #B1B1B1; 
      border-radius:10px; 
      font-size:15px; 
      outline:none;
      transition: box-shadow .12s, border-color .12s;
      padding-right:60px;
    }
    .campo textarea { 
      min-height:140px; 
      resize:vertical; 
    }

    .campo input:focus, 
    .campo textarea:focus, 
    .campo select:focus { 
      border-color:#2D8EFF; 
      box-shadow: 0 4px 14px rgba(45,142,255,0.08); 
    }

    .contador { 
      position:absolute; 
      right:12px; 
      bottom:10px; 
      font-size:12px; 
      color:#8A8A8A; 
      display:none; 
      pointer-events:none; 
    }
    .error { 
      color:#E33629; 
      font-size:13px; 
      margin-top:6px; 
      min-height:18px; 
    }
    .requerido:after {
      content: " *";
      color: red;
    }

    /* shake */
    .shake { 
      animation: shake .36s; 
    }
    @keyframes shake { 0%,100%{transform:translateX(0);
    } 25%{transform:translateX(-6px);
    } 75%{transform:translateX(6px);
    } }

    /* ---------- FILE INPUT (estilo denuncias) ---------- */
    .file-input-wrapper {
      position:relative;
      display:flex;
      align-items:center;
      gap:10px;
      border:1px solid #B1B1B1;
      border-radius:12px;
      padding:8px 10px;
      background:#fff;
      cursor:pointer;
      transition: border-color .12s, box-shadow .12s;
    }
    .file-input-wrapper:hover { border-color:#2D8EFF; box-shadow:0 4px 12px rgba(45,142,255,0.05); }
    .file-input-wrapper.disabled { opacity:0.6; cursor:not-allowed; pointer-events:none; }

    .file-input-button {
      background:#ADEBFFD9;
      border:1px solid #9ecce6;
      color:#061F3E;
      padding:8px 14px;
      border-radius:8px;
      font-size:14px;
      font-weight:600;
      white-space:nowrap;
    }
    .file-input-text { color:#6f6f6f; font-size:14px; flex:1; word-break:break-word; }

    .file-input-wrapper input[type="file"] {
      position:absolute; left:0; top:0; width:100%; height:100%; opacity:0; cursor:pointer;
    }

    /* previews */
    #preview-imagen { display:flex; gap:16px; flex-wrap:wrap; margin-top:12px; }
    .imagen-preview { width:200px; display:flex; flex-direction:column; align-items:center; }
    .imagen-preview img {
      width:100%; height:130px; object-fit:cover; border:1px solid #D1D5DB; border-radius:8px; cursor:pointer;
      transition: transform .12s;
    }
    .imagen-preview img:hover { transform: scale(1.02); }
    .imagen-preview .info { width:100%; display:flex; justify-content:space-between; align-items:center; margin-top:8px; gap:8px; }
    .imagen-preview .nombre { font-size:13px; color:#403F48; white-space:nowrap; overflow:hidden; text-overflow:ellipsis; max-width:150px; }
    .imagen-preview .eliminar {
      background:#EB7373; color:#fff; border:none; border-radius:50%; width:26px; height:26px; cursor:pointer; font-weight:700;
    }

    /* botón publicar */
    .boton-publicar {
      display:block; margin:18px auto; background:#61C9A8; color:#061F3E; border:none; border-radius:25px;
      padding:12px 28px; font-size:18px; font-weight:700; cursor:pointer;
    }
    .boton-publicar:hover { background:#4CA88C; }

    /* ---------- MODALES ---------- */
    .modal-confirmacion {
      display:none; position:fixed; inset:0; z-index:99999; background:rgba(0,0,0,.6);
      justify-content:center; align-items:center;
    }
    .modal-contenido {
      background:#fff; padding:22px; border-radius:12px; width:520px; max-width:94%;
      text-align:center; box-shadow:0 6px 24px rgba(0,0,0,.25);
      font-family:'Poppins',sans-serif;
    }
    .modal-contenido h2 { font-size:20px; margin-bottom:10px; color:#0d2740; }
    .modal-contenido p { font-size:15px; color:#444; margin-bottom:18px; }
    .modal-botones { display:flex; gap:18px; justify-content:space-between; }
    .btn-cancelar { flex:1; background:#EB7373; border:none; color:#061F3E; padding:10px; border-radius:8px; font-weight:700; cursor:pointer; }
    .btn-confirmar { flex:1; background:#61C9A8; border:none; color:#061F3E; padding:10px; border-radius:8px; font-weight:700; cursor:pointer; }
    .btn-cancelar:hover { background:#c45858; } .btn-confirmar:hover { background:#16a085; }

    /* modal imagen ampliada */
    .image-modal .modal-contenido { padding:12px; position:relative; }
    .image-modal .modal-close {
      position:absolute; right:12px; top:12px; background:rgba(0,0,0,0.08);
      border:none; width:36px; height:36px; border-radius:8px; cursor:pointer; font-size:18px;
    }
    .image-modal img { max-width:100%; height:auto; border-radius:8px; display:block; margin:0 auto 12px; }
    .image-modal .modal-filename { font-size:14px; color:#333; margin-bottom:10px; text-align:center; }
    /* toast */
    .toast-personalizado { position:fixed; top:22px; left:50%; transform:translateX(-50%); background:#0d6efd; color:#fff; padding:12px 18px; border-radius:8px; z-index:110000; font-weight:700; box-shadow:0 6px 18px rgba(0,0,0,.18); }
    .toast-personalizado.danger { background:#dc3545; }

   .campo input::placeholder,
    .campo textarea:: ::placeholder { crolo: #B1B1B1; }

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

    .card-formulario {
      background: #fff;
      padding: 25px 30px;
      border-radius: 16px;
      box-shadow: 0 6px 15px rgba(0,0,0,0.08);
      margin-top: 20px;
  }

  </style>
</head>
<body>
<header>
  <div class="logo"><img src="imagenes/logo.png" alt="logo"></div>
  <nav><a href="#" id="volverNoticias">Volver a Noticias</a></nav>
</header>

<div class="contenedor-principal">
  <h1>Publicar Nueva Noticia</h1>

  <?php if (!empty($errorenviar)): ?>
    <div style="background:#ffecec;border:1px solid #f5c2c2;padding:10px;border-radius:8px;margin-bottom:12px;color:#842029;">
      <?= htmlspecialchars($errorenviar) ?>
    </div>
  <?php endif; ?>

  <form id="formNoticia" action="publicar_noticia.php" method="POST" enctype="multipart/form-data" novalidate>
    <!-- CATEGORIA (nuevo) -->
    <div class="campo">
      <label for="categoria" class="requerido">Categoría:</label>
      <select id="categoria" name="categoria" required>
        <option value="">Seleccione una categoría</option>
        <option value="Deportes">Deportes</option>
        <option value="Clima">Clima</option>
        <option value="Educacion">Educación</option>
        <option value="Turismo">Turismo</option>
      </select>
      <div id="errorCategoria" class="error"></div>
    </div>

    <!-- TITULO -->
    <div class="campo">
      <label for="titulo" class="requerido">Título:</label>
      <input type="text" id="titulo" name="titulo" maxlength="150" placeholder="Escribe el título de la noticia">
      <div id="contadorTitulo" class="contador">0/150</div>
      <div id="errorTitulo" class="error"></div>
    </div>

    <!-- DESCRIPCION -->
    <div class="campo">
      <label for="descripcion" class="requerido">Descripción:</label>
      <textarea id="descripcion" name="descripcion" maxlength="3000" placeholder="Escribe la descripción de tu noticia"></textarea>
      <div id="contadorDescripcion" class="contador">0/3000</div>
      <div id="errorDescripcion" class="error"></div>
    </div>

    <!-- FECHA DEL HECHO -->
    <div class="campo">
      <label for="fecha" class="requerido">Fecha del hecho:</label>
      <input type="date" id="fecha" name="fecha">
      <div id="errorFecha" class="error"></div>
    </div>

    <!-- IMAGENES -->
    <div class="campo">
      <div style="display:flex; justify-content:space-between; align-items:center; gap:10px;">
        <label>Imágenes (máx. 3, solo JPEG)</label>
        <span style="color:#8a8a8a; font-size:13px;">Puedes subir hasta 3 imágenes (.jpeg)</span>
      </div>

      <div id="file-wrapper" class="file-input-wrapper" role="button" aria-label="Elegir imágenes">
        <span class="file-input-button">Elegir archivo</span>
        <span id="file-text" class="file-input-text">No se ha seleccionado ninguna imagen</span>
        <input type="file" id="imagen" name="imagen[]" accept="image/jpeg" multiple>
      </div>

      <div id="preview-imagen"></div>
      <div id="errorImagen" class="error"></div>
    </div>

    <!-- BLOQUEAR COMENTARIOS -->
    <div class="campo" style="display:flex; align-items:center; gap:10px;">
      <input type="checkbox" id="bloquear_comentarios" name="bloquear_comentarios">
      <label for="bloquear_comentarios">Bloquear comentarios</label>
    </div>

    <button class="boton-publicar" type="button" id="btnPublicar">Publicar Noticia</button>
  </form>
</div>

<!-- MODAL PUBLICAR -->
<div class="modal-confirmacion" id="modalPublicar">
  <div class="modal-contenido">
    <h2>¿Publicar esta noticia?</h2>
    <p>Una vez publicada, estará disponible para todos los lectores.</p>
    <div class="modal-botones">
      <button class="btn-cancelar" onclick="cerrarModal('modalPublicar')">Cancelar</button>
      <button class="btn-confirmar" onclick="document.getElementById('formNoticia').submit()">Confirmar</button>
    </div>
  </div>
</div>

<!-- MODAL VOLVER -->
<div class="modal-confirmacion" id="modalVolver">
  <div class="modal-contenido">
    <h2>¿Estás seguro de volver a la vista de noticias?</h2>
    <p>Esta acción cancelara los cambios hechos en el formulario.</p>
    <div class="modal-botones">
      <button class="btn-cancelar" onclick="cerrarModal('modalVolver')">Cancelar</button>
      <button class="btn-confirmar" onclick="window.location='noticias.php'">Confirmar</button>
    </div>
  </div>
</div>


<!-- MODAL IMAGEN AMPLIADA -->
<div class="modal-confirmacion image-modal" id="imageModal">
  <div class="modal-contenido">
    <button class="modal-close" aria-label="Cerrar" onclick="closeImageModal()">✕</button>
    <img id="modalImage" src="#" alt="Imagen ampliada">
    <div id="modalFilename" class="modal-filename"></div>
    <div style="display:flex;justify-content:center;">
      <button class="btn-confirmar" onclick="closeImageModal()">Cerrar</button>
    </div>
  </div>
</div>

<script>
/* ---------- UTIL / TOAST ---------- */
function mostrarToast(mensaje, tipo = 'danger') {
  const t = document.createElement('div');
  t.className = 'toast-personalizado' + (tipo === 'danger' ? ' danger' : '');
  t.textContent = mensaje;
  document.body.appendChild(t);
  setTimeout(()=> t.remove(), 4500);
}

/* ---------- ELEMENTOS ---------- */
const titulo = document.getElementById('titulo');
const descripcion = document.getElementById('descripcion');
const fecha = document.getElementById('fecha');
const categoria = document.getElementById('categoria');

const contadorTitulo = document.getElementById('contadorTitulo');
const contadorDescripcion = document.getElementById('contadorDescripcion');

const errorTitulo = document.getElementById('errorTitulo');
const errorDescripcion = document.getElementById('errorDescripcion');
const errorFecha = document.getElementById('errorFecha');
const errorCategoria = document.getElementById('errorCategoria');
const errorImagen = document.getElementById('errorImagen');

const fileWrapper = document.getElementById('file-wrapper');
const inputImagen = document.getElementById('imagen');
const fileText = document.getElementById('file-text');
const previewContainer = document.getElementById('preview-imagen');

const btnPublicar = document.getElementById('btnPublicar');

/* ---------- CONTADORES (dentro del campo) ---------- */
function manejarContador(input, contador, max) {
  input.addEventListener('focus', () => {
    contador.style.display = 'block';
    contador.textContent = `${input.value.length}/${max}`;
  });
  input.addEventListener('input', () => {
    contador.textContent = `${input.value.length}/${max}`;
  });
  input.addEventListener('blur', () => {
    if (input.value.trim() === '') contador.style.display = 'none';
  });
}
manejarContador(titulo, contadorTitulo, 150);
manejarContador(descripcion, contadorDescripcion, 3000);

/* ---------- VALIDACIONES REACTIVAS ---------- */
function shake(el) {
  el.classList.add('shake');
  setTimeout(()=> el.classList.remove('shake'), 420);
}

function validarTitulo() {
  const l = titulo.value.trim().length;
  errorTitulo.textContent = '';
  if (l === 0) { errorTitulo.textContent = 'El título es obligatorio.'; shake(titulo); return false; }
  if (l < 10)  { errorTitulo.textContent = 'El título debe tener al menos 10 caracteres.'; shake(titulo); return false; }
  if (l > 150) { errorTitulo.textContent = 'Haz alcanzado el límite de 150 caracteres.'; shake(titulo); return false; }
  return true;
}
function validarDescripcion() {
  const l = descripcion.value.trim().length;
  errorDescripcion.textContent = '';
  if (l === 0) { errorDescripcion.textContent = 'La descripción es obligatoria.'; shake(descripcion); return false; }
  if (l < 300) { errorDescripcion.textContent = 'La descripción debe tener al menos 300 caracteres.'; shake(descripcion); return false; }
  if (l > 3000) { errorDescripcion.textContent = 'Haz alcanzado el límite de 3000 caracteres.'; shake(descripcion); return false; }
  return true;
}
function validarFecha() {
  errorFecha.textContent = '';
  if (!fecha.value) { errorFecha.textContent = 'La fecha es obligatoria.'; shake(fecha); return false; }
  return true;
}
function validarCategoria() {
  errorCategoria.textContent = '';
  if (!categoria.value) { errorCategoria.textContent = 'La categoría es obligatoria.'; shake(categoria); return false; }
  return true;
}

/* eventos reactivos */
titulo.addEventListener('input', validarTitulo);
descripcion.addEventListener('input', validarDescripcion);
fecha.addEventListener('blur', validarFecha);
categoria.addEventListener('change', validarCategoria);

/* ---------- PREVIEW / IMAGENES (máx 3 JPEG) ---------- */
let archivosSeleccionados = [];

function actualizarFileWrapperState() {
  if (archivosSeleccionados.length >= 3) {
    fileWrapper.classList.add('disabled');
    inputImagen.disabled = true;
    fileText.textContent = `${archivosSeleccionados.length} imagen(es) seleccionadas (límite alcanzado)`;
  } else {
    fileWrapper.classList.remove('disabled');
    inputImagen.disabled = false;
    if (archivosSeleccionados.length === 0) fileText.textContent = 'No se ha seleccionado ninguna imagen';
    else fileText.textContent = `${archivosSeleccionados.length} imagen(es) seleccionadas`;
  }
}

function esJPEG(file) {
  const name = file.name.toLowerCase();
  return file.type === 'image/jpeg' || name.endsWith('.jpg') || name.endsWith('.jpeg');
}

inputImagen.addEventListener('change', function () {
  const nuevos = Array.from(this.files);
  let validos = [];
  let rechazados = 0;
  nuevos.forEach(f => {
    if (esJPEG(f)) validos.push(f);
    else rechazados++;
  });
  if (rechazados > 0) mostrarToast('Solo se permiten imágenes en formato JPEG.', 'danger');

  archivosSeleccionados = archivosSeleccionados.concat(validos);
  if (archivosSeleccionados.length > 3) {
    archivosSeleccionados = archivosSeleccionados.slice(0,3);
    mostrarToast('Máximo 3 imágenes. Se han descartado las restantes.', 'danger');
  }
  actualizarPreview();
  actualizarFileWrapperState();
  this.value = '';
});

function actualizarPreview() {
  previewContainer.innerHTML = '';
  archivosSeleccionados.forEach((file, index) => {
    const reader = new FileReader();
    reader.onload = function (e) {
      const wrapper = document.createElement('div');
      wrapper.className = 'imagen-preview';

      // imagen
      const img = document.createElement('img');
      img.src = e.target.result;
      img.alt = file.name;
      img.title = 'Ver ampliada';
      img.addEventListener('click', () => abrirImagen(e.target.result, file.name));
      wrapper.appendChild(img);

      // info: nombre + eliminar
      const info = document.createElement('div');
      info.className = 'info';

      const nombre = document.createElement('div');
      nombre.className = 'nombre';
      nombre.textContent = file.name;

      const btnEliminar = document.createElement('button');
      btnEliminar.type = 'button';
      btnEliminar.className = 'eliminar';
      btnEliminar.textContent = '×';
      btnEliminar.title = 'Eliminar imagen';
      btnEliminar.addEventListener('click', () => {
        archivosSeleccionados.splice(index, 1);
        actualizarPreview();
        actualizarFileWrapperState();
      });

      info.appendChild(nombre);
      info.appendChild(btnEliminar);
      wrapper.appendChild(info);
      previewContainer.appendChild(wrapper);
    };
    reader.readAsDataURL(file);
  });

  // Sincronizar input.files para envío (DataTransfer)
  const dt = new DataTransfer();
  archivosSeleccionados.forEach(f => dt.items.add(f));
  inputImagen.files = dt.files;
}

/* ---------- MODAL IMAGEN AMPLIADA ---------- */
function abrirImagen(src, name) {
  document.getElementById('modalImage').src = src;
  document.getElementById('modalFilename').textContent = name;
  document.getElementById('imageModal').style.display = 'flex';
}
function closeImageModal() {
  document.getElementById('imageModal').style.display = 'none';
  document.getElementById('modalImage').src = '#';
  document.getElementById('modalFilename').textContent = '';
}
document.addEventListener('keydown', (e) => { if (e.key === 'Escape') closeImageModal(); });

/* ---------- MODALES CONFIRMACION ---------- */
function abrirModal(id) { document.getElementById(id).style.display = 'flex'; }
function cerrarModal(id) { document.getElementById(id).style.display = 'none'; }

document.getElementById('volverNoticias').addEventListener('click', (e) => {
  e.preventDefault();
  abrirModal('modalVolver'); // si quieres otro modal para volver, puedes crear otro similar
});

/* ---------- PUBLICAR (validación final) ---------- */
btnPublicar.addEventListener('click', () => {
  // Validar categoria, titulo, descripcion, fecha
  const catOk = validarCategoria();
  const titOk = validarTitulo();
  const descOk = validarDescripcion();
  const fechaOk = validarFecha();

  // Validar imágenes: si hay y si todas son jpeg (cliente ya filtró)
  if (archivosSeleccionados.length > 0) {
    const anyNotJpeg = archivosSeleccionados.some(f => !esJPEG(f));
    if (anyNotJpeg) {
      errorImagen.textContent = 'Solo se permiten imágenes JPEG.';
      mostrarToast('Solo se permiten imágenes JPEG.', 'danger');
    } else {
      errorImagen.textContent = '';
    }
  } else {
    errorImagen.textContent = '';
  }

  if (catOk && titOk && descOk && fechaOk) {
    abrirModal('modalPublicar');
  } else {
    mostrarToast('Corrige los errores en el formulario.', 'danger');
  }
});
</script>
</body>
</html>
