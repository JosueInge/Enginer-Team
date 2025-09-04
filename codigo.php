<<<<<<< HEAD
=======
<div class="chatbot-container" id="asistente">
    <!-- Encabezado -->
    <div class="chatbot-header">
        <span>Asistente Virtual</span>
        <button class="cerrar" onclick="toggleAsistente()">✕</button>
    </div>

    <!-- Cuerpo -->
    <div class="chatbot-body" id="chat-cuerpo">
        <div class="chatbot-avatar">
            <img src="imagenes/chatbot.png" alt="ChatBot">
        </div>

        <!-- Mensaje de bienvenida -->
        <div class="mensaje-bot">
            <strong>Asistente Virtual</strong><br>
            Hola <?= htmlspecialchars($nombre_usuario) ?>, ¿en qué puedo ayudarte?
        </div>

        <!-- Opciones rápidas -->
        <div class="chatbot-opciones">
            <button onclick="enviarPregunta('¿Como se envia una noticia?')">¿Cómo se envía una noticia?</button>
            <button onclick="enviarPregunta('¿Como se reporta una noticia?')">¿Cómo se reporta una noticia?</button>
            <button onclick="enviarPregunta('¿Cuales son las politicas del periodico digital?')">¿Cuáles son las políticas?</button>
        </div>
    </div>

    <!-- Entrada usuario -->
    <div class="chatbot-input">
        <input type="text" id="entradaUsuario" placeholder="Escribe un mensaje...">
        <button onclick="procesarEntrada()">Enviar</button>
    </div>
</div>

<!-- BOTÓN flotante -->
<button class="boton-ayuda" onclick="toggleAsistente()">¿Necesita ayuda?</button>
>>>>>>> 0635e45e325e93a6c37c5875f48a24ebd3589b74
