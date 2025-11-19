<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Reportar Noticia - Comunicado Digital</title>
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

    /* ENCABEZADO */
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
      font-size: 24px;;
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

    /* CONTENEDOR PRINCIPAL */
    .contenedor-reporte {
      max-width: 1300px;
      margin: 40px auto;
      padding: 0 20px;
    }

    /* TITULO PRINCIPAL */
    .titulo-principal {
      font-family: 'Poppins', sans-serif;
      font-size: 32px;
      font-weight: 700;
      color: var(--color-primario);
      text-align: center;
      margin-bottom: 40px;
    }

    /* FORMULARIO */
    .formulario-reporte {
      background-color: white;
      border-radius: 16px;
      padding: 40px;
      box-shadow: 0 4px 20px rgba(0,0,0,0.08);
    }

    .fila-formulario {
      display: flex;
      gap: 30px;
      margin-bottom: 30px;
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
      transform: scale(1.02);
    }

    .input-texto:hover, .input-fecha:hover, .selector:hover, .textarea-comentario:hover {
      transform: scale(1.01);
      box-shadow: 0 2px 8px rgba(0,0,0,0.1);
    }

    .contador-fecha {
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

    /* SECCION EVIDENCIAS */
    .section-evidencias {
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
      transition: all 0.3s ease;
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
      padding: 30px 20px;
      cursor: pointer;
      transition: all 0.3s ease;
      width: 135px;
      height: 35px;
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
      width: 350px;
      height: 200px;
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

    .btn-sliminar-archivo:hover {
      background: white;
      transform: scale(1.1);
    }

    /* CONTADOR DE CARACTERES */
    .contenedor-contador {
      position: relative;
    }

    .contador-caracteres {
      position: absolute;
      right: 15px;
      bottom: 15px;
      font-family: 'Inter', sans-serif;
      font-size: 16px;
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

    /* BOTON ENVIAR */
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

    /* MODALES */
    .modal {
      position: fixed;
      top: 0;
      left: 0;
      width: 100%;
      height: 100%;
      background-color: rgba(0,0,0,0.5);
      display: flex;
      justify-content: center;
      align-items: center;
      z-index: 1000;
      opacity: 0;
      visibility: hidden;
      transition: all 0.3s ease;
    }

    .modal.activo {
      opacity: 1;
      visibility: visible;
    }

    .modal-contenido {
      background-color: white;
      border-radius: 12px;
      padding: 30px;
      width: 90%;
      max-width: 500px;
      box-shadow: 0 10px 30px rgba(0,0,0,0.2);
      transform: translateY(-20px);
      transition: transform 0.3s ease;
    }

    .modal.activo .modal-contenido {
      transform: translateY(0);
    }

    .modal-titulo {
      font-family: 'Poppins', sans-serif;
      font-size: 16px;
      font-weight: 700;
      color: var(--color-texto-oscuro);
      text-align: center;
      margin-bottom: 15px;
    }

    .modal-texto {
      font-family: 'Inter', sans-serif;
      font-size: 16px;
      color: var(--color-texto-oscuro);
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
      box-shadow: 0 2px 8px rgba(0,0,0,0.15);
    }

    /* VISTA AMPLIA DE IMAGEN */
    .vista-amplia {
      position: fixed;
      top: 0;
      left: 0;
      width: 100%;
      height: 100%;
      background-color: rgba(0,0,0,0.9);
      display: flex;
      justify-content: center;
      align-items: center;
      z-index: 1001;
      opacity: 0;
      visibility: hidden;
      transition: all 0.3s ease;
    }

    .vista-amplia.activa {
      opacity: 1;
      visibility: visible;
    }

    .vista-amplia img {
      width: 80%;
      height: auto;
      max-height: 80vh;
      object-fit: contain;
      border-radius: 8px;
    }

    .btn-cerrar-vista {
      position: absolute;
      top: 20px;
      right: 20px;
      background: none;
      border: none;
      color: var(--color-error);
      font-size: 30px;
      cursor: pointer;
      transition: all 0.3s ease;
    }

    .btn-cerrar-vista:hover {
      transform: scale(1.1);
    }

    /* MENSAJES DE ERROR */
    .mensaje-error {
      background-color: #F8D7DA;
      border: 1px solid #F5C2C7;
      color: #842029;
      font-family: 'Inter', sans-serif;
      font-size: 16px;
      padding: 10px 15px;
      border-radius: 6px;
      margin-top: 8px;
      display: none;
      animation: sacudida 0.5s ease;
    }

    .mensaje-error.activo {
      display: block;
    }

    @keyframes sacudida {
      0%, 100% { transform: translateX(0); }
      25% { transform: translateX(-5px); }
      75% { transform: translateX(5px); }
    }

    .campo-error {
      border-color: var(--color-error) !important;
      animation: sacudida 0.5s ease;
    }

    /* ALERTA DE EXITO */
    .alerta-exito {
      position: fixed;
      top: 20px;
      right: 20px;
      background-color: var(--color-exito);
      color: white;
      padding: 15px 25px;
      border-radius: 8px;
      font-family: 'Inter', sans-serif;
      font-size: 16px;
      box-shadow: 0 4px 15px rgba(0,0,0,0.2);
      z-index: 1002;
      opacity: 0;
      transform: translateX(100%);
      transition: all 0.3s ease;
    }

    .alerta-exito.activa {
      opacity: 1;
      transform: translateX(0);
    }

    /* RESPONSIVO */
    @media (max-width: 1024px) {
      .fila-formulario {
        flex-direction: column;
        gap: 20px;
      }
      
      .contenedor-carga {
        width: 100%;
      }

      .vista-previa-item {
        width: 100%;
        max-width: 350px;
      }
    }

    @media (max-width: 768px) {
      .encabezado-reporte {
        padding: 15px 20px;
        flex-direction: column;
        gap: 15px;
      }

      .volver-detalles {
        font-size: 20px;
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
      }

      .vista-amplia img {
        width: 95%;
        height: auto;
        max-height: 80vh;
      }
    }
  </style>
</head>
<body>
  <!-- ENCABEZADO -->
   <header class="encabezado-reporte">
    <img src="imagenes/logo.png" alt="Logo Comunicado Digital" class="logo">
    <a href="#" class="volver-detalles" id="volverDetalles">Volver a detalles</a>
  </header>

  <!-- CONTENEDOR PRINCIPAL -->
   <div class="contenedor-reporte">
    <h1 class="titulo-principal">Reportar Noticia</h1>
    
    <form class="formulario-reporte" id="formularioReporte">
      <!-- FILA 1: Titulo y Fecha -->
       <div class="fila-formulario">
        <div class="campo-formulario">
          <label class="etiqueta">Título de la noticia</label>
          <input type="text" class="input-texto" id="tituloNoticia" readonly>
        </div>

        <div class="campo-formulario">
          <label class="etiqueta">Fecha de publicación</label>
          <div class="contenedor-fecha">
            <input type="text" class="input-fecha" id="fechaPublicacion" readonly>
            <button type="button" class="icono-calendario">📅</button>
          </div>
        </div>
      </div>
    
      <!-- FILA2: Motivo y Usuario --> 
       <div class="fila-formulario">
        <div class="campo-formulario">
          <label class="etiqueta">Motivo</label>
          <select class="selector" id="motivoReporte">
            <option value="">Selecciona un motivo</option>
            <option value="informacion_erroea">Información errónea</option>
            <option value="informacion_erroea">Contenido ofensivo</option>
            <option value="informacion_erroea">Violación de derechos de autor</option>
            <option value="informacion_erroea">Spam</option>
            <option value="informacion_erroea">Otro</option>
          </select>
          <div class="mensaje-error" id="errorMotivo"></div>
        </div>

        <div class="campo-formulario">
          <label class="etiqueta">Usuario</label>
          <input type="text" class="input-texto" id="usuarioReporte" readonly>
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

            <input type="file" id="inputArchivo" accept=".jpg,.jpeg" multiple style="display: none;">

            <div class="vista-previa-contenedor" id="vistaPreviaContenedor">
              <!-- Las vistas previas se generara aqui dinamicamente -->
            </div>
          </div>
        </div>

        <!-- CAMPO COMENTARIO -->
         <div class="campo-formulario">
          <label class="etiqueta">Comentario</label>
          <div class="contenedor-contador">
            <textarea class="textarea-comentario" id="comentarioReporte" placeholder="Escribe un comentario" maxlength="1000"></textarea>
            <div class="contador-caracteres" id="contadorCaracteres">0/1000</div>
          </div>
          <div class="mensaje-error" id="errorComentario"></div>
        </div>

        <!-- BOTON ENVIAR -->
         <div class="contenedor-btn-enviar">
          <button type="button" class="btn-enviar" id="btnEnviarReportar">Enviar reporte</button>
        </div>
      </form>
    </div>

    <!-- Modal para reportar noticia -->
 <div id="modal-reporte" class="modal" style="display: none; position: fixed; top: 0; left: 0; width: 100%; heigth: 100%; background-color: rgba(0,0,0,0.5); z-index: 1000;">
    <div style="position: absolute; top: 50%; left: 50%; transform: translate(-50%, -50%); background: white; padding: 30px; border-radius: 8px; width: 500px; max-width: 500px; max-width: 90%">
        <h3 style="font-family: 'Poppins', sans-serif; color: #1661AC; margin-bottom: 20px;">Reportar Noticia</h3> 

          <form id="form-reporte">
            <div style="margin-bottom: 15px;">
              <label style="display: block; margin-bottom: 5px; font-weight: bold;">Noticia del reporte:</label>
              <select id="motivo-reporte" style="width: 100%; padding: 8px; border: 1px solid #B1B1B1; border-radius: 4px;">
                <option value="">Seleccione un motivo</option>
                <option value="contenido_inapropiado">Contenido inapropiado</option>
                <option value="informacion_falsa">Información falsa</option>
                <option value="spam">Spam</ooption>
                <option value="derechos_autor">Derechos de autor</option>
                <option value="otro">Otro</option>
              </select>
          </div>

          <div style="margin-bottom: 15px;">
            <label style="display: block; margin-bottom: 5px; font-weigth: bold;">Evidencias (JPEG):</label>
            <input type="file" id="evidencias-reporte" accept=".jpg,.jpeg" multiple style="width: 100%;">
            <small style="color: #74737C;">Puede seleccionar múltiples archivos JPEG</small>
          </div>

          <div style="margin-bottom: 15px;">
            <label style="display: blockl margin-bottom: 5px; font-weight: bold;">Comentario (100-1000 caracteres):</label>
            <textarea id="comentario-reporte" style="width: 100%; height: 120px; padding: 8px; border: 1px solid $B1B1B1; border-radius: 4px; resize: vertical;" placeholder="Describa detalladamente el motivo de su reporte..."></textarea>
          </div>

          <div style="display: flex; gap: 10px; justify-content: flex-end;">
            <button type="button" onclick="cerrarModalReporte()" style="padding: 10px 20px; background: #B1B1B1; color: white; border: none; border-radius: 4px; cursor: pointer;">Cancelar</button>
            <button type="button" id="btn-enviar-reporte" onclick="enviarReporte(<?= $id_noticia ?>)" style="padding: 10px 20px; background: #1661AC; color: while; border: none; border-radius: 4px; cursor: pointer;">Enviar Reporte</button>
          </div>
        </form>
      </div>
    </div>

    <!-- Funcion para abrir e  modal -->
     <script>
      function abrirModalReporte() {
        document.genElementById('modal-reporte').style.display = 'block';
      }
      </script>


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

    <!-- MODAL CONFIRMAR ENVIO -->
     <div class="modal" id="modalEnviar">
      <div class="modal-contenido">
        <h3 class="modal-titulo">¿Estás seguro de enciar tu reporte?</h3>
        <p class="modal-texto">Tu reporte será  enviado a los administradores para que sea revisado</p>
        <div class="modal-botones">
          <button class="btn-modal btn-cancelar" id="btnCancelarEnviar">Cancelar</button>
          <button class="btn-modal btn-confirmar" id="btnConfirmarEnviar">Confirmar</button>
        </div>
      </div>
    </div>

    <!-- VISTA AMPLIA DE IMAGEN -->
     <div class="vista-amplia" id="vistaAmplia">
      <button class="btn-cerrar-vista" id="btnCerrarVista">x</button>
      <img id="imagenAmpliada" src="" alt="vista ampliada">
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
      const volverDetalles = document.getElementById('volverDetalles');
      const modalVolver = document.getElementById('modalVolver');
      const btnCancelarVolver = document.getELementById('btnCancelarVolver');
      const btnConfirmarVolver = document.getElementById('btnConfirmarVolver');
      
      const btnElegirArchivo = document.getElementById('btnElegirArchivo');
      const inputArchivo = document.getElementById('inputArchivo');
      const textoArchivo = document.getElementById('textoArchivo');
      const vistaPreviaContenedor = document.getElementById('vistaPreviaContenedor');

      const comentarioReporte = document.getElementById('comentarioReporte');
      const contadorCaracteres = document.getElemenById('contadorCaracteres');
      const motivoReporte = document.getElemenById('motivoReporte');

      const btnEnviarReporte = document.getElementById('btnEnviarReporte');
      const modalEnviar = document.getElemenById('modalEnviar');
      const btnCancelarEnviar = document.getElemenById('btnCancelarEnviar');
      const btnConfirmarEnviar = document.getElemenById('btnConfirmarEnviar');

      const vistaAmplia = document.getElemenById('vistaAmplia');
      const imagenAmpliada = document.getElemenById('imagenAmpliada');
      const btnCerrarVista = document.getElemenById('btnCerrarVista');

      const alertaExito = document.getElemenById('alertaExito');

      const errorMotivo = document.getElemenById('errorMotivo');
      const errorComentario = document.getElemenById('errorComentario');

      // Inicializacion
      document.addEventListener('DOMContentLoaded', function() {
        //Simular datos de la noticias 
        document.getElementById('tituloNoticia').value = 'Titulo de la noticia de ejemplo';
        document.getElementById('fechaPublicacion').value = '15/03/2024';
        document.getElementById('usuarioReporte').value = 'Usuario Actual';

        // Configurar eventos
        configurarEventos();
      });

      function configurarEventos() {
        // Modal volver a detalles
        volverDetalles.addEventListener('click', function(e) {
          e.preventDefault();
          mostrarModal(modalVolver);
        });

        btnCancelarVolver.addEventListener('click', function() {
          ocultarModal(modalVolver);
        });

        btnConfirmarVolver.addEventListener('click', function() {
          // Redirigir a detalles de noticia (dimulado, no backend)
          window.location.href = 'detalles_noticias.html';
        });

        // Gestion de archivos
        btnElegirArchivo.addEventListener('click', function() {
          if (archivosSeleccionados.length < maxArchivos) {
            inputArchivo.click();
          }
        });

        inputArchivo.addEventListener('change', function(e) {
          manejarSeleccionArchivos(e.target.files);
        });

        // Contador de caracteres
        comentarReporte.addEventListener('focus', function() {
          contadorCaracteres.classList.add('visible');
        });

        comentarReporte.addEventListener('blur', function() {
          if (this.value.length === 0) {
            contadorCaracteres.classList.remove('visible');
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
        }); 

        // Envio del formulario
        btnEnviarReporte.addEventListener('click', function() {
          if (validarFormulario()) {
            enviarReporteBackend(1);
          }
        });

        btnCancelarEnviar.addEventListener('click', function() {
          ocultarModal(modalEnviar);
        });

        btnConfirmarEnviar.addEventListener('click', function() {
          enviarReporte();
        });

        // Vista ampliada de imagen
        btnCerrarVista.addEventListener('click', function() {
          ocultarVistaAmplia();
        });
      }

      function manejarSeleccionArchivos(archivos) {
        const archivosArray = Array.from(archivos);
        const archivosRestantes = maxArchivos - archivosSeleccionados.length;
        const archivosAProcesar = archivosArray.slice(0, archivosRestantes);

        archivosAProcesar.forEach(archivo => {
          if (archivo.type.startWith('image/') && (archivo.type === 'image/jpeg' || archivo.name.toLowerCase().endsWith('.jpg') || archivo.name.toLowerCase().endWith('.jpeg'))) {
            archivosSeleccionados.push(archivo);

            const reader = new FileReader();
            reader.onload = function(e) {
              crearVistaPrevia(archivo.name, e.target.result);
            };
            reader.readAsDataURL(archivo);
          }
        });

        actualizarInterfazArchivos();
      }

      function crearVistaPrevia(nombreArchivo, url) {
        const item = document.createElement('div');
        item.className = 'vista-previa-item';

        const img = document.createElement('img');
        img.src = url;
        img.alt = nombreArchivo;
        img.addEventListener('click', function() {
          mostrarVistaAmplia(url);
        });

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
        archivoSeleccionados.forEach(archivo => {
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
          textoArchivo.textContent = '1 archivos seleccionado';
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
      
      function mostrarVistaAmplia(url) {
        imagenAmpliada.src = url;
        vistaAmplia.classList.add('activa');
      }

      function ocultarVistaAmplia() {
        vistaAmplia.classList.remove('activa');
      }

      function motrarModal(modal) {
        modal.classList.add('activo');
      }

      function ocultarModal(modal) {
        modal.classList.remove('activo');
      }

      function validarFormulario() {
        let valido = true;

        // Validar motivo
        if (!motivoReporte.value) {
          mostrarError(errorMotivo, 'Seleccione un motivo');
          motivoReporte.classList.add('campo-error');
          valido = false;;
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

        // Ocultar automaticamente despues de 5 segundos
        setTimeout(() => {
          elemento.classList.remove('activo');
        }, 5000)
      }

      function ocultarError(elemento) {
        elemento.classList.remove('activo');
      }

      function enviarReporte() {
        // Simular envio del formulario
        console.log('Enviando reporte...');
        console.log('Motivo:', motivoReporte.value);
        console.log('Comentario:', comentarioReporte.value);
        console.log('Archivos:', archivosSeleccionados.length);

        // Cerrar modal
        ocultarModal(modalEnviar);

        // Mostrar alerta de exito
        alertaExito.classList.add('activa');

        // Redirigir despues de 2 segundos
        setTimeOut(() => {
          alertaExito.classList.remove('activa');
          // Redirigir a detalles de noticia (simular)
          window.location.href = 'ver_noticia.html'
        }, 2000);
      }

      // Funcion para manejar el envio de reportes
      function enviarReporteBackend(noticiaId) {
        const motivo = document.getElementById('motivoReporte').value;
        const comentario = document.getElementById('comentarioReporte').value;

        // Validaciones basicas en el frontend
        if (!motivo) {
          alert('Selecciona un motivo');
          return;
        }

        if (!comentario) {
          alert('El comentario es obligatorio');
          return;
        }

        if (comentario.length < 100) {
          alert('El comentario debe tener al menos 100 caracteres.');
          return;
        }

        if (comentario.length > 1000) {
          alert('Haz alcanzado el límite de 1000 caracteres')ñ
          return;
        }

        // Crear FormData 
        const formData = new FormData();
        formData.append('noticia_id', noticiaId);
        formData.append('motivo', motivo);
        formData.append('comentario', comentario);

        // Agregar archivos de evidencia
        archivosSeleccionados.forEach(archivo => {
          formData.append('evidencias[]', archivo);
        });

        // Mostrar loading
        const btnEnviar = document.getElementById('btnEnviarReportar');
        const originalText = btnEnviar.innerHTML;
        btnEnviar.innerHTML = 'Enviando...';
        btnEnviar.disabled = true;

        // Enviar peticion
        fetch('/api/reportes_noticias.php', {
          method: 'POST',
          body: formData
        })
        .then(response => response.json())
        .then(data => {
          if (data.success) {
            alert('Reporte enviado correctamente');
            // Cerrar modal o limpiar formulario
            windows.location.href = 'ver_noticia.php?id=' + noticiaId;
          } else {
            alert('Error: ' + data.message);
          }
        })
        .catch(error => {
          console.error('Error:', error);
          alert('Error al enviar el reporte');
        })
        .finally(() => {
          btnEnviar.innerHTML = originalText;
          btnEnviar.disabled = false;
        });
      }

      // Funcion para cerrar modal de reporte 
      function cerrarModalReporte() {
        const modal = document.getElementById('modal-reporte');
        if (modal) {
          modal.style.display = 'none';
        }

        // Limpiar formulario
        document.getElementById('motivo-reporte').value = '';
        document.getElementById('comentario-reporte').value = '';
        document.getElementById('evidencias-reporte').value = '';
        document.getElementById('contador-caracteres').textContent = '0/1000';
      }

      // Contador de caracteres para el comentario
      document.addEventListener('DOMContentLoaded', function() {
        const comentarioTextarea = document.getElementById('comentario-reporte');
        const contador = document.getElementById('contador-caracteres');

        if (comentarioTextarea && contador) {
          comentarioTextarea.addEventListener('input', function() {
            const caracteres = this.value.length;
            contador.textContent = `${caracteres}/1000`;

            if (caracteres < 100) {
              contador.style.color = '#ff0000';
            } else if (caracteres > 1000) {
              contador.style.color = '#ff0000';
            } else {
              contador.style.color = '#74737C';
            }
          });
        }
      });
    </script>
  </body>
  </html>