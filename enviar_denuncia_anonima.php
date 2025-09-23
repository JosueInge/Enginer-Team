<?php
// -------------------- BACKEND PHP --------------------
session_start();

// Conexión a base de datos (ajusta credenciales)
$conexion = new mysqli("localhost", "root", "", "tu_base");
if ($conexion->connect_error) {
    die("Error en conexión: " . $conexion->connect_error);
}

$mensaje = "";
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $titulo = trim($_POST['titulo'] ?? "");
    $descripcion = trim($_POST['descripcion'] ?? "");
    $fecha_evento = trim($_POST['fecha_evento'] ?? "");
    $errores = [];

    // Validaciones servidor
    if (strlen($titulo) < 10 || strlen($titulo) > 150) {
        $errores[] = "El título debe tener entre 10 y 150 caracteres.";
    }
    if (strlen($descripcion) < 300 || strlen($descripcion) > 3000) {
        $errores[] = "La descripción debe tener entre 300 y 3000 caracteres.";
    }
    if (empty($fecha_evento)) {
        $errores[] = "La fecha del evento es obligatoria.";
    }
    if (!isset($_FILES['imagenes']) || $_FILES['imagenes']['error'][0] !== UPLOAD_ERR_OK) {
        $errores[] = "Debes subir al menos una imagen.";
    }

    if (empty($errores)) {
        // Guardar en base
        $stmt = $conexion->prepare("INSERT INTO eventos (titulo, descripcion, fecha_evento) VALUES (?, ?, ?)");
        $stmt->bind_param("sss", $titulo, $descripcion, $fecha_evento);
        if ($stmt->execute()) {
            $mensaje = "Evento publicado correctamente ✅";
        } else {
            $mensaje = "Error al guardar: " . $conexion->error;
        }
    } else {
        $mensaje = implode("<br>", $errores);
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
<<<<<<< HEAD
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
=======
<meta charset="UTF-8">
<title>Publicar Evento</title>
<style>
    body { font-family: Poppins, sans-serif; background:#f8f9fa; margin:0; padding:20px; }
    .contenedor { max-width:600px; margin:auto; background:#fff; padding:20px; border-radius:12px; box-shadow:0 2px 6px rgba(0,0,0,.1);}
    h2 { text-align:center; color:#1661AC; }
    label { display:block; margin-top:15px; font-weight:600; }
    input[type=text], textarea, input[type=date] {
        width:100%; padding:10px; border:1px solid #ccc; border-radius:8px; font-size:14px;
    }
    textarea { min-height:120px; resize:vertical; }
    .contador { font-size:12px; color:#666; float:right; }
    .contador.error { color:red; }
    .btn { margin-top:20px; width:100%; height:48px; background:#61C9A8; border:none; border-radius:12px;
           font-size:20px; font-weight:bold; color:#1B314B; cursor:pointer; }
    .btn:hover { background:#4CA88C; }
    .mensaje { margin-top:15px; padding:10px; background:#eee; border-radius:8px; color:#333; }
    #preview-imagenes img { width:100px; margin:5px; border-radius:8px; }
</style>
>>>>>>> 1c32133142b1ba498ce7a5b04b68cb7f13433626
</head>
<body>
<div class="contenedor">
    <h2>Publicar Evento</h2>
    <?php if ($mensaje): ?>
        <div class="mensaje"><?= $mensaje ?></div>
    <?php endif; ?>
    <form method="post" enctype="multipart/form-data" id="formEvento">
        <label for="titulo">Título</label>
        <span id="contadorTitulo" class="contador">0/150</span>
        <input type="text" id="titulo" name="titulo" maxlength="150" required>

        <label for="descripcion">Descripción</label>
        <span id="contadorDescripcion" class="contador">0/3000</span>
        <textarea id="descripcion" name="descripcion" maxlength="3000" required></textarea>

        <label for="fecha_evento">Fecha del evento</label>
        <input type="date" id="fecha_evento" name="fecha_evento" required>

        <label for="inputImagenes">Imágenes</label>
        <input type="file" id="inputImagenes" name="imagenes[]" accept="image/*" multiple required>
        <div id="preview-imagenes"></div>

        <button type="submit" class="btn" id="btnEnviar">Publicar</button>
    </form>
</div>

<script>
(function(){
  const titulo = document.getElementById('titulo');
  const descripcion = document.getElementById('descripcion');
  const contadorTitulo = document.getElementById('contadorTitulo');
  const contadorDescripcion = document.getElementById('contadorDescripcion');
  const inputImagenes = document.getElementById('inputImagenes');
  const preview = document.getElementById('preview-imagenes');

  // Contadores en vivo
  function actualizarContador(campo, contadorElem, max, min){
    const len = campo.value.length;
    contadorElem.textContent = len + "/" + max;
    if(len < (min||0) || len > max){
      contadorElem.classList.add('error');
    } else {
      contadorElem.classList.remove('error');
    }
  }
  titulo.addEventListener('input', ()=> actualizarContador(titulo, contadorTitulo, 150, 10));
  descripcion.addEventListener('input', ()=> actualizarContador(descripcion, contadorDescripcion, 3000, 300));

  // Previsualización imágenes
  inputImagenes.addEventListener('change', ()=>{
    preview.innerHTML = "";
    Array.from(inputImagenes.files).forEach(file=>{
      if(file.type.startsWith("image/")){
        const reader = new FileReader();
        reader.onload = e=>{
          const img = document.createElement('img');
          img.src = e.target.result;
          img.style.width = "100px";
          img.style.margin = "5px";
          img.style.borderRadius = "8px";
          preview.appendChild(img);
        };
        reader.readAsDataURL(file);
      }
    });
  });

  // Validación antes de enviar
  document.getElementById('formEvento').addEventListener('submit', function(e){
    let errores = [];

    if(titulo.value.trim().length < 10 || titulo.value.trim().length > 150){
      errores.push("El título debe tener entre 10 y 150 caracteres.");
    }
    if(descripcion.value.trim().length < 300 || descripcion.value.trim().length > 3000){
      errores.push("La descripción debe tener entre 300 y 3000 caracteres.");
    }
    if(!document.getElementById('fecha_evento').value){
      errores.push("La fecha del evento es obligatoria.");
    }
    if(inputImagenes.files.length === 0){
      errores.push("Debes subir al menos una imagen.");
    }

    if(errores.length > 0){
      e.preventDefault();
      alert("Corrige lo siguiente:\n\n" + errores.join("\n"));
    }
  });
})();
</script>
</body>
</html>
