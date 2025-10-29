<?php
session_start();
include 'conexion.php';
include 'menu.php';
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
<link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600;700&family=Inter:wght@400;600&display=swap" rel="stylesheet">
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
    margin-bottom: 10px;
}
.inicia-sesion {
    font-family: 'Poppins', sans-serif;
    font-size: 16px;
    font-style: italic;
    color: #403F48;
    margin-bottom: 20px;
}
.comentario {
    border: 1px solid #B1B1B1;
    border-radius: 8px;
    padding: 10px 15px;
    display: flex;
    align-items: flex-start;
    gap: 15px;
    width: 1000px;
    margin-bottom: 15px;
    background: #fff;
}
.comentario img {
    width: 50px;
    height: 50px;
    border-radius: 50%;
}
.info-comentario {
    flex: 1;
}
.info-comentario strong {
    font-family: 'Poppins', sans-serif;
    font-size: 16px;
    font-weight: 600;
    color: #1661AC;
}
.fecha-comentario {
    font-family: 'Inter', sans-serif;
    font-size: 12px;
    color: #B1B1B1;
    margin-left: 8px;
}
.texto-comentario {
    font-family: 'Inter', sans-serif;
    font-size: 16px;
    color: #403F48;
    margin-top: 5px;
}
.btn-ver-mas {
    display: block;
    width: 275px;
    height: 50px;
    margin: 25px auto;
    background-color: #1661AC;
    color: white;
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
    .comentario {
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

        <!-- Comentarios estáticos -->
        <div class="seccion-comentarios">
            <h2>Comentarios</h2>
            <p class="inicia-sesion">Inicia sesión para dejar un comentario</p>

            <div class="comentario">
                <img src="imagenes/usuarios/default.png" alt="Usuario">
                <div class="info-comentario">
                    <strong>María López</strong><span class="fecha-comentario">12/10/2025 14:30</span>
                    <p class="texto-comentario">Excelente cobertura de la noticia. Muy completa la información.</p>
                </div>
            </div>

            <div class="comentario">
                <img src="imagenes/usuarios/default.png" alt="Usuario">
                <div class="info-comentario">
                    <strong>Carlos Pérez</strong><span class="fecha-comentario">13/10/2025 09:15</span>
                    <p class="texto-comentario">Me parece importante que se hable de estos temas. Felicidades al autor.</p>
                </div>
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
function cambiarImagen(imagen) {
    document.getElementById('imagenPrincipal').src = 'imagenes/noticias/' + imagen;
}
</script>

</body>
</html>