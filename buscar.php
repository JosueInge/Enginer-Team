<?php
session_start();
include 'conexion.php';

$categoria = $_GET['categoria'] ?? 'inicio';
$term = trim($_GET['term'] ?? '');
$like = '%' . $term . '%';

// Se Ejecuta la consulta según la categoría
switch ($categoria) {
  case 'denuncias':
    // Solo buscar en denuncias aprobadas
    $sql = "SELECT id, titulo, imagen, imagen2, imagen3, fecha, '' AS autor, 'denuncias' AS origen
            FROM propuestas_denuncias
            WHERE estado = 'aprobada' AND titulo LIKE ?
            ORDER BY fecha DESC";
    $stmt = $conexion->prepare($sql);
    $stmt->bind_param('s', $like);
    break;

  case 'inicio':
    // Busca en ambas tablas (noticias y denuncias)
    $sql = "
      SELECT id, categoria, titulo, imagen, imagen2, imagen3, fecha, autor, 'noticias' AS origen
      FROM noticias
      WHERE titulo LIKE ?
      UNION ALL
      SELECT id, NULL AS categoria, titulo, imagen, imagen2, imagen3, fecha, '' AS autor, 'denuncias' AS origen
      FROM propuestas_denuncias
      WHERE estado = 'aprobada' AND titulo LIKE ?
      ORDER BY fecha DESC";
    $stmt = $conexion->prepare($sql);
    $stmt->bind_param('ss', $like, $like);
    break;

  default:
    // Busca por categoría en noticias
    $sql = "SELECT id, categoria, titulo, imagen, imagen2, imagen3, fecha, autor, 'noticias' AS origen
            FROM noticias
            WHERE categoria = ? AND titulo LIKE ?
            ORDER BY fecha DESC";
    $stmt = $conexion->prepare($sql);
    $stmt->bind_param('ss', $categoria, $like);
    break;
}

$stmt->execute();
$res = $stmt->get_result();
?>

<section class="seccion-ultimas-noticias" style="border: none; border-radius: 10px; padding: 15px; background-color: #fff;">
  <div class="contenedor-ultimas-noticias">
    <?php if ($res->num_rows === 0): ?>
      <div class="sin-resultados text-center w-100">No se encontraron coincidencias</div>
    <?php else: ?>
      <?php while ($row = $res->fetch_assoc()):
        $imagenes = [];
        if (!empty($row['imagen']))  $imagenes[] = $row['imagen'];
        if (!empty($row['imagen2'])) $imagenes[] = $row['imagen2'];
        if (!empty($row['imagen3'])) $imagenes[] = $row['imagen3'];

        $link = ($row['origen'] === 'denuncias')
          ? "ver_denuncia.php?id={$row['id']}"
          : "detalle_noticia.php?id={$row['id']}";
        ?>
        <div class="tarjeta-ultima-noticia">
          <!-- Carrusel de imágenes -->
          <div id="carouselResultado<?= $row['id'] ?>" class="carousel slide carrusel-ultimas" data-bs-ride="carousel" data-bs-interval="5000">
            <div class="carousel-inner">
              <?php if (!empty($imagenes)): ?>
                <?php foreach ($imagenes as $index => $img): ?>
                  <div class="carousel-item <?= $index === 0 ? 'active' : '' ?>">
                    <a href="<?= $link ?>">
                      <img src="imagenes/<?= $row['origen'] === 'denuncias' ? 'denuncias' : 'noticias' ?>/<?= htmlspecialchars($img) ?>"
                           alt="<?= htmlspecialchars($row['titulo']) ?>"
                           onerror="this.style.display='none'; this.parentNode.innerHTML='<div class=\'imagen-placeholder w-100 h-100\'>Sin imagen</div>'; ">
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
              <button class="carousel-control-prev" type="button" data-bs-target="#carouselResultado<?= $row['id'] ?>" data-bs-slide="prev">
                <span class="carousel-control-prev-icon" aria-hidden="true"></span>
                <span class="visually-hidden">Anterior</span>
              </button>
              <button class="carousel-control-next" type="button" data-bs-target="#carouselResultado<?= $row['id'] ?>" data-bs-slide="next">
                <span class="carousel-control-next-icon" aria-hidden="true"></span>
                <span class="visually-hidden">Siguiente</span>
              </button>
            <?php endif; ?>
          </div>

          <!-- Contenido -->
          <div class="contenido-ultima-noticia">
            <h3 class="titulo-ultima-noticia">
              <a href="<?= $link ?>"><?= htmlspecialchars($row['titulo']) ?></a>
            </h3>
            <div class="info-ultima-noticia">
              <span><?= date('d/m/Y', strtotime($row['fecha'])) ?></span>
              <span class="separador-info">|</span>
              <span><?= !empty($row['autor']) ? htmlspecialchars($row['autor']) : 'Desconocido' ?></span>
            </div>
          </div>
        </div>
      <?php endwhile; ?>
    <?php endif; ?>
  </div>
</section>

<style>
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

  /*TARJETAS*/
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

  /*CARRUSEL*/
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

  /*CONTENIDO*/
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

  /*PLACEHOLDER*/
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

  /*RESPONSIVO*/
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
</style>