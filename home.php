<?php
// home.php
$categoria_actual = 'inicio';
include 'conexion.php';
include 'chatbot.php';
include 'menu.php';



$noticia_id_destacada = $noticia_destacada['id'] ?? 0;

$termino_busqueda = '';
$where = '';
$params = [];

if (isset($_GET['busqueda']) && !empty($_GET['busqueda'])) {
    $termino_busqueda = trim($_GET['busqueda']);
    $where = "WHERE titulo LIKE ? OR descripcion LIKE ? OR autor LIKE ?";
    $params = array_fill(0, 3, '%' . $termino_busqueda . '%');
}

// Obtener la noticia más reciente
$sql_destacada = "SELECT * FROM noticias 
                 WHERE fecha <= CURDATE() 
                 ORDER BY fecha DESC, id DESC 
                 LIMIT 3";
$result_destacada = $conexion->query($sql_destacada);
$noticia_destacada = $result_destacada->fetch_assoc();

// Obtener las ultimas 8 noticias (para 2 filas de 4 en desktop)
$sql_ultimas = "SELECT * FROM noticias 
                WHERE fecha <= CURDATE() AND id != ?
                ORDER BY fecha DESC, id DESC
                LIMIT 8";
$stmt_ultimas = $conexion->prepare($sql_ultimas);
$stmt_ultimas->bind_param("i", $noticia_id_destacada);
$stmt_ultimas->execute();
$result_ultimas = $stmt_ultimas->get_result();

// Función para verificar si una imagen existe
function imagenExiste($nombre_imagen) {
    return !empty($nombre_imagen) && file_exists('imagenes/noticias/' . $nombre_imagen);
}

// Funcion para obtener imagenes de una noticia
function obtenerImagenNoticia($noticia) {
  $imagenes = [];
  foreach (['imagen', 'imagen2', 'imagen3'] as $campo) {
    if (!empty($noticia[$campo]) && imagenExiste($noticia[$campo])) {
        $imagenes[] = $noticia[$campo];
    }
  }
  return $imagenes;
}
?>
<script src="buscador.js" defer></script>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Inicio - Comunicado Digital</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600;700&family=Inter&display=swap" rel="stylesheet">
    <style>
        :root {
            --color-primario: #1661AC;
            --color-secundario: #2D8EFF;
            --color-texto: #000000;
            --color-texto-secundario: #74737C;
        }
        
        body {
            font-family: 'Inter', sans-serif;
            background-color: #f8f9fa;
        }
        
        /* TARJETA DESTACADA */
        .tarjeta-destacada {
            max-width: 1300px;
            height: auto;
            min-height: 940px;
            margin: 40px auto;
            border: 2px solid #1661AC;
            border-radius: 25px;
            background: #ffffff;
            box-shadow: 0 8px 32px rgba(0,0,0,0.1);
            overflow: hidden;
            transition: transform 0.3s ease, box-shadow 0.3s ease;
        }
        
        .tarjeta-destacada:hover {
            transform: translateY(-5px);
            box-shadow: 0 12px 40px rgba(0,0,0,0.15);
        }
        
        /* CARRUSEL DESTACADO */
        .carrusel-destacado {
            height: 625px;
            border-radius: 25px 25px 0 0;
            overflow: hidden;
            position: relative;
        }
        
        .carrusel-destacado .carousel-item {
            height: 625px;
        }
        
        .carrusel-destacado .carousel-item img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            border-radius: 25px 25px 0 0;
        }
        
        .carousel-control-prev,
        .carousel-control-next {
            width: 60px;
            height: 60px;
            background: rgba(0,0,0,0.5);
            border-radius: 50%;
            top: 50%;
            transform: translateY(-50%);
            margin: 0 20px;
        }
        
        .carousel-indicators {
            bottom: 20px;
        }
        
        .carousel-indicators button {
            width: 12px;
            height: 12px;
            border-radius: 50%;
            margin: 0 5px;
        }
        
        /* PLACEHOLDER PARA IMÁGENES */
        .imagen-placeholder {
            background: linear-gradient(45deg, #1661AC, #2D8EFF);
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-family: 'Poppins', sans-serif;
            font-size: 18px;
            font-weight: 600;
            text-align: center;
        }
        
        /* ENCABEZADO TARJETA */
        .encabezado-tarjeta {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 15px 40px 0 40px;
            margin-top: 15px;
            margin-bottom: 10px;
            font-family: 'Poppins', sans-serif;
            font-size: 16px;
            color: var(--color-texto-secundario);
        }
        
        .elementos-encabezado {
            display: flex;
            gap: 15px;
            align-items: center;
        }
        
        .separador {
            color: var(--color-texto-secundario);
        }
        
        /* TÍTULO DESTACADO */
        .titulo-destacado {
            padding: 10px 40px 8px 40px;
            margin: 0;
        }
        
        .titulo-destacado a {
            font-family: 'Poppins', sans-serif;
            font-size: 28px;
            font-weight: 700;
            color: var(--color-primario);
            text-decoration: none;
            display: -webkit-box;
            -webkit-line-clamp: 2;
            -webkit-box-orient: vertical;
            overflow: hidden;
            line-height: 1.3;
            transition: color 0.3s ease;
        }
        
        .titulo-destacado a:hover {
            color: var(--color-secundario);
            text-decoration: underline;
            text-decoration-color: var(--color-secundario);
            text-underline-offset: 4px;
        }
        
        /* RESUMEN DESTACADO */
        .resumen-destacado {
            padding: 8px 40px 30px 40px;
            font-family: 'Inter', sans-serif;
            font-size: 20px;
            color: var(--color-texto);
            line-height: 1.5;
            display: -webkit-box;
            -webkit-line-clamp: 4;
            -webkit-box-orient: vertical;
            overflow: hidden;
            text-align: left;
        }
        
        /* TARJETAS SECUNDARIAS */
        .contenedor-noticias {
            max-width: 1300px;
            margin: 60px auto;
            padding: 0 20px;
        }
        
        .tarjeta-noticia {
            background: #ffffff;
            border-radius: 20px;
            overflow: hidden;
            box-shadow: 0 4px 20px rgba(0,0,0,0.08);
            transition: transform 0.3s ease, box-shadow 0.3s ease;
            height: 100%;
            margin-bottom: 30px;
        }
        
        .tarjeta-noticia:hover {
            transform: translateY(-5px);
            box-shadow: 0 8px 30px rgba(0,0,0,0.15);
        }
        
        .tarjeta-noticia img {
            width: 100%;
            height: 250px;
            object-fit: cover;
        }
        
        .contenido-tarjeta {
            padding: 20px;
        }
        
        .encabezado-pequeno {
            font-family: 'Poppins', sans-serif;
            font-size: 14px;
            color: var(--color-texto-secundario);
            margin-bottom: 10px;
            display: flex;
            gap: 10px;
            align-items: center;
        }
        
        .titulo-pequeno {
            font-family: 'Poppins', sans-serif;
            font-size: 18px;
            font-weight: 600;
            color: var(--color-primario);
            margin-bottom: 10px;
            display: -webkit-box;
            -webkit-line-clamp: 2;
            -webkit-box-orient: vertical;
            overflow: hidden;
            line-height: 1.3;
        }
        
        .titulo-pequeno a {
            color: inherit;
            text-decoration: none;
            transition: color 0.3s ease;
        }
        
        .titulo-pequeno a:hover {
            color: var(--color-secundario);
            text-decoration: underline;
            text-decoration-color: var(--color-secundario);
        }
        
        .resumen-pequeno {
            font-family: 'Inter', sans-serif;
            font-size: 16px;
            color: var(--color-texto);
            line-height: 1.4;
            display: -webkit-box;
            -webkit-line-clamp: 3;
            -webkit-box-orient: vertical;
            overflow: hidden;
        }
        
        /* RESPONSIVO */
        @media (max-width: 1200px) {
            .tarjeta-destacada {
                margin: 30px 20px;
                min-height: auto;
            }
            
            .encabezado-tarjeta,
            .titulo-destacado,
            .resumen-destacado {
                padding-left: 30px;
                padding-right: 30px;
            }
        }
        
        @media (max-width: 768px) {
            .carrusel-destacado {
                height: 400px;
            }
            
            .carrusel-destacado .carousel-item {
                height: 400px;
            }
            
            .encabezado-tarjeta {
                flex-direction: column;
                gap: 10px;
                align-items: flex-start;
            }
            
            .elementos-encabezado {
                flex-wrap: wrap;
                gap: 8px;
            }
            
            .titulo-destacado a {
                font-size: 24px;
            }
            
            .resumen-destacado {
                font-size: 18px;
            }
            
            .carousel-control-prev,
            .carousel-control-next {
                width: 40px;
                height: 40px;
                margin: 0 10px;
            }
        }
        
        @media (max-width: 576px) {
            .tarjeta-destacada {
                margin: 20px 15px;
                border-radius: 20px;
            }
            
            .carrusel-destacado {
                height: 300px;
                border-radius: 20px 20px 0 0;
            }
            
            .carrusel-destacado .carousel-item {
                height: 300px;
            }
            
            .encabezado-tarjeta,
            .titulo-destacado,
            .resumen-destacado {
                padding-left: 20px;
                padding-right: 20px;
            }
            
            .titulo-destacado a {
                font-size: 20px;
            }
            
            .resumen-destacado {
                font-size: 16px;
            }
        }

        /* SECCIÓN ÚLTIMAS NOTICIAS */
        .seccion-ultimas-noticias {
    max-width: 1300px;
    margin: 60px auto;
    padding: 0 20px;
}
.titulo-ultimas-noticias {
    font-family: 'Poppins', sans-serif;
    font-size: 24px;
    font-weight: 700;
    color: #403F48;
    text-align: left;
    margin-bottom: 30px;
}
.contenedor-ultimas-noticias {
    display: grid;
    grid-template-columns: repeat(4, 1fr);
    gap: 20px;
    justify-content: center;
}

/* Tarjetas */
.tarjeta-ultima-noticia {
    background: #fff;
    border-radius: 16px;
    overflow: hidden;
    box-shadow: 0 4px 16px rgba(0,0,0,0.08);
    transition: all 0.3s ease;
    max-width: 320px;
    margin: 0 auto;
}
.tarjeta-ultima-noticia:hover {
    transform: translateY(-6px);
    box-shadow: 0 10px 30px rgba(0,0,0,0.15);
}

/* Carrusel interno */
.carrusel-ultimas {
    height: 220px;
    border-radius: 12px;
    overflow: hidden;
    position: relative;
}
.carrusel-ultimas img {
    width: 100%;
    height: 100%;
    object-fit: cover;
    border-radius: 12px;
}
.carrusel-ultimas .carousel-control-prev,
.carrusel-ultimas .carousel-control-next {
    width: 35px;
    height: 35px;
    background: rgba(0,0,0,0.4);
    border-radius: 50%;
    top: 50%;
    transform: translateY(-50%);
    opacity: 0;
    transition: opacity 0.3s ease;
}
.tarjeta-ultima-noticia:hover .carousel-control-prev,
.tarjeta-ultima-noticia:hover .carousel-control-next {
    opacity: 1;
}
.carrusel-ultimas .carousel-indicators {
    bottom: 8px;
}
.carrusel-ultimas .carousel-indicators button {
    width: 8px;
    height: 8px;
    border-radius: 50%;
}

/* Contenido */
.contenido-ultima-noticia {
    padding: 18px 15px 25px 15px;
}
.titulo-ultima-noticia {
    font-family: 'Poppins', sans-serif;
    font-size: 20px;
    font-weight: 700;
    color: #1661AC;
    text-align: left;
    margin-bottom: 12px;
    line-height: 1.3;
}
.titulo-ultima-noticia a {
    text-decoration: none;
    color: inherit;
    transition: color 0.3s ease;
}
.titulo-ultima-noticia a:hover {
    color: #2D8EFF;
    text-decoration: underline;
    text-decoration-color: #2D8EFF;
}
.info-ultima-noticia {
    font-family: 'Poppins', sans-serif;
    font-size: 16px;
    color: #74737C;
    display: flex;
    justify-content: flex-start;
    align-items: center;
    gap: 10px;
}
.separador-info {
    color: #74737C;
}

/* Placeholder */
.imagen-placeholder {
    background: linear-gradient(45deg, #1661AC, #2D8EFF);
    display: flex;
    align-items: center;
    justify-content: center;
    color: #fff;
    font-family: 'Poppins', sans-serif;
    font-size: 14px;
    font-weight: 600;
}

/* Responsivo */
@media (max-width: 1200px) {
    .contenedor-ultimas-noticias {
        grid-template-columns: repeat(3, 1fr);
    }
}
@media (max-width: 992px) {
    .contenedor-ultimas-noticias {
        grid-template-columns: repeat(2, 1fr);
    }
}
@media (max-width: 576px) {
    .contenedor-ultimas-noticias {
        grid-template-columns: 1fr;
    }
    .titulo-ultimas-noticias {
        text-align: center;
    }
}
.noticia-principal {
    border: 3px solid;
    border-radius: 10px;
    padding: 15px;
    background-color: #fff;
    transition: box-shadow 0.3s ease;
}

.noticia-principal:hover {
    box-shadow: 0 0 15px rgba(0, 123, 225, 0.4);
}

.btn {
    display:flex;
    gap: 20px;
    justify-content: center;
    align-items: center;
    padding: 0 24px;
    height: 40px;
    border-radius: 25px;
    font-size: 16px;
    font-weight: 700;
    font-family: 'Poppins', sans-serif;
    text-decoration: none;
    color: #fff;
    background-color: #4C00DA;
    border: none;
    cursor: pointer;
    transition: all 0.3 ease;
    white-space: nowrap;
    min-width: auto;
    width: 150px; 
}

.btn:hover {
    background:#3B00AD;
}
    </style>
</head>
<body>
    <!-- Incluir menú -->


    <!-- CONTENIDO PRINCIPAL -->
    <div class="container-fluid">
        <div id="contenedor-noticias" class="contenedor-noticias">
            <!-- NOTICIA DESTACADA -->
            <?php if ($noticia_destacada): ?>
            <div class="tarjeta-destacada">
                <!-- Carrusel de imágenes -->
                <?php 
                $imagenes = [];
                // Verificar y agregar solo las imágenes que existen
                if (!empty($noticia_destacada['imagen']) && imagenExiste($noticia_destacada['imagen'])) {
                    $imagenes[] = $noticia_destacada['imagen'];
                }
                if (!empty($noticia_destacada['imagen2']) && imagenExiste($noticia_destacada['imagen2'])) {
                    $imagenes[] = $noticia_destacada['imagen2'];
                }
                if (!empty($noticia_destacada['imagen3']) && imagenExiste($noticia_destacada['imagen3'])) {
                    $imagenes[] = $noticia_destacada['imagen3'];
                }
                ?>
                
                <div class="carrusel-destacado">
                    <div id="carouselDestacado" class="carousel slide" data-bs-ride="carousel" data-bs-interval="5000">
                        <?php if (count($imagenes) > 1): ?>
                        <div class="carousel-indicators">
                            <?php foreach ($imagenes as $index => $imagen): ?>
                            <button type="button" data-bs-target="#carouselDestacado" data-bs-slide-to="<?= $index ?>" 
                                    class="<?= $index === 0 ? 'active' : '' ?>" aria-current="<?= $index === 0 ? 'true' : 'false' ?>" 
                                    aria-label="Slide <?= $index + 1 ?>"></button>
                            <?php endforeach; ?>
                        </div>
                        <?php endif; ?>
                        
                        <div class="carousel-inner">
                            <?php if (count($imagenes) > 0): ?>
                                <?php foreach ($imagenes as $index => $imagen): ?>
                                <div class="carousel-item <?= $index === 0 ? 'active' : '' ?>">
                                    <a href="detalle_noticia.php?id=<?= $noticia_destacada['id'] ?>">
                                        <img src="imagenes/noticias/<?= $imagen ?>" 
                                             class="d-block w-100" 
                                             alt="<?= htmlspecialchars($noticia_destacada['titulo']) ?>"
                                             onerror="this.style.display='none'; this.parentNode.innerHTML='<div class=\'imagen-placeholder w-100 h-100\'>Imagen no disponible</div>';">
                                    </a>
                                </div>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <!-- Si no hay imágenes, mostrar placeholder -->
                                <div class="carousel-item active">
                                    <div class="imagen-placeholder w-100 h-100">
                                        <div>
                                            <i class="fas fa-image" style="font-size: 48px; margin-bottom: 15px;"></i><br>
                                            No hay imágenes para esta noticia
                                        </div>
                                    </div>
                                </div>
                            <?php endif; ?>
                        </div>
                        
                        <?php if (count($imagenes) > 1): ?>
                        <button class="carousel-control-prev" type="button" data-bs-target="#carouselDestacado" data-bs-slide="prev">
                            <span class="carousel-control-prev-icon" aria-hidden="true"></span>
                            <span class="visually-hidden">Anterior</span>
                        </button>
                        <button class="carousel-control-next" type="button" data-bs-target="#carouselDestacado" data-bs-slide="next">
                            <span class="carousel-control-next-icon" aria-hidden="true"></span>
                            <span class="visually-hidden">Siguiente</span>
                        </button>
                        <?php endif; ?>
                    </div>
                </div>
                
                <!-- Encabezado con información -->
                <div class="encabezado-tarjeta">
                    <div class="elementos-encabezado">
                        <span class="categoria"><?= htmlspecialchars($noticia_destacada['categoria']) ?></span>
                        <span class="separador">|</span>
                        <span class="autor"><?= htmlspecialchars($noticia_destacada['autor']) ?></span>
                        <span class="separador">|</span>
                        <span class="fecha"><?= date('d/m/Y', strtotime($noticia_destacada['fecha'])) ?></span>
                        <span class="separador">|</span>
                        <span class="hora"><?= date('H:i', strtotime($noticia_destacada['fecha'])) ?></span>
                    </div>
                </div>
                
                <!-- Título -->
                <div class="titulo-destacado">
                    <a href="detalle_noticia.php?id=<?= $noticia_destacada['id'] ?>">
                        <?= htmlspecialchars($noticia_destacada['titulo']) ?>
                    </a>
                </div>
                
                <!-- Resumen -->
                <div class="resumen-destacado">
                    <?= nl2br(htmlspecialchars($noticia_destacada['descripcion'])) ?>
                </div>
            </div>
            <?php endif; ?>

            <!-- SESIÓN ÚLTIMAS NOTICIAS -->
            <section class="seccion-ultimas-noticias" style="bordder: 3px solid #007BFF; border-radius: 10px; padding: 15px; background-color: #fff;">
                <h2 class="titulo-ultimas-noticias">Últimas noticias</h2>

                <div class="contenedor-ultimas-noticias">
                    <?php while ($noticia = $result_ultimas->fetch_assoc()):
                        $imagenes = obtenerImagenNoticia($noticia);
                    ?>
                    <div class="tarjeta-ultima-noticia">
                        <!-- Carrusel de imágenes -->
                        <div id="carouselUltimas<?= $noticia['id'] ?>" class="carousel slide carrusel-ultimas" data-bs-ride="carousel" data-bs-interval="5000">
                            <div class="carousel-inner">
                                <?php if (!empty($imagenes)): ?>
                                    <?php foreach ($imagenes as $index => $imagen): ?>
                                    <div class="carousel-item <?= $index === 0 ? 'active' : '' ?>">
                                        <a href="detalle_noticia.php?id=<?= $noticia['id'] ?>">
                                            <img src="imagenes/noticias/<?= $imagen ?>" 
                                                 alt="<?= htmlspecialchars($noticia['titulo']) ?>" 
                                                 onerror="this.style.display='none'; this.parentNode.innerHTML='<div class=\'imagen-placeholder w-100 h-100\'>Sin imagen</div>';">
                                        </a>
                                    </div>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <div class="carousel-item active">
                                        <div class="imagen-placeholder w-100 h-100">Sin imagen</div>
                                    </div>
                                <?php endif; ?>
                            </div>

                            <?php if (count($imagenes) > 1): ?>
                                <button class="carousel-control-prev" type="button" data-bs-target="#carouselUltimas<?= $noticia['id'] ?>" data-bs-slide="prev">
                                    <span class="carousel-control-prev-icon" aria-hidden="true"></span>
                                    <span class="visually-hidden">Anterior</span>
                                </button>
                                <button class="carousel-control-next" type="button" data-bs-target="#carouselUltimas<?= $noticia['id'] ?>" data-bs-slide="next">
                                    <span class="carousel-control-next-icon" aria-hidden="true"></span>
                                    <span class="visually-hidden">Siguiente</span>
                                </button>
                            <?php endif; ?>
                        </div>

                        <!-- Contenido -->
                        <div class="contenido-ultima-noticia">
                            <h3 class="titulo-ultima-noticia">
                                <a href="detalle_noticia.php?id=<?= $noticia['id'] ?>">
                                    <?= htmlspecialchars($noticia['titulo']) ?>
                                </a>
                            </h3>
                            <div class="info-ultima-noticia">
                                <span><?= date('d/m/Y', strtotime($noticia['fecha'])) ?></span>
                                <span class="separador-info">|</span>
                                <span><?= !empty($noticia['autor']) ? htmlspecialchars($noticia['autor']) : 'Desconocido' ?></span>
                            </div>
                        </div>
                    </div>
                    <?php endwhile; ?>
                </div>
            </section>
        </div>
    </div>

    <!-- Font Awesome para los iconos -->
    <script src="https://kit.fontawesome.com/3d3e3e3d3e.js" crossorigin="anonymous"></script>

    <?php include 'footer.php'; ?>
</body>
</html>