<?php
session_start();
include 'conexion.php';

$categoria = $_GET['categoria']  ?? 'inicio';
$term      = trim($_GET['term'] ?? '');

$like = '%' . $term . '%';

switch ($categoria) {

  case 'denuncias':
    $sql = "SELECT id, ''  AS categoria, titulo, descripcion, imagen, fecha, '' AS autor
            FROM propuestas_denuncias
            WHERE estado = 'aprobada'
              AND titulo LIKE ?
            ORDER BY fecha DESC";
    $stmt = $conexion->prepare($sql);
    $stmt->bind_param('s', $like);
    break;

  case 'inicio':
      $sql  = "SELECT id, categoria, titulo, descripcion, imagen, fecha, autor
               FROM propuestas_noticias
               WHERE estado='aprobada' AND titulo LIKE ?
               ORDER BY fecha DESC";
      $stmt = $conexion->prepare($sql);
      $stmt->bind_param('s', $like);
      break;

  default:           /* para las demas categorias, clima, deportes, etc. */
      $sql  = "SELECT id, categoria, titulo, descripcion, imagen, fecha, autor
               FROM propuestas_noticias
               WHERE estado='aprobada'
                 AND categoria = ?
                 AND titulo LIKE ?
               ORDER BY fecha DESC";
      $stmt = $conexion->prepare($sql);
      $stmt->bind_param('ss', $categoria, $like);
      break;
}

$stmt->execute();
$res = $stmt->get_result();

ob_start();
while ($row = $res->fetch_assoc()) {
  ?>
  <article class="noticia-card">
    <a href="<?= $categoria==='denuncias' ? 'ver_denuncia.php?id=' : 'ver_noticia.php?id=' ?><?= $row['id'] ?>">
      <h2 class="noticia-titulo"><?= htmlspecialchars($row['titulo']) ?></h2>
    </a>

    <div class="noticia-meta">
      <span><?= htmlspecialchars($row['categoria']) ?></span>
      <span><?= htmlspecialchars($row['autor']) ?></span>
      <span><?= htmlspecialchars($row['fecha']) ?></span>
    </div>

    <?php if (!empty($row['imagen'])): ?>
      <div class="imagen-contenedor">
        <img class="noticia-imagen"
             src="<?= $categoria==='denuncias'
                      ? 'imagenes/denuncias/'
                      : 'imagenes/noticias/' ?><?= htmlspecialchars($row['imagen']) ?>"
             alt="<?= htmlspecialchars($row['titulo']) ?>">
      </div>
    <?php endif; ?>

    <p class="noticia-resumen"><?= nl2br(htmlspecialchars($row['descripcion'])) ?></p>
  </article>
  <?php
}
$salida = ob_get_clean();

if ($salida === '') {
  echo '<div class="sin-resultados">🔍 No se encontraron resultados para tu búsqueda</div>';
  ?>
  <style>
    .sin-resultados {
      background-color: #e9ecef;
      color: #495057;
      border: 1px solid #ced4da;
      padding: 20px;
      margin: 20px;
      border-radius: 8px;
      font-size: 16px;
      text-align: center;
      font-weight: bold;
      box-shadow: 0 2px 6px rgba(0,0,0,0.1);
    }
  </style>
  <?php
} else {
  echo $salida;
}
