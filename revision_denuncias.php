<?php
session_start();
include 'conexion.php';
include 'menu3.php';

if (!isset($_SESSION['usuario_id'])) {
    header("Location: login.php");
    exit();
}
if ($_SESSION['usuario_rol'] !== 'Administrador') {
    header("Location: noticias.php");
    exit();
}

// Se combinan ambas tablas de denuncias
$sql = "
    SELECT *, 'propuestas_denuncias' as tabla_origen FROM propuestas_denuncias 
    WHERE estado = 'pendiente'
    UNION ALL
    SELECT *, 'propuestas_denuncias_anonima' as tabla_origen FROM propuestas_denuncias_anonima 
    WHERE estado = 'pendiente'
    ORDER BY fecha DESC
    LIMIT 100
";
$resultado = $conexion->query($sql);
$noticias = [];
if ($resultado) {
    $noticias = $resultado->fetch_all(MYSQLI_ASSOC);
}
?>

<!DOCTYPE html>
<html charset="UTF-8">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Revision de Denuncias - Periódico Digital Comunitario</title>
    <link href="https://fonts.googleapis.com/css2?family=Open+Sans:wght@700;400&family=Poppins:wght@400;600;700&display=swap" rel="stylesheet">
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #fff;
            margin: 0;
            color: #333;
        }
        
        .contenedor {
            max-width: 1200px;
            margin: 130px auto 30px;
            padding: 0 20px;
        }
        .titulo-session {
            font-family: 'Poppins', sans-serif;
            font-size: 24px;
            font-weight: bold;
            color: #403F48;
            text-align: left;
            margin-left: -40px;
        }
        .titulo-seccion {
            color: #0d5c9b;
            border-bottom: 2px solid #0d5c9b;
            padding-bottom: 10px;
            margin-bottom: 20px;
        }
        .search-container {
            display: flex;
            align-items: center;
            width: 60%;
            max-width: 500px;
            margin-left: -40px;
        }
        .search-container input {
            width: 100%;
            padding: 8px 12px;
            border: 1px solid #438DCB;
            border-radius: 10px;
            font-family: 'Open Sans', sans-serif;
            font-size: 16px;
            color: #8A8991;
        }
        .search-icon {
            width: 20px;
            height: 20px;
            opacity: 0.8;
            margin-right: 10px;
            cursor: pointer;
            color: #403F48;
        }
        .search-container input:hover,
        .search-container input:focus {
            transform: scale(1.03);
            box-shadow: 0px 0px 6px #438DCB;
            border-color: #438DCB;
        }
        .search-container input:focus { outline: none; }
        .search-icon:hover {
            transform: scale(1.15);
            filter: brightness(1.2);
        }
        .search-container input,
        .search-icon {
            transition: transform 0.2s ease, box-shadow 0.2s ease, filter 0.2s ease;
        }
        .noticias-pendientes {
            display: grid;
            grid-template-columns: repeat(4, 320px);
            gap: 10px;
            margin-top: 20px; 
            justify-content: center;
        }

        /* Responsivo */
        @media (max-width: 1300px) {
            .noticias-pendientes { grid-template-columns: repeat(3, 320px); }
        }
        @media (max-width: 1000px) {
            .noticias-pendientes { grid-template-columns: repeat(2, 320px); }
        }
        @media (max-width: 700px) {
            .noticias-pendientes { grid-template-columns: 1fr; }
        }
        .noticia-card {
            background-color: #fff;
            border-radius: 10px;
            overflow: hidden;
            box-shadow: 0 3px 10px rgba(0,0,0,0.1);
            transition: transform 0.3s ease, box-shadow 0.3s ease;
            max-width: 320px;
            width: 100%;
            margin: 0 auto;
            display: flex;
            flex-direction: column;
            margin-top: 10px;
        }
        .noticia-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 5px 15px rgba(0,0,0,0.2);
        }
        .noticia-imagen {
            width: 100%;
            height: 220px;
            object-fit: cover;
            border-radius: 12px;
            display: block;
        }
        .carousel {
            position: relative;
            width: 100%;
            height: 220px;
            overflow: hidden;
            border-radius: 12px;
            background-color: #ddd;
            cursor: pointer;
        }
        .carousel img {
            position: absolute;
            width: 100%;
            height: 100%;
            object-fit: cover;
            opacity: 0;
            transition: opacity 0.6s ease-in-out;
        }
        .carousel img.activa {
            opacity: 1;
        }
        .noticia-contenido {
            padding: 5px;
        }
        .noticia-categoria {
            display: inline-block;
            background-color: #0d5c9b;
            color: white;
            padding: 5px 10px;
            border-radius: 5px;
            font-size: 12px;
            margin-bottom: 10px;
        }
        .noticia-titulo {
            font-family: 'Poppins', sans-serif;
            font-size: 20px;
            font-weight: bold;
            margin: 10px 0;
            color: #1661AC;
            text-align: left;
            cursor: pointer;
            transition: color 0.2s ease;
        }
        .noticia-titulo:hover {
            color: #2D8EFF;
        }
        .noticia-meta {
            display: flex;
            justify-content: flex-start;
            align-items: center;
            gap: 10px;
            font-family: 'Poppins', sans-serif;
            font-size: 16px;
            color: #74737C;
            margin-top: 10px;
            border-top: none;
            padding-top: 0;
        }
        .noticia-meta span:not(:last-child)::after {
            content: "|";
            margin-left: 10px;
        }
        .sin-noticias {
            text-align: center;
            padding: 50px;
            color: #666;
            font-size: 18px;
            grid-column: 1 / -1;
        }
        .sin-coincidencias {
            text-align: center;
            padding: 60px 20px;
            font-family: 'Poppins', sans-serif;
            font-size: 24px;
            color: #403F48;
            display: none;
        }
        /* Boton ver más */
        .vermas-container {
            display: flex;
            justify-content: center;
            margin: 20px 0 40px;
        }
        .btn-vermas {
            width: 170px;
            height: 50px;
            background-color: #1661AC;
            color: #FFFFFF;
            border: none;
            border-radius: 8px;
            font-family: 'Open Sans', sans-serif;
            font-size: 20px;
            font-weight: 700;
            cursor: pointer;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            transition: transform 0.18s ease, box-shadow 0.18s ease, background-color 0.18s ease;
        }
        .btn-vermas:hover {
            background-color: #2D8EFF;
            transform: translateY(-6px);
            box-shadow: 0 8px 20px rgba(45,142,255,0.18);
        }
        .btn-vermas:active {
            transform: translateY(-3px);
        }
    </style>
</head>
<body>

        <div class="contenedor">
            <h1 class="titulo-session">Denuncias pendientes de publicar</h1>
            <div style="margin-top: 20px; display: flex; align-items: center; gap: 10px;">
                <div class="search-container">
                    <img src="imagenes/lupa1.png" class="search-icon" onclick="filtrarNoticias()">
                    <input type="text" id="buscador" placeholder="Buscar por título, autor o categoría" oninput="filtrarNoticias()">
                </div>
            </div>

            <?php if (empty($noticias)): ?>
                <div class="sin-noticias">
                    No hay denuncias pendientes de revision en este momento.
                </div>
            <?php else: ?>
                <div class="sin-coincidencias" id="sin-coincidencias">
                    No se encontraron coincidencias
                </div>
                <div class="noticias-pendientes" id="lista-noticias">
                    <?php foreach ($noticias as $noticia): ?>
                        <?php
                            // Preparar arreglo de imágenes (soporta imagen, imagen2, imagen3)
                            $imgs = [];
                            foreach (['imagen', 'imagen2', 'imagen3'] as $campo) {
                                if (!empty($noticia[$campo]) && file_exists('imagenes/denuncias/' . $noticia[$campo])) {
                                    $imgs[] = $noticia[$campo];
                                }
                            }
                        ?>
                        <div class="noticia-card">
                            <?php if (!empty($imgs)): ?>
                                <div class="carousel" data-interval="5000" data-id="<?php echo htmlspecialchars($noticia['id']); ?>" data-tabla="<?php echo htmlspecialchars($noticia['tabla_origen']); ?>">
                                    <?php foreach ($imgs as $i => $img): ?>
                                        <img src="imagenes/denuncias/<?php echo htmlspecialchars($img); ?>" 
                                             alt="<?php echo htmlspecialchars($noticia['titulo']); ?>"
                                             class="<?php echo $i === 0 ? 'activa' : ''; ?>">
                                    <?php endforeach; ?>
                                </div>
                            <?php else: ?>
                                <div class="carousel" data-interval="5000" data-id="<?php echo htmlspecialchars($noticia['id']); ?>" data-tabla="<?php echo htmlspecialchars($noticia['tabla_origen']); ?>">
                                    <img src="imagenes/denuncias/default.jpg" alt="Sin imagen" class="activa">
                                </div>
                            <?php endif; ?>

                            <div class="noticia-contenido">
                                <h3 class="noticia-titulo" data-id="<?php echo htmlspecialchars($noticia['id']); ?>" data-tabla="<?php echo htmlspecialchars($noticia['tabla_origen']); ?>"><?php echo htmlspecialchars($noticia['titulo']); ?></h3>

                                <div class="noticia-meta">
                                    <span><?php echo date('d/m/Y', strtotime($noticia['fecha'])); ?></span>
                                    <span>Anónimo</span>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
                <div class="vermas-container">
                    <button id="btn-vermas" class="btn-vermas">Ver más</button>
                </div>
            <?php endif; ?>
    
        </div>
</body>
<script>
// Inicializar carruseles y paginación
document.addEventListener('DOMContentLoaded', function() {
    // Click handlers para redirigir a detalles
    document.querySelectorAll('.carousel, .noticia-titulo').forEach(el => {
        el.addEventListener('click', function(e) {
            const id = this.dataset.id;
            const tabla = this.dataset.tabla;
            if (id && tabla) {
                window.location.href = 'detalle_denuncia.php?id=' + id + '&tabla=' + encodeURIComponent(tabla);
            }
        });
    });

    // Carruseles por tarjeta
    const carousels = document.querySelectorAll('.carousel');
    carousels.forEach(carousel => {
        const imgs = carousel.querySelectorAll('img');
        if (imgs.length <= 1) return; // No rotar si hay una sola imagen

        let currentIndex = 0;
        const interval = parseInt(carousel.dataset.interval || 5000, 10);

        setInterval(() => {
            imgs[currentIndex].classList.remove('activa');
            currentIndex = (currentIndex + 1) % imgs.length;
            imgs[currentIndex].classList.add('activa');
        }, interval);
    });

    // Paginación
    const lista = document.getElementById('lista-noticias');
    const cards = Array.from(document.querySelectorAll('#lista-noticias .noticia-card'));
    const btn = document.getElementById('btn-vermas');
    const INITIAL_SHOW = 20; 
    const BATCH = 8; 
    let shownCount = INITIAL_SHOW;

    cards.forEach((c, i) => { c.dataset.index = i; });

    function applyInitialPagination() {
        cards.forEach((c, i) => {
            if (i < shownCount) c.style.display = 'block';
            else c.style.display = 'none';
        });
        // Mostrar siempre el botón mientras exista al menos una tarjeta
        if (cards.length === 0) btn.style.display = 'none';
        else btn.style.display = 'inline-flex';
    }

    if (cards.length === 0) {
        if (btn) btn.style.display = 'none';
    } else {
        applyInitialPagination();
    }

    if (btn) {
        btn.addEventListener('click', () => {
            const prev = shownCount;
            shownCount += BATCH;
            cards.forEach((c, i) => {
                if (i < shownCount) c.style.display = 'block';
            });
            if (cards.length <= shownCount) btn.style.display = 'none';

            // desplazar suavemente hasta la primera tarjeta nueva
            const firstNew = cards[prev];
            if (firstNew) firstNew.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
        });
    }
});

function filtrarNoticias() {
    const termino = document.getElementById('buscador').value.toLowerCase().trim();
    const noticias = document.querySelectorAll('#lista-noticias .noticia-card');
    let hayCoincidencias = false;

    noticias.forEach(noticia => {
        const titulo = noticia.querySelector('.noticia-titulo')?.textContent.toLowerCase() || '';

        if (titulo.includes(termino)) {
            noticia.style.display = 'block';
            hayCoincidencias = true;
        } else {
            noticia.style.display = 'none';
        }
    });

    const mensajeSin = document.getElementById('sin-coincidencias');
    if (termino !== '' && !hayCoincidencias) {
        mensajeSin.style.display = 'block';
    } else {
        mensajeSin.style.display = 'none';
    }

    // Controlar visibilidad del botón Ver más
    const btn = document.getElementById('btn-vermas');
    if (btn) {
        const total = document.querySelectorAll('#lista-noticias .noticia-card').length;
        // Durante búsqueda, ocultar el botón
        if (termino !== '') {
            btn.style.display = 'none';
        } else {
            // Mostrar el botón mientras exista al menos una tarjeta
            btn.style.display = (total === 0) ? 'none' : 'inline-flex';
        }
    }
}
</script>
</html>