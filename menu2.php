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
  <a href="denuncia_anonima.php" class="nav-link <?= ($currentPage == 'denuncia_anonima.php') ? 'active' : '' ?>">Denuncias</a>
  <form id="formBuscador" action="javascript:void(0);">
  <div class="Buscador-menu2">
    <img src="imagenes/lupa.png" alt="Buscar">
    <input  id="inputBusqueda"
            type="text"
            name="term"
            autocomplete="off"
            placeholder="Buscar título..."
            data-categoria="<?= $categoria_actual ?? 'inicio' ?>">
  </div>
</form>
</nav>

<?php else: ?>
<!-- Menu para usuarios logueados - Version simplificada para inicio.php -->
<header class="encabezado-menu2">
  <div class="logo-menu2">
    <img src="imagenes/logo.png" alt="logo">
  </div>
  <div class="redes-menu2">
    <a target="_blank" href="https://www.instagram.com/"><img src="imagenes/instagram.png" height="35"/></a>
    <a target="_blank" href="https://www.facebook.com/"><img src="imagenes/facebook.png" height="35" style="margin-left: 12px;"/></a>
    <a target="_blank" href="https://x.com/?lang=es"><img src="imagenes/X.png" height="35" style="margin-left: 10px;"/></a>
  </div> 
  <div class="informacion-menu2">
    <span style="margin-left: 15px; color: #ffffff;">
      Bienvenido, <?= htmlspecialchars($_SESSION['usuario_nombre']) ?>
    </span>
    <a href="#" style="margin-left: 15px; color: #ffffff;">Contacto</a>
    <a href="sobrenosotros.php" style="margin-left: 15px; color: #ffffff;">Sobre Nosotros</a>

    <?php if ($_SESSION['usuario_rol'] === 'Administrador'): ?>
      <div class="icono-noticias-menu2">
        <img src="imagenes/notificacion.png" alt="Revision" class="icono-notificacion-menu2">
        <?php if ($total_notificaciones > 0): ?>
          <span class="badge-notificaciones-menu2"><?= $total_notificaciones ?></span>
        <?php endif; ?>
        <div class="menu-desplegable-noticias-menu2">
          <a href="revision_noticias.php">
            Noticias
            <?php if ($noticias_pendientes > 0): ?>
              <span class="badge-mini-menu2"><?= $noticias_pendientes ?></span>
            <?php endif; ?>
          </a>
          <a href="revision_denuncias.php">
            Denuncias
            <?php if ($denuncias_pendientes > 0): ?>
              <span class="badge-mini-menu2"><?= $denuncias_pendientes ?></span>
            <?php endif; ?>
          </a>
          <a href="revision_reportes.php">
            Reportes
            <?php if ($reportes_pendientes > 0): ?>
              <span class="badge-mini-menu2"><?= $reportes_pendientes ?></span>
            <?php endif; ?>
          </a>
        </div>
      </div>
    <?php endif; ?>

    <div class="menu-configuracion-menu2">
      <img src="imagenes/configurar.png" class="icono-configuracion-menu2" alt="Configuracion">
      <div class="menu-desplegable-menu2">
          <a href="actualizar_perfil.php">Configurar Perfil</a>
          <a href="logout.php" id="btnSesion">Cerrar Sesion</a>
      </div>
    </div>
    </div>
  </header>

  <nav class="barra-menu2">
  <a href="inicio.php" class="nav-link-menu2 <?= ($currentPage == 'inicio.php') ? 'active-menu2' : '' ?>">Inicio</a>
  <a href="clima.php" class="nav-link-menu2 <?= ($currentPage == 'clima.php') ? 'active-menu2' : '' ?>">Clima</a>
  <a href="noticias.php" class="nav-link-menu2 <?= ($currentPage == 'noticias.php') ? 'active-menu2' : '' ?>">Deportes</a>
  <a href="educacion.php" class="nav-link-menu2 <?= ($currentPage == 'educacion.php') ? 'active-menu2' : '' ?>">Educación</a>
  <a href="turismo.php" class="nav-link-menu2 <?= ($currentPage == 'turismo.php') ? 'active-menu2' : '' ?>">Turismo</a>
  <a href="denuncia.php" class="nav-link-menu2 <?= ($currentPage == 'denuncia.php') ? 'active-menu2' : '' ?>">Denuncias</a>
  <form id="formBuscador" action="javascript:void(0);">
  <div class="Buscador-menu2">
    <img src="imagenes/lupa.png" alt="Buscar">
    <input  id="inputBusqueda"
            type="text"
            name="term"
            autocomplete="off"
            placeholder="Buscar título..."
            data-categoria="<?= $categoria_actual ?? 'inicio' ?>">
    </div>
  </form>
</nav>
<style>
    .encabezado-menu2 {
    background-color: #0d5c9b;
    color: white;
    padding: 10px 20px;
    display: flex;
    justify-content: space-between;
    align-items: center;
    position: fixed;
    top: 0;
    left: 0;
    right: 0;
    z-index: 1000;
    height: 70px;
}

.logo-menu2 img {
    height: 50px;
}

.informacion-menu2 {
    margin-right: 10px;
    display: flex;
    align-items: center;
}

.barra-menu2 {
    background-color: #bebaba;
    display: flex;
    justify-content: space-around;
    padding: 10px;
    font-weight: bold;
    position: fixed;
    top: 70px;
    left: 0;
    right: 0;
    z-index: 999;
    height: 50px;
    align-items: center;
}

.barra-menu2 a {
    color: #000000;
    text-decoration: none;
    padding: 8px 15px;
    border-radius: 5px;
    transition: 0.3s;
}

.barra-menu2 a.active-menu2 {
    background-color: #0d5c9b;
    color: white;
}

.barra-menu2 a:hover {
    background-color: #0d5c9b;
    color: white;
}

/* Buscador específico */
.Buscador-menu2 {
    position: relative;
    width: 200px;
}

.Buscador-menu2 input {
    width: 100%;
    padding: 8px 8px 8px 35px;
    border: 1px solid #ccc;
    border-radius: 4px;
    box-sizing: border-box;
}

.Buscador-menu2 img {
    position: absolute;
    top: 50%;
    left: 10px;
    transform: translateY(-50%);
    width: 16px;
    height: 16px;
    pointer-events: none;
}

/* Menú configuración */
.menu-configuracion-menu2 {
    position: relative;
    display: inline-block;
    margin-left: 15px;
}

.icono-configuracion-menu2 {
    width: 30px;
    height: 30px;
    cursor: pointer;
    transition: transform 0.3s;
}

.icono-configuracion-menu2:hover {
    transform: rotate(30deg);
}

.menu-desplegable-menu2 {
    display: none;
    position: absolute;
    right: 0;
    background-color: white;
    min-width: 160px;
    box-shadow: 0 8px 16px rgba(0,0,0,0.2);
    z-index: 1001;
    border-radius: 4px;
}

.menu-desplegable-menu2 a {
    color: #333;
    padding: 12px 16px;
    text-decoration: none;
    display: block;
    transition: background-color 0.3s;
}

.menu-desplegable-menu2 a:hover {
    background-color: #f1f1f1;
}

.menu-configuracion-menu2:hover .menu-desplegable-menu2 {
    display: block;
}

/* Notificaciones para administradores */
.icono-noticias-menu2 {
    position: relative;
    margin-left: 20px;
    display: inline-block;
    cursor: pointer;
}

.icono-notificacion-menu2 {
    height: 25px;
}

.badge-notificaciones-menu2 {
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

.menu-desplegable-noticias-menu2 {
    display: none;
    position: absolute;
    right: 0;
    background-color: white;
    min-width: 160px;
    box-shadow: 0 8px 16px rgba(0,0,0,0.2);
    z-index: 1001;
    border-radius: 4px;
}

.menu-desplegable-noticias-menu2 a {
    color: #333;
    padding: 12px 16px;
    text-decoration: none;
    display: block;
    transition: background-color 0.3s;
}

.menu-desplegable-noticias-menu2 a:hover {
    background-color: #f1f1f1;
}

.icono-noticias-menu2:hover .menu-desplegable-noticias-menu2 {
    display: block;
}

.badge-mini-menu2 {
    background-color: red;
    color: white;
    font-size: 10px;
    padding: 2px 6px;
    border-radius: 50%;
    font-weight: bold;
    margin-left: 8px;
}

/* Redes sociales */
.redes-menu2 {
    display: flex;
    align-items: center;
    gap: 12px;
}

.redes-menu2 img {
    height: 32px;
}

/* Comportamiento scroll */
.encabezado-menu2.oculto {
    transform: translateY(-100%);
    transition: transform 0.3s ease;
}

.barra-menu2.oculto {
    transform: translateY(-120px);
    transition: transform 0.3s ease;
}

/* Asegurar que el contenido no quede oculto detrás del menú fijo */
body {
    padding-top: 120px; /* 70px header + 50px nav */
}

/* Responsive */
@media (max-width: 768px) {
    .encabezado-menu2 {
        padding: 10px 15px;
        height: 60px;
    }
    
    .barra-menu2 {
        top: 60px;
        height: 45px;
        padding: 8px;
    }
    
    .logo-menu2 img {
        height: 40px;
    }
    
    .Buscador-menu2 {
        width: 150px;
    }
    
    .informacion-menu2 span {
        font-size: 14px;
    }
    
    body {
        padding-top: 105px; /* 60px header + 45px nav */
    }
}

@media (max-width: 576px) {
    .encabezado-menu2 {
        flex-wrap: wrap;
        height: auto;
        min-height: 60px;
    }
    
    .barra-menu2 {
        top: auto;
        position: relative;
        margin-top: 60px;
    }
    
    body {
        padding-top: 0;
    }
    
    .informacion-menu2 {
        flex-wrap: wrap;
        gap: 8px;
    }
}

</style>
    
<script>
// Script para ocultar/mostrar menú al hacer scroll
let lastScrollTop = 0;
const encabezadoMenu2 = document.querySelector('.encabezado-menu2');
const barraMenu2 = document.querySelector('.barra-menu2');

if (encabezadoMenu2 && barraMenu2) {
    window.addEventListener('scroll', function() {
        let scrollTop = window.pageYOffset || document.documentElement.scrollTop;
        
        if (scrollTop > lastScrollTop && scrollTop > 100) {
            // Scrolling down
            encabezadoMenu2.classList.add('oculto');
            barraMenu2.classList.add('oculto');
        } else {
            // Scrolling up
            encabezadoMenu2.classList.remove('oculto');
            barraMenu2.classList.remove('oculto');
        }
        lastScrollTop = scrollTop;
    });
}
</script>

<?php endif; ?>