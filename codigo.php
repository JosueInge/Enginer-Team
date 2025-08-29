<?php
  $categoria_actual = 'inicio';
  session_start();
  include 'menu.php';
  include 'conexion.php';
  
  if (!isset($_SESSION['usuario_id'])) {
    header("Location: login.php"); 
    exit();
  }
  $nombreUsuario = isset($_SESSION['usuario_nombre']) ? htmlspecialchars($_SESSION['usuario_nombre']) : null;

$termino_busqueda = ''; 
$where = '';
$params = [];

if (isset($_GET['busqueda']) && !empty($_GET['busqueda'])) {
    $termino_busqueda = trim($_GET['busqueda']);
    $where = "WHERE titulo LIKE ? OR descripcion LIKE ? OR autor LIKE ?";
    $params = array_fill(0, 3, '%' . $termino_busqueda . '%');
}

// Consulta base con posibilidad de búsqueda
$query = "SELECT * FROM propuestas_noticias WHERE estado = 'aprobada' ORDER BY fecha DESC";

$stmt = $conexion->prepare($query);

if (!empty($params)) {
    $types = str_repeat('s', count($params));
    $stmt->bind_param($types, ...$params);
}

$stmt->execute();
$resultado = $stmt->get_result();
$noticias = $resultado->fetch_all(MYSQLI_ASSOC);
$stmt->close();
$conexion->close();
?>

<script src="buscador.js" defer></script>
<?php
// Debug temporal para ver el rol (opcional, puedes quitarlo después)
if (isset($_SESSION['usuario_rol'])) {
  echo "<pre>ROL ACTUAL: " . $_SESSION['usuario_rol'] . "</pre>";
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
  <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600&display=swap" rel="stylesheet">
  <meta charset="UTF-8" /> 
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Comunicado Digital</title>
  <style>
    /* ... Tus estilos se mantienen igual ... */
  </style>
</head>
<body>
  <div class="contenido-principal">
    <div id="contenedor-noticias">

      <?php if (empty($noticias)): ?>
        <div class="sin-noticias">
          <h2>No hay noticias publicadas aún</h2>
          <p>¡Sé el primero en compartir una noticia!</p>
        </div>
      <?php else: ?>
        <?php foreach ($noticias as $noticia): ?>
          <article class="noticia-card" style="position: relative;">
            <?php if (isset($_SESSION['usuario_rol']) && $_SESSION['usuario_rol'] === 'Administrador'): ?>
            <div class="menu-admin dropdown" style="position: absolute; top: 15px; right: 15px;">
              <span style="cursor: pointer;">⋮</span>
              <div class="dropdown-content">
                <a href="editar_noticia.php?id=<?= $noticia['id'] ?>">Editar</a>
                <a href="eliminar_noticia.php?id=<?= $noticia['id'] ?>" onclick="return confirm('¿Deseas eliminar esta noticia?')">Eliminar</a>
              </div>
            </div>
          <?php endif; ?>
            <a href="ver_noticia.php?id=<?= $noticia['id'] ?>" style="text-decoration: none; color: inherit;">
              <h2 class="noticia-titulo"><?= htmlspecialchars($noticia['titulo']) ?></h2>
            </a>
            <div class="noticia-meta">
              <span><?= htmlspecialchars($noticia['categoria']) ?></span>
              <span><?= htmlspecialchars($noticia['autor']) ?></span>
              <span><?= htmlspecialchars($noticia['fecha']) ?></span>
            </div>
            <?php if ($noticia['imagen']): ?>
              <div class="imagen-contenedor">
                <img src="imagenes/noticias/<?= htmlspecialchars($noticia['imagen']) ?>" class="noticia-imagen" alt="<?= htmlspecialchars($noticia['titulo']) ?>">
              </div>
            <?php endif; ?>
            <p class="noticia-resumen"><?= nl2br(htmlspecialchars($noticia['descripcion'])) ?></p>
          </article>
        <?php endforeach; ?>
      <?php endif; ?>
    </div>
  </div>
    <?php if (isset($_SESSION['usuario_rol']) && $_SESSION['usuario_rol'] === 'Administrador'): ?>
      <a href="publicar_noticia.php" class="boton-publicar">Publicar Noticia</a>
    <?php endif; ?>

    <?php if (isset($_SESSION['usuario_rol']) && $_SESSION['usuario_rol'] === 'Poblador'): ?>
      <a href="enviar_noticia.php" class="boton-publicar">Enviar una noticia</a>
    <?php endif; ?>

    <link rel="stylesheet" href="asistente_virtual.css">
    <?php include 'chatbot.php'; ?>
    <script src="chatbot.js"></script>

    <script>
      // ... Tu JS sigue igual ...
    </script>
    <?php include "footer.php"; ?>
</body>
</html>
