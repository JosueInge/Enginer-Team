<?php
// Determina si el usuario ha iniciado sesión
$usuarioLogueado = isset($_SESSION['usuario_nombre']);
$currentPage = basename($_SERVER['PHP_SELF']); // Para resaltar el menú activo
$noticias_pendientes = 0;
if ($usuarioLogueado && isset($_SESSION['usuario_rol']) && $_SESSION['usuario_rol'] === 'Administrador') {
  include_once 'conexion.php';
  $query = "SELECT COUNT(*) AS total FROM propuestas_noticias WHERE estado = 'pendiente'";
  $result = $conexion->query($query);
  if ($result && $row = $result->fetch_assoc()) {
    $noticias_pendientes = $row['total'];
  }

  $denuncias_pendientes = 0;
  $query2 = "SELECT COUNT(*) AS total FROM propuestas_denuncias WHERE estado = 'pendiente'";
  $result2 = $conexion->query($query2);
  if ($result2 && $row2 = $result2->fetch_assoc()) {
    $denuncias_pendientes = $row2['total'];
  }
  
  $reportes_noticias = 0;
  $reportes_denuncias = 0;

  $query1 = "SELECT COUNT(DISTINCT propuestas_noticias_id) AS total FROM reportes WHERE estado = 'pendiente'";
  $result1 = $conexion->query($query1);
  if ($result1 && $row1 = $result1->fetch_assoc()) {
      $reportes_noticias = $row1['total'];
  }

  $query2 = "SELECT COUNT(DISTINCT propuestas_denuncias_id) AS total FROM reportesdenuncias WHERE estado = 'pendiente'";
  $result2 = $conexion->query($query2);
  if ($result2 && $row2 = $result2->fetch_assoc()) {
      $reportes_denuncias = $row2['total'];
  }

  $reportes_pendientes = $reportes_noticias + $reportes_denuncias;

$total_notificaciones = $noticias_pendientes + $denuncias_pendientes + $reportes_pendientes;
}
?>

<?php if (!$usuarioLogueado): ?>
<!-- Menu para usuarios no logueados -->
<header class="encabezado1">
  <div class="logo1">
    <img src="imagenes/logo.png" alt="logo">
  </div>
  <div class="redes1">
    <a target="_blank" href="https://www.instagram.com/"><img src="imagenes/instagram.png" height="35"/></a>
    <a target="_blank" href="https://www.facebook.com/"><img src="imagenes/facebook.png" height="35" style="margin-left: 12px;"/></a>
    <a target="_blank" href="https://x.com/?lang=es"><img src="imagenes/X.png" height="35" style="margin-left: 10px;"/></a>
  </div> 
  <div class="informacion1">
    <a href="#" style="margin-left: 15px; color: #ffffff;">Contacto</a>
    <a href="sobrenosotros.php" style="margin-left: 15px; color: #ffffff;">Sobre Nosotros</a>
    <a id="linkSesion" href="login.php" style="margin-left: 15px; color: #ffffff;">Iniciar Sesión</a>
  </div>
</header>

<nav class="barra1">
  <a href="home.php" class="nav-link <?= ($currentPage == 'home.php') ? 'active' : '' ?>">Inicio</a>
  <a href="clima1.php" class="nav-link <?= ($currentPage == 'clima1.php') ? 'active' : '' ?>">Clima</a>
  <a href="deporte1.php" class="nav-link <?= ($currentPage == 'deporte1.php') ? 'active' : '' ?>">Deportes</a>
  <a href="educacion1.php" class="nav-link <?= ($currentPage == 'educacion1.php') ? 'active' : '' ?>">Educación</a>
  <a href="turismo1.php" class="nav-link <?= ($currentPage == 'turismo1.php') ? 'active' : '' ?>">Turismo</a>
  <form action="/transporte/search" method="post">
    <div class="Buscador">
      <img src="imagenes/lupa.png" alt="Buscar">
      <input type="text" name="keyword" placeholder="Buscar" required>
    </div>
  </form>
</nav>

<?php else: ?>
<!-- Menu para usuarios logueados -->
<header class="encabezado">
  <div class="logo">
    <img src="imagenes/logo.png" alt="logo">
  </div>
  <div class="redes">
    <a target="_blank" href="https://www.instagram.com/"><img src="imagenes/instagram.png" height="35"/></a>
    <a target="_blank" href="https://www.facebook.com/"><img src="imagenes/facebook.png" height="35" style="margin-left: 12px;"/></a>
    <a target="_blank" href="https://x.com/?lang=es"><img src="imagenes/X.png" height="35" style="margin-left: 10px;"/></a>
  </div> 
  <div class="informacion">
    <span style="margin-left: 15px; color: #ffffff;">
      Bienvenido, <?= htmlspecialchars($_SESSION['usuario_nombre']) ?>
    </span>
    <a href="#" style="margin-left: 15px; color: #ffffff;">Contacto</a>
    <a href="sobrenosotros.php" style="margin-left: 15px; color: #ffffff;">Sobre Nosotros</a>
    <?php if ($_SESSION['usuario_rol'] === 'Administrador'): ?>
      <div class="icono-noticias">
        <img src="imagenes/notificacion.png" alt="Revision" class="icono-notificacion">
        <?php if ($total_notificaciones > 0): ?>
          <span class="badge-notificaciones"><?= $total_notificaciones ?></span>
        <?php endif; ?>
        <div class="menu-desplegable-noticias">
          <a href="revision_noticias.php">
            Noticias
            <?php if ($noticias_pendientes > 0): ?>
              <span class="badge-mini"><?= $noticias_pendientes ?></span>
            <?php endif; ?>
          </a>
          <a href="revision_denuncias.php">
            Denuncias
            <?php if ($denuncias_pendientes > 0): ?>
              <span class="badge-mini"><?= $denuncias_pendientes ?></span>
            <?php endif; ?>
          </a>
          <a href="revision_reportes.php">
            Reportes
            <?php if ($reportes_pendientes > 0): ?>
              <span class="badge-mini"><?= $reportes_pendientes ?></span>
            <?php endif; ?>
          </a>
        </div>
      </div>

      <style>
        .icono-noticias {
          position: relative;
          margin-left: 20px;
          display: inline-block;
          cursor: pointer;
        }

        .icono-notificacion {
          height: 25px;
        }

        .badge-notificaciones {
          position: absolute;
          top: -5px;
          right: -5px;
          background-color: red;
          color: white;
          font-size: 10px;
          padding: 2px 6px;
          border-radius: 50%;
          font-weight: bold;
        }

        .menu-desplegable-noticias {
          display: none;
          position: absolute;
          right: 0;
          background-color: white;
          min-width: 160px;
          box-shadow: 0 8px 16px rgba(0,0,0,0.2);
          z-index: 1001;
          border-radius: 4px;
        }

        .menu-desplegable-noticias a {
          color: #333;
          padding: 12px 16px;
          text-decoration: none;
          display: block;
          transition: background-color 0.3s;
        }

        .menu-desplegable-noticias a:hover {
          background-color: #f1f1f1;
        }

        .icono-noticias:hover .menu-desplegable-noticias {
          display: block;
        }
        .badge-mini {
          background-color: red;
          color: white;
          font-size: 10px;
          padding: 2px 6px;
          border-radius: 50%;
          font-weight: bold;
          margin-left: 8px;
        }
      </style>
    <?php endif; ?>
    <div class="menu-configuracion"> 
      <img src="imagenes/configurar.png" class="icono-configuracion" alt="Configuracion">
      <div class="menu-desplegable">        
          <a href="actualizar_perfil.php">Configurar Perfil</a>
          <a href="logout.php" id="btnSesion">Cerrar Sesión</a>
      </div>
    </div>
  </div>

</header>

<nav class="barra">
  <a href="inicio.php" class="nav-link <?= ($currentPage == 'inicio.php') ? 'active' : '' ?>">Inicio</a>
  <a href="clima.php" class="nav-link <?= ($currentPage == 'clima.php') ? 'active' : '' ?>">Clima</a>
  <a href="noticias.php" class="nav-link <?= ($currentPage == 'noticias.php') ? 'active' : '' ?>">Deportes</a>
  <a href="educacion.php" class="nav-link <?= ($currentPage == 'educacion.php') ? 'active' : '' ?>">Educación</a>
  <a href="turismo.php" class="nav-link <?= ($currentPage == 'turismo.php') ? 'active' : '' ?>">Turismo</a>
  <a href="denuncia.php" class="nav-link <?= ($currentPage == 'denuncia.php') ? 'active' : '' ?>">Denuncias</a>
    </a> 
  <form action="noticias.php" method="post">
    <div class="Buscador">
      <img src="imagenes/lupa.png" alt="Buscar">
      <input type="text" name="busqueda" placeholder="Buscar" required>
    </div>
  </form>
</nav>
<?php endif; ?>