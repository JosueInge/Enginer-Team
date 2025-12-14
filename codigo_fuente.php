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
  header("Location: noticias.php");
  exit();
}

$id_noticia = intval($_GET['id']);
$usuario_id = $_SESSION['usuario_id'] ?? null;
$usuario_nombre = $_SESSION['usuario_nombre'] ?? 'Usuario';

// Obtener datos de la noticia 
$stmt = $conexion->prepare("SELECT titulo, fecha, autor FROM propuestas_noticias WHERE id = ?");
$stmt->bind_param("i", $id_noticia);
$stmt->execute();
$noticia = $stmt->get_result()->fetch_assoc();
$stmt->close();

if (!$noticia) {
  header("Location: noticias.php");
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
        $ruta_destino = 'imagenes/reportes_noticias/' . $nombre_archivo;

        // Crear directotio si no existe
        if (!is_dir('imagenes/reportes_noticias')) {
          mkdir('imagenes/reportes_noticias', 0755, true);
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

  // Si no hay errores, guardar reporte en la tabla CORRECTA
  if (empty($errores)) {
    $evidencias_string = !empty($evidencias) ? implode(',', $evidencias) : null;

    // CORRECCIÓN: Insertar en reportes_noticias en lugar de reportes_denuncias
    $stmt = $conexion->prepare("INSERT INTO reportes_noticias (noticia_id, titulo_noticia, fecha_publicacion, motivo, usuario_id, usuario_nombre, evidencias, comentario, fecha_reporte) VALUES (?, ?, ?, ?, ?, ?, ?, ?, NOW())");
    $stmt->bind_param("isssisss", $id_noticia, $noticia['titulo'], $noticia['fecha'], $motivo, $usuario_id, $usuario_nombre, $evidencias_string, $comentario);

    if ($stmt->execute()) {
      $mensaje_exito = "Reporte enviado correctamente";
    } else {
      $errores[] = "Error al guardar el reporte en la base de datos: " . $conexion->error;
    }
   $stmt->close();
  }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title><?= htmlspecialchars($noticia['titulo']) ?> - Comunicado Digital</title>
  <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600;700&family=Inter:wght@400;600&display=swap" rel="stylesheet">
  <style>
    /* ESTILOS IGUALES QUE ANTES... */
  </style>
</head>
<body>
  <!-- ENCABEZADO -->
   <header class="encabezado-reporte">
    <img src="imagenes/logo.png" alt="Logo Comunicado Digital" class="logo">
    <a href="ver_noticia.php?id=<?= $id_noticia ?>" class="volver-detalles" id="volverDetalles">Volver a detalles</a>
  </header>

  <!-- CONTENEDOR PRINCIPAL -->
   <div class="contenedor-reporte">
    <h1 class="titulo-principal">Reportar Noticia</h1>

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
          <label class="etiqueta">Título de la noticia</label>
          <input type="text" class="input-texto" id="tituloNoticia" value="<?= htmlspecialchars($noticia['titulo']) ?>" readonly>
        </div>

        <div class="campo-formulario">
          <label class="etiqueta">Fecha de publicación</label>
          <div class="contenedor-fecha">
            <input type="text" class="input-fecha" id="fechaPublicacion" value="<?= date('d/m/Y', strtotime($noticia['fecha'])) ?>" readonly>
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
            <option value="contenido_inapropiado">Contenido inapropiado</option>
            <option value="informacion_falsa">Información falsa</option>
            <option value="derechos_autor">Derechos de autor</option>
            <option value="spam">Spam</option>
            <option value="informacion_erronea">Otro</option>
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

            <input type="file" id="inputArchivo" name="evidencias[]" accept=".jpg,.jpeg" multiple style="display: none;">

            <div class="vista-previa-contenedor" id="vistaPreviaContenedor">
              <!-- Las vistas previas se generara aqui dinamicamente -->
            </div>
          </div>
        </div>

        <!-- CAMPO COMENTARIO -->
         <div class="campo-formulario">
          <label class="etiqueta">Comentario *</label>
          <div class="contenedor-contador">
            <textarea class="textarea-comentario" id="comentarioReporte" name="comentario" placeholder="Describe detalladamente el motivo de tu reporte (minimo 100 caracteres)" maxlength="1000" required></textarea>
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
      <h3 class="modal-titulo">¿Estás seguro de volver a los detalles de esta noticia?</h3>
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
          enviarFormulario();
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
            window.location.href = 'ver_noticia.php?id=<?= $id_noticia ?>';
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
                archivo.name.toLowerCase().endsWith('.jpg') ||
                archivo.name.toLowerCase().endsWith('.jpeg'))) {
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
          // Recargar la pagina para mostrar mensaje de exito
          location.reload();
        })
        .catch(error => {
          console.error('Error:', error);
          alert('Error al enviar el reporte');
          btnEnviarReporte.disabled = false;
          btnEnviarReporte.textContent = 'Enviar reporte';
        });
      }
    </script>
  </body>
  </html>