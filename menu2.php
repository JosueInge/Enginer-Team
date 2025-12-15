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
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;700&family=Montserrat:wght@500;600;700&family=Inter&family=Open+Sans&display=swap" rel="stylesheet">
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
            <input id="inputBusqueda" type="text" name="term" autocomplete="off" placeholder="Buscar título..." data-categoria="<?= $categoria_actual ?? 'inicio' ?>">
        </div>
    </form>
</nav>

<?php else: ?>
<!-- Menu para usuarios logueados - Version simplificada para inicio.php -->
<header class="encabezado-menu2">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;700&family=Montserrat:wght@500;600;700&family=Inter&family=Open+Sans&display=swap" rel="stylesheet">
    <div class="logo-menu2">
        <img src="imagenes/logo.png" alt="logo">
    </div>
    <div class="informacion-menu2">
        <span style="margin-left: 15px; color: #ffffff;"> Bienvenido, <?= htmlspecialchars($_SESSION['usuario_nombre']) ?> </span>
        <div class="linea-vertical"></div>

        <?php if ($_SESSION['usuario_rol'] === 'Administrador'): ?>
        <div class="icono-noticias-menu2">
            <img src="imagenes/notificacion.png" alt="Revision" class="icono-notificacion-menu2">
            <?php if ($total_notificaciones > 0): ?>
                <span class="badge-notificaciones-menu2"><?= $total_notificaciones ?></span>
            <?php endif; ?>

            <div class="menu-desplegable-noticias-menu2">
                <a href="revision_noticias.php"> Noticias 
                    <?php if ($noticias_pendientes > 0): ?>
                        <span class="badge-mini-menu2"><?= $noticias_pendientes ?></span>
                    <?php endif; ?>
                </a>
                <a href="revision_denuncias.php"> Denuncias 
                    <?php if ($denuncias_pendientes > 0): ?>
                        <span class="badge-mini-menu2"><?= $denuncias_pendientes ?></span>
                    <?php endif; ?>
                </a>
                <a href="revision_reportes.php"> Reportes 
                    <?php if ($reportes_pendientes > 0): ?>
                        <span class="badge-mini-menu2"><?= $reportes_pendientes ?></span>
                    <?php endif; ?>
                </a>
            </div>
        </div>
        <?php endif; ?>

        <div class="menu-configuracion-menu2">
            <img src="imagenes/solar_user-bold.png" class="icono-configuracion-menu2" id="iconoMenu2" alt="Configuracion">
            <div class="menu-desplegable-menu2" id="menuDesplegable2">
                <a href="actualizar_perfil.php">Configurar Perfil</a>
                <hr class="separador-menu2">
                <a href="logout.php" id="btnSesion">Cerrar Sesion</a>
            </div>
        </div>
    </div>
</header>

<nav class="barra-menu2">
  <div class="menu-hamburguesa-container">
      <img src="imagenes/menuAmburguesa.png" alt="Menú" class="icono-hamburguesa" id="btnHamburguesa">
  </div>

  <!-- Menu amburguesa -->
  <div class="menu-lateral" id="menuLateral">
      <!-- Botón cerrar -->
      <div class="cerrar-menu" id="btnCerrarMenu">&times;</div>
      <!-- Título -->
      <h2 class="titulo-menu">Comunicado Digital</h2>
      <!-- Sección redes sociales -->           
      <div class="seccion-redes">
          <p class="texto-siguenos">Síguenos</p>
          <div class="iconos-redes">
              <a href="#"><img src="imagenes/facebook1.png" alt="Facebook"></a>
              <a href="#"><img src="imagenes/instagram1.png" alt="Instagram"></a>
              <a href="#"><img src="imagenes/x1.png" alt="X"></a>
          </div>
      </div>

      <!-- Línea separadora -->
      <hr class="linea-separadora">

      <!-- Categorías -->
      <div class="seccion-categorias">
          <a href="politica.php" class="categoria">Política</a>
          <a href="cultura.php" class="categoria">Cultura</a>
          <a href="entretenimiento.php" class="categoria">Entretenimiento</a>
          <a href="social.php" class="categoria">Social</a>
          <a href="salud.php" class="categoria">Salud</a>
          <a href="medioambiente.php" class="categoria">Medio ambiente</a>
          <a href="tendencia.php" class="categoria">Tendencia</a>
      </div>

      <!-- Línea separadora -->
      <hr class="linea-separadora">

      <!-- Enlaces secundarios -->
      <div class="seccion-secundarios">
          <a href="publicidad.php" class="secundario">Contratar publicidad</a>
          <a href="terminos.php" class="secundario">Términos y condiciones</a>
          <a href="privacidad.php" class="secundario">Políticas de privacidad</a>
      </div>

      <!-- Texto legal -->
      <p class="texto-legal">© 2025 Comunicado Digital. Todos los derechos reservados</p>
  </div>

    <a href="inicio.php" class="nav-link-menu2 <?= ($currentPage == 'inicio.php') ? 'active-menu2' : '' ?>">Inicio</a>
    <a href="clima.php" class="nav-link-menu2 <?= ($currentPage == 'clima.php') ? 'active-menu2' : '' ?>">Clima</a>
    <a href="noticias.php" class="nav-link-menu2 <?= ($currentPage == 'noticias.php') ? 'active-menu2' : '' ?>">Deportes</a>
    <a href="educacion.php" class="nav-link-menu2 <?= ($currentPage == 'educacion.php') ? 'active-menu2' : '' ?>">Educación</a>
    <a href="turismo.php" class="nav-link-menu2 <?= ($currentPage == 'turismo.php') ? 'active-menu2' : '' ?>">Turismo</a>
    <a href="denuncia.php" class="nav-link-menu2 <?= ($currentPage == 'denuncia.php') ? 'active-menu2' : '' ?>">Denuncias</a>
    <form class="Buscador-menu2" id="formBuscador" action="javascript:void(0);">
      <div class="Buscador-menu2">
        <img src="imagenes/lupa1.png" alt="Buscar">
        <input id="inputBusqueda" type="text" name="term" autocomplete="off" placeholder="Buscar"
          data-categoria="<?= $categoria_actual ?? 'inicio' ?>">
      </div>
    </form>
</nav>

<style>
  .encabezado-menu2 { 
    background-color: #061F3E; 
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
    height: 90px; 
  } 
  .logo-menu2 img { 
    height: 50px; 
  } 
  .informacion-menu2 { 
    margin-right: 10px; 
    font-family:'Open Sans',sans-serif;
    display: flex; 
    font-size: 20px;
    font-weight: 500;
    align-items: center; 
  } 
  .linea-vertical { 
    width: 1px; 
    height: 34px; 
    background-color: #D9D9D9; 
    margin: 0 15px; 
  } 
  /*Barra de categorias*/
  .barra-menu2 { 
    background-color: #FFFFFF; 
    display: flex; 
    justify-content: space-around; 
    padding: 10px; 
    font-weight: bold; 
    position: fixed; 
    top: 80px; 
    left: 0; 
    right: 0; 
    z-index: 999; 
    height: 90px; 
    align-items: center;
    border-bottom: 2px solid #EFEFF0; 
  } 
  .barra-menu2 a { 
    font-family:'Poppins',sans-serif;
    font-weight: bold;
    color: #403F48; 
    font-size: 20px;
    text-decoration: none; 
    padding: 8px 15px; 
    border-radius: 8px; 
    transition: 0.3s; 
  } 
  .barra-menu2 a.active-menu2 { 
    background: linear-gradient(to right, #61C9A880 0%, #61C9A880 100%);/*61C9A8 es el color, pero se le agrega 80 para la opacacidad.*/
    color: #061F3E;
  } 
  /* Buscador específico */ 
  .Buscador-menu2 { 
    position: relative;
    display: flex;
    align-items: center;
    gap: 8px; 
    width: fit-content; 
  } 
  .Buscador-menu2 input { 
    width: 235px;
    height: 36px;
    padding: 8px;
    border: 1px solid #438DCB;
    border-radius: 10px;
    box-sizing: border-box; 
  }
  .Buscador-menu2 input:focus { 
    outline: none; 
    border-color: #2D8EFF; 
    box-shadow: 0 0 5px rgba(45, 142, 255, 0.5); 
  }
  .Buscador-menu2 img { 
    width: 18px;
    height: 18px;
    cursor: pointer;
  } 
  /* Menú configuración */ 
  .menu-configuracion-menu2 { 
    position: relative; 
    display: inline-block; 
    margin-left: 15px; 
  } 
  .icono-configuracion-menu2 { 
    width: 40px; 
    height: 40px; 
    cursor: pointer; 
    transition: transform 0.3s; 
  } 
  .menu-desplegable-menu2 { 
    display: none; 
    position: absolute; 
    right: 0; 
    background-color: #FFFFFF; 
    width: 180px; 
    height: 100px; 
    box-shadow: 0 8px 16px rgba(0,0,0,0.2); 
    z-index: 1001; 
    border-radius: 8px; 
    top: 50px; 
    border: 1px solid #2D8EFF; 
    font-family:'Inter',sans-serif; 
    font-size: 16px; 
  } 
  .menu-desplegable-menu2 a { 
    color: #403F48; 
    padding: 12px 16px; 
    text-decoration: none; 
    display: block; 
    transition: background-color 0.3s; 
    border-radius: 8px; 
    position: relative; 
    overflow: hidden; 
  } 
  .menu-desplegable-menu2 a:active { 
    background-color: #e0e0e0; 
  } 
  .ripple { 
    position: absolute; 
    border-radius: 50%; 
    background-color: rgba(45, 142, 255, 0.3); 
    transform: scale(0); 
    animation: ripple-animation 0.6s linear; 
    pointer-events: none; 
  } 
  @keyframes ripple-animation { 
    to { transform: scale(3); 
    opacity: 0; } 
  } 
  .separador-menu2 { 
    border: none; 
    border-top: 1px solid #2D8EFF; 
    margin: 0; 
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
    z-index: 1001; border-radius: 4px; 
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
  .icono-noticias-menu2:hover 
  .menu-desplegable-noticias-menu2 { 
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
  /* Comportamiento scroll */ 
  .encabezado-menu2.oculto { 
    transform: translateY(-100%); 
    transition: transform 0.6s ease; 
  } 
  .barra-menu2.oculto { 
    transform: translateY(-180px);
    transition: transform 0.6s ease; 
  } 
  /* Asegurar que el contenido no quede oculto detrás del menú fijo */ 
  body { 
    padding-top: 120px; 
    /* 70px header + 50px nav */ 
  } 

  /*Menu amburguesa*/
  .menu-hamburguesa-container {
      display: flex;
      align-items: center;
      justify-content: center;
  }
  .icono-hamburguesa {
      width: 32px;
      height: 32px;
      cursor: pointer;
  }

  .menu-lateral {
      position: fixed;
      top: 70px;
      left: -525px; /*oculto por defecto*/
      width: 525px;
      max-width: 100%;
      height: calc(100% - 70px);
      background-color: #FFFFFF;
      z-index: 1400;
      padding: 30px 20px;
      box-shadow: 3px 0 15px rgba(0,0,0,0.2);
      overflow-y: auto;
      transition: left 0.4s ease;
  }

  .menu-lateral.open {
      left:0;
    }

  .cerrar-menu {
      position: absolute;
      top: 5px;
      right: 20px;
      font-size: 32px;
      color: #061F3E;
      cursor: pointer;
      margin-top: 10px;
  }
  .titulo-menu {
      font-family: 'Montserrat', sans-serif;
      font-size: 24px;
      font-weight: bold;
      text-align: center;
      color: #061F3E;
      margin-bottom: 20px;
      margin-top: 15px;
  }
  .seccion-redes {
      display: flex; 
      align-items: center;
      justify-content: center;
      gap: 50px;
      margin-bottom: 20px;
  }
  .texto-siguenos {
      font-family: 'Montserrat', sans-serif;
      font-size: 20px;
      font-weight: bold;
      color: #061F3E;
      margin-bottom: 10px;
      margin: 0;
  }
  .iconos-redes {
      display: flex;      
      gap: 12px;  
  }
  .iconos-redes img{
      width: 24px;       
      height: 24px;
      object-fit: contain;
      filter: brightness(0) saturate(100%) invert(12%) sepia(26%) saturate(2098%) hue-rotate(187deg) brightness(95%) contrast(90%);
      transition: filter 0.3s;
      cursor: pointer;           
  }
  .iconos-redes img:hover {
    filter: brightness(0) saturate(100%) invert(56%) sepia(74%) saturate(2481%) hue-rotate(194deg) brightness(99%) contrast(98%);
  }
  .linea-separadora {
      border: none;
      border-top: 1px solid #2F8EFF;
      margin: 15px 0;
  }
  .seccion-categorias a, .seccion-secundarios a {
      display: block;
      font-family: 'Montserrat', sans-serif;
      font-weight: 600;
      font-size: 24px;
      color: #061F3E;
      text-align: left;
      margin-left: 30px;
      padding: 10px 0;
      text-decoration: none;
      transition: background-color 0.3s;
  }
  .seccion-categorias a:hover, .seccion-secundarios a:hover {
      background-color: #C7F1FF;
  }
  .texto-legal {
      font-family: 'Inter', sans-serif;
      font-size: 20px;
      color: #2D8EFF;
      text-align: center;
      margin-top: 20px;
  }

  /* activa el icono hamburguesa al dar click en el */
  .icono-hamburguesa.activo {
    color: #061F3E;
    background: #61C9A880;
    border: 1px solid #61C9A880;
    border-radius: 5px;
    box-shadow: 0 0 6px #61C9A880;
    padding: 5px; /* crea espacio alrededor del icono */
    transition: all 0.3s ease;
  }

  .icono-hamburguesa {
    box-sizing: content-box;
  }

  /* activa las categorias al dar click en ellas */
  .categoria.activo {
    background: #C7F1FF;
    color: #061F3E;
    border-radius: 8px;
  }

  /*Responsive*/
  @media (max-width: 768px) { 
    .encabezado-menu2 { 
      height: 60px; 
      padding: 10px 15px; 
    } 
    .barra-menu2 { 
      top: 60px; 
      height: 45px; 
      padding: 8px; 
      justify-content: space-between;
    } 
    .logo-menu2 img { 
      height: 40px; 
    } 
    .Buscador-menu2 { 
      width: 150px; 
      order: 3;
    } 
    .informacion-menu2 span { 
      font-size: 14px; 
    } 
    /*Mostrar solo Inicio + buscador + hamburguesa*/
    .barra-menu2 a:not([href="inicio.php"]) {
      display: none;
    }
    /* Inicio visible */
    .barra-menu2 a[href="inicio.php"] {
      display: inline-block;
      order: 1;
    }
    /* Icono hamburguesa */
    .barra-menu2 {
      order: 2;
    }
    body { 
      padding-top: 105px; 
      /* 60px header + 45px nav */ 
    } 
    .menu-lateral {
        width: 100%;
        left: -100%;
        padding: 20px 10px;
    }
    .seccion-redes {
        display: flex;        
        flex-direction: row; 
        align-items: center; 
        justify-content: center;
    }
  } 

  @media (max-width: 576px) { 
    .encabezado-menu2 { 
      flex-wrap: wrap; 
      height: auto; 
      min-height: 60px; 
    } 
    .barra-menu2 { 
      position: fixed;
      top: 60px; 
      left: 0;
      right: 0;
      z-index: 1100;
      position: relative; 
      margin-top: 60px; 
      justify-content: space-between;
      height: 50px;
      padding: 8px 15px;
      background-color: #FFFFFF;
    } 
    body { 
      padding-top: 100px; 
    }
    .informacion-menu2 { 
      flex-wrap: wrap; 
      gap: 8px; 
    } 
    .menu-lateral {
      width: 100%;
      left: -100%;
    }
  }

</style>

<script>
  // Comportamiento al hacer scroll
  let lastScrollTop = 0;
  const encabezadoMenu2 = document.querySelector('.encabezado-menu2');
  const barraMenu2 = document.querySelector('.barra-menu2');

  window.addEventListener('scroll', function() {
      let scrollTop = window.pageYOffset || document.documentElement.scrollTop;

      if (menuLateral && menuLateral.style.left === '0px') {
      }

      if (scrollTop > lastScrollTop && scrollTop > 100) {
          encabezadoMenu2.classList.add('oculto');
          barraMenu2.classList.add('oculto');
      } else {
          encabezadoMenu2.classList.remove('oculto');
          barraMenu2.classList.remove('oculto');
      }
      lastScrollTop = scrollTop;
  });

// Menú desplegable de configuración
  const icono = document.getElementById("iconoMenu2");
  const menu = document.getElementById("menuDesplegable2");

  icono.addEventListener("click", function(event) {
      event.stopPropagation(); 
      menu.style.display = (menu.style.display === "block") ? "none" : "block";
  });

  document.addEventListener("click", function() {
      menu.style.display = "none";
  });

  menu.addEventListener("click", function(event) {
      event.stopPropagation();
  });

// Efecto de onda del menú desplegable de configuración.
  document.querySelectorAll('.menu-desplegable-menu2 a').forEach(link => {
      link.addEventListener('click', function (e) {
          e.preventDefault();

          const ripple = document.createElement('span');
          ripple.classList.add('ripple');
          const rect = this.getBoundingClientRect();
          const size = Math.max(rect.width, rect.height);
          ripple.style.width = ripple.style.height = `${size}px`;
          ripple.style.left = `${e.clientX - rect.left - size / 2}px`;
          ripple.style.top = `${e.clientY - rect.top - size / 2}px`;
          this.appendChild(ripple);

          // Tiempo del efecto.
          setTimeout(() => {
              // Si el enlace es "Cerrar Sesión"
              if (this.id === 'btnSesion') {
                  const modal = new bootstrap.Modal(document.getElementById('modalCerrarSesion'));
                  modal.show();
              } else {
                  // Redirigir a la página correspondiente
                  window.location.href = this.getAttribute('href');
              }

              // Cerrar el menú de configuración después del efecto
              const menu = document.getElementById("menuDesplegable2");
              menu.style.display = "none";

          }, 400); // duración del efecto ripple

          setTimeout(() => ripple.remove(), 600);
      });
  });

  // Comportamiento menú amburguesa
  const btnHamburguesa = document.getElementById('btnHamburguesa');
  const menuLateral = document.getElementById('menuLateral');
  const btnCerrarMenu = document.getElementById('btnCerrarMenu');

  btnHamburguesa.addEventListener('click', () => {
      menuLateral.style.left = '0';
      document.body.style.overflow = 'hidden';
  });

  // Cerrar menú
  btnCerrarMenu.addEventListener('click', () => {
      menuLateral.style.left = '-525px';
      document.body.style.overflow = '';
  });

  // Cerrar menú al hacer clic fuera
  document.addEventListener('click', (e) => {
      if (!menuLateral.contains(e.target) && !btnHamburguesa.contains(e.target)) {
          menuLateral.style.left = '-525px';
          document.body.style.overflow = '';
      }
  });

  // --- NUEVO: comportamiento visual (hover activo) ---
  const categorias = document.querySelectorAll('.seccion-categorias a');

  // Guardar categoría activa al hacer clic
  categorias.forEach(link => {
    link.addEventListener('click', () => {
      localStorage.setItem('categoriaActiva', link.getAttribute('href'));

      // Quitar activo de todas y agregar a la clickeada
      categorias.forEach(l => l.classList.remove('activo'));
      link.classList.add('activo');

      // Activar hover del icono hamburguesa
      btnHamburguesa.classList.add('activo');
    });
  });

  // Mantener activa la categoría y el icono al recargar
  window.addEventListener('DOMContentLoaded', () => {
    const categoriaActiva = localStorage.getItem('categoriaActiva');
    if (categoriaActiva) {
      categorias.forEach(link => {
        if (link.getAttribute('href') === categoriaActiva) {
          link.classList.add('activo');
        }
      });
      btnHamburguesa.classList.add('activo');
    }
  });

  // Limpiar estado activo si estamos en páginas principales
  const paginasSinCategoria = [
    'inicio.php', 'clima.php', 'noticias.php',
    'educacion.php', 'turismo.php', 'denuncia.php'
  ];
  const rutaActual = window.location.pathname;

  if (paginasSinCategoria.some(pagina => rutaActual.includes(pagina))) {
    localStorage.removeItem('categoriaActiva');
    btnHamburguesa.classList.remove('activo');
  }
</script>
<?php endif; ?>