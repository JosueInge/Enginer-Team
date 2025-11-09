<?php
session_start();
include 'conexion.php';
include 'chatbot.php';

if (isset($_SESSION['usuario_id'])) {
    include 'menu2.php';
} else {
    include 'menu.php';
}

// Obtener ID de noticia
$id_noticia = isset($_GET['id']) ? intval($_GET['id']) : 0;
$usuario_id = $_SESSION['usuario_id'] ?? null;
$es_admin = ($_SESSION['usuario_rol'] ?? '') === 'Administrador';

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

// Manejar acciones AJAX para comentarios
if (isset($_POST['eliminar_id'])) {
    $id_comentario = intval($_POST['eliminar_id']);
    $stmt = $conexion->prepare("DELETE FROM comentarios WHERE id = ? AND (usuario_id = ? OR ? = 1)");
    $es_admin_flag = $es_admin ? 1 : 0;
    $stmt->bind_param("iii", $id_comentario, $usuario_id, $es_admin_flag);
    $stmt->execute();
    $stmt->close();
    echo json_encode(["success" => true]);
    exit();
}

if (isset($_POST["editar_id"], $_POST['editar_texto'])) {
    $id_comentario = intval($_POST['editar_id']);
    $texto = trim($_POST['editar_texto']);
    $stmt = $conexion->prepare("UPDATE comentarios SET texto = ? WHERE id = ? AND usuario_id = ?");
    $stmt->bind_param("sii", $texto, $id_comentario, $usuario_id);
    $stmt->execute();
    $stmt->close();
    echo json_encode(["success"=> true]);
    exit();
}

if (isset($_POST['nuevo_comentario'])) {
    $texto = trim($_POST['nuevo_comentario']);
    if (!empty($texto)) {
        $stmt = $conexion->prepare("INSERT INTO comentarios (propuestas_noticias_id, usuario_id, texto) VALUES (?, ?, ?)");
        $stmt->bind_param("iss", $id_noticia, $usuario_id, $texto);
        $stmt->execute();
        $stmt->close();
    }
    echo json_encode(["success" => true]);
    exit();
}

// Obtener comentarios 
$stmt = $conexion->prepare("SELECT c.*, u.nombre, u.avatar FROM comentarios c JOIN usuarios u ON c.usuario_id = u.id WHERE c.propuestas_noticias_id = ? ORDER BY c.fecha DESC"); 
$stmt->bind_param("i", $id_noticia);
$stmt->execute();
$comentarios = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
$stmt->close();

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
    text-align: left;
    margin: 20px 0 10px 0;
}

/* META INFO */
.meta-noticia {
    font-family: 'Poppins', sans-serif;
    font-size: 16px;
    color: #74737C;
    display: flex;
    flex-wrap: wrap;
    align-items: center;
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
    display: flex;
    flex-direction: column;
    align-items: flex-start;
}
.fecha-hecho {
    font-family: 'Inter', sans-serif;
    font-weight: 700;
    font-size: 20px;
    color: #403F48;
    margin-bottom: 10px;
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
    margin-bottom: 10px;
}
.texto-inicia-sesion {
    font-family: 'Poppins', sans-serif;
    font-size: 16px;
    font-style: italic;
    color: #403F48;
    margin-bottom: 15px;
}

/* CAJA DE COMENTARIO */
.caja-comentario-container {
    position: relative;
    margin-bottom: 20px;
}
.caja-comentario {
    width: 1000px;
    min-height: 45px;
    max-height: 200px;
    border: 1px solid #B1B1B1;
    border-radius: 12px;
    padding: 10px 15px;
    font-family: 'Inter', sans-serif;
    font-size: 16px;
    color: #B1B1B1;
    resize: none;
    outline: none;
    transition: border-color 0.3s ease, color 0.3s ease;
}
.caja-comentario:focus {
    border-color: #2D8EFF;
    color: #403F48;
}
.caja-comentario::placeholder {
    color: #B1B1B1;
}
.btn-publicar {
    position: absolute;
    right: 10px;
    bottom: 10px;
    width: 100px;
    height: 40px;
    background-color: #1661AC;
    color: #FFFFFF;
    font-family: 'Poppins', sans-serif;
    font-weight: 700;
    font-size: 16px;
    border: none;
    border-radius: 6px;
    cursor: pointer;
    transition: background 0.3s ease;
}
.btn-publicar:hover {
    background-color: #2D8EFF;
}

/* COMENTARIO */
.comentario {
    width: 1000px;
    min-height: 80px;
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
    flex-grow: 1;
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

/* ICONO DE OPCIONES */
.icono-opciones {
    position: absolute;
    right: 15px;
    top: 15px;
    width: 24px;
    height: 24px;
    cursor: pointer;
    display: flex;
    flex-direction: column;
    justify-content: space-between;
    padding: 5px 0;
}

.punto {
    width: 4px;
    height: 4px;
    background-color: #1661AC;
    border-radius: 50%;
}

.menu-opciones {
    position: absolute;
    right: 0;
    top: 30px;
    width: 150px;
    height: 80px;
    background-color: #FFFFFF;
    border: 1px solid #2D8EFF;
    border-radius: 8px;
    z-index: 10;
    display: none;
    flex-direction: column;
}

.opcion {
    flex: 1;
    display: flex;
    align-items: center;
    justify-content: center;
    font-family: 'Inter', sans-serif;
    font-size: 16px;
    color: #403F48;
    cursor: pointer;
    transition: background-color 0.2s;
}

.opcion:hover {
    background-color: #f0f0f0;
}

.separador {
    height: 1px;
    background-color: #2D8EFF;
    width: 100%;
}

/* CAJA DE EDICIÓN */
.caja-edicion {
    width: 900px;
    min-height: 60px;
    max-height: 150px;
    border: 1px solid #B1B1B1;
    border-radius: 12px;
    padding: 10px 15px;
    font-family: 'Inter', sans-serif;
    font-size: 16px;
    color: #403F48;
    resize: none;
    outline: none;
    margin-top: 10px;
}

.btn-guardar {
    width: 90px;
    height: 40px;
    background-color: #2D8EFF;
    color: #FFFFFF;
    font-family: 'Poppins', sans-serif;
    font-weight: 700;
    font-size: 16px;
    border: none;
    border-radius: 6px;
    cursor: pointer;
    margin-top: 10px;
    float: right;
}

/* MODAL DE ELIMINACIÓN */
.modal-eliminar {
    position: fixed;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background-color: rgba(0, 0, 0, 0.5);
    display: flex;
    justify-content: center;
    align-items: center;
    z-index: 100;
}

.modal-contenido {
    background-color: #FFFFFF;
    padding: 30px;
    border-radius: 8px;
    width: 400px;
    text-align: center;
}

.modal-contenido h3 {
    font-family: 'Poppins', sans-serif;
    font-size: 16px;
    font-weight: 700;
    color: #403F48;
    margin-bottom: 20px;
}

.botones-modal {
    display: flex;
    justify-content: space-between;
    margin-top: 20px;
}

.btn-cancelar {
    width: 120px;
    height: 40px;
    background-color: #EB7373;
    color: #061F3E;
    font-family: 'Inter', sans-serif;
    font-size: 16px;
    border: none;
    border-radius: 8px;
    cursor: pointer;
}

.btn-confirmar {
    width: 120px;
    height: 40px;
    background-color: #61C9A8;
    color: #061F3E;
    font-family: 'Inter', sans-serif;
    font-size: 16px;
    border: none;
    border-radius: 8px;
    cursor: pointer;
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

/* BOTÓN DE REPORTAR */
.reportar-container {
    display: flex;
    justify-content: flex-end;
    margin-top: 20px;
    position: relative;
}

.btn-reportar {
    width: 40px;
    height: 40px;
    background-color: transparent;
    border: none;
    cursor: pointer;
    position: relative;
}

.btn-reportar svg {
    fill: #1661AC;
    transition: fill 0.3s ease;
}

.btn-reportar:hover svg {
    fill: #2D8EFF;
}

.tooltip {
    position: absolute;
    top: -35px;
    right: 0;
    width: 150px;
    height: 25px;
    background-color: #2D8EFF;
    color: #FFFFFF;
    font-family: 'Poppins', sans-serif;
    font-weight: 700;
    font-size: 16px;
    display: flex;
    align-items: center;
    justify-content: center;
    border-radius: 4px;
    opacity: 0;
    transition: opacity 0.3s ease;
    pointer-events: none;
}

.btn-reportar:hover .tooltip {
    opacity: 1;
}

/* CONTADOR DE CARACTERES */
.contador-caracteres {
    font-family: 'Inter', sans-serif;
    font-size: 14px;
    color: #B1B1B1;
    text-align: right;
    margin-top: 5px;
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
    .comentario, .caja-comentario, .caja-edicion {
        width: 100%;
    }
    .anuncio-lateral {
        position: relative;
        top: 0;
        margin-top: 30px;
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

        <!-- Botón de reportar (solo para usuarios logueados) -->
        <?php if (isset($_SESSION['usuario_id'])): ?>
        <div class="reportar-container">
            <a href="formulario_reporte.php?id_noticia=<?= $id_noticia ?>" class="btn-reportar">
                <svg xmlns="http://www.w3.org/2000/svg" width="40" height="40" viewBox="0 0 24 24">
                    <path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm1 15h-2v-2h2v2zm0-4h-2V7h2v6z"/>
                </svg>
                <div class="tooltip">Reportar noticia</div>
            </a>
        </div>
        <?php endif; ?>

        <!-- SECCIÓN DE COMENTARIOS -->
        <div class="seccion-comentarios">
            <h2>Comentarios</h2>
            
            <?php if (isset($_SESSION['usuario_id'])): ?>
                <div class="caja-comentario-container">
                    <textarea class="caja-comentario" placeholder="Escribe un comentario..." maxlength="1000"></textarea>
                    <div class="contador-caracteres">0/1000</div>
                    <button class="btn-publicar">Publicar</button>
                </div>
            <?php else: ?>
                <p class="texto-inicia-sesion">Inicia sesión para dejar un comentario</p>
            <?php endif; ?>

            <div id="lista-comentarios">
                <?php foreach ($comentarios as $comentario): ?>
                    <div class="comentario" data-id="<?= $comentario['id'] ?>">
                        <img src="imagenes/usuarios/<?= $comentario['avatar'] ?? 'default.png' ?>" alt="Usuario">
                        <div class="info-comentario">
                            <div>
                                <strong><?= htmlspecialchars($comentario['nombre']) ?></strong>
                                <span class="fecha-comentario"><?= date('d/m/Y H:i', strtotime($comentario['fecha'])) ?></span>
                            </div>
                            <p class="texto-comentario"><?= htmlspecialchars($comentario['texto']) ?></p>
                        </div>
                        
                        <?php if (isset($_SESSION['usuario_id']) && ($_SESSION['usuario_id'] == $comentario['usuario_id'] || $es_admin)): ?>
                            <div class="icono-opciones">
                                <div class="punto"></div>
                                <div class="punto"></div>
                                <div class="punto"></div>
                                <div class="menu-opciones">
                                    <div class="opcion editar-comentario">Editar</div>
                                    <div class="separador"></div>
                                    <div class="opcion eliminar-comentario">Eliminar</div>
                                </div>
                            </div>
                        <?php endif; ?>
                    </div>
                <?php endforeach; ?>
            </div>

            <button class="btn-ver-mas">Ver más comentarios</button>
        </div>
    </div>

    <!-- Columna derecha: anuncio -->
    <div class="anuncio-lateral">
        <img src="imagenes/cinemark.jpeg" alt="Anuncio publicitario">
    </div>
</div>

<!-- Modal de eliminación -->
<div class="modal-eliminar" id="modalEliminar" style="display: none;">
    <div class="modal-contenido">
        <h3>¿Estás seguro de eliminar tu comentario?</h3>
        <div class="botones-modal">
            <button class="btn-cancelar">Cancelar</button>
            <button class="btn-confirmar">Confirmar</button>
        </div>
    </div>
</div>

<script>
// Variables globales
let comentarioEditando = null;
let comentarioEliminando = null;

// Cambiar imagen principal
function cambiarImagen(imagen) {
    document.getElementById('imagenPrincipal').src = 'imagenes/noticias/' + imagen;
}

// Control de caracteres en el textarea
document.addEventListener('DOMContentLoaded', function() {
    const cajaComentario = document.querySelector('.caja-comentario');
    const contador = document.querySelector('.contador-caracteres');
    
    if (cajaComentario) {
        cajaComentario.addEventListener('input', function() {
            const caracteres = this.value.length;
            contador.textContent = `${caracteres}/1000`;
            
            // Ajustar altura automáticamente
            this.style.height = 'auto';
            this.style.height = (this.scrollHeight) + 'px';
        });
    }
    
    // Mostrar/ocultar menú de opciones
    const iconosOpciones = document.querySelectorAll('.icono-opciones');
    iconosOpciones.forEach(icono => {
        icono.addEventListener('click', function(e) {
            e.stopPropagation();
            const menu = this.querySelector('.menu-opciones');
            const todosMenus = document.querySelectorAll('.menu-opciones');
            
            // Ocultar otros menús
            todosMenus.forEach(m => {
                if (m !== menu) m.style.display = 'none';
            });
            
            // Mostrar/ocultar este menú
            menu.style.display = menu.style.display === 'flex' ? 'none' : 'flex';
        });
    });
    
    // Cerrar menús al hacer clic en cualquier parte
    document.addEventListener('click', function() {
        document.querySelectorAll('.menu-opciones').forEach(menu => {
            menu.style.display = 'none';
        });
    });
    
    // Manejar edición de comentarios
    document.querySelectorAll('.editar-comentario').forEach(boton => {
        boton.addEventListener('click', function() {
            const comentario = this.closest('.comentario');
            const textoComentario = comentario.querySelector('.texto-comentario');
            const textoOriginal = textoComentario.textContent;
            const idComentario = comentario.dataset.id;
            
            // Crear textarea de edición
            const textarea = document.createElement('textarea');
            textarea.className = 'caja-edicion';
            textarea.value = textoOriginal;
            textarea.maxLength = 1000;
            
            // Crear botón guardar
            const btnGuardar = document.createElement('button');
            btnGuardar.className = 'btn-guardar';
            btnGuardar.textContent = 'Guardar';
            
            // Reemplazar contenido
            textoComentario.replaceWith(textarea);
            comentario.querySelector('.icono-opciones').style.display = 'none';
            comentario.appendChild(btnGuardar);
            
            // Ajustar altura del textarea
            textarea.style.height = 'auto';
            textarea.style.height = (textarea.scrollHeight) + 'px';
            
            // Guardar comentario editado
            btnGuardar.addEventListener('click', function() {
                const nuevoTexto = textarea.value.trim();
                if (nuevoTexto && nuevoTexto !== textoOriginal) {
                    // Enviar solicitud AJAX
                    const formData = new FormData();
                    formData.append('editar_id', idComentario);
                    formData.append('editar_texto', nuevoTexto);
                    
                    fetch(window.location.href, {
                        method: 'POST',
                        body: formData
                    })
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            // Restaurar vista normal con nuevo texto
                            const nuevoParrafo = document.createElement('p');
                            nuevoParrafo.className = 'texto-comentario';
                            nuevoParrafo.textContent = nuevoTexto;
                            
                            textarea.replaceWith(nuevoParrafo);
                            btnGuardar.remove();
                            comentario.querySelector('.icono-opciones').style.display = 'flex';
                        }
                    });
                } else {
                    // Restaurar vista normal sin cambios
                    const nuevoParrafo = document.createElement('p');
                    nuevoParrafo.className = 'texto-comentario';
                    nuevoParrafo.textContent = textoOriginal;
                    
                    textarea.replaceWith(nuevoParrafo);
                    btnGuardar.remove();
                    comentario.querySelector('.icono-opciones').style.display = 'flex';
                }
            });
        });
    });
    
    // Manejar eliminación de comentarios
    document.querySelectorAll('.eliminar-comentario').forEach(boton => {
        boton.addEventListener('click', function() {
            comentarioEliminando = this.closest('.comentario');
            document.getElementById('modalEliminar').style.display = 'flex';
        });
    });
    
    // Manejar modal de eliminación
    document.querySelector('.btn-cancelar').addEventListener('click', function() {
        document.getElementById('modalEliminar').style.display = 'none';
        comentarioEliminando = null;
    });
    
    document.querySelector('.btn-confirmar').addEventListener('click', function() {
        if (comentarioEliminando) {
            const idComentario = comentarioEliminando.dataset.id;
            
            // Enviar solicitud AJAX
            const formData = new FormData();
            formData.append('eliminar_id', idComentario);
            
            fetch(window.location.href, {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    comentarioEliminando.remove();
                    document.getElementById('modalEliminar').style.display = 'none';
                    comentarioEliminando = null;
                }
            });
        }
    });
    
    // Publicar nuevo comentario
    const btnPublicar = document.querySelector('.btn-publicar');
    if (btnPublicar) {
        btnPublicar.addEventListener('click', function() {
            const texto = document.querySelector('.caja-comentario').value.trim();
            if (texto) {
                // Enviar solicitud AJAX
                const formData = new FormData();
                formData.append('nuevo_comentario', texto);
                
                fetch(window.location.href, {
                    method: 'POST',
                    body: formData
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        // Recargar la página para mostrar el nuevo comentario
                        location.reload();
                    }
                });
            }
        });
    }
});
</script>

</body>
</html>