<?php
if (session_status() === PHP_SESSION_NONE ){
    session_start();
}

$usuario_logueado = isset($_SESSION['usuario_id']);
$nombre_usuario = $_SESSION['usuario_nombre'] ?? 'Invitado';
$imagen_usuario = $_SESSION['usuario_imagen'] ?? 'imagenes/avatar-default.png';
$pagina_actual = basename($_SERVER['PHP_SELF']);
$mostrar_login = !$usuario_logueado && $pagina_actual === 'home.php';

// PROCESAR SOLICITUD AJAX PARA LA IA
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['mensaje'])) {
    $mensaje = trim($_POST['mensaje']);

    // Configuración de la API de OpenAI
    $apiKey = "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"; // <-- Coloca tu API Key aquí
    $endpoint = "https://api.openai.com/v1/chat/completions";

    $data = [
        "model" => "gpt-4o-mini",
        "messages" => [
            ["role" => "system", "content" => "Eres un asistente virtual del periódico digital “Comunicado Digital”. 
Tu función es ayudar a los usuarios únicamente con temas relacionados con el sitio: noticias, políticas, servicios y funciones disponibles. 
Los usuarios pueden referirse al sitio como: “esta app”, “esta aplicación”, “este periódico”, “este periódico digital”; entiende que son equivalentes.

Comportamiento:
1. Saludos: responde de forma natural y amistosa, siempre ofreciendo ayuda.
2. Responde preguntas sobre:
   - Políticas de privacidad (sección Sobre nosotros en el encabezado superior).
   - Cómo cambiar contraseña y foto de perfil (ícono tuerca → Configurar perfil).
   - Cómo enviar una noticia (botón inferior derecho).
   - Cómo enviar una denuncia (categoría Denuncias → botón inferior derecho).
   - Cómo comentar una noticia (clic en título → sección comentarios).
   - Cómo reportar una noticia (ícono entre contenido y comentarios).
3. Si la pregunta está fuera del contexto, responde: “Lo siento, no entendí tu mensaje. ¿Podrías reformularlo o preguntar de otra manera ?”
4. Mantén un tono cordial y profesional.

Contexto del sitio:
- Administradores revisan noticias, denuncias y reportes antes de publicarlas.
- Categorías: Inicio (mixto), Clima, Deportes, Educación, Turismo, Denuncias.
- Buscador en la barra derecha: las búsquedas deben hacerse en Inicio o en la categoría correcta.
- Más de 3 reportes en una noticia = prioridad para administradores, posible eliminación.
- Noticias pueden bloquear comentarios (marcando opción en formulario).
- Comentarios: usuarios pueden editar/borrar los suyos; administradores solo pueden eliminar.
- Imagen por defecto para nuevos usuarios.
- Para ver detalles de una noticia: clic en el título."],
            ["role" => "user", "content" => $mensaje]
        ],
        "temperature" => 0.7
    ];

    $ch = curl_init($endpoint);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        "Content-Type: application/json",
        "Authorization: Bearer " . $apiKey
    ]);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));

    $response = curl_exec($ch);
    curl_close($ch);

    $result = json_decode($response, true);
    $respuestaIA = $result['choices'][0]['message']['content'] ?? "Lo siento, no encontré información.";

    echo json_encode(["respuesta" => $respuestaIA]);
    exit;
}
?>

<button class="boton-ayuda" onclick="toggleAsistente()">¿Necesita ayuda?</button>

<div class="asistente-container" id="asistente">
    <div class="asistente-header">
        Asistente virtual
        <button class="cerrar" onclick="toggleAsistente()">X</button>
    </div>

    <div class="asistente-body" id="chat-cuerpo">
        <?php if(!$usuario_logueado): ?>
            <p style="text-align: center;">¡Hola! Para acceder al chat, primero debes registrarte o iniciar sesión</p>
            <div style="text-align: center; margin-top: 10px; font-weight: bold;">
                <img src="imagenes/chatbot.png" alt="ChatBot" style="width: 120px; height: auto;" />
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
                    Iniciar Sesión
                </a>
            </div>
        <?php else: ?>
            <div style="text-align: center; margin-top: 10px;">
                <img src="imagenes/chatbot.png" alt="ChatBot" style="width: 100px; height: auto; margin-bottom: 10px;">
            </div>
            <div class="mensaje">
                <strong>ChatBot</strong><br>
                Hola, <strong><?= htmlspecialchars($nombre_usuario) ?></strong> ¿En qué puedo ayudarte?
            </div>

            <!-- MENÚ DE PREGUNTAS -->
            <div class="asistente-opciones">
                <button onclick="enviarPregunta('¿Como se envia una noticia?')">¿Como se envia una noticia?</button>
                <button onclick="enviarPregunta('¿Como se reporta una noticia?')">¿Como se reporta una noticia?</button>
                <button onclick="enviarPregunta('¿Cuales son las politicas del periodico digital?')">¿Cuales son las politicas del periodico digital?</button>
            </div>
        <?php endif; ?>
    </div>

    <?php if ($usuario_logueado): ?>
        <div class="asistente-input">
            <input type="text" id="entradaUsuario" placeholder="Escribe un mensaje..." />
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
    body { font-family: Arial, sans-serif; }
    .boton-ayuda { position: fixed; bottom: 1px; left: 20px; background-color: #0d5c9b; color: white; border: none; border-radius: 10px 10px 0 0; padding: 12px 20px; cursor: pointer; z-index: 1000; font-weight: bold; }
    .asistente-container { display: none; position: fixed; bottom: 60px; left: 20px; width: 300px; max-height: 400px; background: white; border: 1px solid #ccc; border-radius: 8px 8px 0 0; box-shadow: 0 4px 8px rgba(0,0,0,0.2); z-index: 1001; overflow: hidden; }
    .asistente-header { background: #0d5c9b; color: white; padding: 10px; text-align: center; position: relative; }
    .asistente-header .cerrar { position: absolute; top: 5px; right: 10px; background: none; border: none; color: white; font-size: 18px; cursor: pointer; }
    .asistente-body { padding: 10px; background: #f0f0f0; overflow-y: auto; max-height: 300px; }
    .mensaje-bot { background: #e0e0e0; border-radius: 10px 10px 10px 0; padding: 10px; margin-top: 10px; width: fit-content; max-width: 80%; }
    .mensaje-usuario { background-color: #cce5ff; text-align: right; margin-left: auto; margin-right: 0; border-radius: 10px 10px 0 10px; padding: 10px; margin-top: 10px; width: fit-content; max-width: 80%; }
    .asistente-input { display: flex; border-top: 1px solid #ccc; }
    .asistente-input input { flex: 1; padding: 8px; border: none; }
    .asistente-input button { background: #0d5c9b; color: white; border: none; padding: 8px 12px; cursor: pointer; }
    .asistente-opciones { margin-top: 10px; }
    .asistente-opciones button { display: block; width: 100%; background: #0d5c9b; color: white; border: none; padding: 8px; margin-bottom: 5px; border-radius: 4px; cursor: pointer; }
</style>
</head>

<script>
    function toggleAsistente() {
        const asistente = document.getElementById('asistente');
        asistente.style.display = (asistente.style.display === 'block') ? 'none' : 'block';
    }

    const nombreUsuario = "<?= htmlspecialchars($nombre_usuario) ?>";

    // Procesar mensaje escrito por el usuario
    async function procesarEntrada() {
        const input = document.getElementById("entradaUsuario");
        const texto = input.value.trim();
        if (!texto) return;
        await enviarMensajeAlBackend(texto);
        input.value = "";
    }

    // Función para enviar mensaje al backend (IA) y mostrar respuesta
    async function enviarMensajeAlBackend(mensaje) {
        const chat = document.getElementById("chat-cuerpo");

        const msgUser = document.createElement("div");
        msgUser.className = "mensaje-usuario";
        msgUser.innerHTML = `<strong>${nombreUsuario}</strong><br>${mensaje}`;
        chat.appendChild(msgUser);
        chat.scrollTop = chat.scrollHeight;

        const msgBot = document.createElement("div");
        msgBot.className = "mensaje-bot";
        msgBot.innerHTML = `<strong>ChatBot</strong><br>Escribiendo...`;
        chat.appendChild(msgBot);
        chat.scrollTop = chat.scrollHeight;

        const formData = new FormData();
        formData.append("mensaje", mensaje);

        try {
            const response = await fetch("chatbot.php", { method: "POST", body: formData });
            const data = await response.json();
            msgBot.innerHTML = `<strong>ChatBot</strong><br>${data.respuesta}`;
        } catch (error) {
            msgBot.innerHTML = `<strong>ChatBot</strong><br>Hubo un error al procesar tu mensaje.`;
        }

        chat.scrollTop = chat.scrollHeight;
    }

    // Función para el menú de preguntas
    async function enviarPregunta(pregunta){
        await enviarMensajeAlBackend(pregunta);
    }

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
