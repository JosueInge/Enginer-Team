<?php 
$categoria_actual = 'denuncias_anonimas';
include 'conexion.php';
include 'chatbot.php';
include 'menu.php';

/* Funcion para obtener denunicas */
function obtenerDenuncias($conexion) {
  $sql = "
    SELECT
      pd.id,
      pd.titulo,
      pd.fecha AS fecha_publicacion,
      pd.imagen,
      pd.imagen2,
      pd.imagen3,
      COALESCE(u.nombre, 'Anonimo') AS autor
    FROM propuestas_denuncias pd
    LEFT JOIN usuarios u ON pd.usuario_id = u.id
    WHERE pd.estado = 'aprobada'
    ORDER BY pd.fecha DESC
    LIMIT 8
  ";

  return $conexion->query($sql);
}

//Funcion para obtener imagenes de una denuncia 
function obtenerImagenes($denuncia) {
  $imagenes = [];
  foreach (['imagen', 'imagen2', 'imagen3'] as $campo) {
    if (!empty($denuncia[$campo]) && file_exists("imagenes/denuncias/" . $denuncia[$campo])) {
      $imagenes[] = $denuncia[$campo];
    }
  }
  return $imagenes;
}

$result_denuncias = obtenerDenuncias($conexion);
?>

<script src="buscador.js" defer></script>
<!DOCTYPE html>
<html lang="es">
  <head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Denuncias - Comunicado Digital</title>
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
        line-height: 1.6;
      }

      /* Seccion denuncias */
      .seccion-ultimas-denuncias {
        max-width: 1300px;
        margin: 60px auto;
        padding: 0 20px;
      }
      .titulo-ultimas-denuncias {
        font-family: 'Poppins', sans-serif;
        font-size: 24px;
        font-weight: 700;
        color: #403F48;
        text-align: left;
        margin-bottom: 30px;
      }
      .contenedor-ultimas-denuncias {
        display: grid;
        grid-template-columns: repeat(4, 1fr);
        gap: 20px;
        justify-content: center;
      }

      /* Tarjetas */
      .tarjeta-ultima-denuncia {
        background: #fff;
        border-radius: 16px;
        overflow: hidden;
        box-shadow: 0 4px 16px rgba(0,0,0,0.08);
        transition: all 0.3s ease;
        max-width: 320px;
        margin: 0 auto;
      }
      .tarjeta-ultima-denuncia:hover {
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
      .tarjeta-ultima-denuncia:hover .carousel-control-prev,
      .tarjeta-ultima-denuncia:hover .carousel-control-next {
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
      .contenido-ultima-denuncia {
        padding: 18px 15px 25px 15px;
      }
      .titulo-ultima-denuncia {
        font-family: 'Poppins', sans-serif;
        font-size: 20px;
        font-weight: 700;
        color: #1661AC;
        text-align: left;
        margin-bottom: 12px;
      }
      .titulo-ultima-denuncia a {
        text-decoration: none;
        color: inherit;
        transition: color 0.3s ease;
      }
      .titulo-ultima-denuncia a:hover {
        color: #2D8EFF;
        text-decoration: underline;
        text-decoration-color: #2D8EFF;
      }
      .info-ultima-denuncia {
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
        .contenedor-ultimas-denuncias {
          grid-template-columns: repeat(3, 1fr);
        }
      }
      @media (max-width: 992px) {
        .contenedor-ultimas-denuncias {
          grid-template-columns: 1fr;
        }.titulo-ultimas-denuncias {
          text-align: center;
        }
      }

      .denuncia-principal {
        border: 3px solid;
        border-radius: 10px;
        padding: 15px;
        background-color: #fff;
        transition: box-shadow 0.3s ease;
      }
      .noticia-principal:hover {
        box-shadow: 0 0 15px rgba(0, 125, 255, 0.4);
      }

      .btn {
        display: flex;
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
        background: #3B00AD;
      }

      /* Boton publicar denuncia */
      .boton-publicar { 
      position: fixed;
      bottom: 30px;
      left: 30px;
      background-color: #0D5C9B;
      color: white;
      border: none;
      padding: 15px 25px;
      border-radius: 50px;
      font-weight: 50px;
      cursor: pointer;
      box-shadow: 0 4px 8px rgba(0,0,0,0.2);
      z-index: 1000;
      text-decoration: none;
    }


  </style>
</head>
<body>

  <div class="container-fluid">
    <section class="seccion-ultimas-denuncias" style="border: 3px solid #e5e8ebff; border-radius: 10px; padding: 15px; background-color: #fff;">
      <h2 class="titulo-ultimas-denuncias">Denuncias ciudadanas</h2>

      <div class="contenedor-ultimas-denuncias">
        <?php while ($denuncia = $result_denuncias->fetch_assoc()):
          $imagenes = obtenerImagenes($denuncia);
      ?>
      <div class="tarjeta-ultima-denuncia">
        <!-- Carrusel de imagenes -->
         <div id="carouselUltimas<?= $denuncia['id'] ?>" class="carousel slide carrusel-ultimas" data-bs-ride="carousel" data-bs-interval="5000">
          <div class="carousel-inner">
            <?php if (!empty($imagenes)): ?>
              <?php foreach ($imagenes as $index => $imagen): ?>
              <div class="carousel-item <?= $index === 0 ? 'active' : '' ?>">
                <a href="ver_denuncia.php?id=<?= $denuncia['id'] ?>">
                  <img src="imagenes/denuncias/<?= $imagen ?>" alt="<?= htmlspecialchars($denuncia['titulo']) ?>">
                </a>
              </div>
              <?php endforeach; ?>
            <?php else: ?>
              <div class="carousel-item active">
                <div class="imagen-placeholder">Sin imagen</div>
            </div>
          <?php endif; ?>
      </div>

      <?php if (count($imagenes) > 1): ?>
        <button class="carousel-control-prev" type="button" data-bs-target="#carouselUltimas<?= $denuncia['id'] ?>" data-bs-slide="prev">
          <span class="carousel-control-prev-icon" aria-hidden="true"></span>
          <span class="visually-hidden">Anterior</span>
      </button>
        <button class="carousel-control-next" type="button" data-bs-target="#carouselUltimas<?= $denuncia['id'] ?>" data-bs-slide="next">
          <span class="carousel-control-next-icon" aria-hidden="true"></span>
          <span class="visually-hidden">Siguiente</span>
        </button>
      <?php endif; ?>
    </div>

          <!-- Contenido -->
          <div class="contenido-ultima-denuncia">
            <h3 class="titulo-ultima-denuncia">
              <a href="ver_denuncia.php?id=<?= $denuncia['id'] ?>">
                <?= htmlspecialchars($denuncia['titulo']) ?>
              </a>
            </h3>
            <div class="info-ultima-denuncia">
              <span><?= date('d/m/Y', strtotime($denuncia['fecha_publicacion'])) ?></span>
              <span>|</span>
              <span><?= !empty($denuncia['autor']) ? htmlspecialchars($denuncia['autor']) : 'Anónimo' ?></span>
            </div>
          </div>
        </div>
      <?php endwhile; ?>
    </div>
</section>
</div>

<a href="enviar_denuncia_anonima.php" class="boton-publicar">Enviar denuncia</a>

<?php if (isset($_SESSION['usuario_rol'])): ?>
  <?php if ($_SESSION['usuario_rol'] === 'Administrador'): ?>
    <a href="publicar_denuncia.php" class="boton-publicar">Publicar Denuncia</a>
  <?php elseif ($_SESSION['usuario_rol'] === 'Poblador'): ?>
    <a href="enviar_denuncia.php" class="boton-publicar">Enviar una denuncia</a>
  <?php endif; ?>
<?php endif; ?>

<script src="https://kit.fontawesome.com/3d3e3e3d3e.js" crossorigin="anonymous"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

<?php include 'footer.php'; ?>
</body>
</html>