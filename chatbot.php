<?php 
if (session_status() === PHP_SESSION_NONE ){
    session_start();
}

$usuario_logueado = isset($_SESSION['usuario_id']);
$nombre_usuario = $_SESSION['usuario_nombre'] ?? 'Invitado';
$imagen_usuario = $_SESSION['usuario_imagen'] ?? 'imagenes/avatar-default.png';

// Inicia historial de conversación si no existe
if (!isset($_SESSION['chat_historial'])) {
    $_SESSION['chat_historial'] = [];
}

// Toma el texto ya como pregnta.
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['mensaje'])) {
    $mensaje = trim($_POST['mensaje']);
    $interrogativas = ['como', 'qué', 'que', 'cuando', 'donde', 'por qué', 'porque', 'para qué', 'puedo', 'hay', 'es', 'sirve', 'tengo', 'quiero'];
    $lower = mb_strtolower($mensaje, 'UTF-8');

    foreach ($interrogativas as $palabra) {
        if (mb_strpos($lower, $palabra) === 0 && substr($mensaje, -1) !== '?') {
            $mensaje .= '?';
            break;
        }
    }
    $esAdvertencia = isset($_POST['advertencia']) && $_POST['advertencia'] === "true";

    // Advertencias por malas palabras
    if ($esAdvertencia) {
        echo json_encode(["respuesta" => "Por favor, utiliza un lenguaje respetuoso."]);
        exit;
    }

    //  API Key de OpenAI
    $apiKey = "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";
    $endpoint = "https://api.openai.com/v1/chat/completions";

    // Rol y contexto del asistente
    $system_message = [
        "role" => "system",
        "content" => "Eres el asistente virtual del periódico digital “Comunicado Digital”. Solo ayudas con temas del sitio: noticias, categorías, denuncias, comentarios, registro, inicio de sesión, perfil, búsquedas, reportes e información institucional. Los usuarios pueden llamarlo “app”, “aplicación” o “periódico”, todo significa lo mismo.\nCOMPORTAMIENTO:\n1. Responde de forma cordial, natural y profesional.\n2. Explica paso a paso y con lenguaje sencillo.\n3. Si el tema no es del periódico digital, responde: “Lo siento, solo puedo ayudarte con información del periódico digital.”\n4. Interpreta mensajes con errores y pide aclaración si el texto es confuso.\n5. No inventes funciones ni secciones que no existen.\nFUNCIONALIDADES CLAVE:\n- Noticias: se listan por fecha (recientes primero). Se leen sin sesión, pero para comentar, denunciar o enviar se requiere inicio de sesión. No hay favoritos, destacadas ni filtro por fecha.\n- Categorías: Inicio, Clima, Deportes, Educación, Turismo y Denuncias.\n- Buscador: en la barra de categorías; busca solo por título.\n- Denuncias: botón “Enviar denuncia” (título, descripción, fecha opcional, hasta 3 imágenes JPG). Con sesión: pública; sin sesión: anónima. Revisadas antes de publicar.\n- Registro e inicio de sesión: desde “Iniciar sesión”. Se puede registrar con correo o con Google/Outlook. Verificación por correo obligatoria. Recuperar contraseña mediante enlace enviado al correo.\n- Perfil (configuración): cambiar avatar o contraseña (requiere la actual). No se puede cambiar el correo.\n- Comentarios: en cada noticia; los usuarios pueden editar o eliminar los suyos. Administradores también pueden eliminarlos.\n- Enviar noticia: requiere sesión. Campos: categoría, título, descripción, fecha y hasta 3 imágenes JPG. Puede bloquear comentarios. Noticias revisadas antes de publicar.\n- Reportes: botón de reporte en cada noticia, con comentario adicional. Solo se puede reportar una vez.\n- Información institucional: “Sobre nosotros” y “Contactos” en el encabezado; redes: Facebook, Instagram y Twitter.\n- Problemas comunes: correo no llega (reintentar o usar Google/Outlook), noticia en revisión, correo sin verificar o app que no carga (cerrar y reabrir).\nREGLAS ESPECIALES:\n- El asistente solo se usa cuando hay sesión iniciada.\n- Explica funciones (comentar, reportar, enviar, etc.) directamente sin decir “debes iniciar sesión”, salvo que el usuario lo pregunte.\n- Adapta las respuestas al contexto, sin parecer un menú."
    ];

    // Agregar nuevos mensajes del usuario al historial
    $_SESSION['chat_historial'][] = [
        "role" => "user",
        "content" => $mensaje
    ];

    // Limita el historial a los últimos 3 mensajes para evitar exceso de tokens
    if (count($_SESSION['chat_historial']) > 3) {
        $_SESSION['chat_historial'] = array_slice($_SESSION['chat_historial'], -3);
    }

    // Combina el mensaje del sistema con el historial del chat
    $messages = array_merge([$system_message], $_SESSION['chat_historial']);

    $data = [
        "model" => "gpt-4o-mini",
        "messages" => $messages,
        "temperature" => 0.6,
        "max_tokens" => 180
    ];

    // Inicializar cURL (transfiere datos entre un cliente y un servidor)
    $ch = curl_init($endpoint);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        "Content-Type: application/json",
        "Authorization: Bearer " . $apiKey
    ]);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));

    $response = curl_exec($ch);

    // Verificar errores de cURL
    if ($response === false) {
        curl_close($ch);
        echo json_encode([
            "respuesta" => "Error de conexión: no se pudo establecer comunicación, Verifica tu conexión e intenta nuevamente."
        ]);
        exit;
    }

    curl_close($ch);

    $result = json_decode($response, true);

    // Verificar si JSON es válido
    if ($result === null) {
        echo json_encode(["respuesta" => "Error al procesar la respuesta del servidor."]);
        exit;
    }

    // Verificar si la API devolvió choices
    if (isset($result['choices'][0]['message']['content'])) {
        $respuestaIA = str_replace(["**", "*"], "", $result['choices'][0]['message']['content']);

        // Guarda la respuesta de la IA en el historial
        $_SESSION['chat_historial'][] = [
            "role" => "assistant",
            "content" => $respuestaIA
        ];

      echo json_encode(["respuesta" => $respuestaIA]);
  } else {
      // Si el error es por límite de tokens o similar, muestra mensaje amigable
      $errorMsg = $result['error']['message'] ?? "";

      if (stripos($errorMsg, "maximum context length") !== false ||
          stripos($errorMsg, "rate limit") !== false ||
          stripos($errorMsg, "token") !== false) {

          echo json_encode(["respuesta" => "No entendí tu pregunta, por favor escribe nuevamente tu consulta."]);

      } else {
          echo json_encode(["respuesta" => "No entendí tu pregunta, por favor escribe nuevamente tu consulta."]);
      }
  }  
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
    right: 20px;
    width: 350px;
    background: #fff;
    border-radius: 8px;
    box-shadow: 0 4px 8px rgba(0,0,0,0.2);
    font-family: 'Poppins', sans-serif;
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
    right: 20px;
    background-color: #61C9ab;
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

    function iniciarCuentaRegresiva(duracionMs) {
        const bloqueoDiv = document.getElementById("bloqueoAviso");
        let tiempoRestante = Math.floor(duracionMs / 1000); // en segundos
        bloqueoDiv.style.display = "block";

        // Mostrar mensaje inicial
        bloqueoDiv.textContent = `Has sido bloqueado por ${tiempoRestante} segundos.`;

        // Intervalo para actualizar cada segundo
        const intervalo = setInterval(() => {
            tiempoRestante--;
            if (tiempoRestante > 0) {
                bloqueoDiv.textContent = `Has sido bloqueado por ${tiempoRestante} segundos.`;
            } else {
                clearInterval(intervalo);
                desbloquearEntrada();
                bloqueoActivo = false;
                contadorAdvertencias = 0;
            }
        }, 1000);
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
            bloquearEntrada();
            bloqueoActivo = true;
            iniciarCuentaRegresiva(tiempoBloqueo);
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