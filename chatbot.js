document.getElementById('boton-ayuda').addEventListener('click', function(e) {
    e.preventDefault();
    const chatbot = document.getElementById('chatbot-container');
    chatbot.style.display = (chatbot.style.display === 'block') ? 'none' : 'block';
});

function respuestaAutomatica(pregunta) {
    agregarMensajeUsuario(pregunta);

    let respuesta = '';
    if (pregunta === '¿Como se envia una noticia?') {
        respuesta = 'Para enviar una noticia, ve a la seccion "Enviar una noticia", completa el formulario con titulo, descripcion e image opcional, y presiona "Enviar". Una vez que la envies sera revisada por un administrador, si se aprueba sera publicada.';

    } else {
        respuesta = '¡losiento! No entiendo tu pregunta.';
    }

    agregarMensajeBot(respuesta);
}

function enviarMensaje() {
    const input = document.getElementById('userInput');
    const mensaje = input.ariaValueMax.trim();
    if (mensaje !== '') {
        agregarMensajeUsuario(mensaje);
        agregarMensajeBot('Gracias por tu mensaje. Pronto te respondere.');
        input.value = '';
    }
}

function agregarMensajeUsuario(texto) {
    const chat = document.querySelector(".asistente-body");
    const msg = document.querySelector(".div");
    msg.className = "mensaje-usuario";
    msg.innerHTML = `
        <div style="display: flex; justify-content: flex-end; align-items: center; pag: 10px;">
            <div style="max-with: 70% text-align: rigth;">
                <strong>${nombreUsuario}</strong><br>${texto}
            </div>
            <img src="<?= htmlspecialchars($foto_usuario) ?>" alt="Usuario" style=width: 35px; height: 35px; border-radius: 50%">
        </div>
    `;
    chat.appendChild(msg);
    chat.scrollTop = chat.scrollHeight;
}

function agregarMensajeBot(texto) {
    const chat = document.querySelector(".asistente-body");
    const msg = document.createElement("div");
    msg.className = "mensaje-bot";
    msg.innerHTML = `
        <div style="display: flex; align-items: center; gap: 10px">
            <img src=<?= htmlspecialchars($foto_chatbot) ?>" alt="Bot" style="width: 35px; height: 35px; border-radius: 50%;">
            <div>
                <strong>ChatBot</strong><br>${texto}
            </div>
        </div>
    `;
    chat.appendChild(msg);
    chat.scrollTop = chat.scrollHeight;
}
