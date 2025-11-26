<?php
session_start();
include 'conexion.php';

// Verificar que el usuario este autenticado
if (!isset($_SESSION['usuario_id'])) {
  header("Location: login.php");
  exit();
}

// Verificar que se haya proporcionado un ID de noticia
if (!isset($_GET['id'])) {
  header("Location: denuncia.php");
  exit();
}

$id_denuncia = intval($_GET['id']);
$usuario_id = $_SESSION['usuario_id'] ?? null;
$usuario_nombre = $_SESSION['usuario_nombre'] ?? 'Usuario';

// Obtener datos de la denuncia 
$stmt = $conexion->prepare("SELECT titulo, fecha FROM propuestas_denuncias WHERE id = ?");
$stmt->bind_param("i", $id_denuncia);
$stmt->execute();
$denuncia = $stmt->get_result()->fetch_assoc();
$stmt->close();

if (!$denuncia) {
  header("Location: denuncia.php");
  exit();
}

// Procesar el formulario de reporte
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $motivo = trim($_POST['motivo'] ?? '');
  $comentario = trim($_POST['comentario'] ?? '');

  // Validaciones
  $errores = [];

  if (empty($motivo)) {
    $errores[] = "Seleccione un motivo";
  }

  if (empty($comentario)) {
    $errores[] = "El comentario es obligatorio";
  } elseif (strlen($comentario) < 100) {
    $errores[] = "El comentario debe tener al menos 100 caracteres";
  } elseif (strlen($comentario) > 1000) {
    $errores[] = "Haz alcanzado el limite de 1000 caracteres";
  }

  // Procesar imagenes
  $evidencias = [];
  if (isset($_FILES['evidencias']) && is_array($_FILES['evidencias']['name'])) {
    foreach ($_FILES['evidencias']['name'] as $key => $name) {
      if ($_FILES['evidencias']['error'][$key] === UPLOAD_ERR_OK) {
        // Validar tipo de archivo
        $tipo_archivo = mime_content_type($_FILES['evidencias']['tmp_name'][$key]);
        if ($tipo_archivo !== 'image/jpeg') {
          $errores[] = "Solo se permiten archivos JPEG";
          continue;
        }

        // Generar nombre unico
        $extension = pathinfo($name, PATHINFO_EXTENSION);
        $nombre_archivo = uniqid() . '_' . $usuario_id . '.' . $extension;
        $ruta_destino = 'imagenes/reportes/' . $nombre_archivo;

        // Crear directotio si no existe
        if (!is_dir('imagenes/reportes')) {
          mkdir('imagenes/reportes', 0755, true);
        }

        // Mover archivo
        if (move_uploaded_file($_FILES['evidencias']['tmp_name'][$key], $ruta_destino)) {
          $evidencias[] = $ruta_destino;
        } else {
          $errores[] = "Error al cargar la imagen: " . $name;
        } 
      }
    }
  }
}

  // Si no hay errores, guardar reporte
  if (empty($errores)) {
    $evidencias_string = !empty($evidencias) ? implode(',', $evidencias) : null;

    if (empty($denuncia['titulo']) || empty($denuncia['fecha'])) {
      $errores[] = "Datos de la noticia incompletos";
    } else {
      $stmt = $conexion->prepare("INSERT INTO reportes_denuncias (denuncia_id, titulo_denuncia, fecha_denuncia, motivo, usuario_id, usuario_nombre, evidencias, comentario, fecha_reporte) VALUES (?, ?, ?, ?, ?, ?, ?, ?, NOW())");

      if ($stmt) {
        $stmt->bind_param("isssisss", $id_denuncia, $denuncia['titulo'], $denuncia['fecha'], $motivo, $usuario_id, $usuario_nombre, $evidencias_string, $comentario);
        
        if ($stmt->execute()) {
          $mensaje_exito = "Reporte enviado correctamente";
          // Limpiar el formulario despues del envio exitoso
          $motivo = '';
          $comentario = '';
    } else {
      $errores[] = "Error al guardar el reporte en la base de datos";
    }
   $stmt->close();
    } else {
      $errores[] = "Error al preparar la consulta: " . $conexion->error;
    }
  }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title><?= htmlspecialchars($denuncia['titulo']) ?> - Comunicado Digital</title>
  <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600;700&family=Inter:wght@400;600&display=swap" rel="stylesheet">
  <style>
    :root {
      --color-primario: #1661AC;
      --color-secundario: #2D8EFF;
      --color-fondo-header: #061F3E;
      --color-texto-claro: #fff;
      --color-texto-oscuro: #403F48;
      --color-texto-gris: #74737C;
      --color-borde: #B1B1B1;
      --color-error: #E33629;
      --color-exito: #61C9A8;
      --color-cancelar: #EB7373;
      --color-fondo-input: #ADEBFF;
    }

    * {
      margin: 0;
      padding: 0;
      box-sizing: border-box;
    }

    body {
      font-family: 'Inter', sans-serif;
      background-color: #f9f9f9;
      line-height: 1.6;
    }

/* Encabezado */
    .encabezado-reporte {
      background-color: var(--color-fondo-header);
      padding: 20px 40px;
      display: flex;
      justify-content: space-between;
      align-items: center;
      box-shadow: 0 2px 10px rgba(0,0,0,0.1);
    }

    .logo {
      height: 50px;
    }
    
    .volver-detalles {
      font-family: 'Poppins', sans-serif;
      font-size: 16px;
      font-weight: 600;
      color: var(--color-texto-claro);
      text-decoration: none;
      transition: all 0.3s ease;
      padding: 10px 15px;
      border-radius: 6px;
    }

    .volver-detalles:hover {
      color: var(--color-primario);
      text-decoration: underline;
      background-color: rgba(255,255,255,0.1);
    }

    /* Contenedor principal */
    .contenedor-reporte {
      max-width: 1300px;
      margin: 40px auto;
      padding: 0 24px;
    }

    /* Titulo principal */
    .titulo-principal {
      font-family: 'Poppins', sans-serif;
      font-size: 32px;
      font-weight: 700;
      color: var(--color-primario);
      text-align: center;
      margin-bottom: 40px;
    }

    /* Formulario */
    .formulario-reporte {
      background-color: white;
      border-radius: 16px;
      padding: 40px;
      box-shadow: 0 4px 20px rgba(0,0,0,0.08);
    }

    .fila-formulario {
      display: flex;
      gap: 30px;
      margin-bottom: 20px;
    }

    .campo-formulario {
      flex: 1;
      display: flex;
      flex-direction: column;
    }

    .etiqueta {
      font-family: 'Poppins', sans-serif;
      font-size: 16px;
      font-weight: 600;
      color: var(--color-texto-oscuro);
      margin-bottom: 8px;
    }

    .input-texto, .input-fecha, .selector, .textarea-comentario {
      font-family: 'Inter', sans-serif;
      font-size: 16px;
      color: var(--color-texto-gris);
      border: 1px solid var(--color-borde);
      border-radius: 12px;
      padding: 12px 15px;
      transition: all 0.3s ease;
      outline: none;
    }

    .input-texto, .input-fecha, .selector {
      width: 100%;
    }

    .input-texto:focus, .input-fecha:focus, .selector:focus, .textarea-comentario:focus {
      border-color: var(--color-secundario);
      box-shadow: 0 0 0 2px rgba(45, 142, 255, 0.1);
    }

    .input-texto:hover, .input-fecha:hover, .selector:hover, .textarea-comentario:hover {
      transform: scale(1.01);
      box-shadow: 0 2px 8px rgba(0,0,0,0.1);
    }

    .contenedor-fecha {
      position: relative;
    }

    .icono-calendario {
      position: absolute;
      right: 15px;
      top: 50%;
      transform: translateY(-50%);
      color: var(--color-borde);
      background: none;
      border: none;
      font-size: 18px;
      cursor: pointer;
    }

    .selector {
      background-color: var(--color-fondo-input);
      color: var(--color-fondo-header);
      cursor: pointer;
    }

    /* Seccion evidencias */
    .seccion-evidencias {
      margin-bottom: 30px;
    }

    .fila-evidencias {
      display: flex;
      justify-content: space-between;
      align-items: center;
      margin-bottom: 15px;
    }

    .indicacion-evidencias {
      font-family: 'Inter', sans-serif;
      font-size: 16px;
      color: var(--color-texto-oscuro);
    }

    .contenedor-carga {
      border: 1px solid var(--color-borde);
      border-radius: 12px;
      padding: 20px;
      transition: all 0.2s ease;
      width: 100%;
    }

    .contenedor-carga:hover, .contenedor-carga:focus-within {
      border-color: var(--color-secundario);
    }

    .contenedor-boton-archivo {
      display: flex;
      align-items: center;
      gap: 20px;
      margin-bottom: 20px;
    }

    .btn-elegir-archivo {
      background-color: var(--color-fondo-input);
      color: var(--color-fondo-header);
      font-family: 'Inter', sans-serif;
      font-size: 16px;
      border: none;
      border-radius: 8px;
      padding: 12px 20px;
      cursor: pointer;
      transition: all 0.3s ease;
      display: flex;
      align-items: center;
      justify-content: center;
    }

    .btn-elegir-archivo:hover {
      transform: scale(1.05);
      box-shadow: 0 2px 8px rgba(0,0,0,0.15);
    }

    .btn-elegir-archivo.limite-alcanzado {
      background-color: var(--color-borde);
      cursor: not-allowed;
    }

    .texto-archivo {
      font-family: 'Inter', sans-serif;
      font-size: 14px;
      color: var(--color-texto-gris);
    }

    .vista-previa-contenedor {
      display: flex;
      gap: 20px;
      flex-wrap: wrap;
    }

    .vista-previa-item {
      position: relative;
      width: 200px;
      height: 150px;
      border: 1px solid var(--color-borde);
      border-radius: 12px;
      overflow: hidden;
    }

    .vista-previa-item img {
      width: 100%;
      height: 100%;
      object-fit: cover;
    }

    .btn-eliminar-archivo {
      position: absolute;
      top: 10px;
      right: 10px;
      background: rgba(255,255,255,0.9);
      border: none;
      border-radius: 50%;
      width: 25px;
      height: 25px;
      display: flex;
      align-items: center;
      justify-content: center;
      cursor: pointer;
      color: var(--color-error);
      font-size: 15px;
      transition: all 0.3s ease;
    }

    .btn-eliminar-archivo:hover {
      background: white;
      transform: scale(1.1);
    }

    /* Contador de caracteres */
    .contenedor-contador {
      position: relative;
    }

    .contador-caracteres {
      position: absolute;
      right: 15px;
      bottom: 15px;
      font-family: 'Inter', sans-serif;
      font-size: 14px;
      color: var(--color-borde);
      display: none;
    }

    .contador-caracteres.visible {
      display: block;
    }

    .contador-caracteres.error {
      color: var(--color-error);
    }

    .textarea-comentario {
      min-height: 120px;
      resize: vertical;
    }

    /* Boton enviar */
    .contenedor-btn-enviar {
      display: flex;
      justify-content: center;
      margin-top: 40px;
    }

    .btn-enviar {
      background-color: var(--color-exito);
      color: #1B314B;
      font-family: 'Poppins', sans-serif;
      font-size: 20px;
      font-weight: 700;
      border: none;
      border-radius: 20px;
      padding: 15px 40px;
      cursor: pointer;
      transition: all 0.3s ease;
      min-height: 50px;
      width: 200px;
    }

    .btn-enviar:hover {
      background-color: #4CA88C;
      transform: scale(1.05);
      box-shadow: 0 4px 15px rgba(97, 201, 168, 0.3);
    }

    .btn-enviar:active {
      transform: scale(0.98);
    }

    /* Modales */
    .modal {
      position: fixed;
      top: 0;
      left: 0;
      width: 100%;
      height: 100%;
      background-color: rgba(0,0,0,0.5);
      display: none;
      justify-content: center;
      align-items: center;
      z-index: 1000;
    }

    .modal-contenido {
      background-color: white;
      border-radius: 12px;
      padding: 30px;
      width: 400px;
      text-align: center;
      box-shadow: 0 10px 30px rgba(0,0,0,0.2);
    }

    .modal-titulo {
      font-family: 'Poppins', sans-serif;
      font-size: 18px;
      font-weight: bold;
      color: #403F48;
      margin-bottom: 15px;
    }

    .modal-texto {
      font-family: 'Inter', sans-serif;
      font-size: 16px;
      color: #404F48;
      text-align: center;
      margin-bottom: 25px;
    }

    .modal-botones {
      display: flex;
      justify-content: space-between;
      gap: 15px;
    }

    .btn-modal {
      font-family: 'Inter', sans-serif;
      font-size: 16px;
      border: none;
      border-radius: 8px;
      padding: 12px 25px;
      cursor: pointer;
      transition: all 0.3s ease;
      flex: 1;
    }

    .btn-cancelar {
      background-color: var(--color-cancelar);
      color: var(--color-fondo-header);
    }

    .btn-confirmar {
      background-color: var(--color-exito);
      color: var(--color-fondo-header);
    }

    .btn-cancelar:hover, .btn-confirmar:hover {
      transform: scale(1.05);
      box-shadow:  0 2px 8px rgba(0,0,0,0.15);
    }

    /* Mensajes de error y exito */
    .mensaje-error {
      background-color: #F8D7DA;
      border: 1px solid #F5C2C7;
      color: #842029;
      font-family: 'Inter', sans-serif;
      font-size: 14px;
      padding: 10px 15px;
      border-radius: 6px;
      margin-top: 8px;
      display: none;
    }

    .mensaje-error.activo {
      display: block;
    }

    .mensaje-exito {
      background-color: #D1E7DD;
      border: 1px solid #BADBCC;
      color: #0F5132;
      font-family: 'Inter', sans-serif;
      font-size: 16px;
      padding: 15px 20px;
      border-radius: 8px;
      margin-bottom: 20px;
      text-align: center;
    }

    .campo-error {
      border-color:var(--color-error) !important;
    }

    /* Alerta de exito */
    .alerta-exito {
      position: fixed;
      top: 20px;
      right: 20px;
      background-color: var(--color-texto);
      color: white;
      padding: 15px 25px;
      border-radius: 8px;
      font-family: 'Inter, sams-serif';
      font-size: 16px;
      box-shadow: 0 4px 15px rgba(0,0,0,0.2);
      z-index: 1002;
      opacity: 0;
      transform: translatex(100%);
      transition: all 0.3s ease;
    }

    .alerta-exito.activa {
      opacity: 1;
      transform: translateX(0);
    }

    /* Responsivo */
    @media (max-width: 1024px) {
      .fila-formulario {
        flex-direction: column;
        gap: 20px;
      }

      .contenedor-carga {
        width: 100%;
      }

      .vista-previa-item {
        position: relative;
        width: 1350px;
        height: 200px;
        border: 1px solid var(--color-borde);
        border-radius: 12px;
        overflow: 12px;
      }
    }

    @media (max-width: 768px) {
      .encabezado-reporte {
        padding: 15px 20px;
        flex-direction: column;
        gap: 15px;
      }

      .volver-detalles {
        font-size: 14px;
      }

      .titulo-principal {
        font-size: 28px;
      }

      .formulario-reporte {
        padding: 25px;
      }

      .fila-evidencias {
        flex-direction: column;
        align-items: flex-start;
        gap: 10px;
      }

      .modal-contenido {
        padding: 20px;
        margin: 20px;
      }
    }
  </style>
</head>
<body>
  <!-- ENCABEZADO -->
   <header class="encabezado-reporte">
    <img src="imagenes/logo.png" alt="Logo Comunicado Digital" class="logo">
    <a href="ver_denuncia.php?id=<?= $id_denuncia ?>" class="volver-detalles" id="volverDetalles">Volver a detalles</a>
  </header>

  <!-- CONTENEDOR PRINCIPAL -->
   <div class="contenedor-reporte">
    <h1 class="titulo-principal">Reportar Denuncia</h1>

    <?php if (isset($mensaje_exito)): ?>
      <div class="mensaje-exito">
        <?= $mensaje_exito ?>
    </div>
    <?php endif; ?>

    <?php if (!empty($errores)): ?>
      <?php foreach ($errores as $error): ?>
        <div class="mensaje-error activo"><?= $error ?></div>
      <?php endforeach; ?>
    <?php endif; ?>
    
    <form class="formulario-reporte" id="formularioReporte" method="POST" enctype="multipart/form-data">
      <!-- FILA 1: Titulo y Fecha -->
       <div class="fila-formulario">
        <div class="campo-formulario">
          <label class="etiqueta">Título de la denuncia</label>
          <input type="text" class="input-texto" id="tituloDenuncia" value="<?= htmlspecialchars($denuncia['titulo']) ?>" readonly>
        </div>

        <div class="campo-formulario">
          <label class="etiqueta">Fecha de publicación</label>
          <div class="contenedor-fecha">
            <input type="text" class="input-fecha" id="fechaPublicacion" value="<?= date('d/m/Y', strtotime($denuncia['fecha'])) ?>" readonly>
            <button type="button" class="icono-calendario">📅</button>
          </div>
        </div>
      </div>
    
      <!-- FILA2: Motivo y Usuario --> 
       <div class="fila-formulario">
        <div class="campo-formulario">
          <label class="etiqueta">Motivo</label>
          <select class="selector" id="motivoReporte" name="motivo" required>
            <option value="">Selecciona un motivo</option>
            <option value="contenido_falso">Contenido falso</option>
            <option value="lenguaje_ofensivo">Lenguaje ofensivo</option>
            <option value="plagio">Plagio</option>
            <option value="informacion_personal_expuesta">Información personal expuesta</option>
          </select>
          <div class="mensaje-error" id="errorMotivo"></div>
        </div>

        <div class="campo-formulario">
          <label class="etiqueta">Usuario</label>
          <input type="text" class="input-texto" id="usuarioReporte" value="<?= htmlspecialchars($usuario_nombre) ?>" readonly>
        </div>
      </div>

      <!-- SECCION EVIDENCIAS -->
       <div class="seccion-evidencias">
          <div class="fila-evidencias">
            <label class="etiqueta">Evidencia</label>
            <span class="indicacion-evidencias">(Puedes cargar hasta 3 imágenes en formato "jpeg", que comprueben el motivo del reporte.)</span>
          </div>

          <div class="contenedor-carga">
            <div class="contenedor-boton-archivo">
              <button type="button" class="btn-elegir-archivo" id="btnElegirArchivo">Elegir archivo</button>
              <span class="text-archivo" id="textoArchivo">No se ha seleccionado ningún archivo</span>
            </div>

            <input type="file" id="inputArchivo" name="evidencias[]"accept=".jpg,.jpeg" multiple style="display: none;">

            <div class="vista-previa-contenedor" id="vistaPreviaContenedor">
              <!-- Las vistas previas se generara aqui dinamicamente -->
            </div>
          </div>
        </div>

        <!-- CAMPO COMENTARIO -->
         <div class="campo-formulario">
          <label class="etiqueta">Comentario *</label>
          <div class="contenedor-contador">
            <textarea class="textarea-comentario" id="comentarioReporte" name="comentario" placeholder="Describe detalladamente el motivo detu reporte (minimo 100 caracteres)" maxlength="1000" required></textarea>
            <div class="contador-caracteres" id="contadorCaracteres">0/1000</div>
          </div>
          <div class="mensaje-error" id="errorComentario"></div>
        </div>

        <!-- BOTON ENVIAR -->
         <div class="contenedor-btn-enviar">
          <button type="button" class="btn-enviar" id="btnEnviarReporte">Enviar reporte</button>
        </div>
      </form>
    </div>

    <!-- MODAL CONFIRMAR ENVIO -->
     <div class="modal" id="modalEnviar">
      <div class="modal-contenido">
        <h3 class="modal-titulo">¿Estás seguro de enviar tu reporte?</h3>
        <p class="modal-texto">Una vez enviado, no podrás modificar el reporte.</p>
        <div class="modal-botones">
          <button class="btn-modal btn-cancelar" id="btnCancelarEnviar">Cancelar</button>
          <button class="btn-modal btn-confirmar" id="btnConfirmarEnviar">Confirmar</button>
      </div>
    </div>
  </div>

  <!-- MODAL VOLVER A DETALLES -->
   <div class="modal" id="modalVolver">
    <div class="modal-contenido">
      <h3 class="modal-titulo">¿Estás seguro de volver a los detalles de esta denuncia?</h3>
      <div class="modal-botones">
        <button class="btn-modal btn-cancelar" id="btnCancelarVolver">Cancelar</button>
        <button class="btn-modal btn-confirmar" id="btnConfirmarVolver">Confirmar</button>
      </div>
    </div>
  </div>

  <!-- ALERTA DE EXITO -->
   <div class="alerta-exito" id="alertaExito">
    ¡Tu reporte fue enviado a los administradores!
  </div>

    <script>
      // Variables globales
      let archivosSeleccionados = [];
      const maxArchivos = 3;

      // Elementos del DOM
      const formularioReporte = document.getElementById('formularioReporte');
      const btnElegirArchivo = document.getElementById('btnElegirArchivo');
      const inputArchivo = document.getElementById('inputArchivo');
      const textoArchivo = document.getElementById('textoArchivo');
      const vistaPreviaContenedor = document.getElementById('vistaPreviaContenedor');
      const comentarioReporte = document.getElementById('comentarioReporte');
      const contadorCaracteres = document.getElementById('contadorCaracteres');
      const motivoReporte = document.getElementById('motivoReporte');
      const btnEnviarReporte = document.getElementById('btnEnviarReporte');
      const modalEnviar = document.getElementById('modalEnviar');
      const btnCancelarEnviar = document.getElementById('btnCancelarEnviar');
      const btnConfirmarEnviar = document.getElementById('btnConfirmarEnviar');
      const alertaExito = document.getElementById('alertaExito');
      const errorMotivo = document.getElementById('errorMotivo');
      const errorComentario = document.getElementById('errorComentario');

      // Inicializacion
      document.addEventListener('DOMContentLoaded', function() {
        configurarEventos();
      });

      function configurarEventos() {
        // Gestion de archivos
        btnElegirArchivo.addEventListener('click', function(e) {
          if (archivosSeleccionados.length < maxArchivos) {
            inputArchivo.click();
          }
        });

        inputArchivo.addEventListener('change', function(e) {
          manejarSeleccionArchivos(e.target.files);
        });

        // Contador de caracteres
        comentarioReporte.addEventListener('focus', function() {
          contadorCaracteres.style.display = 'block';
        });

        comentarioReporte.addEventListener('blur', function() {
          if (this.value.length === 0) {
            contadorCaracteres.style.display = 'none';
          }
        });

        comentarioReporte.addEventListener('input', function() {
          const longitud = this.value.length;
          contadorCaracteres.textContent = `${longitud}/1000`;

          if (longitud < 100) {
            contadorCaracteres.classList.add('error');
          } else {
            contadorCaracteres.classList.remove('error');
          }

          // Auto-expandir
          this.style.height = 'auto';
          this.style.height = (this.scrollHeight) + 'px';
        });

        // Envio del formulario
        btnEnviarReporte.addEventListener('click', function() {
          if (validarFormulario()) {
            mostrarModal(modalEnviar);
          }
        });

        btnCancelarEnviar.addEventListener('click', function() {
          ocultarModal(modalEnviar);
        });

        btnConfirmarEnviar.addEventListener('click', function() {
          ocultarModal();
        });

        // Cerrar modal al hacer click fuera 
        modalEnviar.addEventListener('click', function(e) {
          if (e.target === this) {
            ocultarModal(modalEnviar);
          }
        });

         // Modal volver a detalles 
        const volverLink = document.getElementById('volverDetalles');
        const modalVolver = document.getElementById('modalVolver');
        const btnCancelarVolver = document.getElementById('btnCancelarVolver');
        const btnConfirmarVolver = document.getElementById('btnConfirmarVolver');

        if (volverLink && modalVolver && btnCancelarVolver && btnConfirmarVolver) {
          volverLink.addEventListener('click', function(e) {
            e.preventDefault();
            mostrarModal(modalVolver);
          });

          btnCancelarVolver.addEventListener('click', function() {
            ocultarModal(modalVolver);
          });

          btnConfirmarVolver.addEventListener('click', function() {
            window.location.href = 'ver_denuncia.php?id=<?= $id_denuncia ?>';
          });

          // Cerrar modal al hacer click afuera
          modalVolver.addEventListener('click', function(e) {
            if (e.target === this) {
              ocultarModal(modalVolver);
            }
          });
        }
      }

      function manejarSeleccionArchivos(archivos) {
        const archivosArray = Array.from(archivos);
        const archivosRestantes = maxArchivos - archivosSeleccionados.length;
        const archivosAProcesar = archivosArray.slice(0, archivosRestantes);

        archivosAProcesar.forEach(archivo => {
          // Validar que sea imagen JPEG
          if (archivo.type.startsWith('image/') &&
              (archivo.type === 'image/jpeg' ||
                archivo.name.toLowerCase().endswith('.jpg') ||
                archivo.name.toLowerCase().endswith('jpeg'))) {
            archivosSeleccionados.push(archivo);

            const reader = new FileReader();
            reader.onload = function(e) {
              crearVistaPrevia(archivo.name, e.target.result);
            };
            reader.readAsDataURL(archivo);
          } else {
            mostrarError(errorComentario, 'Solo se permiten archivos JPEG');
          }
        });

        actualizarInterfazArchivos();
        inputArchivo.value = '';
      }

      function crearVistaPrevia(nombreArchivo, url) {
        const item = document.createElement('div');
        item.className = 'vista-previa-item';

        const img = document.createElement('img');
        img.src = url;
        img.alt = nombreArchivo;

        const btnEliminar = document.createElement('button');
        btnEliminar.className = 'btn-eliminar-archivo';
        btnEliminar.innerHTML = 'x';
        btnEliminar.addEventListener('click', function(e) {
          e.stopPropagation();
          eliminarArchivo(nombreArchivo);
        });

        item.appendChild(img);
        item.appendChild(btnEliminar);
        vistaPreviaContenedor.appendChild(item);
      }

      function eliminarArchivo(nombreArchivo) {
        archivosSeleccionados = archivosSeleccionados.filter(archivo => archivo.name !== nombreArchivo);

        // Recrear todas las vistas previas
        vistaPreviaContenedor.innerHTML = '';
        archivosSeleccionados.forEach(archivo => {
          const reader = new FileReader();
          reader.onload = function(e) {
            crearVistaPrevia(archivo.name, e.target.result);
          };
          reader.readAsDataURL(archivo);
        });

        actualizarInterfazArchivos();
      }

      function actualizarInterfazArchivos() {
        const cantidad = archivosSeleccionados.length;

        if (cantidad === 0) {
          textoArchivo.textContent = 'No se ha seleccionado ningún archivo';
        } else if (cantidad === 1) {
          textoArchivo.textContent = '1 archivo seleccionado';
        } else {
          textoArchivo.textContent = `${cantidad} archivos seleccionados`; 
        }

        if (cantidad >= maxArchivos) {
          btnElegirArchivo.classList.add('limite-alcanzado');
          btnElegirArchivo.disabled = true;
        } else {
          btnElegirArchivo.classList.remove('limite-alcanzado');
          btnElegirArchivo.disabled = false;
        }
      }

      function mostrarModal(modal) {
        modal.style.display = 'flex';
      }

      function ocultarModal(modal) {
        modal.style.display = 'none';
      }

      function validarFormulario() {
        let valido = true;

        // Validar motivo
        if (!motivoReporte.value) {
          mostrarError(errorMotivo, 'Selecciona un motivo');
          motivoReporte.classList.add('campo-error');
          valido = false;
        } else {
          ocultarError(errorMotivo);
          motivoReporte.classList.remove('campo-error');
        }

        // Validar comentario
        const comentario = comentarioReporte.value.trim();
        if (!comentario) {
          mostrarError(errorComentario, 'El comentario es obligatorio');
          comentarioReporte.classList.add('campo-error');
          valido = false;
        } else if (comentario.length < 100) {
          mostrarError(errorComentario, 'El comentario debe tener al menos 100 caracteres');
          comentarioReporte.classList.add('campo-error');
          valido = false;
        } else if (comentario.length > 1000) {
          mostrarError(errorComentario, 'Has alcanzado el límite de 1000 caracteres');
          comentarioReporte.classList.add('campo-error');
          valido = false;
        } else {
          ocultarError(errorComentario);
          comentarioReporte.classList.remove('campo-error');
        }

        return valido;
      }

      function mostrarError(elemento, mensaje) {
        elemento.textContent = mensaje;
        elemento.classList.add('activo');

        // Ocultar mensaje despues de 5 segundos
        setTimeout(() => {
          ocultarError(elemento);
        }, 5000);
      }

      function ocultarError(elemento) {
        elemento.classList.remove('activo');
        elemento.textContent = '';
      }

      function enviarFormulario() {
        // Crear FormData para enviar archivos
        const formData = new FormData(formularioReporte);

        // Agregar archivos seleccionados
        archivosSeleccionados.forEach((archivo, index) => {
          formData.append('evidencias[]', archivo);
        });

        // Mostrar loading
        btnEnviarReporte.disabled = true;
        btnEnviarReporte.textContent = 'Enviando...';
        ocultarModal(modalEnviar);

        // Enviar formulario
        fetch('', {
          method: 'POST',
          body: formData
        })
        .then(response => {
            if (!response.ok) {
              throw new Error('Error en la respuesta del servidor');
            }
            return response.text();
        })
        .then(html => {
          // Recargar la pagina para mostrar mensaje de exito o error
          location.reload();
        })
        .catch(error => {
          console.error('Error:', error);
          alert('Error al enviar el reporte');
          btnEnviarReporte.disabled = false;
          btnEnviarReporte.textContent = 'Enviar reporte';
        });
      }

      // Modal volver a detalles
      document.getElementById('volverDetalles').addEventListener('click', function(e) {
        e.preventDefault();
        mostrarModal(modalVolver);
      });

      document.getElementById('btnCancelarVolver').addEventListener('click', function() {
        ocultarModal(modalVolver);
      });

      document.getELementById('btnConfirmarVolver').addEventListener('click', function() {
        window.location.href = 'ver_denuncia.php?id=<?= $id_denuncia ?>';
      });

    </script>
  </body>
  </html>