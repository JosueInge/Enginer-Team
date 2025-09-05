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

    $apiKey = "TU_API_KEY_AQUI"; // Coloca tu API Key
    $endpoint = "https://api.openai.com/v1/chat/completions";

    $data = [
        "model" => "gpt-4o-mini",
        "messages" => [
            ["role" => "system", "content" => "Eres un asistente virtual del periódico digital..."],
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
    border-radius: 12px;
    box-shadow: 0 4px 12px rgba(0,0,0,0.2);
    font-family: 'Arial', sans-serif;
    overflow: hidden;
    z-index: 1001;
}

/* Header */
.chatbot-header {
    background: #1B314B;
    color: #fff;
    text-align: center;
    font-weight: bold;
    padding: 12px;
    position: relative;
    font-size: 16px;
}
.chatbot-header .cerrar {
    position: absolute;
    top: 10px;
    right: 14px;
    background: none;
    border: none;
    color: white;
    font-size: 18px;
    cursor: pointer;
}

/* Cuerpo */
.chatbot-body {
    padding: 15px;
    background: #fff;
    max-height: 320px;
    overflow-y: auto;
    text-align: center;
}

/* Avatar */
.chatbot-avatar img {
    width: 120px;
    height: auto;
    margin-bottom: 10px;
}

/* Mensajes */
.mensaje-bot {
    background: #c6c9c8ff;
    border-radius: 8px;
    padding: 10px;
    margin: 10px auto;
    text-align: left;
    max-width: 85%;
    font-size: 14px;
}
.mensaje-bot strong {
    color: #0d5c9b;
}
.mensaje-usuario {
    background: #d1ecff;
    color: #000;
    border-radius: 8px;
    padding: 10px;
    margin: 10px auto;
    text-align: right;
    max-width: 85%;
    font-size: 14px;
    font-weight: bold;
}

/* Opciones rápidas */
.chatbot-opciones {
    margin-top: 10px;
}
.chatbot-opciones button {
    display: block;
    width: 100%;
    background: #1661AC;
    color: white;
    border: none;
    padding: 12px;
    margin: 6px 0;
    border-radius: 6px;
    font-weight: bold;
    font-size: 14px;
    cursor: pointer;
    transition: 0.3s;
}
.chatbot-opciones button:hover {
    background: #094477;
}

/* Input en línea con botón */
.chatbot-input {
    display: flex;
    flex-direction: row;
    align-items: center;
    gap: 8px;
    padding: 5px;
}

.chatbot-input input {
    flex: 1;
    padding: 8px;
    border: 1px solid #ccc;
    border-radius: 8px;
    outline: none;
}

.chatbot-input button {
    padding: 8px 15px;
    background-color: #0d5c9b;
    color: white;
    border: none;
    border-radius: 8px;
    cursor: pointer;
}

.chatbot-input button:hover {
    background-color: #094477;
}

/* Mensajes de error */
#chat-error-msg {
    color: #ff0000;
    font-size: 14px;
    margin-top: 5px;
    font-weight: bold;
}

/* Botón flotante */
.boton-ayuda {
    position: fixed;
    bottom: 0;
    left: 20px;
    background-color: #0d5c9b;
    color: white;
    border: none;
    border-radius: 10px 10px 0 0;
    padding: 12px 20px;
    cursor: pointer;
    z-index: 1000;
    font-weight: bold;
    font-size: 14px;
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
      <?php if(!$usuario_logueado): ?>
          <!-- VISTA PARA USUARIOS NO LOGUEADOS -->
          <div class="mensaje-bot">
              ¡Hola! Para acceder al chat, primero debes registrarte o iniciar sesión.
          </div>
          <div class="chatbot-avatar">
              <img src="imagenes/chatbot.png" alt="ChatBot">
          </div>
          <div style="margin-top: 15px;">
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
          <!-- VISTA PARA USUARIOS LOGUEADOS -->
          <div class="chatbot-avatar">
              <img src="imagenes/chatbot.png" alt="ChatBot">
          </div>
          <div class="mensaje-bot">
              <strong>Asistente Virtual</strong><br>
              Hola, <?= htmlspecialchars($nombre_usuario) ?>, ¿en qué puedo ayudarte?
          </div>

          <div class="chatbot-opciones">
              <button onclick="enviarPregunta('¿Como se envia una noticia?')">¿Cómo se envía una noticia?</button>
              <button onclick="enviarPregunta('¿Como se reporta una noticia?')">¿Cómo se reporta una noticia?</button>
              <button onclick="enviarPregunta('¿Cuales son las politicas del periodico digital?')">¿Cuáles son las políticas?</button>
          </div>
      <?php endif; ?>
  </div>

  <?php if ($usuario_logueado): ?>
  <div class="chatbot-input">
      <input type="text" id="entradaUsuario" placeholder="Escribe un mensaje...">
      <button onclick="procesarEntrada()">Enviar</button>
  </div>
  <div id="chat-error-msg"></div>
  <?php endif; ?>
</div>

<!-- Botón flotante -->
<button class="boton-ayuda" onclick="toggleAsistente()">¿Necesita ayuda?</button>

<script>
function toggleAsistente() {
    const asistente = document.getElementById('asistente');
    asistente.style.display = (asistente.style.display === 'block') ? 'none' : 'block';
}

const nombreUsuario = "<?= htmlspecialchars($nombre_usuario) ?>";

function mostrarError(mensaje) {
    const errorDiv = document.getElementById("chat-error-msg");
    errorDiv.textContent = mensaje;
    setTimeout(() => { errorDiv.textContent = ""; }, 3000);
}

// Procesar entrada con validación
async function procesarEntrada() {
    const input = document.getElementById("entradaUsuario");
    if (!input) return;

    const textoOriginal = input.value;
    const texto = textoOriginal.trim();

    if (textoOriginal === "") {
        mostrarError("Escribe un mensaje antes de enviar");
        return;
    }
    if (texto === "") {
        mostrarError("El mensaje no puede estar vacío");
        return;
    }

    input.value = "";
    await enviarMensajeAlBackend(texto);
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

// Preguntas rápidas
async function enviarPregunta(pregunta) {
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
