<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Centro de Ayuda</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&family=Inter:wght@400;500&display=swap" rel="stylesheet">
    <style>
        /* Estilos generales */
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Poppins', sans-serif;
            background-color: #f8f9fa;
            color: #333;
            line-height: 1.6;
        }

        .container {
            max-width: 1250px;
            margin: 0 auto;
            padding: 0 20px;
        }

        /* Más espacio entre logo y enlace en el header */
        .header .container {
            max-width: 1450px; /* aumenta la separación entre ambos elementos */
        }

        /* Header */
        .header {
            background-color: #061F3E;
            padding: 15px 0;
            position: sticky;
            top: 0;
            z-index: 100;
        }

        .header-content {
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .logo-menu2 img { 
          height: 50px;
        } 

        .back-link {
            color: #FFFFFF;
            font-family: 'Poppins', sans-serif;
            font-size: 24px;
            font-weight: 600;
            text-decoration: none;
            transition: all 0.3s ease;
        }

        .back-link:hover {
            color: #1661AC;
            text-decoration: underline;
        }

        /* Título principal */
        .main-title {
            margin-top: 30px;
            margin-bottom: 20px;
        }

        .main-title h1 {
            font-family: 'Poppins', sans-serif;
            font-size: 32px;
            font-weight: 700;
            color: #1661AC;
            margin-bottom: 10px;
        }

        .divider {
            height: 3px;
            background-color: #061F3E;
            width: 100%;
            max-width: 1250px;
            margin-bottom: 20px;
        }

        .description {
            font-family: 'Poppins', sans-serif;
            font-size: 16px;
            font-weight: 600;
            color: #403F48;
            margin-bottom: 40px;
            max-width: 900px;
        }

        /* Secciones FAQ */
        .section {
            margin-bottom: 40px;
        }

        .section-title {
            font-family: 'Poppins', sans-serif;
            font-size: 20px;
            font-weight: 700;
            color: #1661AC;
            margin-bottom: 20px;
            padding-bottom: 8px;
            border-bottom: 1px solid #eaeaea;
        }

        /* Acordeón FAQ */
        .faq-item {
            border-bottom: 1px solid #eaeaea;
            margin-bottom: 10px;
        }

        .faq-question {
            padding: 18px 20px 18px 0;
            cursor: pointer;
            position: relative;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .faq-question h3 {
            font-family: 'Poppins', sans-serif;
            font-size: 20px;
            font-weight: 600;
            color: #403F48;
            margin: 0;
            flex: 1;
        }

        .faq-icon {
            font-size: 10px;
            color: #1661AC;
            transition: transform 0.3s ease;
            margin-left: 15px;
            min-width: 10px;
        }

        .faq-answer {
            max-height: 0;
            overflow: hidden;
            transition: max-height 0.5s ease;
            padding: 0 20px;
        }

        .faq-answer.show {
            max-height: 4000px;
            padding-bottom: 20px;
        }

        .faq-answer ul, .faq-answer ol {
            margin-left: 20px;
            margin-top: 10px;
            margin-bottom: 10px;
        }

        .faq-answer li {
            font-family: 'Inter', sans-serif;
            font-size: 16px;
            color: #403F48;
            margin-bottom: 8px;
        }

        .faq-answer p {
            font-family: 'Inter', sans-serif;
            font-size: 16px;
            color: #403F48;
            margin-bottom: 15px;
        }

        .faq-answer img {
            max-width: 100%;
            height: auto;
            border-radius: 8px;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
            margin: 20px 0;
            display: block;
        }

        /* Fila de imágenes en línea */
        .images-row {
            display: flex;
            gap: 16px;
            align-items: center;
            justify-content: flex-start;
            margin: 12px 0 8px;
            flex-wrap: wrap;
        }
        .images-row img {
            flex: 1 1 380px;
            max-width: 600px;
            height: auto;
        }

        .faq-item.active .faq-icon {
            transform: rotate(90deg);
        }

        /* Responsividad */
        @media (max-width: 768px) {
            .container {
                padding: 0 15px;
            }

            .header-content {
                flex-direction: column;
                align-items: flex-start;
            }

            .logo {
                margin-bottom: 10px;
            }

            .back-link {
                font-size: 20px;
                align-self: flex-end;
            }

            .main-title h1 {
                font-size: 28px;
            }

            .section-title {
                font-size: 18px;
            }

            .faq-question h3 {
                font-size: 18px;
            }

            .faq-answer li, .faq-answer p {
                font-size: 15px;
            }
        }

        @media (max-width: 480px) {
            .main-title h1 {
                font-size: 24px;
            }

            .back-link {
                font-size: 18px;
            }

            .description {
                font-size: 15px;
            }

            .faq-question {
                padding: 15px 15px 15px 0;
            }

            .faq-question h3 {
                font-size: 16px;
            }

            .faq-answer li, .faq-answer p {
                font-size: 14px;
            }
        }

        /* Estados hover */
        .faq-question:hover h3 {
            color: #1661AC;
        }
    </style>
</head>
<body>
    <!-- Header -->
    <header class="header">
        <div class="container header-content">
            <div class="logo-menu2">
                <img src="imagenes/logo.png" alt="logo">
            </div>
            <a href="inicio.php" class="back-link">Volver</a>
        </div>
    </header>

    <!-- Contenido principal -->
    <main class="container">
        <!-- Título principal -->
        <div class="main-title">
            <h1>Centro de ayuda</h1>
            <div class="divider"></div>
            <p class="description">
                En esta sección encontrarás respuestas a preguntas frecuentes y una guía rápida sobre cómo utilizar las funciones principales de la plataforma. Explora cada categoría para conocer cómo registrarte, navegar, publicar contenido y administrar tu cuenta.
            </p>
        </div>

        <!-- Sección: Registro e inicio de sesión -->
        <section class="section">
            <h2 class="section-title">Registro e inicio de sesión</h2>
            
            <div class="faq-item">
                <div class="faq-question">
                    <img src="imagenes/flechaDespliegue.png" class="faq-icon">
                    <h3>¿Cómo crear una cuenta?</h3>
                </div>
                <div class="faq-answer">
                    <ul>
                        <li>En la esquina superior derecha del inicio del sitio web encontrarás el botón “Regístrate”, haz clic en ese botón y se abrirá el formulario donde vas a proporcionar tus datos.</li>
                        <img src="imagenes/ayuda/registro.png" style="max-width: 500px; height: 200px; margin: 15px 0;">
                        <li>Escribe tu nombre de usuario, introduce un correo electrónico válido al que tengas acceso, crea una contraseña segura, confirma tu contraseña y por último lee y acepta nuestros Términos y condiciones y Políticas de privacidad. ¡Ten en cuenta que debes cumplir con los requisitos de la contraseña para poder crear tu cuenta!.</li>
                        <div class="images-row">
                            <img src="imagenes/ayuda/vistaDelFormularioAlIngresar.png">
                            <img src="imagenes/ayuda/verificacioncheckscreacioncontraseñaregistro.png">
                        </div>
                        <li>Presiona el botón “Continuar”, tras un registro exitoso se te enviará un correo de verificación a tu dirección.</li>
                        <div class="images-row">
                            <img src="imagenes/ayuda/clicbotonregistrar.png">
                            <img src="imagenes/ayuda/zoombotonregistrar.png">
                        </div>
                        <li>Abre el correo y haz clic en el enlace para activar tu cuenta.</li>
                        <div class="images-row">
                            <img src="imagenes/ayuda/Capturalinkcorreo.png">
                        </div>
                        <li>¡Listo! Ya formas parte de nuestra comunidad. Inicia sesión y comienza a informarte.</li>
                        <div class="images-row">
                            <img src="imagenes/ayuda/VerificacionExitosa.jpeg">
                            <img src="imagenes/ayuda/LoginDespuesDeValidarCuentaCreada.png">
                        </div>
                    </ul>
                </div>
            </div>
            
            <div class="faq-item">
                <div class="faq-question">
                    <img src="imagenes/flechaDespliegue.png" class="faq-icon">
                    <h3>¿Olvidé mi contraseña, cómo puedo recuperarla?</h3>
                </div>
                <div class="faq-answer">
                    <ul>
                        <li>En la página de inicio de sesión, haz clic en "Olvidé mi contraseña"</li>
                        <div class="images-row">
                            <img src="imagenes/ayuda/vistaDelLoginParaIngresarAOlvideMiContraseña.png">
                            <img src="imagenes/ayuda/PalabraOlvideMiContraseña.png">
                        </div>
                        <li>Introduce la dirección de correo electrónico asociada a tu cuenta.</li>
                        <div class="images-row">
                            <img src="imagenes/ayuda/IngresarCorreoRecuperarContraseña.png">
                        </div>
                        <li>Recibirás un correo con un enlace para reestablecer tu contraseña.</li>
                        <div class="images-row">
                            <img src="imagenes/ayuda/linkReestablecerContraseña.png">
                        </div>
                        <li>Ingresa tu nueva contraseña para reestablecer.</li>
                        <div class="images-row">
                            <img src="imagenes/ayuda/VistaReestablecerContraseña.png">
                        </div>
                        <li>De clic en el boton "Actualizar contraseña"</li>
                        <div class="images-row">
                            <img src="imagenes/ayuda/ClicBotonActualizarContraseña.png">
                        </div>
                        <li>¡Ahora inicia sesión con tu nueva contraseña!.</li>
                        <div class="images-row">
                            <img src="imagenes/ayuda/MensajeExitosoDeReestablecimientoDeContraseña.png">
                        </div>
                    </ul>
                </div>
            </div>
            
            <div class="faq-item">
                <div class="faq-question">
                    <img src="imagenes/flechaDespliegue.png" class="faq-icon">
                    <h3>¿Puedo cambiar mi contraseña después de registrarme?</h3>
                </div>
                <div class="faq-answer">
                    <ul>
                        <li>Sí, puedes cambiar tu contraseña, desde la sección de configuración de perfil.</li>
                        <li>Haz clic en el icono de perfil que se encuentra en la esquina superior derecha, luego selecciona "Configurar Perfil".</li>
                        <div class="images-row">
                            <img src="imagenes/ayuda/SeñalandoPerfil.png">
                            <img src="imagenes/ayuda/LlegarAPerfil.png">
                        </div>
                        <li>Ingresa tu contraseña actual para poder guardar los cambios a realizar, luego ingresa tu nueva contraseña (anótala en un lugar seguro).</li>
                        <div class="images-row">
                            <img src="imagenes/ayuda/ContraseñaActualyNueva.png">
                        </div>
                        <li>Luego haz clic en "Guardar cambios".</li>
                        <div class="images-row">
                            <img src="imagenes/ayuda/ClicBotonGuardarNuevaContraseña.png">
                            <img src="imagenes/ayuda/BotonCambiarContraseñaDesdePerfil.png">
                        </div>
                        <li>Y ¡listo! tu contraseña ha sido actualizada exitosamente.</li> 
                        <div class="images-row">
                            <img src="imagenes/ayuda/MensajeDeExitoAlCambiarLaContraseña.png"> 
                            <img src="imagenes/ayuda/ZoomCambioExitosoDeContraseña.png">
                        </div>
                    </ul>
                </div>
            </div>
        </section>

        <!-- Sección: Enviar noticias -->
        <section class="section">
            <h2 class="section-title">Enviar noticias</h2>
            
            <div class="faq-item">
                <div class="faq-question">
                    <img src="imagenes/flechaDespliegue.png" class="faq-icon">
                    <h3>¿Cómo puedo enviar una noticia?</h3>
                </div>
                <div class="faq-answer">
                    <ul>
                        <li>Para enviar una noticia sigue los siguientes pasos:</li>
                        <li>Haz clic en el botón "Enviar una noticia" en la parte inferior izquierda de la página</li>
                        <div class="images-row">
                            <img src="imagenes/ayuda/BotonEnviarNoticia.png">
                            <img src="imagenes/ayuda/ZoomBotonEnviarUnaNoticia.png">
                        </div>
                        <li>Completa el formulario con categoria, título, descripcion y etiquetas relevantes.</li>
                        <div class="images-row">
                            <img src="imagenes/ayuda/VistaEnviarNoticia.png">
                        </div>
                        <li>Puedes adjuntar hasta 3 imágenes relacionadas.</li>
                        <div class="images-row">
                            <img src="imagenes/ayuda/BotonElegirArchivoEnviarNoticia.png">
                        </div>
                        <li>Dando clic en esta casilla indicas que deseas que tu noticia no sea comentada (Nadie podra realizar ningun comentario de tu noticia).</li>
                        <div class="images-row">
                            <img src="imagenes/ayuda/CasillaDeBloqueoDeComentarios.png">
                            <img src="imagenes/ayuda/ZoomCasillaBloquearComentarios.png">
                        </div>
                        <li>Revisa tu noticia y haz clic en el boton "Enviar Noticia".</li>
                        <div class="images-row">
                            <img src="imagenes/ayuda/BotonEnviarNoticiaDelFormulario.png">
                        </div>
                        <li>Te pedira que confirmes el envio de la noticia, daz clic en el boton "Confirmar" para el envio.</li>
                        <div class="images-row">
                            <img src="imagenes/ayuda/ConfirmarModalDeEnvioDeNoticias.png">
                        </div>
                        <li>Y ¡Listo!.</li>
                        <div class="images-row">
                            <img src="imagenes/ayuda/EnvioExitosoDeLaNoticia.png">
                        </div>
                        <li>Y Tu noticia ha sido enviada exitosamente.</li>
                        <div class="images-row">
                            <img src="imagenes/ayuda/ZoomDelEnvioExitosoDeLaNoticia.png">
                        </div>
                        <li>Ahora debes esperar a que un editor revise y apruebe tu noticia para su publicación!!!</li>
                    </ul>
                </div>
            </div>
        </section>

        <!-- Sección: Reportes de contenido -->
        <section class="section">
            <h2 class="section-title">Reportes de contenido</h2>
            
            <div class="faq-item">
                <div class="faq-question">
                    <img src="imagenes/flechaDespliegue.png" class="faq-icon">
                    <h3>¿Cómo reportar contenido inapropiado?</h3>
                </div>
                <div class="faq-answer">
                    <ul>
                        <li>Para reportar contenido inapropiado tanto para denuncias como noticias son los mismos pasos!.</li>
                        <li>Dar clic ya sea en la imagen o el titulo de la noticia o denuncia.</li>
                        <div class="images-row">
                            <img src="imagenes/ayuda/ClicTituloOImagen.png">
                        </div>
                        <li>Cuando muestre mas informacion de la noticia o denuncia, debajo de la descripcion busca este icono</li>
                        <div class="images-row">
                            <img src="imagenes/ayuda/VistaVerNoticia.png">
                            <img src="imagenes/ayuda/IconoDeReporte.png">
                        </div>
                        <li>Haz clic en ese icono, se mostrara el formulario para que completes el reporte.</li>
                        <div class="images-row">
                            <img src="imagenes/ayuda/FormularioDeReporte.png">
                        </div>
                        <li>Selecciona el motivo del reporte: Contenido inapropiado, Informacion falsa, Derechos de autor, etc.</li>
                        <div class="images-row">
                            <img src="imagenes/ayuda/SeleccionarMotivoDelReporte.png">
                        </div>
                        <li>Proporciona detalles adicionales si es necesario.</li>
                        <div class="images-row">
                            <img src="imagenes/ayuda/DatosAdicionalesReporte.png">
                        </div>
                        <li>Haz clic en "Enviar reporte"</li>
                        <div class="images-row">
                            <img src="imagenes/ayuda/BotonEnviarReporte.png">
                        </div>
                        <li>Te pedira que confirmes el envio del reportes daz clic en el boton "confirmar" para enviar el reporte.</li>
                        <div class="images-row">
                            <img src="imagenes/ayuda/ConfirmarModalReporte.png">
                        </div>
                        <li>Y ¡Listo! Tu reporte ha sido enviado exitosamente.</li>
                    </ul>
                </div>
            </div>
        </section>

        <!-- Sección: Enviar denuncias -->
        <section class="section">
            <h2 class="section-title">Enviar denuncias</h2>
            
            <div class="faq-item">
                <div class="faq-question">
                    <img src="imagenes/flechaDespliegue.png" class="faq-icon">
                    <h3>¿Cómo enviar una denuncia?</h3>
                </div>
                <div class="faq-answer">
                    <ul>
                        <li>Busca "Denuncias" en la barra de categorias.</li>
                        <div class="images-row">
                            <img src="imagenes/ayuda/CategoriaDenuncias.png">
                        </div>
                        <li>Haz clic en dicha categoría, se mostrara un listado de denuncias.</li>
                        <div class="images-row">
                            <img src="imagenes/ayuda/ListadoDeDenuncias.png">
                        </div>
                        <li>Busca el boton "Enviar una denuncia" en la esquina inferior izquierda.</li>
                        <div class="images-row">
                            <img src="imagenes/ayuda/ClicBotonEnviarUnaDenuncia.png">
                            <img src="imagenes/ayuda/ZoomBotonEnviarUnaDenuncia.png">
                        </div>
                        <li>Da clic sobre el boton y te mostrara un formulario para ingresar los detalles de la denuncia.</li>
                        <li>Debes ingresar toda la información requerida en el formulario, titulo, descripción, fecha del evento denunciado y opcionalmente imágenes.</li>
                        <div class="images-row">
                            <img src="imagenes/ayuda/FormularioDeDenuncias.png">
                        </div>
                        <li>Puedes buscar imagenes que respalden tu denuncia y adjuntarlas en el formulario, desde este boton.</li>
                        <div class="images-row"> 
                            <img src="imagenes/ayuda/BotonElegirArchivoEnviarDenuncia.png">
                            <img src="imagenes/ayuda/ZoomElegirArchivosEnviarDenuncia.png">
                        </div>
                        <li>Verifica que toda la información sea correcta y luego haz clic en "Enviar denuncia".</li>
                        <div class="images-row">
                            <img src="imagenes/ayuda/BotonEnviarDenuncia.png">
                        </div>
                        <li>Te pedira confirmar el envío de la denuncia, dar clic en "Confirmar".</li>
                        <div class="images-row">
                            <img src="imagenes/ayuda/ConfirmarModalEnvioDenuncia.png">
                        </div>
                        <li>Y ¡Listo! Tu denuncia ha sido enviada correctamente.</li>
                        <div class="images-row">
                            <img src="imagenes/ayuda/MensajeDeExitoDelEnvioDeLaDenuncia.png">
                        </div>
                        <li>Sera revisada por los administradores antes de ser publicada.</li>
                    </ul>
                </div>
            </div>
        </section>

        <!-- Sección: Realizar comentarios -->
        <section class="section">
            <h2 class="section-title">Realizar comentarios</h2>
            
            <div class="faq-item">
                <div class="faq-question">
                    <img src="imagenes/flechaDespliegue.png" class="faq-icon">
                    <h3>¿Cómo realizo un comentario en una noticia?</h3>
                </div>
                <div class="faq-answer">
                    <ul>
                        <li>Busca la noticia a la que deseas comentar.</li>
                        <li>Dar clic ya sea en la imagen o en el título de la noticia.</li>
                        <div class="images-row">
                            <img src="imagenes/ayuda/ClicTituloOimagenParaLlegarAComentarios.png">
                        </div>
                        <li>Cuando veas mas detalles de la noticia que haz elegido...</li>
                        <div class="images-row">
                            <img src="imagenes/ayuda/VistaDeLaNoticiaAcomentar.png">
                        </div>
                        <li>Te diriges hacia la parte de abajo de dicha noticia.</li>
                        <li>Ahi podras visualizar el apartado de "Comentarios".</li>
                        <li>Pero solo podras realizar tu comentario si estos NO han sido bloqueados para dicha noticia.</li>
                        <div class="images-row">
                            <img src="imagenes/ayuda/VisualizacionApartadoDeComentarios.png">
                        </div>
                        <li>Escribes tu comentario con respecto a dicha noticia, en este apartado.</li>
                        <div class="images-row">
                            <img src="imagenes/ayuda/CajaDeComentariosDeNoticias.png">
                        </div>
                        <li>Luego clic en el boton "Publicar".</li>
                        <div class="images-row">
                            <img src="imagenes/ayuda/BotonPublicarComentario.png">
                        </div>
                        <li>Y ¡Listo! Tu comentario será visible para otros usuarios en la parte de abajo del apartado.</li>
                        <div class="images-row">
                            <img src="imagenes/ayuda/VisualizacionDelComentario.png">
                        </div>
                    </ul>
                </div>
            </div>
        </section>
    </main>

    <script>
        // Funcionalidad del acordeón FAQ
        document.addEventListener('DOMContentLoaded', function() {
            const faqQuestions = document.querySelectorAll('.faq-question');
            
            faqQuestions.forEach(question => {
                question.addEventListener('click', function() {
                    const faqItem = this.parentElement;
                    const answer = this.nextElementSibling;
                    
                    // Cerrar otros items abiertos en la misma sección
                    const currentSection = faqItem.closest('.section');
                    const openItems = currentSection.querySelectorAll('.faq-item.active');
                    
                    openItems.forEach(item => {
                        if (item !== faqItem) {
                            item.classList.remove('active');
                            item.querySelector('.faq-answer').classList.remove('show');
                        }
                    });
                    
                    // Alternar el item actual
                    faqItem.classList.toggle('active');
                    
                    if (faqItem.classList.contains('active')) {
                        answer.classList.add('show');
                    } else {
                        answer.classList.remove('show');
                    }
                });
            });
        });
    </script>
</body>
</html>