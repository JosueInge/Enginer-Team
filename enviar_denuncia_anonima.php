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
