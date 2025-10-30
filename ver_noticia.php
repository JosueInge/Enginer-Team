<?php
session_start();
include 'conexion.php';

// Determinar qué menú incluir
if (isset($_SESSION['usuario_id'])) {
    include 'menu2.php';
} else {
    include 'menu.php';
}

include 'chatbot.php';

// Obtener ID de noticia
$id_noticia = isset($_GET['id']) ? intval($_GET['id']) : 0;

// Obtener datos de la noticia
$sql = "SELECT * FROM noticias WHERE id = ?";
$stmt = $conexion->prepare($sql);
$stmt->bind_param("i", $id_noticia);
$stmt->execute();
$resultado = $stmt->get_result();
$noticia = $resultado->fetch_assoc();

// Si no existe la noticia
if (!$noticia) {
    echo "<h2>Noticia no encontrada</h2>";
    exit;
}

// Obtener imágenes
$imagenes = [];
foreach (['imagen', 'imagen2', 'imagen3'] as $campo) {
    if (!empty($noticia[$campo]) && file_exists('imagenes/noticias/' . $noticia[$campo])) {
        $imagenes[] = $noticia[$campo];
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title><?= htmlspecialchars($noticia['titulo']) ?> - Comunicado Digital</title>
<link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600;700&family=Inter:wght@400;600&family=Open+Sans:wght@700&display=swap" rel="stylesheet">
<style>
body {
    font-family: 'Inter', sans-serif;
    background-color: #f9f9f9;
    margin: 0;
    padding: 0;
}

/* CONTENEDOR PRINCIPAL */
.contenedor-detalle {
    display: flex;
    justify-content: center;
    align-items: flex-start;
    gap: 30px;
    max-width: 1300px;
    margin: 40px auto;
    padding: 20px;
}

/* COLUMNA DE NOTICIA */
.columna-noticia {
    flex: 1;
    max-width: 1000px;
}

/* TÍTULO */
.titulo-noticia {
    font-family: 'Poppins', sans-serif;
    font-size: 28px;
    font-weight: 700;
    color: #1661AC;
    margin-bottom: 10px;
}

/* META INFO */
.meta-noticia {
    font-family: 'Poppins', sans-serif;
    font-size: 16px;
    color: #74737C;
    display: flex;
    gap: 10px;
    margin-bottom: 20px;
}

/* GALERÍA */
.galeria {
    text-align: center;
}
.galeria-principal {
    width: 1000px;
    height: 500px;
    object-fit: cover;
    border-radius: 8px;
}
.miniaturas {
    display: flex;
    justify-content: center;
    gap: 8px;
    margin-top: 10px;
}
.miniaturas img {
    width: 200px;
    height: 125px;
    object-fit: cover;
    border-radius: 8px;
    cursor: pointer;
    transition: border 0.2s ease;
}
.miniaturas img:hover {
    border: 2px solid #2D8EFF;
}

/* FECHA DEL HECHO + DESCRIPCIÓN */
.detalle-descripcion {
    margin-top: 30px;
}
.fecha-hecho {
    font-family: 'Inter', sans-serif;
    font-weight: 700;
    font-size: 20px;
    color: #403F48;
}
.descripcion {
    font-family: 'Inter', sans-serif;
    font-size: 20px;
    color: #403F48;
    margin-top: 10px;
    line-height: 1.6;
    text-align: justify;
}

/* SECCIÓN COMENTARIOS */
.seccion-comentarios {
    margin-top: 40px;
}
.seccion-comentarios h2 {
    font-family: 'Poppins', sans-serif;
    font-size: 24px;
    font-weight: 700;
    color: #061F3E;
    margin-bottom: 15px;
}

/* MENSAJE PARA NO LOGUEADOS */
.mensaje-inicia-sesion {
    font-family: 'Poppins', sans-serif;
    font-size: 16px;
    font-style: italic;
    color: #403F48;
    margin-bottom: 20px;
    padding: 15px;
    background: #f8f9fa;
    border-radius: 8px;
    border: 1px solid #e9ecef;
    text-align: center;
}

/* CAJA DE COMENTARIO */
.caja-comentario {
    width: 1000px;
    height: 45px;
    border: 1px solid #B1B1B1;
    border-radius: 8px;
    padding: 10px 15px;
    font-family: 'Inter', sans-serif;
    font-size: 16px;
    color: #403F48;
    resize: none;
    margin-bottom: 20px;
}

/* COMENTARIO */
.comentario {
    width: 1000px;
    border: 1px solid #B1B1B1;
    border-radius: 8px;
    background-color: #fff;
    display: flex;
    align-items: flex-start;
    gap: 15px;
    padding: 10px 15px;
    margin-bottom: 15px;
    position: relative;
}

.comentario img {
    width: 50px;
    height: 50px;
    border-radius: 50%;
    object-fit: cover;
    margin-top: 5px;
}

.info-comentario {
    display: flex;
    flex-direction: column;
    justify-content: center;
    flex: 1;
}

.info-comentario strong {
    font-family: 'Poppins', sans-serif;
    font-size: 16px;
    font-weight: 600;
    color: #1661AC;
    margin-right: 10px;
}

.fecha-comentario {
    font-family: 'Inter', sans-serif;
    font-size: 12px;
    color: #B1B1B1;
}

.texto-comentario {
    font-family: 'Inter', sans-serif;
    font-size: 16px;
    color: #403F48;
    margin-top: 5px;
}

/* ICONO 3 PUNTOS PARA COMENTARIOS */
.icono-menu-comentario {
    position: absolute;
    right: 15px;
    top: 15px;
    font-size: 20px;
    color: #1661AC;
    cursor: pointer;
    padding: 5px;
    transition: color 0.3s ease;
    background: none;
    border: none;
}

.icono-menu-comentario:hover {
    color: #2D8EFF;
}

/* MENU DESPLEGABLE PARA COMENTARIOS */
.menu-opciones-comentario {
    position: absolute;
    right: 0;
    top: 40px;
    width: 150px;
    height: 80px;
    background: #FFFFFF;
    border: 1px solid #2D8EFF;
    border-radius: 8px;
    box-shadow: 0 4px 12px rgba(0,0,0,0.1);
    z-index: 1000;
    display: none;
    flex-direction: column;
}

.menu-opcion-comentario {
    font-family: 'Inter', sans-serif;
    font-size: 16px;
    color: #403F48;
    padding: 12px 15px;
    cursor: pointer;
    transition: background 0.3s ease;
    flex: 1;
    display: flex;
    align-items: center;
    border: none;
    background: none;
    width: 100%;
}

.menu-opcion-comentario:hover {
    background: #f0f8ff;
}

.menu-opcion-comentario:first-child {
    border-radius: 8px 8px 0 0;
}

.menu-opcion-comentario:last-child {
    border-radius: 0 0 8px 8px;
}

.separador-menu-comentario {
    height: 1px;
    background: #2D8EFF;
    margin: 0;
    border: none;
}

/* CAJA DE EDICIÓN DE COMENTARIOS */
.caja-edicion-comentario {
    width: 900px;
    min-height: 80px;
    border: 1px solid #B1B1B1;
    border-radius: 12px;
    padding: 15px;
    font-family: 'Inter', sans-serif;
    font-size: 16px;
    color: #403F48;
    line-height: 1.4;
    resize: vertical;
    margin-top: 10px;
    display: none;
}

.contenedor-botones-comentario {
    display: none;
    justify-content: flex-end;
    margin-top: 10px;
    gap: 10px;
}

.btn-guardar-comentario {
    width: 90px;
    height: 40px;
    background: #2D8EFF;
    color: #FFFFFF;
    font-family: 'Poppins', sans-serif;
    font-weight: 700;
    font-size: 16px;
    border: none;
    border-radius: 6px;
    cursor: pointer;
    transition: background 0.3s ease;
}

.btn-guardar-comentario:hover {
    background: #1a75e0;
}

.btn-cancelar-comentario {
    width: 90px;
    height: 40px;
    background: #74737C;
    color: #FFFFFF;
    font-family: 'Poppins', sans-serif;
    font-weight: 700;
    font-size: 16px;
    border: none;
    border-radius: 6px;
    cursor: pointer;
    transition: background 0.3s ease;
}

.btn-cancelar-comentario:hover {
    background: #5a5960;
}

/* BOTÓN "VER MÁS COMENTARIOS" */
.btn-ver-mas {
    display: block;
    width: 275px;
    height: 50px;
    margin: 25px auto;
    background-color: #1661AC;
    color: #FFFFFF;
    font-family: 'Open Sans', sans-serif;
    font-weight: 700;
    font-size: 16px;
    border: none;
    border-radius: 6px;
    cursor: pointer;
    transition: background 0.3s ease;
}

.btn-ver-mas:hover {
    background-color: #2D8EFF;
}

/* ANUNCIO LATERAL */
.anuncio-lateral {
    position: sticky;
    top: 100px;
    width: 200px;
    height: 725px;
}
.anuncio-lateral img {
    width: 100%;
    height: 100%;
    border-radius: 10px;
    object-fit: cover;
}

/* RESPONSIVO */
@media (max-width: 1024px) {
    .contenedor-detalle {
        flex-direction: column;
        align-items: center;
    }
    .galeria-principal {
        width: 100%;
        height: auto;
    }
    .comentario, .caja-comentario, .caja-edicion-comentario {
        width: 100%;
    }
}
</style>
</head>
<body>

<div class="contenedor-detalle">
    <!-- Columna izquierda: contenido -->
    <div class="columna-noticia">
        <h1 class="titulo-noticia"><?= htmlspecialchars($noticia['titulo']) ?></h1>

        <div class="meta-noticia">
            <span><?= htmlspecialchars($noticia['categoria']) ?></span>
            <span>|</span>
            <span><?= htmlspecialchars($noticia['autor']) ?></span>
            <span>|</span>
            <span><?= date('d/m/Y', strtotime($noticia['fecha'])) ?></span>
            <span>|</span>
            <span><?= date('H:i', strtotime($noticia['fecha'])) ?></span>
        </div>

        <!-- Galería -->
        <div class="galeria">
            <img id="imagenPrincipal" src="imagenes/noticias/<?= $imagenes[0] ?? 'default.jpg' ?>" alt="Imagen principal" class="galeria-principal">
            <div class="miniaturas">
                <?php foreach ($imagenes as $img): ?>
                    <img src="imagenes/noticias/<?= $img ?>" alt="Miniatura" onclick="cambiarImagen('<?= $img ?>')">
                <?php endforeach; ?>
            </div>
        </div>

        <!-- Descripción -->
        <div class="detalle-descripcion">
            <p class="fecha-hecho">Fecha del hecho: <?= date('d/m/Y', strtotime($noticia['fecha'])) ?></p>
            <p class="descripcion"><?= nl2br(htmlspecialchars($noticia['descripcion'])) ?></p>
        </div>

        <!-- SECCIÓN DE COMENTARIOS -->
        <div class="seccion-comentarios">
            <h2>Comentarios</h2>
            
            <?php if (isset($_SESSION['usuario_id']) && ($_SESSION['tipo_usuario'] == 'poblador' || $_SESSION['tipo_usuario'] == 'administrador')): ?>
                <!-- Caja de comentario solo para usuarios logueados (pobladores o administradores) -->
                <textarea class="caja-comentario" placeholder="Escribe un comentario..."></textarea>
            <?php else: ?>
                <!-- Mensaje para usuarios no logueados -->
                <div class="mensaje-inicia-sesion">
                    Inicia sesión como poblador o administrador para dejar un comentario
                </div>
            <?php endif; ?>

            <!-- Comentario con menú de opciones -->
            <div class="comentario" id="comentario1">
                <img src="imagenes/usuarios/default.png" alt="Usuario">
                <div class="info-comentario">
                    <div>
                        <strong>David</strong>
                        <span class="fecha-comentario">04/09/2025 10:00</span>
                    </div>
                    <p class="texto-comentario" id="textoComentario1">Ojalá pronto terminen estas lluvias.</p>
                    
                    <!-- Caja de edición para comentario -->
                    <textarea class="caja-edicion-comentario" id="cajaEdicionComentario1" placeholder="Edita tu comentario..."></textarea>
                    
                    <!-- Botones de acción para comentario -->
                    <div class="contenedor-botones-comentario" id="contenedorBotonesComentario1">
                        <button class="btn-cancelar-comentario" onclick="cancelarEdicionComentario('comentario1')">Cancelar</button>
                        <button class="btn-guardar-comentario" onclick="guardarComentario('comentario1')">Guardar</button>
                    </div>
                </div>
                
                <!-- Icono de 3 puntos (solo visible para usuarios logueados) -->
                <?php if (isset($_SESSION['usuario_id']) && ($_SESSION['tipo_usuario'] == 'poblador' || $_SESSION['tipo_usuario'] == 'administrador')): ?>
                    <button class="icono-menu-comentario" onclick="toggleMenuComentario('comentario1')">⋯</button>
                    
                    <!-- Menú desplegable -->
                    <div class="menu-opciones-comentario" id="menuOpcionesComentario1">
                        <button class="menu-opcion-comentario" onclick="editarComentario('comentario1')">Editar</button>
                        <hr class="separador-menu-comentario">
                        <button class="menu-opcion-comentario" onclick="eliminarComentario('comentario1')">Eliminar</button>
                    </div>
                <?php endif; ?>
            </div>

            <button class="btn-ver-mas">Ver más comentarios</button>
        </div>
    </div>

    <!-- Columna derecha: anuncio -->
    <div class="anuncio-lateral">
        <img src="imagenes/cinemark.jpeg" alt="Anuncio publicitario">
    </div>
</div>

<script>
let menuComentarioAbierto = null;

function toggleMenuComentario(comentarioId) {
    const menu = document.getElementById('menuOpciones' + comentarioId.charAt(0).toUpperCase() + comentarioId.slice(1));
    
    // Cerrar otros menús abiertos
    if (menuComentarioAbierto && menuComentarioAbierto !== menu) {
        menuComentarioAbierto.style.display = 'none';
    }
    
    // Abrir/cerrar menú actual
    if (menu.style.display === 'flex') {
        menu.style.display = 'none';
        menuComentarioAbierto = null;
    } else {
        menu.style.display = 'flex';
        menuComentarioAbierto = menu;
    }
}

function editarComentario(comentarioId) {
    const textoComentario = document.getElementById('textoComentario' + comentarioId.charAt(0).toUpperCase() + comentarioId.slice(1));
    const cajaEdicion = document.getElementById('cajaEdicionComentario' + comentarioId.charAt(0).toUpperCase() + comentarioId.slice(1));
    const contenedorBotones = document.getElementById('contenedorBotonesComentario' + comentarioId.charAt(0).toUpperCase() + comentarioId.slice(1));
    const menu = document.getElementById('menuOpciones' + comentarioId.charAt(0).toUpperCase() + comentarioId.slice(1));
    
    // Ocultar menú
    menu.style.display = 'none';
    menuComentarioAbierto = null;
    
    // Mostrar caja de edición
    cajaEdicion.value = textoComentario.textContent;
    cajaEdicion.style.display = 'block';
    contenedorBotones.style.display = 'flex';
    textoComentario.style.display = 'none';
    
    // Autoajustar altura
    cajaEdicion.style.height = 'auto';
    cajaEdicion.style.height = (cajaEdicion.scrollHeight) + 'px';
    
    // Enfocar la caja de edición
    cajaEdicion.focus();
}

function cancelarEdicionComentario(comentarioId) {
    const textoComentario = document.getElementById('textoComentario' + comentarioId.charAt(0).toUpperCase() + comentarioId.slice(1));
    const cajaEdicion = document.getElementById('cajaEdicionComentario' + comentarioId.charAt(0).toUpperCase() + comentarioId.slice(1));
    const contenedorBotones = document.getElementById('contenedorBotonesComentario' + comentarioId.charAt(0).toUpperCase() + comentarioId.slice(1));
    
    cajaEdicion.style.display = 'none';
    contenedorBotones.style.display = 'none';
    textoComentario.style.display = 'block';
}

function guardarComentario(comentarioId) {
    const textoComentario = document.getElementById('textoComentario' + comentarioId.charAt(0).toUpperCase() + comentarioId.slice(1));
    const cajaEdicion = document.getElementById('cajaEdicionComentario' + comentarioId.charAt(0).toUpperCase() + comentarioId.slice(1));
    const contenedorBotones = document.getElementById('contenedorBotonesComentario' + comentarioId.charAt(0).toUpperCase() + comentarioId.slice(1));
    
    // Aquí iría la lógica para guardar en la base de datos
    textoComentario.textContent = cajaEdicion.value;
    
    cajaEdicion.style.display = 'none';
    contenedorBotones.style.display = 'none';
    textoComentario.style.display = 'block';
    
    alert('Comentario guardado correctamente');
}

function eliminarComentario(comentarioId) {
    if (confirm('¿Estás seguro de que quieres eliminar este comentario?')) {
        // Aquí iría la lógica para eliminar de la base de datos
        const comentario = document.getElementById(comentarioId);
        comentario.style.display = 'none';
        alert('Comentario eliminado');
    }
    
    const menu = document.getElementById('menuOpciones' + comentarioId.charAt(0).toUpperCase() + comentarioId.slice(1));
    menu.style.display = 'none';
    menuComentarioAbierto = null;
}

function cambiarImagen(imagen) {
    document.getElementById('imagenPrincipal').src = 'imagenes/noticias/' + imagen;
}

// Autoajustar altura de las cajas de edición mientras se escribe
document.addEventListener('input', function(event) {
    if (event.target.classList.contains('caja-edicion-comentario')) {
        event.target.style.height = 'auto';
        event.target.style.height = (event.target.scrollHeight) + 'px';
    }
});

// Cerrar menú al hacer clic fuera
document.addEventListener('click', function(event) {
    if (menuComentarioAbierto && !event.target.classList.contains('icono-menu-comentario')) {
        const menu = menuComentarioAbierto;
        if (!menu.contains(event.target)) {
            menu.style.display = 'none';
            menuComentarioAbierto = null;
        }
    }
});

// Cerrar menú al hacer scroll
window.addEventListener('scroll', function() {
    if (menuComentarioAbierto) {
        menuComentarioAbierto.style.display = 'none';
        menuComentarioAbierto = null;
    }
});
</script>

</body>
</html>