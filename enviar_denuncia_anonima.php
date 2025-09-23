<?php
session_start();
include 'conexion.php'; // debe definir $conexion (mysqli)

// ------------------- FUNCIONES ------------------- //
function guardarLog($mensaje) {
    $rutaLog = __DIR__ . '/logs/errores.log';
    if (!is_dir(__DIR__ . '/logs')) mkdir(__DIR__ . '/logs', 0777, true);
    $fecha = date('Y-m-d H:i:s');
    file_put_contents($rutaLog, "[$fecha] $mensaje" . PHP_EOL, FILE_APPEND);
}

$mensajeToast = null;
$tipoToast = null;

// Valores por defecto (para re-renderizar el formulario si hay error)
$titulo = $_POST['titulo'] ?? '';
$descripcion = $_POST['descripcion'] ?? '';
$fecha_evento = $_POST['fecha_evento'] ?? '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'submit_denuncia') {
    // Limpieza
    $titulo = trim($_POST['titulo'] ?? '');
    $descripcion = trim($_POST['descripcion'] ?? '');
    $fecha_evento = trim($_POST['fecha_evento'] ?? '');

    // Validaciones backend
    $errores = [];

    if ($titulo === '') {
        $errores['titulo'] = "El titulo es obligatorio.";
    } elseif (mb_strlen($titulo) < 10) {
        $errores['titulo'] = "La descripción debe tener al menos 10 caracteres.";
    } elseif (mb_strlen($titulo) > 150) {
        $errores['titulo'] = "Haz alcanzado el límite de 150 caracteres.";
    }

    if ($descripcion === '') {
        $errores['descripcion'] = "La descripción es obligatoria.";
    } elseif (mb_strlen($descripcion) < 300) {
        $errores['descripcion'] = "La descripción debe tener al menos 300 caracteres.";
    } elseif (mb_strlen($descripcion) > 3000) {
        $errores['descripcion'] = "Haz alcanzado el límite de 3000 caracteres.";
    }

    if ($fecha_evento === '') {
        $errores['fecha_evento'] = "La fecha es obligatoria";
    } elseif (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $fecha_evento)) {
        $errores['fecha_evento'] = "La fecha es inválida.";
    } elseif (strtotime($fecha_evento) > time()) {
        // opcional: evitar fechas futuras
        $errores['fecha_evento'] = "La fecha no puede ser futura.";
    }

    // Manejo de imágenes (.jpeg solamente), máximo 3, tamaño máx 3MB cada una
    $imagenes_nombres = [null, null, null];
    $imagenes_guardadas = [];
    $upload_dir = DIR . '/imagenes/denuncias/';
    if (!is_dir($upload_dir)) mkdir($upload_dir, 0755, true);

    if (isset($_FILES['imagenes']) && is_array($_FILES['imagenes']['name']) && count(array_filter($_FILES['imagenes']['name'])) > 0) {
        $archivos = $_FILES['imagenes'];
        $numFiles = count($archivos['name']);
        $contadorValidos = 0;

        for ($i = 0; $i < $numFiles && $contadorValidos < 3; $i++) {
            if ($archivos['error'][$i] !== UPLOAD_ERR_OK) continue;

            $tmp = $archivos['tmp_name'][$i];
            $name = $archivos['name'][$i];
            $size = $archivos['size'][$i];
            $mime = mime_content_type($tmp);
            $ext = strtolower(pathinfo($name, PATHINFO_EXTENSION));
            $imgInfo = @getimagesize($tmp);

            // Validar JPEG solo (.jpg or .jpeg)
            if (!in_array($mime, ['image/jpeg']) || !in_array($ext, ['jpg', 'jpeg']) || !$imgInfo || $imgInfo['mime'] !== 'image/jpeg') {
                guardarLog("Formato inválido al intentar subir: $name (mime: $mime ext: $ext)");
                $errores['imagen_formato'] = true;
                break;
            }

            // Tamaño max 3MB
            if ($size > 3 * 1024 * 1024) {
                guardarLog("Imagen demasiado grande: $name ($size bytes)");
                $errores['imagen_tamano'] = true;
                break;
            }

            $nombreUnico = uniqid('den_') . '.' . 'jpg';
            $destino = $upload_dir . $nombreUnico;

            if (!move_uploaded_file($tmp, $destino)) {
                guardarLog("Error moviendo archivo: $name");
                $errores['imagen_guardar'] = true;
                break;
            }

            $imagenes_guardadas[] = $nombreUnico;
            $contadorValidos++;
        }
    }

    // Si hubo error de imágenes de formato, lo manejamos (no guardamos en BD)
    if (isset($errores['imagen_formato'])) {
        $mensajeToast = "El formato de tu archivo no es admitido. Intenta cargar un archivo (.jpeg).";
        $tipoToast = "danger";
    } elseif (isset($errores['imagen_tamano'])) {
        $mensajeToast = "Una de las imágenes excede el tamaño permitido (3MB).";
        $tipoToast = "danger";
    } elseif (isset($errores['imagen_guardar'])) {
        $mensajeToast = "Error al procesar las imágenes.";
        $tipoToast = "danger";
    }

    // Si no hay errores, insertar en BD
    if (empty($errores) && !$mensajeToast) {
        $img1 = $imagenes_guardadas[0] ?? null;
        $img2 = $imagenes_guardadas[1] ?? null;
        $img3 = $imagenes_guardadas[2] ?? null;

        // Ajusta el nombre de la tabla/conn si tu conexion.php usa otra variable
        $stmt = $conexion->prepare("INSERT INTO propuestas_denuncias_anonima (titulo, descripcion, imagen, imagen2, imagen3, fecha_evento, estado) VALUES (?, ?, ?, ?, ?, ?, 'pendiente')");
        if (!$stmt) {
            guardarLog("Error prepare BD: " . $conexion->error);
            $mensajeToast = "Error interno al preparar la consulta.";
            $tipoToast = "danger";
        } else {
            $stmt->bind_param("ssssss", $titulo, $descripcion, $img1, $img2, $img3, $fecha_evento);
            if ($stmt->execute()) {
                $mensajeToast = "¡Tu denuncia fue enviada a los administradores!";
                $tipoToast = "success";
                // Limpiar campos para el render
                $titulo = $descripcion = $fecha_evento = '';
            } else {
                guardarLog("Error execute BD: " . $stmt->error);
                $mensajeToast = "Error al guardar la denuncia en la base de datos.";
                $tipoToast = "danger";
            }
            $stmt->close();
        }
    }
}
?>
<!doctype html>
<html lang="es">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width,initial-scale=1">
  <title>Enviar denuncia anónima - Comunicado Digital</title>

  <!-- Fuentes -->
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet">

  <!-- Iconos -->
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

  <style>
    :root{
      --azul-oscuro: #1661AC;
      --header-bg: #061F3E;
      --gris-oscuro: #403F48;
      --gris-medio: #74737C;
      --borde: #B1B1B1;
      --celeste: #2D8EFF;
      --verde: #61C9A8;
      --verde-hover: #4CA88C;
      --rojo: #E33629;
      --rojo-borde: #F5C2C7;
      --fondo-rojo: #F8D7DA;
      --toast-success: #2BAF7F;
    }

    *{box-sizing:border-box}
    body{font-family:"Inter",system-ui,Arial;background:#ffffff;color:var(--gris-oscuro);margin:0;}

/* --------------- ENCABEZADO --------------- */
    header{
      background:var(--header-bg);
      color:#fff;
      display:flex;
      justify-content:space-between;
      align-items:center;
      padding:16px 28px;
    }
    .header-left img{height:44px;}
    .header-right a{
      color:#fff;
      font-family:'Poppins';
      font-size:20px;
      font-weight:600;
      text-decoration:none;
    }
    .header-right a:hover{text-decoration:underline;color:var(--azul-oscuro);}

/* --------------- CONTENEDOR PRINCIPAL --------------- */
    .contenedor{max-width:920px;margin:36px auto;padding:0 16px;}
    .card-form{background:#fff;border-radius:12px;padding:28px 36px;box-shadow:0 6px 18px rgba(6,31,62,0.06);}

/* TITULO */
    .titulo-principal{font-family:'Poppins';font-size:32px;font-weight:700;color:var(--azul-oscuro);text-align:center;margin-bottom:6px;}
    .subtitulo{font-family:'Inter';font-size:20px;color:var(--gris-oscuro);text-align:center;margin-bottom:18px;}

/* ALERT INFORMATIVO */
    .alert-informacion{
      display:flex;gap:12px;align-items:flex-start;
      background:#ADEBFF;padding:12px 16px;border-radius:12px;margin:12px auto 22px;max-width:820px;
      color:var(--gris-oscuro);font-family:'Inter';font-size:16px;
    }
    .alert-informacion i{font-size:20px;color:var(--gris-oscuro);margin-top:2px;}

/* FORM ELEMENTS */
    form .campo{margin-bottom:20px;position:relative;}
    form label{display:block;margin-bottom:8px;font-family:'Poppins';font-size:16px;font-weight:600;color:var(--gris-oscuro);}
    form input[type="text"], form input[type="date"], form textarea, .file-container{
      width:100%;padding:12px 16px;border:1px solid var(--borde);border-radius:12px;font-family:'Inter';font-size:16px;color:#061F3E;transition:all .18s ease;outline:none;
    }
    form input[type="text"]::placeholder, form textarea::placeholder, form input[type="date"]::placeholder{color:var(--borde);font-family:'Inter';}
    form input[type="text"]:hover, form textarea:hover, form input[type="date"]:hover, .file-container:hover{box-shadow:0 6px 18px rgba(45,142,255,0.06);transform:scale(1.01);border-color:var(--celeste);}
    form input[type="text"]:focus, form textarea:focus, form input[type="date"]:focus, .file-container:focus-within{border-color:var(--celeste);box-shadow:0 6px 18px rgba(45,142,255,0.12);transform:scale(1.01);}

/* Contador (hidden until active) */
    .contador{position:absolute;right:16px;bottom:8px;font-family:'Inter';font-size:16px;color:var(--borde);display:none;}
    .contador.visible{display:block;}
    .contador.error{color:var(--rojo);}

/* shake animation for error */
    @keyframes shake{0%{transform:translateX(0)}20%{transform:translateX(-6px)}40%{transform:translateX(6px)}60%{transform:translateX(-4px)}80%{transform:translateX(4px)}100%{transform:translateX(0)}}

/* Descripción textarea */
    textarea{min-height:140px;resize:vertical;line-height:1.4;}

/* Fecha: icono a la derecha */
    .campo-fecha{position:relative;}
    .campo-fecha .fa-calendar-alt{position:absolute;right:14px;top:50%;transform:translateY(-50%);color:var(--borde);pointer-events:none;font-size:18px;}

/* Archivo */
    .label-file-row{display:flex;justify-content:space-between;align-items:center;gap:10px;margin-bottom:8px;}
    .label-file-row .nota{font-family:'Inter';font-size:16px;color:var(--gris-oscuro);}
    .file-container{display:flex;align-items:center;gap:12px;}
    .btn-file{
      background:#ADEBFF;border:1px solid #ADEBFF;border-radius:8px;padding:6px 12px;font-family:'Inter';font-size:16px;color:#061F3E;cursor:pointer;border-radius:8px;display:inline-flex;align-items:center;justify-content:center;
    }
    .btn-file.disabled{background:var(--borde);border-color:var(--borde);cursor:not-allowed;color:#fff;pointer-events:none;}
    .estado-archivo{font-family:'Inter';font-size:14px;color:var(--gris-medio);}

/* Preview */
    #preview-imagenes{display:flex;flex-wrap:wrap;gap:15px;margin-top:12px;}
    .imagen-preview{width:350px;position:relative;}
    .imagen-preview img{width:100%;height:200px;object-fit:cover;border-radius:12px;border:1px solid var(--borde);cursor:pointer;transition:all .18s ease;}
    .imagen-preview img:hover{transform:scale(1.03);box-shadow:0 8px 20px rgba(0,0,0,0.12);border-color:var(--celeste);}
    .imagen-preview .eliminar{
      position:absolute;right:10px;top:10px;background:var(--rojo);color:#fff;border:none;border-radius:50%;width:30px;height:30px;display:flex;align-items:center;justify-content:center;cursor:pointer;font-size:15px;
    }

/* Botón publicar */
    .boton-publicar{width:200px;height:50px;background:var(--verde);color:#1B314B;border:none;border-radius:12px;font-family:'Poppins';font-weight:700;font-size:20px;cursor:pointer;min-height:48px;transition:all .18s ease;display:block;margin:18px auto 0;}
    .boton-publicar:hover{background:var(--verde-hover);box-shadow:0 8px 18px rgba(76,168,140,0.18);transform:translateY(-3px);}

/* Mensaje de error debajo del campo */
    .mensaje-error{
      display:block;margin-top:8px;padding:8px 10px;background:var(--fondo-rojo);border:1px solid var(--rojo-borde);border-radius:6px;color:#842029;font-family:'Inter';font-size:16px;
    }

/* Modales genéricos (usamos simple overlay con display) */
    .modal-overlay{position:fixed;left:0;top:0;width:100%;height:100%;background:rgba(0,0,0,0.6);display:none;align-items:center;justify-content:center;z-index:9999;}
    .modal-contenido{background:#fff;border-radius:12px;max-width:560px;width:92%;padding:22px;box-shadow:0 10px 30px rgba(0,0,0,0.25);text-align:center;}
    .modal-contenido h2{font-family:'Poppins';font-size:16px;color:var(--gris-oscuro);font-weight:700;margin-bottom:10px;}
    .modal-contenido p{font-family:'Inter';font-size:16px;color:var(--gris-oscuro);margin-bottom:20px;}
    .modal-botones{display:flex;gap:16px;justify-content:space-between;}
    .modal-btn-cancel{background:#EB7373;border:none;color:#061F3E;padding:10px;border-radius:8px;font-family:'Inter';font-size:16px;cursor:pointer;flex:1;}
    .modal-btn-confirm{background:var(--verde);border:none;color:#061F3E;padding:10px;border-radius:8px;font-family:'Inter';font-size:16px;cursor:pointer;flex:1;}

/* Modal imagen */
    .modal-imagen{display:none;position:fixed;z-index:10000;left:0;top:0;width:100%;height:100%;background:rgba(0,0,0,0.85);align-items:center;justify-content:center;padding:20px;}
    .contenedor-modal{background:#fff;border-radius:12px;max-width:820px;width:100%;box-shadow:0 10px 30px rgba(0,0,0,0.3);overflow:hidden;}
    .modal-header{display:flex;justify-content:space-between;align-items:center;padding:12px 18px;background:var(--header-bg);color:#fff;}
    .modal-imagen-content{width:800px;height:600px;max-width:100%;max-height:80vh;object-fit:contain;border-radius:8px;margin:8px auto;display:block;}
    .modal-header .cerrar{background:none;border:none;color:#ff5252;font-size:22px;cursor:pointer;padding:4px 8px;border-radius:6px;}

/* Responsive */
    @media(max-width:900px){
      .imagen-preview{width:48%}
      .titulo-principal{font-size:28px}
      .card-form{padding:22px}
    }
    @media(max-width:600px){
      header{flex-direction:row;gap:8px;padding:12px}
      .imagen-preview{width:100%}
      .contenedor{margin:18px auto}
    }
  </style>
</head>
<body>

  <!-- HEADER -->
  <header>
    <div class="header-left">
      <img src="imagenes/logo_blanco.png" alt="Logo Comunicado Digital"> <!-- añade tu logo blanco en esta ruta -->
    </div>
    <div class="header-right">
      <a href="denuncia_anonima.php" id="linkVolver">Volver a Denuncias</a>
    </div>
  </header>

  <main class="contenedor">
    <div class="card-form">
      <h1 class="titulo-principal">Enviar denuncia anónima</h1>
      <p class="subtitulo">Tu denuncia será revisada por los administradores</p>

      <div class="alert-informacion" role="status">
        <i class="fas fa-shield-alt"></i>
        <div>Tu denuncia es 100 % anónima. No pediremos datos personales ni podremos rastrear tu identidad.</div>
      </div>

      <form id="formDenuncia" method="POST" enctype="multipart/form-data" novalidate>
        <input type="hidden" name="action" value="submit_denuncia">

        <!-- TÍTULO -->
        <div class="campo">
          <label for="titulo">Título: <span style="color:var(--rojo)">*</span></label>
          <input id="titulo" name="titulo" type="text" placeholder="Escribe el título de la denuncia" value="<?= htmlspecialchars($titulo) ?>">
          <small id="contadorTitulo" class="contador">0/150</small>
          <div id="error-titulo" class="mensaje-error" style="display: <?= isset($errores['titulo']) ? 'block' : 'none' ?>;">
            <?= htmlspecialchars($errores['titulo'] ?? '') ?>
          </div>
        </div>

        <!-- DESCRIPCION -->
        <div class="campo">
          <label for="descripcion">Descripción: <span style="color:var(--rojo)">*</span></label>
          <textarea id="descripcion" name="descripcion" placeholder="Escribe la descripción de la denuncia"><?= htmlspecialchars($descripcion) ?></textarea>
          <small id="contadorDescripcion" class="contador">0/3000</small>
          <div id="error-descripcion" class="mensaje-error" style="display: <?= isset($errores['descripcion']) ? 'block' : 'none' ?>;">
            <?= htmlspecialchars($errores['descripcion'] ?? '') ?>
          </div>
        </div>

        <!-- FECHA -->
        <div class="campo">
          <label for="fecha_evento">Fecha del evento denunciado: <span style="color:var(--rojo)">*</span></label>
          <div class="campo-fecha">
            <input id="fecha_evento" name="fecha_evento" type="date" value="<?= htmlspecialchars($fecha_evento) ?>" placeholder="Selecciona la fecha">
            <i class="fas fa-calendar-alt"></i>
          </div>
          <div id="error-fecha" class="mensaje-error" style="display: <?= isset($errores['fecha_evento']) ? 'block' : 'none' ?>;">
            <?= htmlspecialchars($errores['fecha_evento'] ?? '') ?>
          </div>
        </div>

        <!-- ARCHIVO -->
        <div class="campo">
          <div class="label-file-row">
            <label for="imagenes">Carga una imagen (Opcional)</label>
            <div class="nota">Puedes cargar hasta 3 imágenes en formato (.jpeg).</div>
          </div>

          <div class="file-container" tabindex="0">
            <label class="btn-file" id="btnSeleccionar">
              Elegir archivo
              <input id="inputImagenes" name="imagenes[]" type="file" accept=".jpg,.jpeg,image/jpeg" multiple style="display:none;">
            </label>
            <div class="estado-archivo" id="estado-archivo">No se ha seleccionado ningún archivo</div>
          </div>

          <div id="preview-imagenes"></div>
          <div id="error-imagen" class="mensaje-error" style="display: none;"></div>
        </div>

        <div class="boton-enviar-container">
          <button type="button" id="btnEnviar" class="boton-publicar">Enviar denuncia</button>
        </div>
      </form>
    </div>
  </main>

  <!-- MODAL: Confirmar Volver -->
  <div class="modal-overlay" id="modal-volver">
    <div class="modal-contenido">
      <h2>¿Estás seguro de volver a la vista de denuncias?</h2>
      <p>Esta acción cancelará los cambios hechos en el formulario.</p>
      <div class="modal-botones">
        <button class="modal-btn-cancel" id="cancelarVolver">Cancelar</button>
        <button class="modal-btn-confirm" id="confirmarVolver">Confirmar</button>
      </div>
    </div>
  </div>

  <!-- MODAL: Confirmar Envío -->
  <div class="modal-overlay" id="modal-confirmar">
    <div class="modal-contenido">
      <h2>¿Estás seguro de enviar tu denuncia?</h2>
      <p style="font-weight:600">Un administrador la revisará y publicará posteriormente.</p>
      <div class="modal-botones" style="margin-top:18px;">
        <button class="modal-btn-cancel" id="cancelarEnviar">Cancelar</button>
        <button class="modal-btn-confirm" id="confirmarEnviar">Confirmar</button>
      </div>
    </div>
  </div>

  <!-- MODAL: Formato inválido -->
  <div class="modal-overlay" id="modal-formato">
    <div class="modal-contenido">
      <p style="font-family:Inter;font-size:20px;color:var(--gris-oscuro)">El formato de tu archivo no es admitido. Intenta cargar un archivo (.jpeg).</p>
      <button class="modal-btn-confirm" id="btnAcuerdo">De acuerdo</button>
    </div>
  </div>

  <!-- MODAL IMAGEN AMPLIADA -->
  <div class="modal-imagen" id="modal-imagen">
    <div class="contenedor-modal">
      <div class="modal-header">
        <h2>Imagen adjunta</h2>
        <button class="cerrar" id="cerrarModalImagen">&times;</button>
      </div>
      <div style="padding:18px;text-align:center;">
        <img id="imagen-ampliada" class="modal-imagen-content" src="#" alt="Imagen ampliada">
      </div>
    </div>
  </div>

  <!-- TOAST (éxito/aviso) -->
  <?php if ($mensajeToast): ?>
    <div style="position:fixed;top:28px;left:50%;transform:translateX(-50%);z-index:11000">
      <div style="background:<?= $tipoToast === 'success' ? '#61C98F' : '#dc3545' ?>;color:#fff;padding:12px 18px;border-radius:8px;box-shadow:0 8px 20px rgba(0,0,0,0.18);font-weight:700;">
        <?= htmlspecialchars($mensajeToast) ?>
      </div>
    </div>
    <?php if ($tipoToast === 'success'): ?>
      <script>
        // redirigir luego de 2.5s a vista de denuncias
        setTimeout(function(){ window.location.href = 'denuncia_anonima.php'; }, 2500);
      </script>
    <?php endif; ?>
  <?php endif; ?>

  <script>
    (function(){
      // Elements
      const titulo = document.getElementById('titulo');
      const descripcion = document.getElementById('descripcion');
      const fecha = document.getElementById('fecha_evento');
      const contadorTitulo = document.getElementById('contadorTitulo');
      const contadorDescripcion = document.getElementById('contadorDescripcion');
      const inputImagenes = document.getElementById('inputImagenes');
      const estadoArchivo = document.getElementById('estado-archivo');
      const preview = document.getElementById('preview-imagenes');
      const errorImagen = document.getElementById('error-imagen');

      const btnEnviar = document.getElementById('btnEnviar');
      const modalConfirmar = document.getElementById('modal-confirmar');
      const modalVolver = document.getElementById('modal-volver');
      const modalFormato = document.getElementById('modal-formato');
      const modalImagen = document.getElementById('modal-imagen');

      const linkVolver = document.getElementById('linkVolver');
      const btnSeleccionar = document.getElementById('btnSeleccionar');

      // Estado archivos seleccionados (File objects)
      let archivosSeleccionados = [];
      const MAX_FILES = 3;
      const MAX_SIZE = 3 * 1024 * 1024; // 3MB

      // Inicializar contadores
      function actualizarContador(campo, contadorElem, maximo, min) {
        const len = campo.value.length;
        contadorElem.textContent = len + '/' + maximo;
        if (document.activeElement === campo) contadorElem.classList.add('visible'); else contadorElem.classList.remove('visible');

        if (len < (min || 0) || len > maximo) contadorElem.classList.add('error'); else contadorElem.classList.remove('error');
      }

      // Mostrar contador solo al enfocarse
      titulo.addEventListener('focus', () => actualizarContador(titulo, contadorTitulo, 150, 10));
      titulo.addEventListener('blur', () => actualizarContador(titulo, contadorTitulo, 150, 10));
      titulo.addEventListener('input', () => actualizarContador(titulo, contadorTitulo, 150, 10));

      descripcion.addEventListener('focus', () => actualizarContador(descripcion, contadorDescripcion, 3000, 300));
      descripcion.addEventListener('blur', () => actualizarContador(descripcion, contadorDescripcion, 3000, 300));
      descripcion.addEventListener('input', () => actualizarContador(descripcion, contadorDescripcion, 3000, 300));

      // Inicializar valores si vienen del servidor
      document.addEventListener('DOMContentLoaded', function(){
        actualizarContador(titulo, contadorTitulo, 150, 10);
        actualizarContador(descripcion, contadorDescripcion, 3000, 300);
      });

      // Validaciones en cliente (devuelven {ok:bool, msg:string})
      function validarTitulo() {
        const v = titulo.value.trim();
        if (!v) return {ok:false, msg:'El titulo es obligatorio'};
        if (v.length < 10) return {ok:false, msg:'La descripción debe tener al menos 10 caracteres.'};
        if (v.length > 150) return {ok:false, msg:'Haz alcanzado el límite de 150 caracteres.'};
        return {ok:true};
      }

      function validarDescripcion() {
        const v = descripcion.value.trim();
        if (!v) return {ok:false, msg:'La descripción es obligatoria.'};
        if (v.length < 300) return {ok:false, msg:'La descripción debe tener al menos 300 caracteres.'};
        if (v.length > 3000) return {ok:false, msg:'Haz alcanzado el límite de 3000 caracteres.'};
        return {ok:true};
      }

      function validarFecha() {
        const v = fecha.value.trim();
        if (!v) return {ok:false, msg:'La fecha es obligatoria'};
        // opcional: no permitir fecha futura
        const sel = new Date(v);
        const hoy = new Date(); hoy.setHours(0,0,0,0);
        if (sel > hoy) return {ok:false, msg:'La fecha no puede ser futura.'};
        return {ok:true};
      }

      // Mostrar mensaje debajo del campo
      function mostrarError(idElemento, mensaje) {
        const el = document.getElementById(idElemento);
        el.textContent = mensaje;
        el.style.display = 'block';
        const campo = idElemento === 'error-titulo' ? titulo : idElemento === 'error-descripcion' ? descripcion : document.getElementById('fecha_evento');
        campo.classList.add('error-borde');
        campo.style.borderColor = 'var(--rojo)';
        campo.style.animation = 'shake .4s';
        setTimeout(()=> campo.style.animation = '', 400);
      }
      function ocultarError(idElemento) {
        const el = document.getElementById(idElemento);
        el.style.display = 'none';
        const campo = idElemento === 'error-titulo' ? titulo : idElemento === 'error-descripcion' ? descripcion : document.getElementById('fecha_evento');
        campo.classList.remove('error-borde');
        campo.style.borderColor = '';
      }

      // Manejo de archivos
      btnSeleccionar.addEventListener('click', (e) => {
        // si está deshabilitado, no abrir
        if (btnSeleccionar.classList.contains('disabled')) return;
        inputImagenes.click();
      });

      inputImagenes.addEventListener('change', function(e){
        handleFiles(Array.from(this.files));
        // limpiar input para poder re-subir mismos archivos si se eliminan
        inputImagenes.value = '';
      });

      function handleFiles(files) {
        // filtramos JPEG
        const invalid = files.filter(f => !(f.type === 'image/jpeg' || f.type === 'image/jpg' || f.name.toLowerCase().endsWith('.jpg') || f.name.toLowerCase().endsWith('.jpeg')));
        if (invalid.length > 0) {
          // abrir modal formato
          modalFormato.style.display = 'flex';
          return;
        }

        // filtramos tamaños mayores a 3MB
        const tooBig = files.filter(f => f.size > MAX_SIZE);
        if (tooBig.length > 0) {
          document.getElementById('error-imagen').textContent = 'Una de las imágenes excede el tamaño permitido (3MB).';
          document.getElementById('error-imagen').style.display = 'block';
          return;
        } else {
          document.getElementById('error-imagen').style.display = 'none';
        }

        // agregar sin pasar 3
        archivosSeleccionados = archivosSeleccionados.concat(files);
        if (archivosSeleccionados.length > MAX_FILES) {
          archivosSeleccionados = archivosSeleccionados.slice(0,MAX_FILES);
        }

        actualizarEstadoYPreview();
      }

      function actualizarEstadoYPreview(){
        preview.innerHTML = '';
        if (archivosSeleccionados.length === 0) {
          estadoArchivo.textContent = 'No se ha seleccionado ningún archivo';
        } else {
          estadoArchivo.textContent = archivosSeleccionados.length + ' archivo(s) seleccionado(s)';
        }

        archivosSeleccionados.forEach((file, idx) => {
          const reader = new FileReader();
          reader.onload = function(ev) {
            const wrapper = document.createElement('div');
            wrapper.className = 'imagen-preview';

            const img = document.createElement('img');
            img.src = ev.target.result;
            img.alt = file.name;
            img.addEventListener('click', () => mostrarImagenAmpliada(ev.target.result));
            wrapper.appendChild(img);

            const btn = document.createElement('button');
            btn.className = 'eliminar';
            btn.type = 'button';
            btn.innerHTML = '&times;';
            btn.title = 'Eliminar';
            btn.addEventListener('click', function(e){
              e.stopPropagation();
              archivosSeleccionados.splice(idx,1);
              actualizarEstadoYPreview();
            });
            wrapper.appendChild(btn);

            preview.appendChild(wrapper);
          };
          reader.readAsDataURL(file);
        });

        // cambiar apariencia de botón si límite alcanzado
        if (archivosSeleccionados.length >= MAX_FILES) btnSeleccionar.classList.add('disabled'); else btnSeleccionar.classList.remove('disabled');
      }

      // Mostrar imagen ampliada
      function mostrarImagenAmpliada(src) {
        document.getElementById('imagen-ampliada').src = src;
        modalImagen.style.display = 'flex';
      }
      document.getElementById('cerrarModalImagen').addEventListener('click', ()=> modalImagen.style.display = 'none');
      modalImagen.addEventListener('click', (e)=> { if (e.target === modalImagen) modalImagen.style.display = 'none'; });

      // Modal formato
      document.getElementById('btnAcuerdo').addEventListener('click', () => { modalFormato.style.display = 'none'; });

      // Volver a denuncias (modal)
      linkVolver.addEventListener('click', function(e){
        e.preventDefault();
        modalVolver.style.display = 'flex';
      });
      document.getElementById('cancelarVolver').addEventListener('click', ()=> modalVolver.style.display = 'none');
      document.getElementById('confirmarVolver').addEventListener('click', ()=> { window.location.href = linkVolver.getAttribute('href'); });

      // Enviar denuncia (abrir modal de confirmación)
      btnEnviar.addEventListener('click', function(){
        // Validar cliente
        // limpiar errores previos
        ocultarError('error-titulo'); ocultarError('error-descripcion'); document.getElementById('error-fecha').style.display='none';

        const vT = validarTitulo();
        const vD = validarDescripcion();
        const vF = validarFecha();
        let hayError = false;

        if (!vT.ok) { mostrarError('error-titulo', vT.msg); hayError = true; }
        if (!vD.ok) { mostrarError('error-descripcion', vD.msg); hayError = true; }
        if (!vF.ok) { document.getElementById('error-fecha').textContent = vF.msg; document.getElementById('error-fecha').style.display='block'; document.getElementById('fecha_evento').style.borderColor='var(--rojo)'; hayError = true; }

        if (hayError) {
          // desplazar vista al primer error
          const primerError = document.querySelector('.mensaje-error[style*="display: block"]');
          if (primerError) primerError.scrollIntoView({behavior:'smooth', block:'center'});
          return;
        }

        // Si todo OK, abrir modal confirmar
        modalConfirmar.style.display = 'flex';
      });

      // Cancelar/Confirmar enviar
      document.getElementById('cancelarEnviar').addEventListener('click', ()=> modalConfirmar.style.display = 'none');
      document.getElementById('confirmarEnviar').addEventListener('click', function(){
        // Construir FormData para enviar con archivos seleccionados
        const form = document.getElementById('formDenuncia');
        const fd = new FormData(form);
        // anexar archivos seleccionados
        archivosSeleccionados.forEach((f, i) => fd.append('imagenes[]', f));
        // acción submit por fetch (para mostrar toast sin reload) - enviamos al mismo archivo PHP
        fetch(window.location.href, { method: 'POST', body: fd })
          .then(resp => resp.text())
          .then(html => {
            // Reemplazamos el body por la respuesta del servidor para que el usuario vea el toast/redirect que produce PHP
            document.open();
            document.write(html);
            document.close();
          }).catch(err => {
            alert('Error al enviar la denuncia. Intenta de nuevo.');
            console.error(err);
          });
      });

      // Cerrar modales con ESC
      document.addEventListener('keydown', (e) => {
        if (e.key === 'Escape') {
          [modalConfirmar, modalVolver, modalFormato, modalImagen].forEach(m => { if (m.style.display === 'flex') m.style.display = 'none'; });
        }
      });

      // Click fuera para cerrar modales
      [modalConfirmar, modalVolver, modalFormato].forEach(m => {
        m.addEventListener('click', (ev)=> { if (ev.target === m) m.style.display = 'none'; });
      });

      // Establecer comportamiento touch-friendly para file-container: clic abre file dialog
      document.querySelector('.file-container').addEventListener('click', ()=> { if (!btnSeleccionar.classList.contains('disabled')) inputImagenes.click(); });

    })();
  </script>
</body>
</html>