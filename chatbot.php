<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$usuario_logueado = isset($_SESSION['usuario_id']);
$nombre_usuario = $_SESSION['usuario_nombre'] ?? 'Invitado';

// PROCESAR SOLICITUD AJAX PARA LA IA
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['mensaje'])) {
    $mensaje = trim($_POST['mensaje']);

    // Configuración de la API de OpenAI
    $apiKey = "AQUI_TU_API_KEY"; // Reemplaza con tu API Key
    $endpoint = "https://api.openai.com/v1/chat/completions";

    $data = [
        "model" => "gpt-4o-mini",
        "messages" => [
            ["role" => "system", "content" => "Eres un asistente virtual del periódico digital 'Comunicado Digital'..."],
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
<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Asistente Virtual</title>
<style>
/* Contenedor general */
.chatbot-container {
    display: none;
    position: fixed;
    bottom: 60px;
    left: 20px;
    width: 320px;
    background: #fff;
    border-radius: 8px;
    box-shadow: 0 4px 12px rgba(0,0,0,0.2);
    overflow: hidden;
    font-family: Arial, sans-serif;
    z-index: 1001;
}

/* Header */
.chatbot-header {
    background: #0d3d8c;
    color: #fff;
    text-align: center;
    font-weight: bold;
    padding: 12px;
    position: relative;
}

.chatbot-header .cerrar {
    position: absolute;
    top: 8px;
    right: 12px;
    background: none;
    border: none;
    color: white;
    font-size: 18px;
    cursor: pointer;
}

/* Cuerpo */
.chatbot-body {
    padding: 15px;
    background: #f9f9f9;
    max-height: 320px;
    overflow-y: auto;
    text-align: center;
}

/* Avatar */
.chatbot-avatar img {
    width: 120px;
    height: auto;
    margin-bottom: 15px;
}

/* Mensajes */
.mensaje-bot {
    background: #e6e6e6;
    border-radius: 8px;
    padding: 10px;
    margin: 10px auto;
    text-align: left;
    max-width: 90%;
}

.mensaje-usuario {
    background: #0d5c9b;
    color: #fff;
    border-radius: 8px;
    padding: 10px;
    margin: 10px auto;
    text-align: right;
    max-width: 90%;
}

/* Opciones rápidas */
.chatbot-opciones {
    margin-top: 15px;
}
.chatbot-opciones button {
    display: block;
    width: 100%;
    background: #0d5c9b;
    color: white;
    border: none;
    padding: 10px;
    margin: 6px 0;
    border-radius: 4px;
    cursor: pointer;
    font-weight: bold;
}

/* Input usuario */
.chatbot-input {
    display: flex;
    border-top: 1px solid #ddd;
}
.chatbot-input input {
    flex: 1;
    border: none;
    padding: 10px;
}
.chatbot-input button {
    background: #0d5c9b;
    border: none;
    color: white;
    padding: 10px 16px;
    cursor: pointer;
}

/* Botón flotante */
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
</style>
</head>
<body>

<!-- Ventana del chatbot -->
<div class="chatbot-container" id="asistente">
    <div class="chatbot-header">
        <span>Asistente Virtual</span>
        <button class="cerrar" onclick="toggleAsistente()">✕</button>
    </div>

    <div class="chatbot-body" id="chat-cuerpo">
        <div class="chatbot-avatar">
            <img src="imagenes/chatbot.png" alt="ChatBot">
        </div>

        <div class="mensaje-bot">
            <strong>Asistente Virtual</strong><br>
            Hola <?= htmlspecialchars($nombre_usuario) ?>, ¿en qué puedo ayudarte?
        </div>

        <div class="chatbot-opciones">
            <button onclick="enviarPregunta('¿Como se envia una noticia?')">¿Cómo se envía una noticia?</button>
            <button onclick="enviarPregunta('¿Como se reporta una noticia?')">¿Cómo se reporta una noticia?</button>
            <button onclick="enviarPregunta('¿Cuales son las politicas del periodico digital?')">¿Cuáles son las políticas?</button>
        </div>
    </div>

    <?php if ($usuario_logueado): ?>
    <div class="chatbot-input">
        <input type="text" id="entradaUsuario" placeholder="Escribe un mensaje...">
        <button onclick="procesarEntrada()">Enviar</button>
    </div>
    <?php endif; ?>
</div>

<!-- Botón flotante -->
<button class="boton-ayuda" onclick="toggleAsistente()">¿Necesita ayuda?</button>

<script>
function toggleAsistente() {
    const asistente = document.getElementById('asistente');
    asistente.style.display = (asistente.style.display === 'block') ? 'none' : 'block';
    if(asistente.style.display === 'block'){
        document.getElementById('chat-cuerpo').scrollTop = document.getElementById('chat-cuerpo').scrollHeight;
    }
}

const nombreUsuario = "<?= htmlspecialchars($nombre_usuario) ?>";

// Procesar mensaje
async function procesarEntrada() {
    const input = document.getElementById("entradaUsuario");
    const texto = input.value.trim();
    if (!texto) return;
    await enviarMensajeAlBackend(texto);
    input.value = "";
}

// Enviar mensaje al backend
async function enviarMensajeAlBackend(mensaje) {
    const chat = document.getElementById("chat-cuerpo");

    const msgUser = document.createElement("div");
    msgUser.className = "mensaje-usuario";
    msgUser.innerHTML = `<strong>${nombreUsuario}</strong><br>${mensaje}`;
    chat.appendChild(msgUser);
    chat.scrollTop = chat.scrollHeight;

    const msgBot = document.createElement("div");
    msgBot.className = "mensaje-bot";
    msgBot.innerHTML = `<strong>Asistente Virtual</strong><br>Escribiendo...`;
    chat.appendChild(msgBot);
    chat.scrollTop = chat.scrollHeight;

    const formData = new FormData();
    formData.append("mensaje", mensaje);

    try {
        const response = await fetch("chatbot.php", { method: "POST", body: formData });
        const data = await response.json();
        msgBot.innerHTML = `<strong>Asistente Virtual</strong><br>${data.respuesta}`;
    } catch (error) {
        msgBot.innerHTML = `<strong>Asistente Virtual</strong><br>Hubo un error al procesar tu mensaje.`;
    }

    chat.scrollTop = chat.scrollHeight;
}

// Enviar pregunta rápida
async function enviarPregunta(pregunta){
    await enviarMensajeAlBackend(pregunta);
}

// Enter para enviar mensaje
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
