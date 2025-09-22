<?php 
session_start();
include 'conexion.php';

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
    $fecha_evento = trim($_POST['fecha_evento'] ?? '');

    // se inicia array para nombres de imágenes
    $imagenes_nombres = [null, null, null];

    // Procesamos imágenes primero
    if (isset($_FILES['imagenes'])) {
        $archivos = $_FILES['imagenes'];
        for ($i = 0; $i < count($archivos['name']); $i++) {
            if ($archivos['error'][$i] === UPLOAD_ERR_OK) {
                $mime = mime_content_type($archivos['tmp_name'][$i]);
                $extension = strtolower(pathinfo($archivos['name'][$i], PATHINFO_EXTENSION));
                $info = getimagesize($archivos['tmp_name'][$i]);

                if ($mime === 'image/jpeg' && ($extension === 'jpg' || $extension === 'jpeg') && $info && $info['mime'] === 'image/jpeg') {
                    $nombreUnico = uniqid() . '.jpg';
                    $ruta_destino = 'imagenes/denuncias/' . $nombreUnico;

                    if (!file_exists('imagenes/denuncias')) {
                        mkdir('imagenes/denuncias', 0755, true);
                    }

                    // Guardar imagen temporalmente, se moverá al enviar correctamente
                    move_uploaded_file($archivos['tmp_name'][$i], $ruta_destino);
                    $imagenes_nombres[$i] = $nombreUnico;
                }
            }
        }
    }

    // Validaciones de campos
    if ($titulo === '' || $descripcion === '' || $fecha_evento === '') {
    guardarLog("Error Denuncia: campos vacíos al enviar los datos.");
    $mensajeToast = "Debes completar todos los campos requeridos.";
    $tipoToast = "danger";

// Validaciones para TÍTULO
} elseif (strlen($titulo) < 10) {
    guardarLog("Error Denuncia: título demasiado corto.");
    $mensajeToast = "El título debe tener al menos 10 caracteres.";
    $tipoToast = "danger";
} elseif (strlen($titulo) > 150) {
    guardarLog("Error Denuncia: título demasiado largo.");
    $mensajeToast = "El título no debe exceder 150 caracteres.";
    $tipoToast = "danger";

// Validaciones para DESCRIPCIÓN
} elseif (strlen($descripcion) < 300) {
    guardarLog("Error Denuncia: descripción demasiado corta.");
    $mensajeToast = "La descripción debe tener al menos 300 caracteres.";
    $tipoToast = "danger";
} elseif (strlen($descripcion) > 3000) {
    guardarLog("Error Denuncia: descripción demasiado larga.");
    $mensajeToast = "La descripción no debe exceder 3000 caracteres.";
    $tipoToast = "danger";
    } elseif (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $fecha_evento)) {
        guardarLog("Error Denuncia: fecha inválida.");
        $mensajeToast = "La fecha del evento es inválida.";
        $tipoToast = "danger";
    } else {
      error_log("DEBUG SQL: Titulo=$titulo, Descripcion=$descripcion, Imagen0={$imagenes_nombres[0]}, Imagen1={$imagenes_nombres[1]}, Imagen2={$imagenes_nombres[2]}, Fecha=$fecha_evento");
        // Inserción en Base de Datos 
        $stmt = $conexion->prepare("INSERT INTO propuestas_denuncias_anonima 
            (titulo, descripcion, imagen, imagen2, imagen3, fecha_evento, estado)
            VALUES (?, ?, ?, ?, ?, ?, 'pendiente')");
        $stmt->bind_param("ssssss", $titulo, $descripcion, $imagenes_nombres[0], $imagenes_nombres[1], $imagenes_nombres[2], $fecha_evento);
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
?>

<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Enviar Denuncia - Comunicado Digital</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600&family=Poppins:wght@400;600;700&display=swap" rel="stylesheet">
  <style>
    * {
      box-sizing: border-box;
      margin: 0;
      padding: 0;
    }
    body {
      font-family: Arial, sans-serif;
      background-color: #fff;
    }

    header {
      background-color: #061F3E;
      color: #fff;
      padding: 12px 20px;
      display: flex;
      justify-content: space-between;
      align-items: center;
    }

    header .logo img {
      height: 45px;
    }

    header a {
      color: #fff;
      font-weight: 600;
      text-decoration: none;
    }

    .contenedor-principal {
      max-width: 650px;
      margin: 40px auto;
      padding: 5px;
      text-align: center;
    }

    .contenedor-principal h1 {
      color: #0072ce;
      font-size: 1.6rem;
      font-weight: bold;
      margin-bottom: 10px;
    }

    .contenedor-principal p {
      color: #555;
      margin-bottom: 25px;
      font-size: 0.95rem;
    }

    .campo {
      text-align: left;
      margin-bottom: 20px;
    }

    .campo label {
      font-weight: 600;
      margin-bottom: 6px;
      display: block;
    }

    .campo input[type="text"],
    .campo input[type="date"],
    .campo textarea,
    .campo input[type="file"] {
      width: 100%;
      padding: 10px;
      border: 1px solid #ddd;
      border-radius: 8px;
      font-size: 14px;
    }

    .campo textarea {
      min-height: 120px;
      resize: none;
    }

    .text-muted {
      font-size: 0.8rem;
      color: #888 !important;
      display: inline;
      font-family: 'Inter', sans-serif;
      font-size: 16px;
    }

    .btn-seleccionar {
      background-color: #0d5c9b;
      color: #fff;
      border: none;
      border-radius: 6px;
      padding: 6px 12px;
      font-size: 14px;
      cursor: pointer;
    }

    .btn-seleccionar:hover {
      background-color: #094574;
    }

    .boton-publicar {
      background-color: #61C9A8;
      color: #1B314B;
      border: none;
      padding: 12px;
      border-radius: 25px;
      font-size: 16px;
      font-weight: bold;
      width: 25%;
      margin-top: 15px;
      transition: background 0.3s;
    }

    .boton-publicar:hover {
      background-color: #16a085;
    }

    .requerido:after {
      content: " *";
      color: red;
    }

    /* es donde se cargan las imagenes de files */
    #preview-imagenes {
    display: flex !important;
    gap: 10px;        
    flex-wrap: wrap;    
    align-items: flex-start; 
}

.imagen-preview {
    display: flex;
    flex-direction: column; 
    align-items: center;
    width: 320px;    
}

.imagen-preview img {
    width: 360px;
    height: 203px;
    object-fit: cover;
    border: 1px solid #ccc;
    border-radius: 4px;
}

.imagen-preview .info {
    display: flex;
    justify-content: space-between;
    width: 100%;
    margin-top: 5px;
}

.imagen-preview .nombre {
    font-size: 0.8rem;
    overflow: hidden;
    text-overflow: ellipsis;
    white-space: nowrap;
}

.imagen-preview .eliminar {
    background: red;
    color: white;
    border: none;
    border-radius: 50%;
    cursor: pointer;
    width: 20px;
    height: 20px;
    line-height: 18px;
    text-align: center;
    font-weight: bold;
}

    .error {
      color: red;
      margin-bottom: 15px;
      padding: 10px;
      background-color: #ffeeee;
      border: 1px solid #ffcccc;
      border-radius: 4px;
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

    .error-borde {
      border: 2px solid red !important;
    }

    /* Botón del input file */
    .custom-file-input::file-selector-button {
      background-color:  #ADEBFFD9;   /* azul */
      color: #061F3E;
      border: 1px solid #ADEBFFD9;
      border-radius: 6px;
      margin-left: 0px;
      padding: 6px 14px;
      margin-right: 10px;
      cursor: pointer;
      font-weight: 500;
      transition: background 0.3s, border-color 0.3s, color 0.3s;
    }

    .custom-file-input:hover::file-selector-button {
      background-color: #94E3FFD9 !important; 
      color: #061F3E !important;
      border-color: #94E3FFD9 !important;
    }

/* Focus (cuando haces clic y queda seleccionado) */
    .custom-file-input:focus::file-selector-button {
      background-color: #94E3FFD9 !important;
      color: #061F3E !important;
      border-color: #94E3FFD9 !important;
      box-shadow: none !important;
    }

    .campo {
      position: relative;
      width: 650px;
      margin-bottom: 20px;
      font-family: 'Poppins', sans-serif;
      font-size: 16px;
    }

    .campo input,
    .campo textarea {
      width: 100%;
      padding-right: 60px;
      box-sizing: border-box;
    }

    .campo small {
      position: absolute;
      right: 10px;
      top: 85%;
      transform: translateY(-50%);
      color: gray;
      pointer-events: none;
      font-size: 12px;
    }

    .campo textarea + small {
      top: auto;
      bottom: 5px;
      transform: none;
    }

    .volver {
      font-family: poppins; 
    }

    .parrafo {
      color: #403F48;
      font-family: 'Inter', sans-serif;
    }

    h1 {
      font-family: 'Poppins', sans-serif;
      color: #1661AC;
    }

    .alert {
      background: #ADEBFFD9;
      padding: 10px 15px;
      margin-bottom: 20px;
      border-radius: 5px;
      font-size: 14px;
      color: #004080;
    }

    .modal-confirmacion {
      display: none; 
      position: fixed;
      z-index: 99999;
      top: 0; left: 0;
      width: 100%; height: 100%;
      background: rgba(0,0,0,0.6);
      justify-content: center;
      align-items: center;
    }

    .modal-contenido {
      background: #fff;
      padding: 25px;
      border-radius: 12px;
      width: 520px;
      text-align: center;
      box-shadow: 0 6px 18px rgba(0,0,0,0.3);
      font-family: 'Poppins', sans-serif;
      animation: fadeIn 0.3s ease;
    }

    .modal-contenido h2 {
      font-size: 20px;
      margin-bottom: 10px;
      color: #0d2740;
      font-family: 'Poppins', sans-serif;
    }

    .modal-contenido p {
      font-size: 15px;
      margin-bottom: 30px;
      color: #444;
    }

    .modal-botones {
      display: flex;
      justify-content: space-between;
      gap: 30px;
    }

    .btn-cancelar {
      flex: 1;
      background: #EB7373;
      border: none;
      color: #061F3E;
      padding: 10px;
      border-radius: 8px;
      cursor: pointer;
      font-weight: bold;
    }

    .btn-confirmar {
      flex: 1;
      background: #61C9A8;
      border: none;
      color: #061F3E;
      padding: 10px;
      border-radius: 8px;
      cursor: pointer;
      font-weight: bold;
    }

    .btn-cancelar:hover { background: #c45858ff; }
    .btn-confirmar:hover { background: #16a085; }

    .volverdenuncias {
      font-family: 'Poppins', sans-serif;
      font-size: 24px;
      color: FFFFF;
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

  .imagen-preview {
  width: 180px;
  position: relative;
}
.imagen-preview img {
  width: 100%;
  height: 120px;
  object-fit: cover;
  border-radius: 10px;
}
.imagen-preview .eliminar {
  position: absolute;
  top: 6px;
  right: 6px;
  background: rgba(220,53,69,0.9);
  border: none;
  border-radius: 50%;
  color: white;
  width: 24px;
  height: 24px;
  cursor: pointer;
  font-size: 16px;
  line-height: 20px;
}
.toast-personalizado {
  top: 30px;
  left: 50%;
  transform: translateX(-50%);
  animation: slideDown 0.4s ease;
}
@keyframes slideDown {
  from { opacity: 0; transform: translate(-50%, -40px); }
  to   { opacity: 1; transform: translate(-50%, 0); }
}

.modal-imagen {
      display: none;
      position: fixed;
      z-index: 10000;
      left: 0;
      top: 0;
      width: 100%;
      height: 100%;
      background-color: rgba(0, 0, 0, 0.85);
      justify-content: center;
      align-items: center;
      padding: 20px;
    }
    
    .modal-contenido-imagen {
      max-width: 650px;
      max-height: 80vh;
      width: auto;
      height: auto;
      border-radius: 8px;
      box-shadow: 0 4px 20px rgba(0, 0, 0, 0.5);
      animation: zoomIn 0.3s ease;
    }
    
    @keyframes zoomIn {
      from {transform: scale(0.9); opacity: 0;}
      to {transform: scale(1); opacity: 1;}
    }
    
    .modal-imagen {
      display: none;
      position: fixed;
      z-index: 10000;
      left: 0;
      top: 0;
      width: 100%;
      height: 100%;
      background-color: rgba(0, 0, 0, 0.85);
      justify-content: center;
      align-items: center;
      padding: 20px;
    }
    
    .contenedor-modal {
      background-color: #fff;
      border-radius: 12px;
      max-width: 650px;
      width: 100%;
      box-shadow: 0 10px 30px rgba(0, 0, 0, 0.3);
      overflow: hidden;
      animation: zoomIn 0.3s ease;
    }
    
    @keyframes zoomIn {
      from {transform: scale(0.9); opacity: 0;}
      to {transform: scale(1); opacity: 1;}
    }
    
    .modal-header {
      display: flex;
      justify-content: space-between;
      align-items: center;
      padding: 15px 20px;
      background-color: #061F3E;
      color: white;
    }
    
    .modal-header h2 {
      font-size: 18px;
      margin: 0;
      font-weight: 600;
      font-family: 'Poppins', sans-serif;
    }
    
    .modal-header .cerrar {
      color: white;
      font-size: 28px;
      font-weight: bold;
      cursor: pointer;
      background: none;
      border: none;
      padding: 0;
      width: 30px;
      height: 30px;
      display: flex;
      align-items: center;
      justify-content: center;
    }
    
    .modal-header .cerrar:hover {
      color: #ff5252;
    }
    
    .modal-body {
      padding: 20px;
      text-align: center;
    }
    
    .modal-imagen-content {
      max-width: 100%;
      max-height: 400px;
      width: auto;
      height: auto;
      border-radius: 8px;
      margin-bottom: 15px;
    }
    
    .modal-info {
      text-align: left;
      margin-top: 15px;
      padding: 15px;
      background-color: #f8f9fa;
      border-radius: 8px;
    }
    
    .modal-info h3 {
      font-size: 16px;
      margin-bottom: 10px;
      color: #061F3E;
      font-family: 'Poppins', sans-serif;
    }
    
    .modal-info p {
      margin: 5px 0;
      color: #555;
      font-family: 'Inter', sans-serif;
    }
    
    .imagen-preview img {
      cursor: pointer;
      transition: transform 0.2s;
      border: 2px solid transparent;
    }
    
    .imagen-preview img:hover {
      transform: scale(1.03);
      border-color: #2D8EFF;
    }
    
    /* Indicador de imagen seleccionada */
    .imagen-seleccionada {
      border: 2px solid #2D8EFF !important;
      box-shadow: 0 0 8px rgba(45, 142, 255, 0.5);
    }  

  </style>
</head>
  <div id="modal-confirmacion" class="modal-confirmacion">
    <div class="modal-contenido">
      <h2>¿Estas seguro de enviar tu denuncia?</h2>
      <p>Un administrador la revisará y publicará posteriormente</p>
      <div class="modal-botones">
        <button id="btnCancelar" class="btn-cancelar">Cancelar</button>
        <button id="btnConfirmar" class="btn-confirmar">Confirmar</button>
      </div>
    </div>
  </div>

 <div id="modal-volver" class="modal-confirmacion">
  <div class="modal-contenido">
    <h2>¿Estás seguro de volver a la vista de denuncias?</h2>
    <p>Esta acción cancelara los cambios hechos en el formulario.</p>
    <div class="modal-botones">
      <button id="btnCancelarVolver" class="btn-cancelar">Cancelar</button>
      <button id="btnConfirmarVolver" class="btn-confirmar">Confirmar</button>
    </div>
  </div>
</div>

<body>
  <div id="modal-imagen" class="modal-imagen">
    <div class="contenedor-modal">
      <div class="modal-header">
        <h2>Imagen adjunta</h2>
        <button class="cerrar">&times;</button>
      </div>
      <div class="modal-body">
        <img class="modal-imagen-content" id="imagen-ampliada">
        <div class="modal-info">
          <h3>Información de la imagen</h3>
          <p id="imagen-nombre"><strong>Nombre:</strong> <span id="nombre-archivo"></span></p>
          <p id="imagen-tipo"><strong>Tipo:</strong> <span id="tipo-archivo"></span></p>
          <p id="imagen-tamaño"><strong>Tamaño:</strong> <span id="tamaño-archivo"></span></p>
        </div>
      </div>
    </div>
  </div>

  <header>
    <div class="logo">
      <img src="imagenes/logo.png" alt="logo">
    </div>
    <a class="volver" href="denuncia_anonima.php" id="btnVolver">Volver a Denuncias</a>
  </header>


  <div class="contenedor-principal">
    <div class="card-formulario">
      <h1>Enviar denuncia anónima</h1>
      <p class="parrafo" >Tu denuncia será revisada por los administradores</p>
    </div>

    <div class="alert">
      🔒 Tu denuncia es 100% anónima. No pediremos datos personales ni podremos rastrear tu identidad.
    </div>

    <form action="enviar_denuncia_anonima.php" method="POST" enctype="multipart/form-data">
      <div class="campo">
        <label for="titulo" class="requerido">Título:</label>
        <input type="text" id="titulo" name="titulo" placeholder="Escribe el título de tu denuncia" value="<?= htmlspecialchars($titulo ?? '') ?>">
        <small id="contadorTitulo">0/150</small>
      </div>

      <div class="campo">
        <label for="descripcion" class="requerido">Descripción:</label>
        <textarea id="descripcion" name="descripcion" placeholder="Escribe la descripción de tu denuncia"><?= htmlspecialchars($descripcion ?? '') ?></textarea>
        <small id="contadorDescripcion">0/3000</small>
      </div>

      <div class="campo">
        <label for="fecha_evento" class="requerido">Fecha del evento denunciado:</label>
        <input type="date" id="fecha_evento" name="fecha_evento" value="<?= htmlspecialchars($fecha_evento ?? '') ?>">
      </div>

      <div class="campo">
        <div class="d-flex justify-content-between align-items-center mb-1">
          <label for="imagenes" class="fw-semibold">Carga una imagen (Opcional)</label>
          <span class="text-muted">Puedes cargar hasta 3 imágenes en formato (.jpeg).</span>
        </div>
        <input type="file" id="imagenes" name="imagenes[]" accept="image/jpeg" multiple class="form-control custom-file-input">
        <div id="preview-imagenes" style="display:flex; gap:10px; margin-top:10px;"></div>
      </div>

      <button class="boton-publicar" type="submit">Enviar denuncia</button>
    </form>
  </div>
<?php if ($mensajeToast): ?>
<script>
document.addEventListener('DOMContentLoaded', function () {
    mostrarToast("<?php echo htmlspecialchars($mensajeToast); ?>", "<?php echo $tipoToast; ?>");
    <?php if ($tipoToast === 'primary'):?>
    setTimeout(() => {
        window.location.href = 'denuncia_anonima.php';
    }, 4000);
    <?php endif; ?>
});
</script>
<?php endif; ?>

<script>
  document.addEventListener('DOMContentLoaded', function () {
    const inputs = document.querySelectorAll('#titulo, #descripcion');

    

    inputs.forEach(input => {
        input.addEventListener('input', function () { 
            const valor = this.value.trim();
            let errorMsg = this.parentNode.querySelector('.mensaje-error');

            if (errorMsg) errorMsg.remove();
            this.classList.remove('error-borde');

            if (valor === '') {
                mostrarError(this, 'Este campo es obligatorio');
            } else if (this.id === 'titulo') {
                if (valor.length < 10) {
                    mostrarError(this, 'El título debe tener al menos 10 caracteres.');
                } else if (valor.length > 150) {
                    mostrarError(this, 'El título no debe exceder 150 caracteres.');
                }
            } else if (this.id === 'descripcion') {
                if (valor.length < 300) {
                    mostrarError(this, 'La descripción debe tener al menos 300 caracteres.');
                } else if (valor.length > 3000) {
                    mostrarError(this, 'La descripción no debe exceder 3000 caracteres.');
                }
            }
        });
    });

    function mostrarError(input, mensaje) {
        let errorMsg = document.createElement('div');
        errorMsg.className = 'mensaje-error';
        errorMsg.textContent = mensaje;
        input.parentNode.appendChild(errorMsg);

        //  borde rojo al input
        input.classList.add('error-borde');
    }

    // Validación de imagen
    const fileInput = document.getElementById('imagenes');
    const preview = document.getElementById('preview-imagenes');

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
    // Actualizamos input.files antes de enviar
        const dataTransfer = new DataTransfer();
        archivosSeleccionados.forEach(f => dataTransfer.items.add(f));
        fileInput.files = dataTransfer.files;

        const tituloVal = titulo.value.trim();
        const descripcionVal = descripcion.value.trim();
        const fechaVal = fecha_evento.value.trim();

        if (tituloVal === '' || descripcionVal === '' || fechaVal === '') {
            e.preventDefault();
            mostrarToast('Debes completar todos los campos requeridos.', 'danger');
            return;
        }
        if (tituloVal.length < 10) {
            e.preventDefault();
            mostrarToast('El título debe tener al menos 10 caracteres.', 'danger');
            return;
        }
        if (tituloVal.length > 150) {
            e.preventDefault();
            mostrarToast('El título no debe exceder 150 caracteres.', 'danger');
            return;
        }
        if (descripcionVal.length < 300) {
            e.preventDefault();
            mostrarToast('La descripción debe tener al menos 300 caracteres.', 'danger');
            return;
        }
        if (descripcionVal.length > 3000) {
            e.preventDefault();
            mostrarToast('La descripción no debe exceder 3000 caracteres.', 'danger');
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
    }, 7000);
}
document.addEventListener('DOMContentLoaded', function() {
    const input = document.getElementById('imagenes');
    const estado = document.getElementById('estado-archivo');
    const previewContainer = document.getElementById('preview-imagenes');
    const form = document.querySelector('form');

    let archivosSeleccionados = [];

    input.addEventListener('change', function() {
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
        reader.onload = function(e) {
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
            btnX.addEventListener('click', function() {
                archivosSeleccionados.splice(index, 1);
                actualizarPreview();
            });
            info.appendChild(btnX);

            wrapper.appendChild(info);
            previewContainer.appendChild(wrapper);
        };
        reader.readAsDataURL(file);
    });

    // Actualizar estado
    estado.textContent = archivosSeleccionados.length > 0
        ? `${archivosSeleccionados.length} archivo(s) seleccionado(s)`
        : 'Ningún archivo seleccionado';

    // Sincronizar input.files
    const dt = new DataTransfer();
    archivosSeleccionados.forEach(f => dt.items.add(f));
    input.files = dt.files;
}

    // Antes de enviar, aseguramos que se mantenga la selección
    form?.addEventListener('submit', function() {
        const dt = new DataTransfer();
        archivosSeleccionados.forEach(f => dt.items.add(f));
        input.files = dt.files;
    });
});


    const inputTitulo = document.getElementById('titulo');
  const contadorTitulo = document.getElementById('contadorTitulo');
  const textareaDescripcion = document.getElementById('descripcion');
  const contadorDescripcion = document.getElementById('contadorDescripcion');

  inputTitulo.addEventListener('input', () => {
    const longitud = inputTitulo.value.length;
    contadorTitulo.textContent = `${longitud}/150`;
  });

  textareaDescripcion.addEventListener('input', () => {
    const longitud = textareaDescripcion.value.length;
    contadorDescripcion.textContent = `${longitud}/3000`;
  });

  document.addEventListener('DOMContentLoaded', () => {
    contadorTitulo.textContent = `${inputTitulo.value.length}/150`;
    contadorDescripcion.textContent = `${textareaDescripcion.value.length}/3000`;
  });

function mostrarToast(mensaje, tipo = 'primary') {
      const toastContainer = document.createElement('div');
      toastContainer.className = 'toast-personalizado text-bg-' + tipo;
      toastContainer.setAttribute('role', 'alert');
      toastContainer.innerHTML = `<div class="contenido-toast">${mensaje}</div>`;
      document.body.appendChild(toastContainer);
      setTimeout(() => toastContainer.remove(), 7000);
  }

  document.addEventListener('DOMContentLoaded', () => {
  const form = document.querySelector('form');
  const modal = document.getElementById('modal-confirmacion');
  const btnCancelar = document.getElementById('btnCancelar');
  const btnConfirmar = document.getElementById('btnConfirmar');

  let formPendiente = null; // Guardamos el form temporalmente

  form.addEventListener('submit', function (e) {
    e.preventDefault(); // detenemos el envío
    formPendiente = this;
    modal.style.display = 'flex'; // mostramos modal
  });

  btnCancelar.addEventListener('click', () => {
    modal.style.display = 'none'; // ocultar modal
    formPendiente = null;
  });

  btnConfirmar.addEventListener('click', () => {
    modal.style.display = 'none';
    if (formPendiente) {
      formPendiente.submit(); // ahora sí enviamos el form
    }
  });
});

document.addEventListener('DOMContentLoaded', () => {
  const btnVolver = document.getElementById('btnVolver');
  const modalVolver = document.getElementById('modal-volver');
  const btnCancelarVolver = document.getElementById('btnCancelarVolver');
  const btnConfirmarVolver = document.getElementById('btnConfirmarVolver');

  btnVolver.addEventListener('click', function(e) {
    e.preventDefault(); // evitamos irnos directamente
    modalVolver.style.display = 'flex'; // mostramos modal
  });

  btnCancelarVolver.addEventListener('click', () => {
    modalVolver.style.display = 'none'; // cerramos modal
  });

  btnConfirmarVolver.addEventListener('click', () => {
    window.location.href = btnVolver.getAttribute('href'); // ahora sí volvemos
  });
});

document.addEventListener('DOMContentLoaded', function() {
      // Modal para visualización de imágenes
      const modalImagen = document.getElementById('modal-imagen');
      const imagenAmpliada = document.getElementById('imagen-ampliada');
      const nombreArchivoSpan = document.getElementById('nombre-archivo');
      const tipoArchivoSpan = document.getElementById('tipo-archivo');
      const tamañoArchivoSpan = document.getElementById('tamaño-archivo');
      const cerrarModal = document.querySelector('.modal-imagen .cerrar');
      
      // Función para formatear el tamaño del archivo
      function formatearTamaño(bytes) {
        if (bytes === 0) return '0 Bytes';
        const k = 1024;
        const sizes = ['Bytes', 'KB', 'MB', 'GB'];
        const i = Math.floor(Math.log(bytes) / Math.log(k));
        return parseFloat((bytes / Math.pow(k, i)).toFixed(2)) + ' ' + sizes[i];
      }
      
      // Función para abrir el modal con la imagen seleccionada
      function abrirModalImagen(src, file) {
        imagenAmpliada.src = src;
        nombreArchivoSpan.textContent = file.name;
        tipoArchivoSpan.textContent = file.type || 'image/jpeg';
        tamañoArchivoSpan.textContent = formatearTamaño(file.size);
        
        modalImagen.style.display = 'flex';
        document.body.style.overflow = 'hidden'; // Evitar scroll
        
        // Marcar la imagen como seleccionada
        const todasImagenes = document.querySelectorAll('#preview-imagenes img');
        todasImagenes.forEach(img => img.classList.remove('imagen-seleccionada'));
        
        // Encontrar y marcar la imagen correspondiente
        for (let img of todasImagenes) {
          if (img.src === src) {
            img.classList.add('imagen-seleccionada');
            break;
          }
        }
      }
      
      // Cerrar modal al hacer clic en la X
      cerrarModal.addEventListener('click', function() {
        modalImagen.style.display = 'none';
        document.body.style.overflow = 'auto'; // Restaurar scroll
      });
      
      // Cerrar modal al hacer clic fuera del contenedor
      modalImagen.addEventListener('click', function(e) {
        if (e.target === modalImagen) {
          modalImagen.style.display = 'none';
          document.body.style.overflow = 'auto';
        }
      });
      
      // Cerrar modal con tecla Escape
      document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape' && modalImagen.style.display === 'flex') {
          modalImagen.style.display = 'none';
          document.body.style.overflow = 'auto';
        }
      });
      
      // Delegación de eventos para las imágenes de vista previa
      document.addEventListener('click', function(e) {
        // Verificar si se hizo clic en una imagen de vista previa
        if (e.target.tagName === 'IMG' && 
            e.target.closest('#preview-imagenes') && 
            !e.target.closest('.eliminar')) {
          
          // Obtener el archivo correspondiente a esta imagen
          const index = Array.from(document.querySelectorAll('#preview-imagenes img')).indexOf(e.target);
          if (index !== -1 && archivosSeleccionados[index]) {
            abrirModalImagen(e.target.src, archivosSeleccionados[index]);
          }
        }
      });
});
</script>
</body>
</html>