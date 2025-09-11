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
    $esAdvertencia = isset($_POST['advertencia']) && $_POST['advertencia'] === "true";

    $apiKey = "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"; // Coloca tu API Key
    $endpoint = "https://api.openai.com/v1/chat/completions";

    // Mensajes base para la IA
    $messages = [
    [
        "role" => "system",
        "content" => "Eres un asistente virtual del periódico digital “Comunicado Digital”. 
Tu función es ayudar a los usuarios únicamente con temas relacionados con el sitio: noticias, categorías, denuncias, comentarios, registro, inicio de sesión, perfil, búsquedas, reportes y políticas del periódico. 
Los usuarios pueden referirse al sitio como: “esta app”, “esta aplicación”, “este periódico”, “este periódico digital”; entiende que son equivalentes.

Comportamiento:
1. Responde siempre de forma natural, cordial y profesional, como si conversaras normalmente con el usuario.
2. Da prioridad a explicar soluciones de manera sencilla, práctica y paso a paso. No uses frases rígidas como si fueran preguntas predefinidas, responde al problema que el usuario plantee.
3. Si la pregunta está fuera del contexto del periódico, responde: “Lo siento, no entendí tu mensaje. ¿Podrías reformularlo o preguntar de otra manera ?”.

Contexto del sitio y posibles dudas:
- Noticias y navegación: las más recientes aparecen primero; en Inicio se ven todas las categorías; se puede leer sin iniciar sesión, pero para comentar, enviar, denunciar o reportar debes iniciar sesión.
- Categorías: Inicio, Clima, Deportes, Educación, Turismo, Denuncias (última en la barra de categorías, a la derecha).
- Búsquedas: solo por título; deben hacerse en Inicio o en la categoría correspondiente.
- Denuncias: siempre son anónimas, con o sin iniciar sesión; solo se permite una imagen; los administradores revisan antes de publicar.
- Registro e inicio de sesión: formulario accesible desde el encabezado (esquina superior derecha); opción de registrarse manualmente o con Google/Outlook; la contraseña debe tener mínimo 8 caracteres, mayúscula, minúscula y un carácter especial. Requiere verificar correo antes de ingresar.
- Recuperar contraseña: enlace “Olvidé mi contraseña” en login → formulario de recuperación.
- Perfil y ajustes: se accede desde el ícono de tuerca en el encabezado; permite cambiar foto, contraseña y cerrar sesión (con confirmación).
- Comentarios: se agregan dentro de cada noticia; cada usuario puede editar o eliminar los suyos; administradores solo eliminan; se pueden bloquear comentarios al enviar una noticia.
- Enviar noticias: botón inferior derecho → formulario. Se revisan antes de publicarse.
- Reportes: icono entre descripción y comentarios; motivos: falso, ofensivo, plagio, datos personales; más de 3 reportes generan prioridad para administradores. Requiere sesión iniciada.
- Información institucional: “Sobre nosotros” y “Contactos” en el encabezado; redes sociales disponibles: Facebook, Instagram, Twitter.
- Problemas frecuentes: 
   - Si no llega el correo de verificación: reintenta registrarte, prueba con Google/Outlook o contacta un administrador. 
   - Si una noticia no aparece publicada: está en revisión de administradores; de momento no hay notificación automática. 
   - Si cierras tu correo sin verificar: deberás esperar a que se reactive o usar otro correo.

Recuerda: siempre adapta la respuesta al caso que exponga el usuario, no como si respondieras a una lista de preguntas, sino conversando naturalmente con él."
    ],
    [
        "role" => "user",
        "content" => $mensaje
    ]
];


    // Sistema adicional para advertencias IA.
    if ($esAdvertencia) {
        $messages[] = [
            "role" => "system",
            "content" => "El usuario ha escrito un mensaje ofensivo o inapropiado. 
Responde con una advertencia cordial, amistosa y profesional, sin insultar. 
No contestes la pregunta normal, solo advierte al usuario sobre su lenguaje."
        ];
    }

    $data = [
        "model" => "gpt-4o-mini",
        "messages" => $messages,
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
  .bloqueo-aviso {
    background: #ffebeb;
    color: #b10000;
    font-weight: bold;
    text-align: center;
    padding: 10px;
    font-size: 14px;
    border: 1px solid #b10000;
    border-radius: 8px;
    margin: 10px;
    display: none;
}
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

.primer-mensaje-bot {
    text-align: center;
    font-size: 14px;
}

/* Mensajes */
.mensaje-bot {
    background: #c6c9c8ff;
    border-radius: 8px;
    padding: 10px;
    margin: 10px 0;
    text-align: left;
    max-width: 85%;
    font-size: 14px;
    display: inline-block;
    float: left;
    clear: both;
}
.mensaje-bot strong {
    color: #0d5c9b;
}
.mensaje-usuario {
    background: #d1ecff;
    color: #000;
    border-radius: 8px;
    padding: 10px;
    margin: 10px 0;
    text-align: right;
    max-width: 85%;
    font-size: 14px;
    font-weight: bold;
    display: inline-block;
    float: right;
    clear: both;
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

.chatbot-input input:focus {
    border: 2px solid #094477;
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

.chatbot-input button.simular-hover {
    background-color: #094477;
}

/* Mensajes de error */
#chat-error-msg {
    color: #ff0000;
    font-size: 14px;
    margin-top: 5px;
    font-weight: bold;
    text-align: center;
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
          <div class="primer-mensaje-bot">
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
  <div id="bloqueoAviso" class="bloqueo-aviso"></div>
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

function mostrarBloqueo(mensaje) {
    const bloqueoDiv = document.getElementById("bloqueoAviso");
    bloqueoDiv.textContent = mensaje;
    bloqueoDiv.style.display = "block";
}

function ocultarBloqueo() {
    const bloqueoDiv = document.getElementById("bloqueoAviso");
    bloqueoDiv.style.display = "none";
}

// Lista de malas palabras
const malasPalabrasCategorias = {
    insultos: [
        "pendejo", "loco", "loca", "tonto", "idiota", "imbecil", "cabrón", "pasmado", "burro", "estúpido", "payaso", "tarado",
        "bobo", "animal", "bestia", "zoquete", "menso", "baboso", "inútil", "pelmazo", "parásito", "rata",
        "lamebotas", "malnacido", "cabestro", "atontado", "subnormal"
    ],
    vulgaridades: [
        "mierda", "puta", "chingada", "joder", "carajo", "coño", "hostia", "gilipollas", "cabrón", "polla",
        "culo", "picha", "verga", "cojones", "huevos", "chingar", "chingón", "chingona", "chingadera", "chingado",
        "pendejada", "chingadazo", "chingones", "chingoncito", "chingoncísima"
    ],
    sexuales: [
        "coger", "follar", "mamada", "mamón", "mamona", "pajero", "pajera", "pajillero", "chuparla", "lamerla",
        "felación", "tragarla", "culiar", "culear", "perrear", "soplapollas", "pornografía", "pornografico",
        "porn", "semen", "orgasmo"
    ],
    racismo: [
        "negro", "maricón", "sudaca", "indio", "gringo", "chino", "prieto", "naco", "zambo", "mulato",
        "moro", "gachupín", "cabecita", "mongolo", "gitano", "africano"
    ],
    homofobia: [
        "marica", "maricón", "puto", "loca", "trava", "travo", "bollera", "lesbi", "camionera", "sidoso",
        "infectado", "degenerado", "invertido", "pluma"
    ],
    otros: [
        "hijo de puta", "malparido", "conchatumadre", "cagón", "cagada", "mierdero", "asqueroso", "zorra",
        "perra", "cerdo", "sucio", "maldito", "desgraciado", "repugnante", "bastardo", "corrupto", "putona",
        "putilla", "golfa", "zángano"
    ]
};


let contadorAdvertencias = 0;
let bloqueoActivo = false;
const tiempoBloqueo = 30000; // 30 segundos

function contieneMalaPalabra(texto) {
    texto = texto.toLowerCase();
    for (const categoria in malasPalabrasCategorias) {
        for (const palabra of malasPalabrasCategorias[categoria]) {
            const regex = new RegExp(palabra, "i");
            if (regex.test(texto)) return true;
        }
    }
    return false;
}

// Bloqueo/Desbloqueo
function bloquearEntrada() {
    const input = document.getElementById("entradaUsuario");
    const boton = document.querySelector(".chatbot-input button");
    if (input && boton) {
        input.disabled = true;
        boton.disabled = true;
        boton.classList.add("simular-hover");
    }
}

function desbloquearEntrada() {
    const input = document.getElementById("entradaUsuario");
    const boton = document.querySelector(".chatbot-input button");
    if (input && boton) {
        input.disabled = false;
        boton.disabled = false;
        boton.classList.remove("simular-hover");
    }
    ocultarBloqueo();
}

// Procesar entrada
async function procesarEntrada() {
    if (bloqueoActivo) {
        mostrarError("Estás bloqueado temporalmente, espera unos segundos...");
        return;
    }

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

    // Limpiar la caja de texto
    input.value = "";

    const chat = document.getElementById("chat-cuerpo");

    // DETECCIÓN DE MALAS PALABRAS
    if (contieneMalaPalabra(texto)) {
        contadorAdvertencias++;

        // Mostrar el mensaje del usuario en el chat
        const msgUser = document.createElement("div");
        msgUser.className = "mensaje-usuario";
        msgUser.innerHTML = `<strong>${nombreUsuario}</strong><br>${texto}`;
        chat.appendChild(msgUser);

        // Bloqueo si supera 2 advertencias
        if (contadorAdvertencias >= 3) {
            mostrarBloqueo("Has sido bloqueado por 30 segundos.");
            bloquearEntrada();
            bloqueoActivo = true;
            setTimeout(() => {
                desbloquearEntrada();
                bloqueoActivo = false;
                contadorAdvertencias = 0;
            }, tiempoBloqueo);
            return;
        }

        // Crear mensaje de advertencia con la IA
        const msgBot = document.createElement("div");
        msgBot.className = "mensaje-bot";
        msgBot.innerHTML = `<strong>ChatBot</strong><br>Escribiendo...`;
        chat.appendChild(msgBot);
        chat.scrollTop = chat.scrollHeight;

        const formData = new FormData();
        formData.append("mensaje", texto);
        formData.append("advertencia", "true"); // Flag para advertencia IA

        try {
            const response = await fetch("chatbot.php", { method: "POST", body: formData });
            const data = await response.json();
            msgBot.innerHTML = `<strong>ChatBot</strong><br>${data.respuesta}`;
        } catch (error) {
            msgBot.innerHTML = `<strong>ChatBot</strong><br>Hubo un error al generar la advertencia.`;
        }

        chat.scrollTop = chat.scrollHeight;
        return;
    }

    // Mensaje normal al backend
    await enviarMensajeAlBackend(texto);
}

// Mensajes normales
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

// Preguntas rapidas
async function enviarPregunta(pregunta) {
    await enviarMensajeAlBackend(pregunta);
}

// Enter para enviar
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