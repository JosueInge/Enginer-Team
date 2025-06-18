<?php

if (session_status() === PHP_SESSION_NONE ){
    session_start();
}

$usuario_logueado = isset($_SESSION['usuario_id']);
$nombre_usuario = $_SESSION['usuario_nombre'] ?? 'Invitado';
$imagen_usuario = $_SESSION['usuario_imagen'] ?? 'imagenes/avatar-default.png';
$pagina_actual = basename($_SERVER['PHP_SELF']);
$mostrar_login = !$usuario_logueado && $pagina_actual === 'home.php';
?>

<button class="boton-ayuda" onclick="toggleAsistente()">¿Necesita ayuda?</button>

<div class="asistente-container" id="asistente">
    <div class="asistente-header">
        Asistente virtual
        <button class="cerrar" onclick="toggleAsistente()">X</button>
    </div>

    <div class="asistente-body" id="chat-cuerpo">
        <?php if(!$usuario_logueado): ?>
            <p style="text-align: center;">¡Hola! Para acceder al chat, primero debes registrate o iniciar sesion</p>
            <div style="text-align: center; margin-top: 10px; font-weight: bold;">
                <img src="imagenes/chatbot.png" alt="ChatBot" style="width: 120px; height: auto; " />
            </div>
            <div style="text-align: center; margin-top: 20px;">
                <a href="login.php"
                    style="display: inline-block;
                        background-color: #0d5c9b;
                        color: white;
                        padding: 10px 20px;
                        text-decoration: none;
                        border-radius: 4px;
                        font-weight: bold;">
                    Iniciar Sesion
                </a>
            </div>
        <?php else: ?>
            <div style="text-align: center; margin-top: 10px;">
                <img src="imagenes/chatbot.png" alt="ChatBot" style="width: 100px; height: auto; margin-bottom: 10px;">
            </div>
            <div class="mensaje">
                <strong>ChatBot</strong><br>
                Hola,<strong><?= htmlspecialchars($nombre_usuario) ?></strong> En que puedo ayudarte?
            </div>
            <div class="asistente-opciones">
                <button onclick="enviarPregunta(this)" data-pregunta="¿Como se envia una noticia?">¿Como se envia una noticia?</button>
                <button onclick="enviarPregunta(this)" data-pregunta="¿Como se reporta una noticia?">¿Como se reporta una noticia?</button>
                <button onclick="enviarPregunta(this)" data-pregunta="¿Cuales son las politicas del periodico digital?">¿Cuales con las politicas del periodico digital?</button>
            </div>
        <?php endif; ?>
    </div>

    <?php if ($usuario_logueado): ?>
        <div class="asistente-input">
            <input type="text" id="entradaUsuario" placeholder="Escribe un mensaje..,"/>
            <button onclick="procesarEntrada()">Enviar</button>
        </div>
    <?php endif; ?>
    </div>

<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0" />
<title>Asistente Virtual</title>
<style>
    body {
        font-family: Arial, sans-serif;
    }
    .boton-ayuda {
        position: fixed;
        bottom: 1px;
        left: 20px;
        background-color: #0d5c9b;
        color: white;
        border: none;
        border-radius: 10px 10px 0 0;
        padding: 12px 20px;
        cursor: pointer;
        z-index: 1000;
        font-weight: bold;
    }

    .asistente-container {
        display: none;
        position: fixed;
        bottom: 60px;
        left: 20px;
        width: 300px;
        max-height: 400px;
        background: white;
        border: 1px solid #ccc;
        border-radius: 8px 8px 0 0;
        box-shadow: 0 4px 8px rgba(0,0,0,0.2);
        z-index: 1001;
        overflow: hidden;
    }
    .asistente-header {
        background: #0d5c9b;
        color: white;
        padding: 10px;
        text-align: center;
        position: relative;
    }
    .asistente-header .cerrar {
        position: absolute;
        top: 5px;
        right: 10px;
        background: none;
        border: none;
        color: white;
        font-size: 18px;
        cursor: pointer;
    }
    .asistente-body {
        padding: 10px;
        background: #f0f0f0;
        overflow-y: auto;
        max-height: 300px;
    }
    .mensaje {
        background: #e0e0e0;
        margin-bottom: 10px;
        padding: 8px 12px;
        border-radius: 4px;
    }
    .mensaje strong {
        color: #0d5c9b
    }
    .asistente-opciones {
        margin-top: 10px;
    }
    .asistente-opciones button {
        display: block;
        width: 100%;
        background: #0d5c9b;
        color: white;
        border: none;
        padding: 8px;
        margin-bottom: 5px;
        border-radius: 4px;
        cursor: pointer;
    }
    .asistente-input {
        display: flex;
        border-top: 1px solid #ccc;
    }
    .asistente-input input {
        flex: 1;
        padding: 8px;
        border: none;
    }
    .asistente-input button {
        background: #0d5c9b;
        color: white;
        border: none;
        padding: 8px 12px;
        cursor: pointer;
    }
    .mensaje-usuario {
        background-color: #cce5ff;
        text-align: right;
        margin-left: auto;
        margin-right: 0;
        border-radius: 10px 10px 0 10px;
        padding: 10px;
        margin-top: 10px;
        width: fit-content;
        max-width: 80%;
    }
    .mensaje-bot {
        background: #e0e0e0;
        border-radius: 10px 10px 10px 0;
        padding: 10px;
        margin-top: 10px;
        width: fit-content;
        max-width: 80%;
    }
</style>

</head>


<script>
    function toggleAsistente() {
        const asistente = document.getElementById('asistente');
        asistente.style.display = (asistente.style.display === 'block') ? 'none' : 'block';
    }

    const nombreUsuario = "<?= htmlspecialchars($nombre_usuario) ?>";

    function enviarPregunta(button) {
        const pregunta = button.getAttribute("data-pregunta");
        const chat = document.querySelector(".asistente-body");

        const msgUser = document.createElement("div");
        msgUser.classname = "mensaje-usuario";
        msgUser.innerHTML = `<strong?>${nombreUsuario}</strong><br>${pregunta}`;
        chat.appendChild(msgUser);

        const respuesta = obtenerRespuesta(pregunta);

        const msgBot = document.createElement("div");
        msgBot.className = "mensaje-bot";
        msgBot.innerHTML = `<strong>ChatBot</strong><br>${respuesta}`;
        chat.appendChild(msgBot);

        chat.scrollTop = chat.scrollHeight;
    }

    function obtenerRespuesta(pregunta) {
        switch (pregunta) {
            case "¿Como se envia una noticia?":
                return `Hola, <strong>${nombreUsuario}</strong>, para enviar una noticia debes ir al boton "Enviar una noticia" en la parte inferior derecha de esta pantalla.`;

            case "¿Como se reporta una noticia?":
                return `Puedes reportar una noticia haciendo clic en el boton de reportar, si los administradores le dan prioridad a tu reporte, la noticia sera eliminada.`;

            case "¿Cuales son las politicas del periodico digital?":
                return `Puedes leer nuestras politicas de uno en la session "sobre noostros". Respetamos la privacidad y promovemos la participacion responsable.`;

            default:
                return `Lo siento, no entendi tu pregunta.`;
        }
    }
</script>
<script>
    function procesarEntrada() {
        const input = document.getElementById("entradaUsuario");
        const texto = input.value.trim();
        if (!texto) return;

        const chat = document.querySelector(".asistente-body");

        const msgUser = document.createElement("div");
        msgUser.className = "mensaje-usuario";
        msgUser.innerHTML = `<strong>${nombreUsuario}</strong><br>${texto}`;
        chat.appendChild(msgUser);

        const respuesta = generarRespuesta(texto);
        const msgBot = document.createElement("div");
        msgBot.className = "mensaje-bot";
        msgBot.innerHTML = `<strong>ChatBot</strong><br>${respuesta}`;
        chat.appendChild(msgBot);

        chat.scrollTop = chat.scrollHeight;
        input.value = "";
    }

    function generarRespuesta(texto) {
        const pregunta = texto.toLowerCase();

        if (pregunta.includes("enviar") && pregunta.includes("noticia")) {
            return "Para enviar una noticia debes ir al botón 'Enviar una noticia' en la parte inferior derecha de esta pantalla.";

        } else if (pregunta.includes("reportar") && pregunta.includes("noticia")) {
            return "Puedes reportar una noticia desde la opción de reportes en el menu.";

        } else if (pregunta.includes("politica") || pregunta.includes("privacidad")) {
            return "Puedes revisar nuestras politicas en la sección 'Sobre nosotros'.";

        } else if (pregunta.includes("hola") || pregunta.includes("buenas")) {
            return "¡Hola! ¿En que puedo ayudarte?"; 

        } else if (pregunta.includes("ok") || pregunta.includes("esta bien")) {
            return "Genial, me alegra que la informacion sea util";

        } else if (pregunta.includes("como hago publica una noticia") || pregunta.includes("noticia publica")) {
            return "Solo los administradores pueden hacer las noticias publicas, si deceas que una noticia se haga publica, debes enviar una noticia desde el boton 'Enviar noticia' de esta forma un administrador la revisara para luego hacerla publica.";

        } else if (pregunta.includes("solo") || pregunta.includes("reportar")) {
            return "Buena pregunta. No, las noticias solo pueden ser eliminadas si mas de un usuario envian un reporte, los administradoren evaluaran el reporte y procederan con la eliminacion de la misma.";

        } else if (pregunta.includes("cambiar") || pregunta.includes("contraseña")) {
            return "Puedes cambiar tu contraseña las veces que lo desees. Te gustaria saber como acceder a los ajustes para realizar un cambio de contraseña?"
        
        } else if (pregunta.includes("si") || pregunta.includes("contraseña")) {
            return "Perfecto. Para acceder a la configuracion debes acceder al icono de y dar clic en 'configura perfil' una vez en la vista tendras un apartado donde podras hacer el cambio de tu contraseña. Pero deberas ingresar tu contraseña actual para realizar el cambio."
        
        } else if (pregunta.includes("gracias") || pregunta.includes("por")) {
            return "Denada. ¡Un gusto de ayudarte!"
        }



        return "Lo siento, no entendi tu mensaje. ¿Podrias intentar con otra pregunta?";
    }
    
</script>
<script>
    document.addEventListener("DOMContentLoaded", function () {
        const input = document.getElementById("entradaUsuario");
        if (input) {
            input.addEventListener("keydown", function (event) {
                if (event.key === "Enter") {
                    event.preventDefault();
                    procesarEntrada();
                }
            });
        }
    });
</script>
</body>
</html>