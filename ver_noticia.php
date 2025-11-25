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
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
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
        if (!empty($texto) && strlen($texto) <= 1000) {
            $stmt = $conexion->prepare("INSERT INTO comentarios (propuestas_noticias_id, usuario_id, texto) VALUES (?, ?, ?)");
            $stmt->bind_param("iis", $id_noticia, $usuario_id, $texto);
            $stmt->execute();
            $stmt->close();
        }
        echo json_encode(["success" => true]);
        exit();
    }
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
    line-height: 1.3;
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
    padding-bottom: 15px;
    border-bottom: 2px solid #e0e0e0;
}

/* GALERÍA */
.galeria {
    text-align: center;
    margin-bottom: 30px;
}
.galeria-principal {
    width: 1000px;
    height: 500px;
    object-fit: cover;
    border-radius: 8px;
    margin-bottom: 10px;
    box-shadow: 0 4px 8px rgba(0,0,0,0.1);
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
    transition: all 0.2s ease;
    border: 2px solid transparent;
}
.miniaturas img:hover,
.miniaturas img.activa {
    border: 2px solid #2D8EFF;
}

/* FECHA DEL HECHO + DESCRIPCIÓN */
.detalle-descripcion {
    margin-top: 30px;
    display: flex;
    gap: 30px;
    align-items: flex-start;
}
.fecha-hecho {
    font-family: 'Inter', sans-serif;
    font-weight: 700;
    font-size: 20px;
    color: #403F48;
    flex: 1;
    min-width: 200px;
}
.descripcion {
    font-family: 'Inter', sans-serif;
    font-size: 18px;
    color: #403F48;
    line-height: 1.8;
    text-align: justify;
    flex: 2;
    text-justify: inter-word;
}

/* ICONO REPORTAR - MODIFICADO */
.reportar-contenedor {
    display: flex;
    justify-content: flex-end;
    margin: 50px 0 60px 0;
    position: relative;
}
.reportar-icono {
    width: 40px;
    height: 40px;
    cursor: pointer;
    color: #1661AC;
    transition: color 0.3s ease;
}
.reportar-icono:hover {
    color: #2D8EFF;
}
.reportar-tooltip {
    position: absolute;
    top: -35px;
    right: 0;
    width: 150px;
    height: 25px;
    background-color: #2D8EFF;
    color: #FFFFFF;
    font-family: 'Poppins', sans-serif;
    font-size: 16px;
    font-weight: bold;
    display: flex;
    align-items: center;
    justify-content: center;
    border-radius: 4px;
    opacity: 0;
    visibility: hidden;
    transition: opacity 0.3s ease;
}
.reportar-contenedor:hover .reportar-tooltip {
    opacity: 1;
    visibility: visible;
}

/* SECCIÓN COMENTARIOS - MODIFICADO */
.seccion-comentarios {
    margin-top: 60px;
    padding-top: 30px;
    border-top: none;
}
.seccion-comentarios h2 {
    font-family: 'Poppins', sans-serif;
    font-size: 24px;
    font-weight: 700;
    color: #061F3E;
    margin-bottom: 20px;
}

/* MENSAJE PARA USUARIOS NO REGISTRADOS */
.mensaje-iniciar-sesion {
    font-family: 'Poppins', sans-serif;
    font-size: 16px;
    font-style: italic;
    color: #403F48;
    margin-bottom: 30px;
    text-align: left;
}

/* CAJA DE COMENTARIO */
.caja-comentario-contenedor {
    position: relative;
    margin-bottom: 30px;
    width: 1000px;
}
.caja-comentario {
    width: 100%;
    min-height: 40px;
    max-height: 100px;
    border: 1px solid #B1B1B1;
    border-radius: 12px;
    padding: 20px;
    font-family: 'Inter', sans-serif;
    font-size: 16px;
    color: #B1B1B1;
    background: none;
    resize: none;
    outline: none;
    transition: all 0.3s ease;
    box-sizing: border-box;
    line-height: 1.5;
}
.caja-comentario:focus {
    border-color: #2D8EFF;
    color: #403F48;
    box-shadow: 0 0 0 2px rgba(45, 142, 255, 0.1);
}
.caja-comentario::placeholder {
    color: #B1B1B1;
}
.btn-publicar {
    position: absolute;
    bottom: 20px;
    right: 20px;
    width: 100px;
    height: 50px;
    background-color: #1661AC;
    color: #FFFFFF;
    font-family: 'Poppins', sans-serif;
    font-weight: bold;
    font-size: 16px;
    border: none;
    border-radius: 6px;
    cursor: pointer;
    transition: background 0.3s ease;
}
.btn-publicar:hover {
    background-color: #2D8EFF;
}
.btn-publicar:disabled {
    background-color: #B1B1B1;
    cursor: not-allowed;
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
    padding: 20px;
    margin-bottom: 15px;
    position: relative;
    box-sizing: border-box;
    transition: box-shadow 0.3s ease;
}
.comentario:hover {
    box-shadow: 0 2px 8px rgba(0,0,0,0.1);
}
.comentario img.avatar {
    width: 50px;
    height: 50px;
    border-radius: 50%;
    object-fit: cover;
    flex-shrink: 0;
}
.info-comentario {
    flex: 1;
    display: flex;
    flex-direction: column;
    justify-content: center;
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
    margin-top: 8px;
    line-height: 1.4;
}

/* ICONO OPCIONES - TRES PUNTOS */
.opciones-comentario {
    position: absolute;
    top: 20px;
    right: 20px;
    cursor: pointer;
    padding: 5px;
    border-radius: 4px;
    transition: background-color 0.3s ease;
}
.opciones-comentario:hover {
    background-color: #f5f5f5;
}
.icono-opciones {
    width: 24px;
    height: 24px;
    color: #1661AC;
    transition: color 0.3s ease;
    display: block;
}
.icono-opciones:hover {
    color: #2D8EFF;
}
.menu-opciones {
    position: absolute;
    top: 35px;
    right: 0;
    width: 150px;
    height: 80px;
    background-color: #FFFFFF;
    border: 1px solid #2D8EFF;
    border-radius: 8px;
    box-shadow: 0 4px 12px rgba(0,0,0,0.15);
    z-index: 100;
    display: none;
    flex-direction: column;
    overflow: hidden;
}
.opcion-menu {
    flex: 1;
    display: flex;
    align-items: center;
    justify-content: center;
    font-family: 'Inter', sans-serif;
    font-size: 16px;
    color: #403F48;
    cursor: pointer;
    transition: background 0.3s ease;
    border: none;
    background: none;
    width: 100%;
    padding: 0;
}
.opcion-menu:hover {
    background-color: #f0f0f0;
}
.opcion-menu:first-child {
    border-bottom: 1px solid #2D8EFF;
}

/* EDICIÓN DE COMENTARIO */
.editar-comentario-contenedor {
    width: 100%;
}
.editar-comentario {
    width: 900px;
    min-height: 80px;
    max-height: 200px;
    border: 1px solid #B1B1B1;
    border-radius: 12px;
    padding: 15px;
    font-family: 'Inter', sans-serif;
    font-size: 16px;
    color: #403F48;
    resize: none;
    outline: none;
    margin-bottom: 15px;
    box-sizing: border-box;
    line-height: 1.5;
}
.editar-comentario:focus {
    border-color: #2D8EFF;
}
.btn-guardar {
    width: 90px;
    height: 40px;
    background-color: #2D8EFF;
    color: #FFFFFF;
    font-family: 'Poppins', sans-serif;
    font-weight: bold;
    font-size: 16px;
    border: none;
    border-radius: 6px;
    cursor: pointer;
    transition: background 0.3s ease;
    float: right;
}
.btn-guardar:hover {
    background-color: #1661AC;
}

/* MODAL ELIMINAR */
.modal-overlay {
    position: fixed;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background-color: rgba(0,0,0,0.5);
    display: none;
    justify-content: center;
    align-items: center;
    z-index: 1000;
}
.modal-eliminar {
    background-color: #FFFFFF;
    padding: 30px;
    border-radius: 12px;
    width: 400px;
    text-align: center;
    box-shadow: 0 8px 24px rgba(0,0,0,0.2);
}
.modal-titulo {
    font-family: 'Poppins', sans-serif;
    font-size: 16px;
    font-weight: bold;
    color: #403F48;
    margin-bottom: 20px;
    line-height: 1.4;
}
.modal-botones {
    display: flex;
    justify-content: space-between;
    gap: 20px;
}
.btn-cancelar {
    flex: 1;
    height: 40px;
    background-color: #EB7373;
    color: #061F3E;
    font-family: 'Inter', sans-serif;
    font-size: 16px;
    border: none;
    border-radius: 8px;
    cursor: pointer;
    transition: background 0.3s ease;
}
.btn-cancelar:hover {
    background-color: #d45c5c;
}
.btn-confirmar {
    flex: 1;
    height: 40px;
    background-color: #61C9A8;
    color: #FFFFFF;
    font-family: 'Inter', sans-serif;
    font-size: 16px;
    border: none;
    border-radius: 8px;
    cursor: pointer;
    transition: background 0.3s ease;
}
.btn-confirmar:hover {
    background-color: #4ab894;
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
/* ANUNCIO LATERAL - MODIFICADO CON IMAGEN */
.anuncio-lateral {
    position: sticky;
    top: 100px;
    width: 200px;
    height: 725px;
    background: white;
    border-radius: 12px;
    box-shadow: 0 4px 12px rgba(0,0,0,0.1);
    overflow: hidden;
    display: flex;
    align-items: center;
    justify-content: center;
    border: 1px solid #e0e0e0;
}

.anuncio-imagen {
    width: 100%;
    height: 100%;
    object-fit: cover;
    border-radius: 12px;
}

.anuncio-texto {
    position: absolute;
    top: 20px;
    left: 0;
    right: 0;
    text-align: center;
    font-family: 'Poppins', sans-serif;
    font-weight: 700;
    font-size: 24px;
    color: #1661AC;
    text-shadow: 1px 1px 2px rgba(0,0,0,0.1);
    z-index: 2;
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
        max-height: 400px;
    }
    .comentario, .caja-comentario-contenedor, .editar-comentario {
        width: 100%;
    }
    .detalle-descripcion {
        flex-direction: column;
    }
    .miniaturas {
        flex-wrap: wrap;
    }
    .miniaturas img {
        width: 150px;
        height: 100px;
    }
    .anuncio-lateral {
        position: relative;
        top: 0;
        width: 100%;
        height: 200px;
        margin-top: 30px;
    }
    .caja-comentario {
        width: 100%;
    }
    .btn-publicar {
        position: relative;
        float: right;
        margin-top: 10px;
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
            <?php if(count($imagenes) > 1): ?>
            <div class="miniaturas">
                <?php foreach ($imagenes as $index => $img): ?>
                    <img src="imagenes/noticias/<?= $img ?>" 
                         alt="Miniatura" 
                         onclick="cambiarImagen('<?= $img ?>', this)"
                         <?= $index === 0 ? 'class="activa"' : '' ?>>
                <?php endforeach; ?>
            </div>
            <?php endif; ?>
        </div>

        <!-- Fecha del hecho y Descripción -->
        <div class="detalle-descripcion">
            <div class="fecha-hecho">
                Fecha del hecho: <?= date('d/m/Y', strtotime($noticia['fecha_hecho'] ?? $noticia['fecha'])) ?>
            </div>
            <div class="descripcion">
                <?= nl2br(htmlspecialchars($noticia['descripcion'])) ?>
            </div>
        </div>

        <!-- Icono Reportar -->
        <div class="reportar-contenedor">
            <div class="reportar-tooltip">Reportar noticia</div>
            <svg class="reportar-icono" onclick="redirigirReporte()" viewBox="0 0 24 24" fill="currentColor">
                <path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm1 15h-2v-2h2v2zm0-4h-2V7h2v6z"/>
            </svg>
        </div>

        <!-- SECCIÓN DE COMENTARIOS -->
        <div class="seccion-comentarios">
            <h2>Comentarios</h2>
            
            <?php if(isset($_SESSION['usuario_id'])): ?>
                <!-- Caja de comentarios para usuarios registrados -->
                <div class="caja-comentario-contenedor">
                    <textarea class="caja-comentario" placeholder="Escribe un comentario..." maxlength="1000"></textarea>
                    <button class="btn-publicar" onclick="publicarComentario()" disabled>Publicar</button>
                </div>
            <?php else: ?>
                <!-- Mensaje para usuarios no registrados -->
                <div class="mensaje-iniciar-sesion">
                    Inicia sesión para dejar un comentario
                </div>
            <?php endif; ?>

            <!-- Lista de comentarios -->
            <div id="lista-comentarios">
                <?php 
                // Comentarios de ejemplo para demostrar los iconos de 3 puntos
                $comentarios_ejemplo = [
                    [
                        'id' => 1,
                        'nombre' => 'David',
                        'avatar' => 'default.png',
                        'texto' => 'Ojalá pronto terminen estas lluvias.',
                        'fecha' => '2025-09-04 10:00:00',
                        'usuario_id' => $usuario_id // Este comentario pertenece al usuario actual
                    ],
                    [
                        'id' => 2,
                        'nombre' => 'María',
                        'avatar' => 'default.png',
                        'texto' => 'Excelente información, gracias por compartir.',
                        'fecha' => '2025-09-04 09:30:00',
                        'usuario_id' => 999 // Este comentario NO pertenece al usuario actual
                    ]
                ];
                
                // Mostrar comentarios de ejemplo si no hay comentarios reales
                $comentarios_a_mostrar = !empty($comentarios) ? $comentarios : $comentarios_ejemplo;
                
                foreach($comentarios_a_mostrar as $comentario): 
                ?>
                <div class="comentario" id="comentario-<?= $comentario['id'] ?>">
                    <img src="imagenes/usuarios/<?= $comentario['avatar'] ?? 'default.png' ?>" alt="Usuario" class="avatar">
                    <div class="info-comentario">
                        <div>
                            <strong><?= htmlspecialchars($comentario['nombre']) ?></strong>
                            <span class="fecha-comentario"><?= date('d/m/Y H:i', strtotime($comentario['fecha'])) ?></span>
                        </div>
                        <p class="texto-comentario"><?= htmlspecialchars($comentario['texto']) ?></p>
                    </div>
                    
                    <!-- Icono de 3 puntos (SOLO PARA PROPIETARIO O ADMIN Y USUARIOS REGISTRADOS) -->
                    <?php if(isset($_SESSION['usuario_id']) && ($_SESSION['usuario_id'] == $comentario['usuario_id'] || $es_admin)): ?>
                    <div class="opciones-comentario">
                        <svg class="icono-opciones" onclick="toggleMenu(<?= $comentario['id'] ?>)" viewBox="0 0 24 24" fill="currentColor">
                            <path d="M12 8c1.1 0 2-.9 2-2s-.9-2-2-2-2 .9-2 2 .9 2 2 2zm0 2c-1.1 0-2 .9-2 2s.9 2 2 2 2-.9 2-2-.9-2-2-2zm0 6c-1.1 0-2 .9-2 2s.9 2 2 2 2-.9 2-2-.9-2-2-2z"/>
                        </svg>
                        <div class="menu-opciones" id="menu-<?= $comentario['id'] ?>">
                            <button class="opcion-menu" onclick="editarComentario(<?= $comentario['id'] ?>)">Editar</button>
                            <button class="opcion-menu" onclick="mostrarModalEliminar(<?= $comentario['id'] ?>)">Eliminar</button>
                        </div>
                    </div>
                    <?php endif; ?>
                </div>
                <?php endforeach; ?>
            </div>

            <button class="btn-ver-mas" onclick="verMasComentarios()">Ver más comentarios</button>
        </div>
    </div>

    <!-- Columna derecha: anuncio -->
   <div class="anuncio-lateral">
    <div class="anuncio-texto"><br></div>
    <img src="imagenes/cinemark.jpeg" alt="Anuncio Cinemark XD" class="anuncio-imagen">
</div>

<!-- Modal Eliminar -->
<div class="modal-overlay" id="modalEliminar">
    <div class="modal-eliminar">
        <div class="modal-titulo">¿Estás seguro de eliminar tu comentario?</div>
        <div class="modal-botones">
            <button class="btn-cancelar" onclick="cerrarModal()">Cancelar</button>
            <button class="btn-confirmar" onclick="eliminarComentario()">Confirmar</button>
        </div>
    </div>
</div>

<script>
let comentarioAEliminar = null;
let comentariosCargados = 5;

// Galería de imágenes
function cambiarImagen(imagen, elemento) {
    document.getElementById('imagenPrincipal').src = 'imagenes/noticias/' + imagen;
    
    // Remover clase activa de todas las miniaturas
    document.querySelectorAll('.miniaturas img').forEach(img => {
        img.classList.remove('activa');
    });
    
    // Agregar clase activa a la miniatura clickeada
    elemento.classList.add('activa');
}

// Reportar noticia
function redirigirReporte() {
    window.location.href = 'reportar.php?id=<?= $id_noticia ?>';
}

// Comentarios (solo para usuarios registrados)
<?php if(isset($_SESSION['usuario_id'])): ?>
document.addEventListener('DOMContentLoaded', function() {
    const textarea = document.querySelector('.caja-comentario');
    const btnPublicar = document.querySelector('.btn-publicar');
    
    if (textarea) {
        textarea.addEventListener('input', function() {
            const caracteres = this.value.length;
            
            // Habilitar/deshabilitar botón
            btnPublicar.disabled = caracteres === 0 || caracteres > 1000;
            
            // Auto-expandir textarea
            this.style.height = 'auto';
            this.style.height = (this.scrollHeight) + 'px';
        });

        // Manejar el evento de focus para cambiar el color del texto
        textarea.addEventListener('focus', function() {
            this.style.color = '#403F48';
        });

        textarea.addEventListener('blur', function() {
            if (this.value === '') {
                this.style.color = '#B1B1B1';
            }
        });
    }
});

function publicarComentario() {
    const textarea = document.querySelector('.caja-comentario');
    const texto = textarea.value.trim();
    
    if (texto.length === 0 || texto.length > 1000) return;
    
    fetch('', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
        },
        body: 'nuevo_comentario=' + encodeURIComponent(texto)
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            location.reload();
        }
    });
}

// Menú de opciones - Icono de 3 puntos
function toggleMenu(comentarioId) {
    const menu = document.getElementById('menu-' + comentarioId);
    const todosMenus = document.querySelectorAll('.menu-opciones');
    
    // Cerrar otros menús
    todosMenus.forEach(m => {
        if (m !== menu) m.style.display = 'none';
    });
    
    // Toggle menú actual
    menu.style.display = menu.style.display === 'flex' ? 'none' : 'flex';
}

// Cerrar menús al hacer click fuera
document.addEventListener('click', function(e) {
    if (!e.target.closest('.opciones-comentario')) {
        document.querySelectorAll('.menu-opciones').forEach(menu => {
            menu.style.display = 'none';
        });
    }
});

// Editar comentario
function editarComentario(comentarioId) {
    const comentarioDiv = document.getElementById('comentario-' + comentarioId);
    const textoActual = comentarioDiv.querySelector('.texto-comentario').textContent;
    
    comentarioDiv.innerHTML = `
        <div class="editar-comentario-contenedor">
            <textarea class="editar-comentario" maxlength="1000">${textoActual}</textarea>
            <button class="btn-guardar" onclick="guardarEdicion(${comentarioId})">Guardar</button>
        </div>
    `;

    // Configurar auto-expansión para el textarea de edición
    const textareaEdicion = comentarioDiv.querySelector('.editar-comentario');
    textareaEdicion.style.height = 'auto';
    textareaEdicion.style.height = (textareaEdicion.scrollHeight) + 'px';
    
    textareaEdicion.addEventListener('input', function() {
        this.style.height = 'auto';
        this.style.height = (this.scrollHeight) + 'px';
    });
}

function guardarEdicion(comentarioId) {
    const textarea = document.querySelector('.editar-comentario');
    const nuevoTexto = textarea.value.trim();
    
    if (nuevoTexto.length === 0 || nuevoTexto.length > 1000) return;
    
    fetch('', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
        },
        body: 'editar_id=' + comentarioId + '&editar_texto=' + encodeURIComponent(nuevoTexto)
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            location.reload();
        }
    });
}

// Eliminar comentario
function mostrarModalEliminar(comentarioId) {
    comentarioAEliminar = comentarioId;
    document.getElementById('modalEliminar').style.display = 'flex';
}

function cerrarModal() {
    document.getElementById('modalEliminar').style.display = 'none';
    comentarioAEliminar = null;
}

function eliminarComentario() {
    if (!comentarioAEliminar) return;
    
    fetch('', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
        },
        body: 'eliminar_id=' + comentarioAEliminar
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            document.getElementById('comentario-' + comentarioAEliminar).remove();
            cerrarModal();
        }
    });
}
<?php endif; ?>

// Ver más comentarios
function verMasComentarios() {
    // Aquí iría la lógica para cargar más comentarios
    alert('Funcionalidad de "Ver más comentarios" en desarrollo');
}

// Cerrar modal al hacer click fuera
document.getElementById('modalEliminar').addEventListener('click', function(e) {
    if (e.target === this) {
        cerrarModal();
    }
});
</script>

</body>
</html>