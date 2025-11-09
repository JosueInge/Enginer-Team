<?php
$categoria_actual = 'Turismo';
include 'conexion.php';

// Configuracion de paginacion
$noticias_por_pagina = 20;
$pagina_actual = isset($_GET['pagina']) ? (int)$_GET['pagina'] : 1;
$offset = ($pagina_actual - 1) * $noticias_por_pagina;

// Obtener el total de noticias combinando noticias y propuestas noticias 
$sql_total = "SELECT COUNT(*) as total FROM (
              SELECT id FROM noticias
              WHERE categoria = 'turismo' AND fecha <= CURDATE()
              UNION ALL
              SELECT id FROM propuestas_noticias
              WHERE categoria = 'turismo' AND estado = 'aprobada' AND fecha <= CURDATE()
          ) as combined";
$result_total = $conexion->query($sql_total);
$total_noticias = $result_total->fetch_assoc()['total'];
$total_paginas = ceil($total_noticias / $noticias_por_pagina);

// Obtener noticias de la categoria turismo combinando noticias y propuestas noticias 
$sql_noticias = "SELECT id, titulo, descripcion, imagen, imagen2, imagen3, autor, fecha, categoria, 'noticia' as fuente
                FROM noticias
                WHERE categoria = 'turismo' AND fecha <= CURDATE()

                UNION ALL 

                SELECT id, titulo, descripcion, imagen, imagen2, imagen3, autor, fecha, categoria, 'propuestas' as fuente
                FROM propuestas_noticias
                WHERE categoria = 'turismo' AND estado = 'aprobada' AND fecha <= CURDATE()

                ORDER BY fecha DESC, id DESC
                LIMIT $noticias_por_pagina OFFSET $offset";
$result_noticias = $conexion->query($sql_noticias);

// Funcion para verificar si una imagen existe
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

// Funcion para generar enlace correcto segun la fuente
function generarEnlaceNoticia($noticia) {
  // Verificar si existe el campo 'fuente'
  if (isset($noticia['fuente']) && $noticia['fuente'] === 'propuestas') {
    return "ver_noticia.php?id=" . $noticia['id'];
  } else {
    return "ver_noticia.php?id=" . $noticia['id'];
  }
}

// Si es una peticion AJAX, devolver solo el HTML de las nuevas noticias 
if (isset($_GET['ajax']) && $_GET['ajax'] == 'true') {
  while ($noticia = $result_noticias->fetch_assoc()):
    $imagenes = obtenerImagenNoticia($noticia);
    ?>
<div class="tarjeta-noticia">
  <!-- Carrusel de imagenes -->
  <div id="carouselNoticia<?= $noticia['id'] ?>" class="carousel slide carrusel-noticia" data-bs-ride="carousel" data-bs-interval="5000">
    <div class="carousel-inner">
      <?php if (!empty($imagenes)): ?>
        <?php foreach ($imagenes as $index => $imagen): ?>
          <div class="carousel-item <?= $index === 0 ? 'active' : '' ?>">
            <a href="ver_noticia.php?id=<?= $noticia['id'] ?>">
              <img src="imagenes/noticias/<?= $imagen ?>"
              alt="<?= htmlspecialchars($noticia['titulo']) ?>"
              onerror="this.style.display='none'; this.parentNode.innerHTML='<div class=\'imagen-placeholder w-100 h-100\'>Sin imagen</div>';">
        </a>
        </div>
        <?php endforeach; ?>
      <?php else: ?>
          <div class="carousel-item active">
            <div class="imagen-placeholder w-100 h-100 d-flex align-items-center justify-content-center">
              Sin imagen
            </div>
          </div>
        <?php endif; ?>
      </div>

        <?php if (count($imagenes) > 1): ?>
          <button class="carousel-control-prev" type="button" data-bs-target="#carouselNoticia<?= $noticia['id'] ?>" data-bs-slide="prev">
            <span class="carousel-control-prev-icon" aria-hidden="true"></span>
            <span class="visually-hidden">Anterior</span>
        </button>
        <button class="carousel-control-next" type="button" data-bs-target="#carouselNoticia<?= $noticia['id'] ?>" data-bs-slide="next">
            <span class="carousel-control-next-icon" aria-hidden="true"></span>
            <span class="visually-hidden">Siguiente</span>
        </button>

        <div class="carousel-indicators">
          <?php foreach ($imagenes as $index => $imagen): ?>
            <button type="button" data-bs-target="#carouselNoticia<?= $noticia['id'] ?>"
                data-bs-slide-to="<?= $index ?>"
                class="<?= $index === 0 ? 'active' : '' ?>"
                aria-label="Slide <?= $index + 1 ?>"></button>
            <?php endforeach; ?>
          </div>
          <?php endif; ?>
          </div>

          <!-- Contenido -->
           <div class="contenido-noticia">
            <h3 class="titulo-noticia">
              <a href="ver_noticia.php?id=<?= $noticia['id'] ?>">
                <?= htmlspecialchars($noticia['titulo']) ?>
              </a>
            </h3>
          <div class="info-noticia">
            <span><?= date('d/m/Y', strtotime($noticia['fecha'])) ?></span>
            <span class="separador-info">|</span>
            <span><?= !empty($noticia['autor']) ? htmlspecialchars($noticia['autor']) : 'Desconocido' ?></span>
          </div>
        </div>
      </div>
    <?php
      endwhile;
      exit;
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8" /> 
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Deporte - Comunicado Digital</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600;700&family=Inter&family=Open+Sans:wght@400;700&display=swap" rel="stylesheet">
  <style>
    :root {
      --color-primario: #1661AC;
      --color-secundario: #2D8EFF;
      --color-texto: #000000;
      --color-texto-secundario: #74737C;
      --color-fondo: #f8f9f9;
    }
    body {
      font-family: 'Inter', sans-serif;
      background-color: var(--color-fondo);
    }
    /* Seccion encabezado categoria */
    .encabezado-categoria {
      max-width: 1300px;
      margin: 30px auto 40px auto;
      padding: 0 20px;
      display: flex;
      align-items: center;
      gap: 20px;
    }
    .titulo-categoria {
      font-family: 'Poppins', sans-serif;
      font-size: 24px;
      font-weight: 700;
      color: #403F48;
      margin: 0;
      white-space: nowrap;
    }
    .linea-divisora {
      flex: 1;
      height: 2px;
      background-color: #403F48;
      max-width: 975px;
    }

    /* Contenedor principal */
    .contenedor-principal {
      max-width: 1300px;
      margin: 0 auto;
      padding: 0 20px;
      display: grid;
      grid-template-columns: 1fr 200px;
      gap: 40px;
      align-items: start;
    }

    /* Seccion noticias */
    .seccion-noticias {
      width: 100%;
    }
    .contenedor-noticias {
      display: grid;
      grid-template-columns: repeat(4, 1fr);
      gap: 25px;
      margin-bottom: 50px;
    }

    /* Tarjetas de noticias */
    .tarjeta-noticia {
      background: #fff;
      border-radius: 16px;
      overflow: hidden;
      box-shadow: 0 4px 16px rgba(0,0,0,0.08);
      transition: all 0.3s ease;
      max-width: 320px;
      margin: 0 auto;
      height: 100%;
    }
    .tarjeta-noticia:hover {
      transform: translateY(-6px);
      box-shadow: 0 10px 30px rgba(0,0,0,0.15);
    }

    /* Carrusel interno */
    .carrusel-noticia {
      height: 220px;
      border-radius: 12px;
      overflow: hidden;
      position: relative;
    }

    .carrusel-noticia img {
      width: 100%;
      height: 100%;
      object-fit: cover;
      border-radius: 12px;
    }

    .carrusel-noticia .carousel-control-prev,
    .carrusel-noticia .carousel-control-next {
      width: 35px;
      height: 35px;
      background: rgba(0,0,0,0.4);
      border-radius: 50%;
      top: 50%;
      transform: translateY(-50%);
      opacity: 0;
      transition: opacity 0.3s ease;
    }

    .tarjeta-noticia:hover .carousel-control-prev,
    .tarjeta-noticia:hover .carousel-control-next {
      opacity: 1;
    }
    .carrusel-noticia .carousel-indicators {
      bottom: 8px;
    }
    .carrusel-noticia .carousel-indicators button {
      width: 8px;
      height: 8px;
      border-radius: 50%;
    }

    /* Contenido tarjeta */
    .contenido-noticia {
      padding: 18px 15px 25px 15px;
    }
    .titulo-noticia {
      font-family: 'Poppins', sans-serif;
      font-size: 20px;
      font-weight: 700;
      color: #1661AC;
      text-align: left;
      margin-bottom: 12px;
      line-height: 1.3;
      display: -webkit-box;
      -webkit-line-clamp: 2;
      -webkit-box-orient: vertical;
      overflow: hidden;
    }
    .titulo-noticia a {
      text-decoration: none;
      color: inherit;
      transition: color 0.3s ease;
    }
    .titulo-noticia a:hover {
      color: #2D8EFF;
      text-decoration: underline;
      text-decoration-color: #2D8EFF;
    }
    .info-noticia {
      font-family: 'Poppins', sans-serif;
      font-size: 16px;
      color: #74737C;
      display: flex;
      justify-content: flex-start;
      align-items: center;
      gap: 8px;
    }
    .separador-info {
      color: #74737C;
    }

    /* Boton ver mas */
    .contenedor-boton {
      display: flex;
      justify-content: center;
      margin: 40px 0 60px 0;
    }
    .btn-ver-mas {
      width: 170px;
      height: 50px;
      background: #2D8EFF;
      border: none;
      border-radius: 8px;
      font-family: 'Open Sans', sans-serif;
      font-size: 20px;
      font-weight: 700;
      color: #fff;
      cursor: pointer;
      transition: all 0.3s ease;
      text-decoration: none;
      display: inline-flex;
      align-items: center;
      justify-content: center;
    }
    .btn-ver-mas:hover {
      background: #1a75e0;
      transform: translateY(-2px);
      box-shadow: 0 4px 12px rgba(45, 142, 255, 0.3);
      color: #fff;
      text-decoration: none;
    }

    .btn-ver-mas.cargando {
      opacity: 0.7;
      cursor: not-allowed;
    }

    /* Contenedor paginacion */
    .contenedor-paginacion {
      display: flex;
      justify-content: center;
      align-items: center;
      gap: 15px;
      margin: 40px 0 60px 0;
      flex-wrap: wrap;
    }

    .info-paginacion {
      font-family: 'Poppins', sans-serif;
      font-size: 16px;
      color: #74737C;
      margin: 0 10px;
    }

    .btn-paginacion {
      padding: 8px 16px;
      background: #fff;
      border: 2px solid #2D8EFF;
      border-radius: 6px;
      font-family: 'Open Sans', sans-serif;
      font-size: 16px;
      font-weight: 600;
      color: #2D8EFF;
      cursor: pointer;
      transition: all 0.3s ease;
      text-decoration: none;
    }

    /* Anuncio lateral */
    .anuncio-lateral {
      position: sticky;
      top: 20px;
      max-width: 200px;
      height: 725px;
      background: #fff;
      border-radius: 12px;
      overflow: hidden;
      box-shadow: 0 4px 16px rgba(0,0,0,0.08);
      transition: all 0.3s ease;
    }
    .anuncio-lateral:hover {
      transform: translateY(-3px);
      box-shadow: 0 6px 20px rgba(0,0,0,0.12);
    }
    .anuncio-lateral a {
      display: block;
      width: 100%;
      height: 100%;
    }

    .anuncio-lateral img {
      width: 100%;
      height: 100%;
      object-fit: cover;
    }
    .placeholder-anuncio {
      width: 100%;
      height: 100%;
      background: linear-gradient(45deg, #1661AC, #2D8EFF);
      display: flex;
      flex-direction: column;
      align-items: center;
      color: white;
      font-family: 'Poppins', sans-serif;
      font-size: 16px;
      font-weight: 600;
      text-align: center;
      padding: 20px;
    }

    /* Placeholder para imagenes */
    .imagen-placeholder {
      background: linear-gradient(45deg, #1661AC, #2D8EFF);
      display: flex;
      align-items: center;
      justify-content: center;
      color: white;
      font-family: 'Poppins', sans-serif;
      font-size: 14px;
      font-weight: 600;
      text-align: center;
    }

    /* Responsivo */
    @media (max-width: 1200px) {
      .contenedor-noticias {
        grid-template-columns: repeat(3, 1fr);
        justify-items: start;
      }

      .contenedor-principal {
        grid-template-columns: 1fr;
        gap: 30px;
      }
      .anuncio-lateral {
        max-width: 100%;
        height: 200px;
        position: static;
      }
      .linea-divisora {
        max-width: 600px;
      }
    }

    @media (max-width: 992px) {
      .contenedor-noticias {
        grid-template-columns: repeat(2, 1fr);
      }
      .encabezado-categoria {
        flex-direction: column;
        align-items: flex-start;
        gap: 15px;
      }
      .linea-divisora {
        width: 100%;
        max-width: none;
      }
    }

    @media (max-width: 576px) {
      .contenedor-noticias {
        grid-template-columns: 1fr;
        justify-items: start;
      }
      .encabezado-categoria {
        padding: 0 15px;
      }
      .titulo-categoria {
        font-size: 20px;
      }
    }
    
    .btn {
    display: inline-flex;
    justify-content: center;
    align-items: center;
    padding: 0 24px;
    height: 45px;
    border-radius: 25px;
    font-size: 16px;
    font-weight: 700;
    font-family: 'Poppins', sans-serif;
    text-decoration: none;
    color: #fff;
    background-color: #4C00DA;
    border: none;
    cursor: pointer;
    transition: all 0.3s ease;
    white-space: nowrap;
    min-width: auto;
    width: auto; 
}
  </style>
</head>
<body>

<?php include 'menu.php'; ?>

<div class="container-fluid">
  <!-- Encabezado categoria -->
   <div class="encabezado-categoria">
      <h1 class="titulo-categoria">Todo sobre el Turismo</h1>
      <div class="linea-divisora"></div>
</div>

<!-- Contenedor principal -->
 <div class="contenedor-principal">
  <!-- Seccion noticias -->
   <div class="seccion-noticias">
    <div class="contenedor-noticias" id="contenedor-noticias">
      <?php while ($noticia = $result_noticias->fetch_assoc()):
        $imagenes = obtenerImagenNoticia($noticia);
?>
<div class="tarjeta-noticia">
  <!-- Carrusel de imagenes -->
   <div id="carouselNoticia<?= $noticia['id'] ?>" class="carousel slide carrusel-noticia" data-bs-ride="carousel" data-bs-interval="5000">
    <div class="carousel-inner">
      <?php if (!empty($imagenes)): ?>
        <?php foreach ($imagenes as $index => $imagen): ?>
        <div class="carousel-item <?= $index === 0 ? 'active' : '' ?>">
            <a href="<?= generarEnlaceNoticia($noticia) ?>">
            <img src="imagenes/noticias/<?= $imagen ?>"
              alt="<?= htmlspecialchars($noticia['titulo']) ?>"
              onerror="this.style.display='none'; this.parentNode.innerHTML='<div class=\'imagen-placeholder w-100 h-100\'>Sin imagen</div>';">
        </a>
        </div>
        <?php endforeach; ?>
      <?php else: ?>
        <div class="carousel-item active">
          <div class="imagen-placeholder w-100 h-100 d-flex align-items-center justify-content-center">
            Sin imagen
      </div>
      </div>
      <?php endif; ?>
      </div>

      <?php if (count($imagenes) > 1): ?>
        <button class="carousel-control-prev" type="button" data-bs-target="#carouselNoticia<?= $noticia['id'] ?>" data-bs-slide="prev">
          <span class="carousel-control-prev-icon" aria-hidden="true"></span>
          <span class="visually-hidden">Anterior</span>
      </button>
      <button class="carousel-control-next" type="button" data-bs-target="#carouselNoticia<?= $noticia['id'] ?>" data-bs-slide="next">
          <span class="carousel-control-next-icon" aria-hidden="true"></span>
          <span class="visually-hidden">Siguiente</span>
      </button>

      <div class="carousel-indicators">
        <?php foreach ($imagenes as $index => $imagen): ?>
          <button type="button" data-bs-target="#carouselNoticia<?= $noticia['id'] ?>"
                  data-bs-slide-to="<?= $index ?>"
                  class="<?= $index === 0 ? 'active' : '' ?>"
                  aria-label="Slide <?= $index + 1 ?>"></button>
        <?php endforeach; ?>
        </div>
      <?php endif; ?>
        </div>

        <!-- Contenido -->
         <div class="contenido-noticia">
          <h3 class="titulo-noticia">
            <a href="<?= generarEnlaceNoticia($noticia) ?>">
              <?= htmlspecialchars($noticia['titulo']) ?>
        </a>
        </h3>
        <div class="info-noticia">
          <span><?= date('d/m/Y', strtotime($noticia['fecha'])) ?></span>
          <span class="separador-info">|</span>
          <span><?= !empty($noticia['autor']) ? htmlspecialchars($noticia['autor']) : 'Desconocido' ?></span>
        </div>
        </div>
        </div>
        <?php endwhile; ?>
        </div>

        <!-- Boton ver mas -->
         <?php if ($pagina_actual < $total_paginas): ?>
         <div class="contenedor-boton">
          <button id="btn-ver-mas" class="btn-ver-mas" data-pagina="<?= $pagina_actual ?>" data-total-paginas="<?= $total_paginas ?>">
            Ver más
         </button>
        </div>
        <?php endif; ?>
        </div>

        <!-- Anuncio lateral -->
         <div class="anuncio-lateral">
          <a href="detalle_anuncio.php?id=1" target="_blanck">
            <?php
            $imagen_anuncio = 'cinemark.jpeg';
            $ruta_anuncio = 'imagenes/anuncios/' . $imagen_anuncio;
            ?>
            <?php if (file_exists($ruta_anuncio)): ?>
              <img src="<?= $ruta_anuncio ?>" alt="Anuncio publicitario">
            <?php else: ?>
            <div class="placeholder-anuncio">
              Anuncio Publicitario<br>
              <small>200x725px</small>
        </div>
        <?php endif; ?>
            </a>
            </div>
            </div>
            </div>
       
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
document.addEventListener('DOMContentLoaded', function() {
    const btnVerMas = document.getElementById('btn-ver-mas');
    const contenedorNoticias = document.getElementById('contenedor-noticias');
    
    if (btnVerMas) {
        btnVerMas.addEventListener('click', function() {
            const paginaActual = parseInt(this.getAttribute('data-pagina'));
            const siguientePagina = paginaActual + 1;
            const totalPaginas = parseInt(this.getAttribute('data-total-paginas'));
            
            // Mostrar estado de carga
            this.classList.add('cargando');
            this.innerHTML = 'Cargando...';
            this.disabled = true;
            
            // Realizar petición AJAX
            fetch(`?pagina=${siguientePagina}&ajax=true`)
                .then(response => response.text())
                .then(html => {
                    // Agregar las nuevas noticias al contenedor
                    contenedorNoticias.innerHTML += html;
                    
                    // Actualizar el estado del botón
                    this.setAttribute('data-pagina', siguientePagina);
                    
                    if (siguientePagina >= totalPaginas) {
                        // Ocultar botón si no hay más páginas
                        this.style.display = 'none';
                    } else {
                        // Restaurar botón
                        this.classList.remove('cargando');
                        this.innerHTML = 'Ver más';
                        this.disabled = false;
                    }
                    
                    // Reinicializar carruseles de Bootstrap para las nuevas noticias
                    const carruseles = contenedorNoticias.querySelectorAll('.carousel');
                    carruseles.forEach(carrusel => {
                        new bootstrap.Carousel(carrusel);
                    });
                })
                .catch(error => {
                    console.error('Error:', error);
                    // Restaurar botón en caso de error
                    this.classList.remove('cargando');
                    this.innerHTML = 'Ver más';
                    this.disabled = false;
                    alert('Error al cargar más noticias. Intenta nuevamente.');
                });
        });
    }
});
</script>

 <?php include 'footer.php'; ?>
</body>
</html>