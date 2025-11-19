VER_DENUNCIA.PHP 


<?php
session_start();
include 'conexion.php';

if (!isset($_GET['id'])) {
    header("Location: noticias.php");
    exit();
}

$id_noticia = intval($_GET['id']);
$usuario_id = $_SESSION['usuario_id'] ?? null;
$es_admin = ($_SESSION['usuario_rol'] ?? '') === 'Administrador';
$es_poblador = isset($_SESSION['usuario_id']); // Verificar si es poblador (usuario logueado)

// Obtener datos de la noticia
$stmt = $conexion->prepare("SELECT * FROM propuestas_denuncias WHERE id = ?");
$stmt->bind_param("i", $id_noticia);
$stmt->execute();
$noticia = $stmt->get_result()->fetch_assoc();
$stmt->close();

if (!$noticia) {
    header("Location: noticias.php");
    exit();
}

// Obtener todos los comentarios
$stmt = $conexion->prepare("SELECT c.*, u.nombre, u.avatar FROM comentariosdenuncias c JOIN usuarios u ON c.usuario_id = u.id WHERE c.propuestas_denuncias_id = ? ORDER BY c.fecha DESC");
$stmt->bind_param("i", $id_noticia);
$stmt->execute();
$todos_comentarios = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
$stmt->close();

// Obtener solo los primeros 3 comentarios para mostrar inicialmente
$comentarios_mostrados = array_slice($todos_comentarios, 0, 3);
$total_comentarios = count($todos_comentarios);
$hay_mas_comentarios = $total_comentarios > 3;

// Manejar acciones AJAX para comentarios
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['eliminar_id'])) {
        $id_comentario = intval($_POST['eliminar_id']);
        $stmt = $conexion->prepare("DELETE FROM comentariosdenuncias WHERE id = ? AND (usuario_id = ? OR ? = 1)");
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
        $stmt = $conexion->prepare("UPDATE comentariosdenuncias SET texto = ?, editado = 1 WHERE id = ? AND usuario_id = ?");
        $stmt->bind_param("sii", $texto, $id_comentario, $usuario_id);
        $stmt->execute();
        $stmt->close();
        echo json_encode(["success"=> true]);
        exit();
    }

    if (isset($_POST['nuevo_comentario'])) {
        $texto = trim($_POST['nuevo_comentario']);
        if (!empty($texto)) {
            $stmt = $conexion->prepare("INSERT INTO comentariosdenuncias (propuestas_denuncias_id, usuario_id, texto, fecha) VALUES (?, ?, ?, NOW())");
            $stmt->bind_param("iis", $id_noticia, $usuario_id, $texto);
            $stmt->execute();
            $stmt->close();
        }
        echo json_encode(["success" => true]);
        exit();
    }

    // Para cargar más comentarios
    if (isset($_POST['cargar_mas'])) {
        $offset = intval($_POST['offset']);
        $limite = 3;
        
        $stmt = $conexion->prepare("SELECT c.*, u.nombre, u.avatar FROM comentariosdenuncias c JOIN usuarios u ON c.usuario_id = u.id WHERE c.propuestas_denuncias_id = ? ORDER BY c.fecha DESC LIMIT ?, ?");
        $stmt->bind_param("iii", $id_noticia, $offset, $limite);
        $stmt->execute();
        $mas_comentarios = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
        $stmt->close();
        
        echo json_encode([
            "success" => true,
            "comentarios" => $mas_comentarios,
            "hay_mas" => ($offset + $limite) < $total_comentarios
        ]);
        exit();
    }
}

// Obtener imágenes
$imagenes = [];
foreach (['imagen', 'imagen2', 'imagen3'] as $campo) {
    if (!empty($noticia[$campo]) && file_exists('imagenes/denuncias/' . $noticia[$campo])) {
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
* {
    margin: 0;
    padding: 0;
    box-sizing: border-box;
}

body {
    font-family: 'Inter', sans-serif;
    background-color: #f9f9f9;
    margin: 0;
    padding: 0;
}

/* HEADER MEJORADO - COLOR CORREGIDO #061F3E Y SIN TEXTO */
.header {
    background-color: #061F3E;
    color: white;
    padding: 15px 20px;
    display: flex;
    justify-content: space-between;
    align-items: center;
    position: fixed;
    top: 0;
    left: 0;
    right: 0;
    z-index: 1000;
    height: 80px;
    box-shadow: 0 2px 10px rgba(0,0,0,0.1);
}

.logo {
    font-family: 'Poppins', sans-serif;
    font-size: 24px;
    font-weight: 700;
    display: flex;
    align-items: center;
    gap: 10px;
}

.logo-img {
    height: 40px;
    width: auto;
}

.nav-links {
    display: flex;
    gap: 15px;
}

.nav-links a {
    color: white;
    text-decoration: none;
    font-family: 'Poppins', sans-serif;
    font-weight: 600;
    font-size: 16px;
    transition: opacity 0.3s;
    padding: 8px 16px;
    border-radius: 6px;
}

.nav-links a:hover {
    opacity: 0.8;
    background-color: rgba(255,255,255,0.1);
}

/* MODAL CERRAR SESIÓN */
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

.modal-sesion {
    background-color: #FFFFFF;
    padding: 30px;
    border-radius: 12px;
    width: 400px;
    text-align: center;
    box-shadow: 0 8px 24px rgba(0,0,0,0.2);
}

.modal-titulo-sesion {
    font-family: 'Poppins', sans-serif;
    font-size: 18px;
    font-weight: bold;
    color: #403F48;
    margin-bottom: 20px;
    line-height: 1.4;
}

.modal-botones-sesion {
    display: flex;
    justify-content: space-between;
    gap: 20px;
}

.btn-cancelar-sesion {
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

.btn-cancelar-sesion:hover {
    background-color: #d45c5c;
}

.btn-confirmar-sesion {
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

.btn-confirmar-sesion:hover {
    background-color: #4ab894;
}

/* CONTENEDOR PRINCIPAL */
.contenedor-detalle {
    display: flex;
    justify-content: center;
    align-items: flex-start;
    gap: 30px;
    max-width: 1300px;
    margin: 100px auto 40px;
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

/* DESCRIPCIÓN */
.descripcion-denuncia {
    font-family: 'Inter', sans-serif;
    font-size: 20px;
    font-weight: 600;
    color: #403F48;
    line-height: 1.6;
    text-align: justify;
    margin-top: 30px;
}

/* ICONO REPORTAR - MEJORADO PARA QUE NO SE VEA CORTADO */
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
    top: -45px;
    right: 0;
    background-color: #2D8EFF;
    color: #FFFFFF;
    font-family: 'Poppins', sans-serif;
    font-size: 14px;
    font-weight: 600;
    padding: 8px 12px;
    border-radius: 6px;
    white-space: nowrap;
    opacity: 0;
    visibility: hidden;
    transition: opacity 0.3s ease;
    box-shadow: 0 2px 8px rgba(0,0,0,0.15);
    z-index: 10;
}
.reportar-tooltip::after {
    content: '';
    position: absolute;
    top: 100%;
    right: 12px;
    border-width: 5px;
    border-style: solid;
    border-color: #2D8EFF transparent transparent transparent;
}
.reportar-contenedor:hover .reportar-tooltip {
    opacity: 1;
    visibility: visible;
}

/* SECCIÓN COMENTARIOS */
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
.sidebar {
    width: 200px;
    flex-shrink: 0;
}

.anuncio-lateral {
    position: sticky;
    top: 100px;
    width: 200px;
    height: 725px;
    background-color: #fff;
    border: 1px solid #ccc;
    border-radius: 12px;
    box-shadow: 0 4px 8px rgba(0,0,0,0.1);
    overflow: hidden;
}

.anuncio-lateral img {
    width: 100%;
    height: 100%;
    object-fit: cover;
}

/* Estados vacíos */
.sin-imagen {
    width: 100%;
    max-width: 1000px;
    height: 500px;
    background: #f0f0f0;
    border-radius: 8px;
    display: flex;
    align-items: center;
    justify-content: center;
    color: #74737C;
    font-family: 'Poppins', sans-serif;
    font-size: 18px;
}

.sin-comentarios {
    text-align: center;
    color: #74737C;
    font-family: 'Inter', sans-serif;
    font-size: 16px;
    padding: 40px;
    background: #f8f9fa;
    border-radius: 8px;
}

.comentario-oculto {
    display: none;
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
    .descripcion-denuncia {
        font-size: 18px;
    }
    .miniaturas {
        flex-wrap: wrap;
    }
    .miniaturas img {
        width: 150px;
        height: 100px;
    }
    .sidebar {
        width: 100%;
        margin-top: 20px;
    }
    
    .anuncio-lateral {
        position: relative;
        top: 0;
        width: 100%;
        max-width: 400px;
        height: 300px;
        margin: 0 auto;
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

@media (max-width: 768px) {
    .header {
        padding: 10px 15px;
        height: 70px;
    }
    
    .logo {
        font-size: 20px;
    }
    
    .nav-links {
        gap: 10px;
    }
    
    .nav-links a {
        font-size: 14px;
    }
    
    .contenedor-detalle {
        margin-top: 80px;
        padding: 0 15px;
        gap: 20px;
    }
    
    .titulo-noticia {
        font-size: 24px;
    }
    
    .descripcion-denuncia {
        font-size: 18px;
    }
    
    .galeria-principal {
        height: 300px;
    }
    
    .miniaturas img {
        width: 150px;
        height: 100px;
    }
    
    .comentario {
        padding: 15px;
    }
    
    .comentario-header {
        flex-direction: column;
        align-items: flex-start;
        gap: 5px;
    }
}

@media (max-width: 480px) {
    .header {
        flex-direction: column;
        height: auto;
        padding: 10px;
    }
    
    .logo {
        margin-bottom: 10px;
    }
    
    .contenedor-detalle {
        margin-top: 120px;
    }
    
    .galeria-principal {
        height: 250px;
    }
    
    .miniaturas img {
        width: 120px;
        height: 80px;
    }
    
    .btn-ver-mas {
        width: 100%;
        max-width: 275px;
    }
}
</style>
</head>
<body>
    <!-- Header Mejorado - Solo logo sin texto y color #061F3E -->
    <header class="header">
        <div class="logo">
            <img src="imagenes/logo.png" alt="Comunicado Digital" class="logo-img">
        </div>
        <nav class="nav-links">
            <a href="javascript:history.back()">Volver</a>
            <?php if($es_poblador): ?>
                <!-- Solo mostrar "Cerrar Sesión" para pobladores (usuarios logueados) -->
                <a href="#" id="cerrarSesionBtn">Cerrar Sesión</a>
            <?php else: ?>
                <!-- Para invitados, mostrar "Iniciar Sesión" -->
                <a href="login.php">Iniciar Sesión</a>
            <?php endif; ?>
        </nav>
    </header>

    <div class="contenedor-detalle">
        <!-- Columna izquierda: contenido -->
        <div class="columna-noticia">
            <h1 class="titulo-noticia"><?= htmlspecialchars($noticia['titulo']) ?></h1>

            <div class="meta-noticia">
                <?php if ($noticia['publica'] ?? true): ?>
                    <span><?= htmlspecialchars($noticia['autor'] ?? 'Anónimo') ?></span>
                    <span>|</span>
                <?php endif; ?>
                <span><?= date('d/m/Y', strtotime($noticia['fecha'])) ?></span>
                <span>|</span>
                <span><?= date('H:i', strtotime($noticia['fecha'])) ?></span>
            </div>

            <!-- Galería -->
            <div class="galeria">
                <?php if (!empty($imagenes)): ?>
                    <img id="imagenPrincipal" src="imagenes/denuncias/<?= $imagenes[0] ?>" alt="Imagen principal" class="galeria-principal">
                    <?php if(count($imagenes) > 1): ?>
                    <div class="miniaturas">
                        <?php foreach ($imagenes as $index => $img): ?>
                            <img src="imagenes/denuncias/<?= $img ?>" 
                                 alt="Miniatura" 
                                 onclick="cambiarImagen('<?= $img ?>', this)"
                                 <?= $index === 0 ? 'class="activa"' : '' ?>>
                        <?php endforeach; ?>
                    </div>
                    <?php endif; ?>
                <?php else: ?>
                    <div class="sin-imagen">Sin imagen disponible</div>
                <?php endif; ?>
            </div>

            <!-- DESCRIPCIÓN -->
            <div class="descripcion-denuncia">
                <?= nl2br(htmlspecialchars($noticia['descripcion'] ?? '')) ?>
            </div>

            <!-- Icono Reportar - MEJORADO: "Reportar denuncia" con diseño mejorado -->
            <div class="reportar-contenedor">
                <div class="reportar-tooltip">Reportar Denuncia</div>
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
                    <?php if (empty($todos_comentarios)): ?>
                        <div class="sin-comentarios">
                            No hay comentarios aún. Sé el primero en comentar.
                        </div>
                    <?php else: ?>
                        <?php foreach ($comentarios_mostrados as $comentario): ?>
                            <div class="comentario" id="comentario-<?= $comentario['id'] ?>">
                                <img src="imagenes/usuarios/<?= $comentario['avatar'] ?? 'default.png' ?>" alt="Usuario" class="avatar">
                                <div class="info-comentario">
                                    <div>
                                        <strong><?= htmlspecialchars($comentario['nombre']) ?></strong>
                                        <span class="fecha-comentario">
                                            <?= date('d/m/Y H:i', strtotime($comentario['fecha'])) ?>
                                            <?= $comentario['editado'] ? ' (editado)' : '' ?>
                                        </span>
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
                        
                        <!-- Comentarios adicionales (ocultos inicialmente) -->
                        <?php for ($i = 3; $i < $total_comentarios; $i++): ?>
                            <div class="comentario comentario-oculto" id="comentario-<?= $todos_comentarios[$i]['id'] ?>">
                                <img src="imagenes/usuarios/<?= $todos_comentarios[$i]['avatar'] ?? 'default.png' ?>" alt="Usuario" class="avatar">
                                <div class="info-comentario">
                                    <div>
                                        <strong><?= htmlspecialchars($todos_comentarios[$i]['nombre']) ?></strong>
                                        <span class="fecha-comentario">
                                            <?= date('d/m/Y H:i', strtotime($todos_comentarios[$i]['fecha'])) ?>
                                            <?= $todos_comentarios[$i]['editado'] ? ' (editado)' : '' ?>
                                        </span>
                                    </div>
                                    <p class="texto-comentario"><?= htmlspecialchars($todos_comentarios[$i]['texto']) ?></p>
                                </div>
                                
                                <!-- Icono de 3 puntos (SOLO PARA PROPIETARIO O ADMIN Y USUARIOS REGISTRADOS) -->
                                <?php if(isset($_SESSION['usuario_id']) && ($_SESSION['usuario_id'] == $todos_comentarios[$i]['usuario_id'] || $es_admin)): ?>
                                <div class="opciones-comentario">
                                    <svg class="icono-opciones" onclick="toggleMenu(<?= $todos_comentarios[$i]['id'] ?>)" viewBox="0 0 24 24" fill="currentColor">
                                        <path d="M12 8c1.1 0 2-.9 2-2s-.9-2-2-2-2 .9-2 2 .9 2 2 2zm0 2c-1.1 0-2 .9-2 2s.9 2 2 2 2-.9 2-2-.9-2-2-2zm0 6c-1.1 0-2 .9-2 2s.9 2 2 2 2-.9 2-2-.9-2-2-2z"/>
                                    </svg>
                                    <div class="menu-opciones" id="menu-<?= $todos_comentarios[$i]['id'] ?>">
                                        <button class="opcion-menu" onclick="editarComentario(<?= $todos_comentarios[$i]['id'] ?>)">Editar</button>
                                        <button class="opcion-menu" onclick="mostrarModalEliminar(<?= $todos_comentarios[$i]['id'] ?>)">Eliminar</button>
                                    </div>
                                </div>
                                <?php endif; ?>
                            </div>
                        <?php endfor; ?>
                    <?php endif; ?>
                </div>

                <?php if ($hay_mas_comentarios): ?>
                    <button class="btn-ver-mas" id="btnVerMas" onclick="cargarMasComentarios()">Ver más comentarios</button>
                <?php endif; ?>
            </div>
        </div>

        <!-- Sidebar con Anuncio -->
        <aside class="sidebar">
            <div class="anuncio-lateral">
                <img src="imagenes/cinemark.jpeg" alt="Anuncio publicitario">
            </div>
        </aside>
    </div>

    <!-- Modal Eliminar Comentario -->
    <div class="modal-overlay" id="modalEliminar">
        <div class="modal-eliminar">
            <div class="modal-titulo">¿Estás seguro de eliminar tu comentario?</div>
            <div class="modal-botones">
                <button class="btn-cancelar" onclick="cerrarModal()">Cancelar</button>
                <button class="btn-confirmar" onclick="eliminarComentario()">Confirmar</button>
            </div>
        </div>
    </div>

    <!-- Modal volver a detalles -->
    <div class="modal" id="modalVolver">
        <div class="modal-contenido">
            <h3 class="modal-titulo">¿Estás seguro de volver a los detalles de esta noticia?</h3>
            <div class="modal-botones">
                <button class="btn-modal btn-cancelar" id="btnCancelarEnviar">Cancelar</button>
                <button class="btn-modal btn-confirmar" id="btnConfirmar">Confirmar</button>
            </div>
        </div>
    </div>

<!-- Modal Confirmar envio -->
 <div class="modal" id="modalEnviar">
    <div class="modal-contenido">
        <h3 class="modal-titulo">¿Estás seguro de enviar tu reporte?</h3>
        <p class="modal-texto">Tu reporte será enviado a los administradores para que sea revisado</p>
        <div class="modal-botones">
            <button class="btn-modal btn-cancelar" id="btnCancelarEnviar">Cancelar</button>
            <button class="btn-modal btn-confirmar" id="btnConfirmar">Confirmar</button>
        </div>
    </div>
</div>


    <!-- Modal Cerrar Sesión - SOLO PARA POBLADORES -->
    <?php if($es_poblador): ?>
    <div class="modal-overlay" id="modalCerrarSesion">
        <div class="modal-sesion">
            <div class="modal-titulo-sesion">¿Estás seguro de que quieres cerrar sesión?</div>
            <div class="modal-botones-sesion">
                <button class="btn-cancelar-sesion" onclick="cerrarModalSesion()">Cancelar</button>
                <button class="btn-confirmar-sesion" onclick="confirmarCerrarSesion()">Cerrar Sesión</button>
            </div>
        </div>
    </div>
    <?php endif; ?>

    <script>
    let comentarioAEliminar = null;
    let cargandoComentarios = false;

    // Galería de imágenes
    function cambiarImagen(imagen, elemento) {
        document.getElementById('imagenPrincipal').src = 'imagenes/denuncias/' + imagen;
        
        // Remover clase activa de todas las miniaturas
        document.querySelectorAll('.miniaturas img').forEach(img => {
            img.classList.remove('activa');
        });
        
        // Agregar clase activa a la miniatura clickeada
        elemento.classList.add('activa');
    }

    // Reportar denuncia
    function redirigirReporte() {
        window.location.href = 'formulario_reporte.php?id=<?= $id_noticia ?>';
    }

    // Modal Cerrar Sesión - SOLO PARA POBLADORES
    function mostrarModalCerrarSesion() {
        document.getElementById('modalCerrarSesion').style.display = 'flex';
    }

    function cerrarModalSesion() {
        document.getElementById('modalCerrarSesion').style.display = 'none';
    }

    function confirmarCerrarSesion() {
        window.location.href = 'logout.php';
    }

    // Event listener para el botón de cerrar sesión (solo para pobladores)
    document.addEventListener('DOMContentLoaded', function() {
        const cerrarSesionBtn = document.getElementById('cerrarSesionBtn');
        if (cerrarSesionBtn) {
            cerrarSesionBtn.addEventListener('click', function(e) {
                e.preventDefault();
                mostrarModalCerrarSesion();
            });
        }
    });

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

    // Cargar más comentarios
    function cargarMasComentarios() {
        if (cargandoComentarios) return;
        
        cargandoComentarios = true;
        const btnVerMas = document.getElementById('btnVerMas');
        btnVerMas.disabled = true;
        btnVerMas.textContent = 'Cargando...';
        
        // Mostrar comentarios ocultos
        const comentariosOcultos = document.querySelectorAll('.comentario-oculto');
        const comentariosAMostrar = Math.min(3, comentariosOcultos.length);
        
        for (let i = 0; i < comentariosAMostrar; i++) {
            if (comentariosOcultos[i]) {
                comentariosOcultos[i].classList.remove('comentario-oculto');
            }
        }
        
        // Verificar si quedan más comentarios por cargar
        setTimeout(() => {
            const comentariosRestantes = document.querySelectorAll('.comentario-oculto').length;
            
            if (comentariosRestantes === 0) {
                btnVerMas.style.display = 'none';
            } else {
                btnVerMas.disabled = false;
                btnVerMas.textContent = 'Ver más comentarios';
            }
            
            cargandoComentarios = false;
        }, 500);
    }

    // Cerrar modales al hacer click fuera
    document.getElementById('modalEliminar').addEventListener('click', function(e) {
        if (e.target === this) {
            cerrarModal();
        }
    });

    <?php if($es_poblador): ?>
    document.getElementById('modalCerrarSesion').addEventListener('click', function(e) {
        if (e.target === this) {
            cerrarModalSesion();
        }
    });
    <?php endif; ?>

    // Efectos de hover para mejor experiencia
    document.addEventListener('DOMContentLoaded', function() {
        const botones = document.querySelectorAll('button');
        botones.forEach(boton => {
            boton.addEventListener('mouseenter', function() {
                this.style.transform = 'translateY(-2px)';
                this.style.boxShadow = '0 4px 12px rgba(0,0,0,0.15)';
            });
            
            boton.addEventListener('mouseleave', function() {
                this.style.transform = 'translateY(0)';
                this.style.boxShadow = 'none';
            });
        });
    });
    </script>
</body>
</html>