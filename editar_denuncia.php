<?php
session_start();
include 'conexion.php';

if (!isset($_SESSION['usuario_id']) || $_SESSION['usuario_rol'] !== 'Administrador') {
    header("Location: login.php");
    exit();
}

$mensajeToast = null;
$error = null;

if (!isset($_GET['id'])) { header("Location: noticias.php"); exit(); }

$id_noticia = intval($_GET["id"]);

// Obtener noticia
$stmt = $conexion->prepare("SELECT * FROM propuestas_denuncias WHERE id = ?");
$stmt->bind_param("i", $id_noticia);
$stmt->execute();
$noticia = $stmt->get_result()->fetch_assoc();
$stmt->close();

if (!$noticia) { header("Location: noticia.php"); exit(); }

if ($noticia['fecha_evento'] === '0000-00-00' || empty($noticia['fecha_evento'])) {
    $noticia['fecha_evento'] = '';
}

$rutaBase = 'imagenes/denuncias/';
$cols = ['imagen', 'imagen2', 'imagen3']; // columnas válidas

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    // Datos principales
    $titulo = $_POST['titulo'] ?? '';
    $descripcion = $_POST['descripcion'] ?? '';
    $fecha_evento = $_POST['fecha_evento'] ?? '';

    // Imágenes a borrar
    $imagenes_para_borrar = json_decode($_POST['imagenes_para_borrar'] ?? '[]', true);
    if (!is_array($imagenes_para_borrar)) $imagenes_para_borrar = [];

    // Actualizar datos
    $stmt = $conexion->prepare(
        "UPDATE propuestas_denuncias SET titulo = ?, descripcion = ?, fecha_evento = ? WHERE id = ?"
    );
    $stmt->bind_param("sssi", $titulo, $descripcion, $fecha_evento, $id_noticia);
    if (!$stmt->execute()) { $error = "Error al actualizar: " . $conexion->error; }
    $stmt->close();

    //  Borrar imágenes
    foreach ($imagenes_para_borrar as $imgNombre) {
        $imgNombre = basename($imgNombre);
        $rutaCompleta = $rutaBase . $imgNombre;

        if (file_exists($rutaCompleta)) @unlink($rutaCompleta);

        foreach ($cols as $col) {
            if ($noticia[$col] === $imgNombre) {
                $stmt2 = $conexion->prepare("UPDATE propuestas_denuncias SET $col = NULL WHERE id = ?");
                $stmt2->bind_param("i", $id_noticia);
                $stmt2->execute();
                $stmt2->close();
                $noticia[$col] = null;
            }
        }
    }

    //  Subir nuevas imágenes
    if (!empty($_FILES['imagenes']['name'][0])) {

        // Obtener columnas disponibles actualizadas
        $stmt = $conexion->prepare("SELECT imagen, imagen2, imagen3 FROM propuestas_denuncias WHERE id = ?");
        $stmt->bind_param("i", $id_noticia);
        $stmt->execute();
        $imagenesActuales = $stmt->get_result()->fetch_assoc();
        $stmt->close();

        foreach ($_FILES['imagenes']['tmp_name'] as $index => $tmpName) {

            if (!is_uploaded_file($tmpName)) continue;

            $nombreOriginal = $_FILES['imagenes']['name'][$index];
            $extension = strtolower(pathinfo($nombreOriginal, PATHINFO_EXTENSION));

            if (!in_array($extension, ['jpg', 'jpeg'])) continue;

            // Crear nombre único y mover archivo
            $nuevoNombre = uniqid('img_', true) . '.' . $extension;
            move_uploaded_file($tmpName, $rutaBase . $nuevoNombre);

            // Colocar en primera columna libre
            foreach ($cols as $col) {
                if (empty($imagenesActuales[$col])) {

                    // Guardar nombre original según columna (imagen_nombre_original, imagen2_nombre_original, ...)
                    $col_nombre_original = $col . "_nombre_original";

                    $stmt = $conexion->prepare("UPDATE propuestas_denuncias SET $col = ?, $col_nombre_original = ? WHERE id = ?");
                    $stmt->bind_param("ssi", $nuevoNombre, $nombreOriginal, $id_noticia);
                    $stmt->execute();
                    $stmt->close();

                    $imagenesActuales[$col] = $nuevoNombre;
                    break;
                }
            }
        }
    }

    // Redirige a denuncias
    if (empty($error)) {
        $mensajeToast = true;  // Activar toast
    }
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>Editar Denuncia</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600&family=Inter&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
    <script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
    <style>

      * {
        box-sizing: border-box;
      }

      body {
        font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        background: #f9fafb;
        margin: 0;
        color: #333;
        line-height: 1.6;
      }
      header {
        background-color: #061F3E;
        color: #fff;
        padding: 12px 24px;
        display: flex;
        justify-content: space-between;
        align-items: center;
        box-shadow: 0 2px 6px rgb(13 92 155 / 0.4);
        position: sticky;
        top: 0;
        z-index: 100;
      }

      /* MODAL PARA ªVOLVER A DENUNCIASª*/
      .modal {
        display: none; 
        position: fixed;
        z-index: 9999;
        left: 0; top: 0;
        width: 100%; height: 100%;
        background-color: rgba(0,0,0,0.4);
      }

      /*Contenedor del modal*/
      .modal-contenido {
        background-color: #fff;
        margin: 15% auto;
        padding: 25px 30px;
        border-radius: 12px;
        width: 90%;
        max-width: 460px;
        text-align: center;
        box-shadow: 0 4px 16px rgba(0,0,0,0.2);
        font-family: 'Poppins', sans-serif;
      }

      /*Título del modal*/
      .modal-contenido h2 {
        font-size: 16px;
        color: #403F48;
        font-weight: bold;
        margin-bottom: 28px;
        font-weight: bold;
      }

      /*Botones del modal*/
      .botones {
        display: flex;
        justify-content: center;
        gap: 50px; 
        margin-top: 20px;
      }

      /*Cancelar (modal)*/
      .btnCancelar {
        position: relative;
        background-color: #EB7373;
        color: #061F3E;
        font-family: 'Inter', sans-serif;
        font-size: 16px;
        overflow: hidden;
        border: none;
        padding: 10px 20px;
        border-radius: 8px;
        cursor: pointer;
        transition: background-color 0.2s ease;
        z-index: 0;
      }
      /*Confirmar (modal)*/
      .btnConfirmar {
        position: relative;
        background-color: #61C9A8;
        color: #061F3E;
        font-family: 'Inter', sans-serif;
        font-size: 16px;
        overflow: hidden;
        border: none;
        padding: 10px 20px;
        border-radius: 8px;
        cursor: pointer;
        transition: background-color 0.2s ease;
        z-index: 0;
      }
        
      /*Efectos hover*/
      .btnCancelar:hover {
        background-color: #e05e5e;
        transform: translateY(-2px) scale(1.02);
        box-shadow: 0 6px 15px rgba(0,0,0,0.2);
        transition: all 0.3s ease;
      }

      .btnCancelar:focus {
        outline: 2px solid #e05e5e;
        transform: scale(1.01);      
        box-shadow: 0 2px 5px rgba(0,0,0,0.1); 
        transition: border-color 0.4s ease, transform 0.4s ease, box-shadow 0.4s ease;
      }

      .btnConfirmar:hover {
        background-color: #4eb892;
        transform: translateY(-2px) scale(1.02);
        box-shadow: 0 6px 15px rgba(0,0,0,0.2);
        transition: all 0.3s ease;
      }

      .btnConfirmar:focus {
        outline: 2px solid #4eb892;
        transform: scale(1.01);      
        box-shadow: 0 2px 5px rgba(0,0,0,0.1); 
        transition: border-color 0.4s ease, transform 0.4s ease, box-shadow 0.4s ease;
      }

      .logo img {
        height: 48px;
        user-select: none;
      }

      nav.informacion a {
        font-weight: 600;
        font-size: 24px;
        text-decoration: none;
        transition: color 0.3s ease, text-decoration 0.3s ease;
        font-family: 'Poppins', sans-serif;
        color: #ffffff;
      }
      
      nav.informacion a:hover,
      nav.informacion a:focus {
        color: #1661AC;
        text-decoration: underline;
      }

      .contenedor-principal {
        max-width: 750px;
        margin: 40px auto;
        padding: 5px;
        text-align: center;
      }

      h1 {
        color: #1661AC;
        margin-bottom: 25px;
        font-weight: 700;
        font-size: 32px;
        text-align: center;
        font-family: 'Poppins', sans-serif;
      }

      .requerido:after {
        content: " *";
        color: red;
      }

      /* contenedor de cada campo */
      .campo {
        margin-bottom: 22px;
      }

      /*etiquetas de todos los campos*/
      .campo label {
        display: block;
        font-weight: 600;
        margin-bottom: 8px;
        color: #403F48;
        font-size: 16px;
        user-select: none;
        font-family: 'Poppins', sans-serif;
        text-align: left;
      }

      /*campo titulo*/
      .titulocampo {
        font-family: 'Inter', sans-serif;
        font-size: 16px;
        border: 1px solid #B1B1B1;
        border-radius: 12px;   
        padding: 10px;     
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

      /*campo descripcion*/
      .descripcioncampo {
        font-family: "Inter", sans-serif;
        font-size: 16px;
        border: 1px solid #B1B1B1;
        border-radius: 12px;    
        padding: 10px; 
        outline: none;
        width: 100%;
        height: 160px;
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

      /* Estilos para el input con icono de calendario */
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
        color: #B1B1B1;
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

      /* Texto: Puedes cargar hasta 3 imágenes en formato...*/
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
        padding: 12px;
        display: flex;
        align-items: center;
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

      /*Texto: No se ha seleccionado ningún archivo */
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
        border-radius: 12px;
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
        justify-content: left; 
        width: 100%;
        margin-top: 5px; 
      } 
      
      .imagen-preview .nombre { font-size: 0.8rem; 
        overflow: hidden; 
        text-overflow: ellipsis; 
        white-space: nowrap;
        margin-left: 15px;
      } 
      
      .imagen-preview .eliminar { 
        background: white; 
        color: #E33629; 
        border: none; 
        border-radius: 50%; 
        cursor: pointer; 
        width: 20px; 
        height: 20px; 
        line-height: 18px; 
        text-align: center; 
        font-weight: bold; 
      }

      /* botones publicar y cancelar */
      .botonesDelFormualario {
        display: flex;
        justify-content: center;
        gap: 150px;
        align-items: center;
        text-align: center;
      }
        
      .boton-publicar {
        background-color: #61C9A8;
        color: #1B314B;
        overflow: hidden;
        border: none;
        border-radius: 23px;
        cursor: pointer;
        font-size: 20px;
        font-weight: 700;
        width: 150px;
        height: 50px;
        transition: background-color 0.2s ease;
        user-select: none;
        font-family: 'Poppins', sans-serif;
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

      .boton-cancelar {
        background-color: #E85D5D;
        color: #1B314B;
        overflow: hidden;
        border: none;
        border-radius: 23px;
        cursor: pointer;
        font-size: 20px;
        font-weight: 700;
        width: 150px;
        height: 50px;
        transition: background-color 0.3s ease, box-shadow 0.3s ease;
        user-select: none;
        font-family: 'Poppins', sans-serif;  
      }

      .boton-cancelar:hover {
        background-color: #E53935;
        transform: translateY(-2px) scale(1.02);
        box-shadow: 0 6px 15px rgba(0,0,0,0.2);
        transition: all 0.3s ease;
      }

      .boton-cancelar:focus {
        outline: 2px solid #E53935;
        transform: scale(1.01);      
        box-shadow: 0 2px 5px rgba(0,0,0,0.1); 
        transition: border-color 0.4s ease, transform 0.4s ease, box-shadow 0.4s ease;
      }

       /* ESTILOS PARA EL MODAL DE "CANCELAR" CAMBIOS*/
      #modalCancelarCambios .modal-contenido h2 {
          font-size: 16px;
          color: #403F48;
          font-weight: bold;
          font-family: 'Poppins', sans-serif;
          text-align: center;
      }

      /* Botón Cancelar */
      #btnCancelarModalCancelar {
          background-color: #EB7373;
          position: relative;
          overflow: hidden;
          color: #061F3E;
          font-family: 'Inter', sans-serif;
          font-size: 16px;
          border: none;
          padding: 10px 22px;
          border-radius: 8px;
          cursor: pointer;
          transition: background 0.3s;
          z-index: 0;
      }

      /* Botón Confirmar */
      #btnConfirmarModalCancelar {
          background-color: #61C9A8;
          position: relative;
          overflow: hidden;
          color: #061F3E;
          font-family: 'Inter', sans-serif;
          font-size: 16px;
          border: none;
          padding: 10px 22px;
          border-radius: 8px;
          cursor: pointer;
          transition: background 0.3s;
          z-index: 0;
      }

      #btnCancelarModalCancelar:hover {
        background-color: #e05e5e;
        transform: translateY(-2px) scale(1.02);
        box-shadow: 0 6px 15px rgba(0,0,0,0.2);
        transition: all 0.3s ease;
      }

      #btnCancelarModalCancelar:focus {
        outline: 2px solid #e05e5e;
        transform: scale(1.01);      
        box-shadow: 0 2px 5px rgba(0,0,0,0.1); 
        transition: border-color 0.4s ease, transform 0.4s ease, box-shadow 0.4s ease;
      }

      #btnConfirmarModalCancelar:hover {
        background-color: #4eb892;
        transform: translateY(-2px) scale(1.02);
        box-shadow: 0 6px 15px rgba(0,0,0,0.2);
        transition: all 0.3s ease;
      }

      #btnConfirmarModalCancelar:focus {
        outline: 2px solid #4eb892;
        transform: scale(1.01);      
        box-shadow: 0 2px 5px rgba(0,0,0,0.1); 
        transition: border-color 0.4s ease, transform 0.4s ease, box-shadow 0.4s ease;
      }

      /* ESTILOS PARA EL MODAL DE "GUARDAR" CAMBIOS*/
      #modalConfirmar .modal-contenido h2 {
          font-size: 16px;
          color: #403F48;
          font-weight: bold;
          font-family: 'Poppins', sans-serif;
          text-align: center;
      }

      /* Botón Cancelar */
      #btnCancelarGuardar {
          background-color: #EB7373;
          overflow: hidden;
          color: #061F3E;
          font-family: 'Inter', sans-serif;
          font-size: 16px;
          border: none;
          padding: 10px 22px;
          border-radius: 8px;
          cursor: pointer;
          transition: background 0.3s;
      }

      /* Botón Confirmar */
      #btnConfirmarGuardar {
          background-color: #61C9A8;
          overflow: hidden;
          color: #061F3E;
          font-family: 'Inter', sans-serif;
          font-size: 16px;
          border: none;
          padding: 10px 22px;
          border-radius: 8px;
          cursor: pointer;
          transition: background 0.3s;
      }

      #btnCancelarGuardar:hover {
        background-color: #e05e5e;
        transform: translateY(-2px) scale(1.02);
        box-shadow: 0 6px 15px rgba(0,0,0,0.2);
        transition: all 0.3s ease;
      }

      #btnCancelarGuardar:focus {
        background-color: #e05e5e;
        transform: scale(1.01);      
        box-shadow: 0 2px 5px rgba(0,0,0,0.1); 
        transition: border-color 0.4s ease, transform 0.4s ease, box-shadow 0.4s ease;
      }

      #btnConfirmarGuardar:hover {
        background-color: #4eb892;
        transform: translateY(-2px) scale(1.02);
        box-shadow: 0 6px 15px rgba(0,0,0,0.2);
        transition: all 0.3s ease;  
      }

      #btnConfirmarGuardar:focus {
        background-color: #4eb892;
        transform: scale(1.01);      
        box-shadow: 0 2px 5px rgba(0,0,0,0.1); 
        transition: border-color 0.4s ease, transform 0.4s ease, box-shadow 0.4s ease;  
      }

      /* mensajes de error */
      .error {
        color: red;
        margin-bottom: 15px;
        padding: 10px;
        background-color: #ffeeee;
        border: 1px solid #ffcccc;
        border-radius: 4px;
      }

      .mensaje-error {
        background-color: #F8D7DA;  
        border: 1px solid #F5C2C7;  
        color: #842029;    
        font-family: 'Inter', sans-serif; 
        font-size: 16px;   
        text-align: left;     
        padding: 10px 14px;
        border-radius: 8px;
        margin-top: 6px;
        display: block;
      }
       /* Alineación */
      .error-borde {
        border: 2px solid red !important;
        outline: none;
      }

      .fechacampo.error-borde {
        border: 2px solid red !important;
        outline: none;
        border-radius: 12px;
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

      /* Modal para formato no admitido */
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

      .btnCerrarModalFormato {
        font-family:'Poppins',sans-serif; 
        position: relative;
        overflow: hidden;
        font-size:20px; 
        color: #061F3E; 
        background: #61C9A8; 
        border:none; 
        padding:10px 20px; 
        border-radius:8px; 
        cursor:pointer;
        transition: background 0.3s;
        z-index: 0;
      }

      .btnCerrarModalFormato:hover {
        background-color: #4CA88C;
        transform: translateY(-2px) scale(1.02);
        box-shadow: 0 6px 15px rgba(0,0,0,0.2);
        transition: all 0.3s ease;
      }

      .btnCerrarModalFormato:focus {
        outline: 2px solid #4CA88C;
        transform: scale(1.01);      
        box-shadow: 0 2px 5px rgba(0,0,0,0.1); 
        transition: border-color 0.4s ease, transform 0.4s ease, box-shadow 0.4s ease;
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
        background: #fff;
        color: #E33639;
        border: none;
        font-size: 34px;
        width: 50px;
        height: 50px;
        border-radius: 50%;
        cursor: pointer;
        display: flex;
        align-items: center;
        justify-content: center;
        z-index: 10001;
      }

      /* contadores de caracteres */
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
        display: inline !important; 
      }

      .contador-error {
        color: #E33639 !important;
      }

      /* Sacudida leve de los campos al haber error */
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
          z-index: 1000;
      }

      @keyframes ripple-effect {
          to {
              transform: scale(4);
              opacity: 0;
          }
      }

      @media (max-width: 600px) {
          .contenedor-principal {
            margin: 20px 15px 40px;
            padding: 25px 20px;
          }
          h1 {
            font-size: 1.6rem;
          }
          .campo input[type="text"],
          .campo textarea {
            padding: 12px 14px;
          }
          .boton-publicar {
            font-size: 1rem;
            padding: 14px 0;
          }
      }

    </style>
</head>
  <?php if (!empty($mensajeToast)): ?>
    <div id="toast-exito" 
      style="
        position: fixed;
        top: 50%;
        left: 50%;
        padding: 15px 20px;
        background: #28a745;
        color: white;
        border-radius: 8px;
        font-size: 16px;
        box-shadow: 0 4px 12px rgba(0,0,0,0.2);
        z-index: 99999;
        opacity: 0;
        transition: opacity .5s ease;
        transform: translate(-50%, -50%);
      ">
        ¡Los cambios fueron guardados correctamente!
    </div>
  <?php endif; ?>
<body>
<header>
    <div class="logo">
        <img src="imagenes/logo.png" alt="logo" />
    </div>
    <nav class="informacion">
      <a href="#" id="btnVolver">Volver a Denuncias</a>
    </nav>
</header>

<div class="contenedor-principal">
    <h1>Editar Denuncia</h1>

    <?php if ($error): ?>
        <div class="error"><?php echo htmlspecialchars($error); ?></div>
    <?php endif; ?>

    <form method="POST" novalidate enctype="multipart/form-data">
        <div class="campo">
            <label for="titulo" class="requerido" >Título:</label>
            <input type="text" class="titulocampo" id="titulo" name="titulo" value="<?php echo htmlspecialchars($noticia['titulo']); ?>" required autocomplete="off"/>
            <small class="contadorDelTitulo" id="contadorTitulo">0/150</small>
        </div>

        <div class="campo">
            <label for="descripcion" class="requerido" >Descripción:</label>
            <textarea id="descripcion" class="descripcioncampo" name="descripcion" required autocomplete="off"><?php echo htmlspecialchars($noticia['descripcion']); ?></textarea>
            <small class="contadorDeDescripcion" id="contadorDescripcion">0/3000</small>
        </div>

        <div class="campo">
            <label for="fecha_evento" class="requerido">Fecha del evento denunciado:</label>
            <div class="input-con-icono">
              <input type="text" id="fecha_evento" name="fecha_evento" placeholder="Selecciona la fecha" class="fechacampo" value="<?= htmlspecialchars($noticia['fecha_evento'] ?? '') ?>">
              <span class="icono-calendario"><img src="imagenes/calendario.png" alt="Calendario"></span>
            </div>
        </div>

        <div class="campo">
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

            <!-- Aquí se muestran las imagenes, previsualizacion -->
            <div id="preview-imagenes"></div>

            <!-- input con las imágenes marcadas para borrar-->
            <input type="hidden" id="imagenes_para_borrar" name="imagenes_para_borrar" value="[]">
          </div>
        </div>

      <div class="botonesDelFormualario">
          <button type="button" class="boton-cancelar">Cancelar</button>
          <button type="button" id="btnGuardar" class="boton-publicar">Guardar</button>
      </div>

    </form>
</div>

      <!-- Modal "Volver a denuncias" -->
  <div id="modalConfirmacion" class="modal">
    <div class="modal-contenido">
      <h2>¿Estás seguro de volver a los detalles de esta denuncia?</h2>
      <div class="botones">
        <button id="btnCancelar" class="btnCancelar" >Cancelar</button>
        <button id="btnConfirmar" class="btnConfirmar" >Confirmar</button>
      </div>
    </div>
  </div>

  <!-- Modal boton "Cancelar" -->
  <div id="modalCancelarCambios" class="modal">
      <div class="modal-contenido">
          <h2>¿Estás seguro de cancelar los cambios realizados de esta denuncia?</h2>
          <div class="botones">
              <button id="btnCancelarModalCancelar" class="btn-cancelar-modal">Cancelar</button>
              <button id="btnConfirmarModalCancelar" class="btn-confirmar-modal">Confirmar</button>
          </div>
      </div>
  </div>

  <!-- Modal boton "Guardar" -->
  <div id="modalConfirmar" class="modal">
      <div class="modal-contenido">
          <h2 class="titulo-modal">¿Estás seguro de guardar los cambios de esta denuncia?</h2>
          <div class="botones">
              <button id="btnCancelarGuardar" class="btn-cancelar">Cancelar</button>
              <button id="btnConfirmarGuardar" class="btn-confirmar">Confirmar</button>
          </div>
      </div>
  </div>

  <!-- Modal cuando el formato de la imagen no es admitido -->
  <div id="modalFormato" class="modal-overlay">
    <div class="modal-contenido">
      <p style="font-family: 'Inter', sans-serif; font-size:20px; color:#403F48;">
        El formato de tu archivo no es admitido. Intenta cargar un archivo (.jpeg).
      </p>
      <div style="margin-top:20px;">
        <button id="btnCerrarModalFormato" class="btnCerrarModalFormato" > De acuerdo </button>
      </div>
    </div>
  </div>

  <script>
    let archivosSeleccionados = [], imagenesParaBorrar = [];
    const rutaBase = "<?= addslashes($rutaBase) ?>";

    document.addEventListener("DOMContentLoaded", () => {

        // Funciones auxiliares
        const $ = id => document.getElementById(id);
        const modalShow = el => { if (el) el.style.display = "block"; };
        const modalClose = el => { if (el) el.style.display = "none"; };

        // Accesos a elementos clave
        const fileInput = $("imagenes");
        const preview = $("preview-imagenes");
        const estado = $("estado-archivo");
        const inputBorrar = $("imagenes_para_borrar");
        const btnGuardar = $("btnGuardar");
        const form = document.querySelector("form");

        // Modal “volver”
        const modalConfirmacion = $("modalConfirmacion");
        $("btnVolver")?.addEventListener("click", e => { e.preventDefault(); modalShow(modalConfirmacion); });
        document.getElementById("btnCancelar")?.addEventListener("click", () => {
            setTimeout(() => {
                modalClose(modalConfirmacion);
            }, 220);  // Espera 220 ms para que se vea el ripple
        });
        $("btnConfirmar")?.addEventListener("click", e => {
            e.preventDefault();
            setTimeout(() => {
                location.href = "denuncia.php";
            }, 220);
        });
        window.addEventListener("click", e => { if (e.target === modalConfirmacion) modalClose(modalConfirmacion); });

        // Funcion para el campo de fecha
        const fpFecha = (typeof flatpickr !== 'undefined') ? flatpickr("#fecha_evento", {
            dateFormat:"Y-m-d",
            allowInput:false,
            altInput:true,
            altFormat:"d-m-Y",
            defaultDate:"<?= (!empty($noticia['fecha_evento']) && $noticia['fecha_evento'] !== '0000-00-00') ? htmlspecialchars($noticia['fecha_evento']) : '' ?>",
            onChange(_, dateStr, inst){
                if (dateStr && dateStr.trim()){
                    // limpiar error si existía
                    const cont = inst.input.closest('.campo');
                    cont?.querySelector('.mensaje-error')?.remove();
                    inst.altInput?.classList.remove('error-borde');
                }
            }
        }) : null;

        // Accesos a campos
        const inputTitulo = $("titulo");
        const contadorTitulo = $("contadorTitulo");
        const textareaDescripcion = $("descripcion");
        const contadorDescripcion = $("contadorDescripcion");
        const fechaInput = $("fecha_evento");

        // Verificación de elementos críticos
        if (!inputTitulo || !textareaDescripcion || !fechaInput) {
            console.warn("Faltan elementos críticos en el DOM (titulo/descripcion/fecha). Verifica los IDs.");
            return;
        }

        // Contadores de caracteres
        function actualizarContador(input, contador, min, max) {
            if (!contador) return;
            const longitud = input.value.length;
            contador.textContent = `${longitud}/${max}`;
            if (longitud < min || longitud > max) {
                contador.classList.add('contador-error');
            } else {
                contador.classList.remove('contador-error');
            }
        }

        inputTitulo.addEventListener('focus', () => { contadorTitulo?.classList.add('contador-activo'); actualizarContador(inputTitulo, contadorTitulo, 10, 150); });
        inputTitulo.addEventListener('blur', () => { contadorTitulo?.classList.remove('contador-activo'); });
        inputTitulo.addEventListener('input', () => { actualizarContador(inputTitulo, contadorTitulo, 10, 150); });

        textareaDescripcion.addEventListener('focus', () => { contadorDescripcion?.classList.add('contador-activo'); actualizarContador(textareaDescripcion, contadorDescripcion, 300, 3000); });
        textareaDescripcion.addEventListener('blur', () => { contadorDescripcion?.classList.remove('contador-activo'); });
        textareaDescripcion.addEventListener('input', () => { actualizarContador(textareaDescripcion, contadorDescripcion, 300, 3000); });

        // Mostrar error en los campos
        function mostrarError(input, mensaje) {
            if (!input) return;
            // buscar el .campo más cercano 
            let contenedor = input.closest ? input.closest('.campo') : null;
            if (!contenedor) contenedor = input.parentNode || document.body;

            // Evitar mensajes duplicados
            if (contenedor.querySelector('.mensaje-error')) return;

            let errorMsg = document.createElement('div');
            errorMsg.className = 'mensaje-error';
            errorMsg.textContent = mensaje;
            contenedor.appendChild(errorMsg);

            // aplicar estilo de borde de error y animación
            if (input.id === 'fecha_evento' && fpFecha && fpFecha.altInput) {
                fpFecha.altInput.classList.add('error-borde', 'shake');
                setTimeout(()=> fpFecha.altInput.classList.remove('shake'), 500);
            } else {
                input.classList.add('error-borde', 'shake');
                setTimeout(()=> input.classList.remove('shake'), 500);
            }

            // Quitar mensaje y borde después de 5s
            setTimeout(() => {
                if (errorMsg && errorMsg.parentNode) errorMsg.remove();
                if (input.id === 'fecha_evento' && fpFecha && fpFecha.altInput) {
                    fpFecha.altInput.classList.remove('error-borde');
                } else {
                    input.classList.remove('error-borde');
                }
            }, 5000);
        }

        // Validaciones en tiempo real de título y descripción
        [inputTitulo, textareaDescripcion].forEach(el => {
            el.addEventListener('input', function () {
                const valor = this.value;
                // eliminar mensaje previo dentro del campo
                const cont = this.closest ? this.closest('.campo') : this.parentNode;
                cont?.querySelector('.mensaje-error')?.remove();
                this.classList.remove('error-borde');

                if (valor.trim() === '') {
                    // mostramos el mensaje de que debe completarse el campo
                    mostrarError(this, 'Complete este campo');
                    return;
                }

                if (this.id === 'titulo') {
                    if (valor.length < 10) mostrarError(this, 'El título debe tener al menos 10 caracteres.');
                    else if (valor.length > 150) mostrarError(this, 'Haz alcanzado el límite de 150 caracteres.');
                } else if (this.id === 'descripcion') {
                    if (valor.length < 300) mostrarError(this, 'La descripción debe tener al menos 300 caracteres.');
                    else if (valor.length > 3000) mostrarError(this, 'Haz alcanzado el límite de 3000 caracteres.');
                }
            });
        });

        // Validación antes de abrir modal Guardar
        function validarFormulario() {
            // limpiar errores previos
            [inputTitulo, textareaDescripcion, fechaInput].forEach(i => {
                const cont = i.closest ? i.closest('.campo') : i.parentNode;
                cont?.querySelector('.mensaje-error')?.remove();
                if (i.id === 'fecha_evento' && fpFecha && fpFecha.altInput) fpFecha.altInput.classList.remove('error-borde');
                i.classList.remove('error-borde');
            });

            const tituloVal = inputTitulo.value.trim();
            const descripcionVal = textareaDescripcion.value.trim();
            const fechaVal = fechaInput.value.trim();

            if (tituloVal === '') { mostrarError(inputTitulo, 'El título es obligatorio.'); return false; }
            if (descripcionVal === '') { mostrarError(textareaDescripcion, 'La descripción es obligatoria.'); return false; }
            if (fechaVal === '') { mostrarError(fechaInput, 'La fecha es obligatoria.'); return false; }

            if (tituloVal.length < 10) { mostrarError(inputTitulo, 'El título debe tener al menos 10 caracteres.'); return false; }
            if (tituloVal.length > 150) { mostrarError(inputTitulo, 'El título no debe exceder 150 caracteres.'); return false; }
            if (descripcionVal.length < 300) { mostrarError(textareaDescripcion, 'La descripción debe tener al menos 300 caracteres.'); return false; }
            if (descripcionVal.length > 3000) { mostrarError(textareaDescripcion, 'La descripción no debe exceder 3000 caracteres.'); return false; }

            return true;
        }

        // pasar imágenes desde PHP al JS de forma segura, provenientes de PHP.
        const imagenesExistentes = <?= json_encode(array_values(array_filter([
            [
                "archivo" => $noticia['imagen'] ?? null,
                "nombre"  => $noticia['imagen_nombre_original'] ?? null
            ],
            [
                "archivo" => $noticia['imagen2'] ?? null,
                "nombre"  => $noticia['imagen2_nombre_original'] ?? null
            ],
            [
                "archivo" => $noticia['imagen3'] ?? null,
                "nombre"  => $noticia['imagen3_nombre_original'] ?? null
            ]
        ], function($x){ return !empty($x["archivo"]); })), JSON_UNESCAPED_UNICODE) ?>;

        // Pregarca imágenes existentes desde DB  si hay
        imagenesExistentes.forEach(n => {
            if (!n) return;
            const v = { name: n.nombre || n.archivo, file: n.archivo, url: rutaBase + n.archivo, esBD: true };
            archivosSeleccionados.push(v);
            mostrarEnPreview(v);
        });

        // Campo de imagenes: validaciones formato y límite
        if (fileInput) {
            fileInput.addEventListener('change', () => {
                const nuevos = Array.from(fileInput.files || []);
                const validos = nuevos.filter(f => f.type === "image/jpeg" && /\.(jpg|jpeg)$/i.test(f.name));

                if (validos.length < nuevos.length) {
                    const m = $("modalFormato");
                    modalShow(m);
                    m?.classList.add("shake");
                    setTimeout(()=> m?.classList.remove("shake"), 500);
                    document.getElementById("btnCerrarModalFormato")?.addEventListener(
                      "click",
                      () => {
                          setTimeout(() => {
                              modalClose(m);
                          }, 220); // espera 220ms para que se vea el ripple
                      },
                      { once: true }
                  );
                }
                //aviso si se sobrepasa el límite
                if ((archivosSeleccionados.length + validos.length) > 3) {
                    mostrarToast('Solo puedes seleccionar hasta 3 imágenes. Se cargarán las primeras 3.', 'danger');
                }

                archivosSeleccionados = archivosSeleccionados.concat(validos).slice(0, 3);
                validos.slice(0, Math.max(0, 3 - (archivosSeleccionados.length - validos.length))).forEach(mostrarEnPreview);

                // Si ya alcanzó 3
                if (archivosSeleccionados.length >= 3) {
                    mostrarToast('Has alcanzado el límite de 3 imágenes.', 'danger');
                }

                fileInput.value = "";
                actualizarEstadoYInput();
            });
        }

        // Actualiza estado, hidden y fileInput 
        function actualizarEstadoYInput(){
            if (estado) {
                estado.textContent = archivosSeleccionados.length 
                    ? `${archivosSeleccionados.length} archivo(s) seleccionado(s)` 
                    : "No se ha seleccionado ningún archivo";
            }

            if (fileInput) {
                const dt = new DataTransfer();
                archivosSeleccionados.forEach(a => { if (!a.esBD) dt.items.add(a); });
                fileInput.files = dt.files;
            }

            if (inputBorrar) inputBorrar.value = JSON.stringify(imagenesParaBorrar);

            const btnSubir = document.querySelector(".btn-subir");
            if (btnSubir) btnSubir.classList.toggle("disabled", archivosSeleccionados.length >= 3);
        }

        // Mostrar en previsualización
        function mostrarEnPreview(a){
            if (!preview) return;
            const div = document.createElement("div");
            div.className = "imagen-preview";

            const src = a.esBD ? a.url : URL.createObjectURL(a);
            div.innerHTML = `
                <img class="imagen-click" src="${src}" alt="${a.name}">
                <div class="info">
                    <span class="nombre">${a.name}</span>
                    <button type="button" class="eliminar">×</button>
                </div>`;

            preview.appendChild(div);

            const btnEliminar = div.querySelector(".eliminar");
            btnEliminar.addEventListener("click", () => {
                if (a.esBD && !imagenesParaBorrar.includes(a.file)) imagenesParaBorrar.push(a.file);
                archivosSeleccionados = archivosSeleccionados.filter(x => x !== a);
                div.remove();
                actualizarEstadoYInput();
            });

            div.querySelector("img").addEventListener("click", () => abrirModalImagen(src, a.name));
            actualizarEstadoYInput();
        }

        // Modal imagen ampliada
        function abrirModalImagen(src, name){
            const m = $("modalImagen");
            if (!m) return;
            m.style.display = "flex";
            $("imagenAmpliada").src = src;
            $("nombreImagenAmpliada").textContent = name;
        }
        $("cerrarModalImagen")?.addEventListener("click", () => {
            modalClose($("modalImagen"));
            $("imagenAmpliada").src = "";
            $("nombreImagenAmpliada").textContent = "";
        });
        $("modalImagen")?.addEventListener("click", (e) => { if (e.target === $("modalImagen")) $("cerrarModalImagen").click(); });

        document.getElementById("btnCerrarModalFormato")?.addEventListener("click", () => {
            setTimeout(() => {
                modalClose(document.getElementById("modalFormato"));
            }, 220); // espera 220ms para que se vea el ripple
        });

        // Toast
        function mostrarToast(msg,tipo="danger"){
            const t=document.createElement("div");
            t.className=`toast-personalizado text-bg-${tipo}`;
            t.setAttribute("role","alert");
            t.innerHTML=`<div class="contenido-toast">${msg}</div>`;
            document.body.appendChild(t);
            setTimeout(()=> t.remove(),7000);
        }

        // Modal “CANCELAR CAMBIOS”
        const modalCancelar = $("modalCancelarCambios");
        const btnCancelarForm = document.querySelector(".boton-cancelar");
        btnCancelarForm?.addEventListener("click", e => {
            setTimeout(() => {
                modalShow(modalCancelar);
            }, 220); // Espera antes de abrir el modal
        });
        document.getElementById("btnCancelarModalCancelar")?.addEventListener("click", () => {
            setTimeout(() => {
                modalClose(modalCancelar);
            }, 220); // Espera antes de cerrar el modal
        });
        document.getElementById("btnConfirmarModalCancelar")?.addEventListener("click", () => {
            setTimeout(() => {
                location.href = "denuncia.php";
            }, 220); //Espera para que el ripple se vea
        });
        window.addEventListener("click", e => { if (e.target===modalCancelar) modalClose(modalCancelar); });

        // Modal “GUARDAR CAMBIOS”, valida antes de abrir
        const modalGuardar = $("modalConfirmar");
        btnGuardar?.addEventListener("click", (e) => {
            e.preventDefault();
            if (validarFormulario()) {
                setTimeout(() => {
                    modalShow(modalGuardar);
                }, 220); // Espera antes de abrir el modal
            }
        });
        document.getElementById("btnCancelarGuardar")?.addEventListener("click", () => {
            setTimeout(() => { // efecto ripple 
                modalClose(modalGuardar);
            }, 220); // Espera antes de cerrar el modal
        });
        document.getElementById("btnConfirmarGuardar")?.addEventListener("click", () => {
            setTimeout(() => { // efecto ripple 
                if (!validarFormulario()) { // revalidar antes de enviar
                    modalClose(modalGuardar);
                    return;
                }
                modalClose(modalGuardar); // preparar hidden ya actualizado en actualizarEstadoYInput() 
                form?.submit(); // enviar formulario
            }, 220); // tiempo de espera para que el ripple se vea
        });
        window.addEventListener("click", e => { if (e.target===modalGuardar) modalClose(modalGuardar); });

        // Mostrar toast de éxito si corresponde, guardado seguro
        const toastEl = document.getElementById('toast-exito');
            if (toastEl) {
                toastEl.style.opacity = "1";
                setTimeout(() => { toastEl.style.opacity = "0"; }, 4000);
                setTimeout(() => { window.location.href = "ver_denuncia.php?id=<?= $id_noticia ?>"; }, 4000);
            }

            // Inicializar contadores con valores iniciales si el campo ya tiene texto.
            actualizarContador(inputTitulo, contadorTitulo, 10, 150);
            actualizarContador(textareaDescripcion, contadorDescripcion, 300, 3000);
        });

        document.querySelectorAll('.btnCancelar, .btnConfirmar, #btnCancelarModalCancelar, #btnConfirmarModalCancelar, #btnCancelarGuardar, #btnConfirmarGuardar, .boton-cancelar, .boton-publicar, .btn-subir, .btnCerrarModalFormato').forEach(button => {
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

  <!-- Modal de imagen ampliada  -->
  <div id="modalImagen" class="modal-imagen-overlay">
    <div class="modal-imagen-contenido">
      <button id="cerrarModalImagen" class="modal-imagen-cerrar">&times;</button>
      <img src="" alt="Imagen ampliada" id="imagenAmpliada">
      <div id="nombreImagenAmpliada" style="position:absolute; bottom:10px; left:50%; transform:translateX(-50%); background:rgba(0,0,0,0.6); color:#fff; padding:5px 10px; border-radius:5px; font-size:14px;"></div>
    </div>
  </div>

</body>
</html>