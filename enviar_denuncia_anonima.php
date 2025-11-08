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

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
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
    $upload_dir = __DIR__ . '/imagenes/denuncias/';
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
                $mensajeToast = "Tu denuncia fue enviada a los administradores, primero será revisada y luego aprobada";
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
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Enviar Denuncia - Comunicado Digital</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600&family=Poppins:wght@400;600;700&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
  <script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
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

    @keyframes shake {
      0% { transform: translateX(0); }
      20% { transform: translateX(-2px); }
      40% { transform: translateX(2px); }
      60% { transform: translateX(-2px); }
      80% { transform: translateX(2px); }
      100% { transform: translateX(0); }
    }

    .shake {
      animation: shake 0.35s ease; /* más corto y suave */
    }

    .ripple {
        position: absolute;
        border-radius: 50%;
        transform: scale(0);
        animation: ripple-effect 0.6s linear;
        background: rgba(255, 255, 255, 0.6);
    }

    @keyframes ripple-effect {
        to {
            transform: scale(4);
            opacity: 0;
        }
    }

    header {
      background-color: #0d2740;
      color: #fff;
      padding: 12px 20px;
      display: flex;
      justify-content: space-between;
      align-items: center;
    }

    header .logo img {
      height: 45px;
    }

    .contenedor-principal {
      max-width: 750px;
      margin: 40px auto;
      padding: 5px;
      text-align: center;
    }

    .requerido:after {
      content: " *";
      color: red;
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
      background-color: #F8D7DA;   /* Fondo */
      border: 1px solid #F5C2C7;   /* Color de borde */
      color: #842029;              /* Texto */
      font-family: 'Inter', sans-serif; /* Tipografía */
      font-size: 16px;             /* Tamaño */
      text-align: left;            /* Alineación */
      padding: 10px 14px;
      border-radius: 8px;
      margin-top: 6px;
      display: block;
    }

    /* mensajes toast */
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
      outline: none;
    }

    .fechacampo.error-borde {
      border: 2px solid red !important;
      outline: none;
      border-radius: 12px;
    }
    /* Boton de volver a denuncias */

    .volverDenuncias {
      display: block;            
      font-family: "Poppins", sans-serif;
      font-size: 24px;      
      font-weight: 600;      
      color: #FFFFFF;          
      padding: 10px 15px;        
      text-decoration: none;      
    }

    .volverDenuncias:hover {
      color: #1661AC;           
      text-decoration: underline;  
    }

    .Subtítuloinformativo {
      color: #403F48;
      font-family: 'Inter', sans-serif;
      font-size: 20px;
    }

    /* Mensaje de modal, al volver a denuncias */
    .modal-overlay {
      position: fixed;
      top: 0; left: 0; right: 0; bottom: 0;
      background: rgba(0, 0, 0, 0.5);
      display: none; /* oculto por defecto */
      justify-content: center;
      align-items: center;
      z-index: 9999;
    }

    /* Caja del modal */
    .modal-contenido {
      background: #fff;
      padding: 25px;
      border-radius: 12px;
      width: 400px;
      text-align: center;
      font-family: 'Poppins', sans-serif;
    }

    /* Título */
    .modal-contenido h2 {
      font-size: 16px;
      color: #403F48;
      font-weight: bold;
      margin-bottom: 15px;
    }

    /* Texto */
    .modal-contenido p {
      font-family: 'Inter', sans-serif;
      font-size: 16px;
      color: #403F48;
      margin-bottom: 20px;
    }

    /* Contenedor de botones */
    .modal-botones {
      display: flex;
      justify-content: space-between;
    }

    /* Botón cancelar */
    .btn-cancelar {
      font-family: 'Inter', sans-serif;
      font-size: 16px;
      background: #EB7373;
      color: #061F3E;
      border: none;
      border-radius: 8px;
      cursor: pointer;
      width: 133px;
      height: 36px;

    }

    /* Botón confirmar */
    .btn-confirmar {
      font-family: 'Inter', sans-serif;
      font-size: 16px;
      background: #61C9A8;
      color: #FFFFFF;
      border: none;
      border-radius: 8px;
      cursor: pointer;
      width: 133px;
      height: 36px;
    }

    .tituloPrincipal {
      font-family: 'Poppins', sans-serif;
      font-size: 32px;
      font-weight: bold;
      color: #1661AC;
    }

    /* Todos los campos */
    .Camposdelformulario {
      text-align: left;
      margin-bottom: 25px;
    }

    /* etiqueta de los campos */
    .Camposdelformulario label {
      font-family: "Poppins", sans-serif;
      font-size: 16px;
      font-weight: 600;  
      color: #403F48;
      margin-bottom: 6px;
      display: block;  
    }

    /* campo de titulo */
    .titulocampo {
      font-family: 'Inter', sans-serif;
      font-size: 16px;
      font-weight: 400;
      border: 1px solid #B1B1B1; 
      border-radius: 12px;   
      padding: 12px;     
      outline: none;
      width: 100%;     
    }

    .titulocampo:hover {
      border-color: #2D8EFF;
      transform: scale(1.01);    
      box-shadow: 0 2px 5px rgba(0,0,0,0.1); 
      transition: border-color 0.4s ease, transform 0.4s ease, box-shadow 0.4s ease;
    }

    .titulocampo:focus {
      border-color: #2D8EFF;
      transform: scale(1.01);      
      box-shadow: 0 2px 5px rgba(0,0,0,0.1); 
      transition: border-color 0.4s ease, transform 0.4s ease, box-shadow 0.4s ease;
    }

    /* campo de descripcion */
    .descripcioncampo {
      font-family: "Inter", sans-serif;
      font-size: 16px;
      border: 1px solid #B1B1B1;
      border-radius: 12px;    
      padding: 12px;  
      outline: none;
      width: 100%;
      left: 20px;
    }

    .descripcioncampo:hover {
      border-color: #2D8EFF;
      transform: scale(1.01);    
      box-shadow: 0 2px 5px rgba(0,0,0,0.1); 
      transition: border-color 0.4s ease, transform 0.4s ease, box-shadow 0.4s ease;
    }

    .descripcioncampo:focus {
      border-color: #2D8EFF;
      transform: scale(1.01);      
      box-shadow: 0 2px 5px rgba(0,0,0,0.1); 
      transition: border-color 0.4s ease, transform 0.4s ease, box-shadow 0.4s ease;
    }

    /* contador de los dos campos*/
    .contadorDelTitulo, .contadorDeDescripcion { 
      font-family: 'Inter', sans-serif;
      font-size: 16px;
      color: #B1B1B1;
      float: right;
      margin-top: -30px;
      position: relative; 
      z-index: 9999; 
      margin-right: 15px;
      display: none;
    }

    .contador-activo {
      display: inline !important; /* contador */
    }

    .contador-error {
      color: #E33639 !important; /* contador */
    }

    .input-con-icono {
      position: relative;
      display: flex;
      align-items: center;
    }

    .input-con-icono input {
      padding-right: 40px;
      font-family: 'Inter', sans-serif;
      font-size: 16px;
      color: #B1B1B1;
      border: 1px solid #B1B1B1;
      border-radius: 12px;
      padding: 12px;
      outline: none;
      transition: border-color 0.3s ease;
      width: 100%; 
      box-sizing: border-box; 
    }

    .icono-calendario {
      position: absolute;
      right: 12px;       /* distancia desde el borde derecho */
      top: 50%;          /* centrar verticalmente */
      transform: translateY(-50%);
      pointer-events: none; /* la imagen no bloquea el input */
    }

    .icono-calendario img {
      width: 24px;  /* tamaño del icono, puedes ajustar */
      height: 24px;
      align-items: center;
    }

    .input-con-icono input:hover {
      border-color: #2D8EFF;
      transform: scale(1.01);    
      box-shadow: 0 2px 5px rgba(0,0,0,0.1); 
      transition: border-color 0.4s ease, transform 0.4s ease, box-shadow 0.4s ease;
    }

    .input-con-icono input:focus {
      border-color: #2D8EFF;
      transform: scale(1.01);      
      box-shadow: 0 2px 5px rgba(0,0,0,0.1); 
      transition: border-color 0.4s ease, transform 0.4s ease, box-shadow 0.4s ease;
    }

    /* Estilo para el calendario */
    .flatpickr-calendar {
        left: 50% !important;
        transform: translateX(-50%) !important;
        border: 2px solid #ADEBFF !important; /* borde celeste del calendario */
        border-radius: 8px !important;
    }

    /* se quita el circulo por defecto y se implementa un cuadro */
    .flatpickr-day {
        border-radius: 4px !important;  /* cuadrado en lugar de círculo */
        border: 1px solid transparent;  /* por defecto sin borde */
        background: transparent !important;
    }

    /* Estilo para el cuadro selector */
    .flatpickr-day.today {
        background: #ADEBFF !important; /* fondo celeste */
        color: #061F3E !important;      /* número oscuro */
        font-weight: bold;
        border: 1px solid #ADEBFF !important; /* borde del mismo color */
    }

    /* Estilo para el dia de hoy */
    .flatpickr-day.selected:not(.today) {
        background: transparent !important; 
        border: 2px solid #ADEBFF !important; /* borde celeste */
        color: #061F3E !important;
    }

    .flatpickr-day:hover {
        background: #D7F2FF !important; /* celeste claro al pasar mouse */
        border-radius: 4px !important;
    }

    .flatpickr-day.selected.today {
        background: #61C3E6 !important; /* celeste más intenso si hoy está seleccionado */
        color: #fff !important;
        border: 2px solid #ADEBFF !important;
    }

      /*texto Puedes cargar hasta 3 imágenes en formato (.jpeg).*/
    .text-muted {
      color: #403F48 !important;
      display: inline;
      font-family: 'Inter', sans-serif;
      font-size: 16px;
    }
    
     /* campo de imagenes */
    .contenedor-archivo {
      border: 1px solid #B1B1B1;
      border-radius: 12px;
      display: flex;
      padding: 0;
      flex-direction: column;
    }

    /* Input file como parte del mismo bloque */
    .contenedor-archivo input[type="file"] {
      border: none !important;      
      box-shadow: none !important;  
      padding: 0;
      display: flex;
    }

    .btn-subir {
      position: relative;
      overflow: hidden;
      background-color: #ADEBFF; 
      color: #061F3E;               
      border: none;
      padding: 6px 12px;
      border-radius: 8px;
      cursor: pointer;
      font-size: 16px;
      font-family: 'Inter', sans-serif;
      transition: background-color 0.2s ease;
      display: inline-block; 
      margin-left: 9px;       
      text-align: left;
      margin-top: 9px;    
      width: 150px;
      z-index: 1;
    }

    .btn-subir.disabled {
      background-color: #B1B1B1 !important;
      color: #061F3E !important;
      cursor: not-allowed;
      pointer-events: none; /* evita que se pueda volver a abrir */
    }

    .fila-boton-estado {
      display: flex;
      align-items: center;
      gap: 10px;          
    }

    /* mensaje "No se ha seleccionado ningun archivo" */
    #estado-archivo {
      color: #B1B1B1;
      font-size: 14px;
      line-height: normal;
    }

    .textoInfimag {
      font-family: 'Inter', sans-serif;
      font-size: 14px;
      color: #74737C;
    }

    .btn-subir:hover {
      background-color: #4CA88C;
      transform: translateY(-2px) scale(1.02);
      box-shadow: 0 6px 15px rgba(0,0,0,0.2);
      transition: all 0.3s ease;
    }

    .btn-subir:focus {
      outline: 2px solid #2D8EFF;
      transform: scale(1.01);      
      box-shadow: 0 2px 5px rgba(0,0,0,0.1); 
      transition: border-color 0.4s ease, transform 0.4s ease, box-shadow 0.4s ease;
    }

    .contenedor-archivo:hover {
      border-color: #2D8EFF;
      transform: scale(1.01);    
      box-shadow: 0 2px 5px rgba(0,0,0,0.1); 
      transition: border-color 0.4s ease, transform 0.4s ease, box-shadow 0.4s ease;
    }

    .contenedor-archivo:focus {
      border-color: #2D8EFF;
      transform: scale(1.01);      
      box-shadow: 0 2px 5px rgba(0,0,0,0.1); 
      transition: border-color 0.4s ease, transform 0.4s ease, box-shadow 0.4s ease; 
    }
    
    /* visualizacion de las imagenes dentro del mismo contenedor */
    #preview-imagenes {
      display: flex!important;
      gap: 15px;
      flex-wrap: wrap;
      padding-bottom: 5px;
      align-items: flex-start;
      margin-top: 3px;
    }
    /* contenedor de las imagenes, nombre y "x" */
    .imagen-preview {
      display: flex;
      flex-direction: column;
      align-items: center;
      width: 350px;   /* todas las tarjetas del mismo ancho */
      margin-left: 5px;
    }

    /* imagenes */
    .imagen-preview img {
      border: 1px solid #B1B1B1;
      border-radius: 8px;
      padding: 8px;
      width: 100%;      /* ancho fijo del contenedor */
      height: 200px;    /* altura uniforme */
      object-fit: cover; 
      background: #f9f9f9;
      display: block;
    }

    .imagen-preview img:hover {
      border-color: #2D8EFF; 
    }

    .imagen-preview img:focus {
      border-color: #2D8EFF; 
    }

    .imagen-preview .info { 
      display: flex; 
      justify-content: space-between; 
      width: 100%;
      margin-top: 5px; 
    } 
  
    .imagen-preview .nombre { font-size: 0.8rem; 
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

    /* Modal para ver imagen ampliada */
    .modal-imagen-overlay {
      position: fixed;
      top: 0; left: 0; right: 0; bottom: 0;
      background: rgba(0,0,0,0.4);
      display: none; /* oculto por defecto */
      justify-content: center;
      align-items: center;
      z-index: 10000;
    }

    .modal-imagen-contenido {
      background: #fff;
      width: 800px;   /* tamaño fijo */
      height: 600px;  /* tamaño fijo */
      border-radius: 12px;
      position: relative;
      display: flex;
      justify-content: center;
      align-items: center;
      overflow: hidden;
    }

    .modal-imagen-contenido img {
      width: 550px;   
      height: 550px; 
      object-fit: contain;  
      border-radius: 8px;
    }

    .modal-imagen-cerrar {
      position: absolute;
      top: 10px;
      right: 10px;
      background: #E33639;
      color: #fff;
      border: none;
      font-size: 24px;
      width: 35px;
      height: 35px;
      border-radius: 50%;
      cursor: pointer;
      display: flex;
      align-items: center;
      justify-content: center;
      z-index: 10001;
    }

    .boton-publicar {
      position: relative;
      overflow: hidden;
      font-family: "Poppins", sans-serif;
      background-color: #61C9A8;
      color: #1B314B;
      border: none;
      padding: 12px;
      border-radius: 25px;
      font-size: 20px;
      font-weight: bold;
      width: 200px;
      height: 50px;
      margin-top: 15px;
      transition: background 0.3s;
    }

    .boton-publicar:hover {
      background-color: #4CA88C;
      transform: translateY(-2px) scale(1.02);
      box-shadow: 0 6px 15px rgba(0,0,0,0.2);
      transition: all 0.3s ease;
    }

    .boton-publicar:focus {
      outline: 2px solid #2D8EFF;
      transform: scale(1.01);      
      box-shadow: 0 2px 5px rgba(0,0,0,0.1); 
      transition: border-color 0.4s ease, transform 0.4s ease, box-shadow 0.4s ease;
    }
/* ALERT INFORMATIVO */
     .alert-informacion{
      display:flex;
      gap:12px;
      align-items:flex-start;
      background:#ADEBFF;
      padding:12px 16px;
      border-radius:12px;
      margin:12px auto 22px;
      max-width:820px;
      color:var(--gris-oscuro);
      font-family:'Inter';
      font-size:16px;
    }
    .alert-informacion i{
      font-size:20px;
      color:var(--gris-oscuro);
      margin-top:2px;
    }


  </style>
</head>
<body>
  <header>
    <div class="logo">
      <img src="imagenes/logo.png" alt="logo">
    </div>
    <h3><a class="volverDenuncias" href="denuncia_anonima.php">Volver a Denuncias</a></h3>
  </header>

  <div class="contenedor-principal">
    <h1 class="tituloPrincipal" >Enviar denuncia anonima</h1>
    <p class="Subtítuloinformativo">Tu denuncia será revisada por los administradores</p>
    
    <div class="alert-informacion" role="status">
        <i class="fas fa-shield-alt"></i>
        <div>Tu denuncia es 100 % anónima. No pediremos datos personales ni podremos rastrear tu identidad.</div>
      </div>

    <form action="enviar_denuncia_anonima.php" method="POST" enctype="multipart/form-data">

      <div class="Camposdelformulario">
        <label for="titulo" class="requerido">Título:</label>
        <input class="titulocampo" type="text" id="titulo" name="titulo" placeholder="Escribe el título de tu denuncia" value="<?= htmlspecialchars($titulo ?? '') ?>">
        <small class="contadorDelTitulo" id="contadorTitulo">0/150</small>
      </div>

      <div class="Camposdelformulario">
        <label for="descripcion" class="requerido">Descripción:</label>
        <textarea class="descripcioncampo" id="descripcion" name="descripcion" placeholder="Escribe la descripción de tu denuncia"><?= htmlspecialchars($descripcion ?? '') ?></textarea>
        <small class="contadorDeDescripcion" id="contadorDescripcion">0/3000</small>
      </div>

      <div class="Camposdelformulario">
        <label for="fecha_evento" class="requerido">Fecha del evento denunciado:</label>
        <div class="input-con-icono">
          <input type="text" id="fecha_evento" name="fecha_evento" placeholder="Selecciona la fecha" class="fechacampo" value="<?= htmlspecialchars($fecha_evento ?? '') ?>">
          <span class="icono-calendario">
            <img src="imagenes/calendario.png" alt="Calendario">
          </span>
        </div>
        </div>

      <div class="Camposdelformulario">
        <div class="d-flex justify-content-between align-items-center mb-1">
          <label for="imagenes" class="fw-semibold">Carga una imagen (Opcional)</label>
          <span class="text-muted">Puedes cargar hasta 3 imágenes en formato (.jpeg).</span>
        </div>

        <div class="contenedor-archivo">
          <input type="file" id="imagenes" name="imagenes[]" accept="image/jpeg" multiple class="form-control custom-file-input" style="display: none;">

          <div class="fila-boton-estado">
            <label for="imagenes" class="btn-subir">Elegir archivos</label>
            <span class="textoInfimag" id="estado-archivo">No se ha seleccionado ningún archivo</span>
          </div>

          <div id="preview-imagenes"></div>
        </div>
      </div>

      <button class="boton-publicar" type="submit">Enviar denuncia</button>
    </form>
  </div>
<?php if ($mensajeToast): ?>

<?php endif; ?>

<script>
let archivosSeleccionados = []; // Global para todo el script

document.addEventListener('DOMContentLoaded', function () {
  <?php if ($mensajeToast): ?>
    mostrarToast("<?php echo htmlspecialchars($mensajeToast); ?>", "<?php echo $tipoToast; ?>");
    <?php if ($tipoToast === 'success'): ?>
    setTimeout(()=>{ window.location.href = 'denuncia_anonima.php'; }, 4000);
    <?php endif; ?>
<?php endif; ?>

    // --- Contadores de título y descripción ---
    const inputTitulo = document.getElementById('titulo');
    const contadorTitulo = document.getElementById('contadorTitulo');
    const textareaDescripcion = document.getElementById('descripcion');
    const contadorDescripcion = document.getElementById('contadorDescripcion');

    function actualizarContador(input, contador, min, max) {
        const longitud = input.value.length;
        contador.textContent = `${longitud}/${max}`;
        if (longitud < min || longitud > max) {
            contador.classList.add('contador-error');
        } else {
            contador.classList.remove('contador-error');
        }
    }

    inputTitulo.addEventListener('focus', () => { contadorTitulo.classList.add('contador-activo'); actualizarContador(inputTitulo, contadorTitulo, 10, 150); });
    inputTitulo.addEventListener('blur', () => { contadorTitulo.classList.remove('contador-activo'); });
    inputTitulo.addEventListener('input', () => { actualizarContador(inputTitulo, contadorTitulo, 10, 150); });

    textareaDescripcion.addEventListener('focus', () => { contadorDescripcion.classList.add('contador-activo'); actualizarContador(textareaDescripcion, contadorDescripcion, 300, 3000); });
    textareaDescripcion.addEventListener('blur', () => { contadorDescripcion.classList.remove('contador-activo'); });
    textareaDescripcion.addEventListener('input', () => { actualizarContador(textareaDescripcion, contadorDescripcion, 300, 3000); });

    // --- Validaciones de inputs título y descripción en tiempo real ---
    const inputs = [inputTitulo, textareaDescripcion];
    inputs.forEach(input => {
        input.addEventListener('input', function () { 
            const valor = this.value.trim();
            let errorMsg = this.parentNode.querySelector('.mensaje-error');
            if (errorMsg) errorMsg.remove();
            this.classList.remove('error-borde');

            if (valor === '') {
                mostrarError(this, 'Complete este campo');
            } else if (this.id === 'titulo') {
                if (valor.length < 10) mostrarError(this, 'El título debe tener al menos 10 caracteres.');
                else if (valor.length > 150) mostrarError(this, 'El título no debe exceder 150 caracteres.');
            } else if (this.id === 'descripcion') {
                if (valor.length < 300) mostrarError(this, 'La descripción debe tener al menos 300 caracteres.');
                else if (valor.length > 3000) mostrarError(this, 'La descripción no debe exceder 3000 caracteres.');
            }
        });
    });

    function mostrarError(input, mensaje) {
        let contenedor = input.closest('.Camposdelformulario');

        // Evitar mensajes duplicados
        if (contenedor.querySelector('.mensaje-error')) return;

        let errorMsg = document.createElement('div');
        errorMsg.className = 'mensaje-error';
        errorMsg.textContent = mensaje;
        contenedor.appendChild(errorMsg);

        if (input.id === 'fecha_evento') {
            if (flatpickrFecha.altInput) flatpickrFecha.altInput.classList.add('error-borde');
            flatpickrFecha.altInput.classList.add('shake'); // <--- animación
            setTimeout(() => flatpickrFecha.altInput.classList.remove('shake'), 500);
        } else {
            input.classList.add('error-borde');
            input.classList.add('shake'); // <--- animación
            setTimeout(() => input.classList.remove('shake'), 500);
        }
        setTimeout(() => {
          if (errorMsg && errorMsg.parentNode) {
            errorMsg.remove();
          }
          if (input.id === 'fecha_evento') {
            if (flatpickrFecha.altInput) {
                flatpickrFecha.altIput.classList.remove('error-borde');
            }
          } else {
            input.classList.remove('error-borde');
          }
        }, 5000);
    }

    // --- Manejo de input de imágenes ---
    const fileInput = document.getElementById('imagenes');
    const previewContainer = document.getElementById('preview-imagenes');
    const estado = document.getElementById('estado-archivo');

    fileInput.addEventListener('change', function() {
        const nuevosArchivos = Array.from(this.files);
        const validos = nuevosArchivos.filter(file => {
            const name = file.name.toLowerCase();
            return file.type === 'image/jpeg' && (name.endsWith('.jpg') || name.endsWith('.jpeg'));
        });
        if (validos.length < nuevosArchivos.length) {
            const modalFormato = document.getElementById('modalFormato');
            modalFormato.style.display = 'flex';
            modalFormato.classList.add('shake'); // <--- animación
            setTimeout(() => modalFormato.classList.remove('shake'), 500);

            const btnCerrarModalFormato = document.getElementById('btnCerrarModalFormato');
            btnCerrarModalFormato.onclick = () => {
                modalFormato.style.display = 'none';
            };
        }

        archivosSeleccionados = archivosSeleccionados.concat(validos);

        if (archivosSeleccionados.length > 3) {
            mostrarToast('Solo puedes seleccionar hasta 3 imágenes.', 'danger');
            archivosSeleccionados = archivosSeleccionados.slice(0,3);
        }

        actualizarPreview();
        fileInput.value = ''; // limpiar input para volver a seleccionar
    });

    function actualizarPreview() {
        previewContainer.innerHTML = '';
        archivosSeleccionados.forEach((file, index) => {
            const reader = new FileReader();
            reader.onload = function(e) {
                const wrapper = document.createElement('div');
                wrapper.className = 'imagen-preview';

                const img = document.createElement('img');
                img.src = e.target.result;
                img.alt = file.name;
                wrapper.appendChild(img);

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
                    archivosSeleccionados.splice(index,1);
                    actualizarPreview();
                });
                info.appendChild(btnX);

                wrapper.appendChild(info);
                previewContainer.appendChild(wrapper);
            }
            reader.readAsDataURL(file);
        });

        estado.textContent = archivosSeleccionados.length > 0
            ? `${archivosSeleccionados.length} archivo(s) seleccionado(s)`
            : 'No se ha seleccionado ningun archivo';

        const dt = new DataTransfer();
        archivosSeleccionados.forEach(f => dt.items.add(f));
        fileInput.files = dt.files;

        const botonSubir = document.querySelector('.btn-subir');
        if (archivosSeleccionados.length >= 3) {
            mostrarToast('Has alcanzado el límite de 3 imágenes.', 'danger');
            botonSubir.classList.add('disabled');
        } else {
            botonSubir.classList.remove('disabled');
        }
    }

    // --- Modal de confirmación de envío ---
    const form = document.querySelector('form');
    const modalEnvio = document.getElementById('modalConfirmarEnvio');
    const btnCancelarEnvio = document.getElementById('btnCancelarEnvio');
    const btnConfirmarEnvio = document.getElementById('btnConfirmarEnvio');
    const fecha_evento = document.getElementById('fecha_evento');

      form.addEventListener('submit', function(e) {
          e.preventDefault();

          // --- Limpiar errores previos debajo de los inputs ---
          [inputTitulo, textareaDescripcion, fecha_evento].forEach(input => {
              let contenedor = input.closest('.Camposdelformulario');
              let errorMsg = contenedor.querySelector('.mensaje-error');
              if (errorMsg) errorMsg.remove();

              if (input.id === 'fecha_evento') {
                  if (flatpickrFecha.altInput) {
                      flatpickrFecha.altInput.classList.remove('error-borde');
                  }
              } else {
                  input.classList.remove('error-borde');
              }
          });

          const tituloVal = inputTitulo.value.trim();
          const descripcionVal = textareaDescripcion.value.trim();
          const fechaVal = fecha_evento.value.trim();

          // --- Validaciones campo por campo ---
          if (tituloVal === '') {
              mostrarError(inputTitulo, 'El titulo es obligatorio.');
              return;
          }
          if (descripcionVal === '') {
              mostrarError(textareaDescripcion, 'La descripción es obligatoria.');
              return;
          }
          if (fechaVal === '') {
              mostrarError(fecha_evento, 'La fecha es obligatoria.');
              return;
          }

          // --- Validaciones ---
          if (tituloVal.length < 10) { 
              mostrarError(inputTitulo, 'El título debe tener al menos 10 caracteres.');
              return; 
          }
          if (tituloVal.length > 150) { 
              mostrarError(inputTitulo, 'El título no debe exceder 150 caracteres.');
              return; 
          }
          if (descripcionVal.length < 300) { 
              mostrarError(textareaDescripcion, 'La descripción debe tener al menos 300 caracteres.');
              return; 
          }
          if (descripcionVal.length > 3000) { 
              mostrarError(textareaDescripcion, 'La descripción no debe exceder 3000 caracteres.');
              return; 
          }

          // --- Si todas las validaciones pasan, mostrar modal ---
          modalEnvio.style.display = 'flex';
      });

    btnCancelarEnvio.addEventListener('click', () => { modalEnvio.style.display = 'none'; });
    btnConfirmarEnvio.addEventListener('click', () => {
        modalEnvio.style.display = 'none';

        const dt = new DataTransfer();
        archivosSeleccionados.forEach(f => dt.items.add(f));
        fileInput.files = dt.files;

        form.submit();
    });

    // --- Toast centrado ---
    function mostrarToast(mensaje, tipo = 'danger') {
        const toastContainer = document.createElement('div');
        toastContainer.className = 'toast-personalizado text-bg-' + tipo;
        toastContainer.setAttribute('role','alert');
        toastContainer.innerHTML = `<div class="contenido-toast">${mensaje}</div>`;
        document.body.appendChild(toastContainer);
        setTimeout(()=> toastContainer.remove(),7000);
    }

    // --- Volver a denuncias con modal ---
    const linkVolver = document.querySelector(".volverDenuncias");
    const modalVolver = document.getElementById("modalVolver");
    const btnCancelar = document.getElementById("btnCancelar");
    const btnConfirmar = document.getElementById("btnConfirmar");

    linkVolver.addEventListener("click", function(e) {
        e.preventDefault();
        modalVolver.style.display = "flex";
    });
    btnCancelar.addEventListener("click", () => { modalVolver.style.display = "none"; });
    btnConfirmar.addEventListener("click", () => { window.location.href = "denuncia_anonima.php"; });

    // --- Modal de imagen ampliada ---
    const modalImagen = document.getElementById('modalImagen');
    const imagenAmpliada = document.getElementById('imagenAmpliada');
    const btnCerrar = document.getElementById('cerrarModalImagen');
    const nombreImagenAmpliada = document.getElementById('nombreImagenAmpliada');

    previewContainer.addEventListener('click', function(e){
        if(e.target.tagName==='IMG'){
            imagenAmpliada.src = e.target.src;
            nombreImagenAmpliada.textContent = e.target.alt; // <-- nombre de la imagen
            modalImagen.style.display = 'flex';
        }
    });

    btnCerrar.addEventListener('click', ()=>{
        modalImagen.style.display='none';
        imagenAmpliada.src='';
        nombreImagenAmpliada.textContent = '';
    });

    modalImagen.addEventListener('click', function(e){
        if(e.target===modalImagen){
            modalImagen.style.display='none';
            imagenAmpliada.src='';
            nombreImagenAmpliada.textContent = '';
        }
    });

    // --- Flatpickr fecha ---
    const flatpickrFecha = flatpickr("#fecha_evento", {
        dateFormat: "Y-m-d",
        allowInput: false,
        altInput: true,
        altFormat: "d-m-Y",
        defaultDate: "<?= htmlspecialchars($fecha_evento ?? '') ?>",
        onChange: function(selectedDates, dateStr, instance) {
            // Si ya hay una fecha seleccionada, limpiar error
            if (dateStr.trim() !== "") {
                let contenedor = instance.input.closest('.Camposdelformulario');
                let errorMsg = contenedor.querySelector('.mensaje-error');
                if (errorMsg) errorMsg.remove();
                if (instance.altInput) {
                    instance.altInput.classList.remove('error-borde');
                }
            }
        }
    });

    // --- Mostrar toast si backend envía mensaje ---
    <?php if ($mensajeToast): ?>
        mostrarToast("<?php echo htmlspecialchars($mensajeToast); ?>", "<?php echo $tipoToast; ?>");
        <?php if ($tipoToast === 'success'): ?>
        setTimeout(()=>{ window.location.href = 'denuncia_anonima.php'; }, 4000);
        <?php endif; ?>
    <?php endif; ?>
});

  document.querySelectorAll('.boton-publicar, .btn-subir').forEach(button => {
      button.addEventListener('click', function(e) {
          const circle = document.createElement('span');
          circle.classList.add('ripple');
          const rect = this.getBoundingClientRect();
          const size = Math.max(rect.width, rect.height);
          circle.style.width = circle.style.height = size + 'px';
          circle.style.left = (e.clientX - rect.left - size/2) + 'px';
          circle.style.top = (e.clientY - rect.top - size/2) + 'px';
          this.appendChild(circle);

          // Eliminar el span después de la animación
          setTimeout(() => circle.remove(), 600);
      });
  });

</script>

  <div id="modalVolver" class="modal-overlay">
    <div class="modal-contenido">
      <h2>¿Estás seguro de volver a la vista de denuncias?</h2>
      <p>Esta acción cancelará los cambios hechos en el formulario</p>
      <div class="modal-botones">
        <button id="btnCancelar" class="btn-cancelar">No, volver</button>
        <button id="btnConfirmar" class="btn-confirmar">Sí, confirmar</button>
      </div>
    </div>
  </div>

  <div id="modalImagen" class="modal-imagen-overlay">
    <div class="modal-imagen-contenido">
      <button id="cerrarModalImagen" class="modal-imagen-cerrar">&times;</button>
      <img src="" alt="Imagen ampliada" id="imagenAmpliada">
      <!-- Contenedor del nombre de la imagen -->
      <div id="nombreImagenAmpliada" style="position:absolute; bottom:10px; left:50%; transform:translateX(-50%);
          background:rgba(0,0,0,0.6); color:#fff; padding:5px 10px; border-radius:5px; font-size:14px;">
      </div>
    </div>
  </div>

  <div id="modalConfirmarEnvio" class="modal-overlay">
    <div class="modal-contenido">
      <h2 style="font-family: 'Poppins', sans-serif; font-size:16px; color:#403F48; font-weight:bold; text-align:center;">
        ¿Estas seguro de enviar tu denuncia?
      </h2>
      <p style="font-family: 'Inter', sans-serif; font-size:16px; color:#403F48; text-align:center; margin-top:10px;">
        Un administrador la revisará y publicará posteriormente.
      </p>
      <div class="modal-botones" style="margin-top:20px;">
        <button id="btnCancelarEnvio" class="btn-cancelar">Cancelar</button>
        <button id="btnConfirmarEnvio" class="btn-confirmar">Confirmar</button>
      </div>
    </div>
  </div>

  <!-- Modal error formato -->
  <div id="modalFormato" class="modal-overlay">
    <div class="modal-contenido">
      <p style="font-family: 'Inter', sans-serif; font-size:20px; color:#403F48;">
        El formato de tu archivo no es admitido. Intenta cargar un archivo (.jpeg).
      </p>
      <div style="margin-top:20px;">
        <button id="btnCerrarModalFormato" 
          style="font-family:'Poppins', sans-serif; font-size:20px; color:#061F3E; background:#61C9A8; border:none; padding:10px 20px; border-radius:8px; cursor:pointer;">
          De acuerdo
        </button>
      </div>
    </div>
  </div>

</body>
</html>