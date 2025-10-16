<?php
$categoria_actual = 'denuncias';
include 'conexion.php';

$termino_busqueda = '';
$where = '';
$params = [];

$query = "SELECT * FROM propuestas_denuncias WHERE estado = 'aprobada' ORDER BY fecha DESC";
$stmt = $conexion->prepare($query);

if (!empty($params)) {
    $types = str_repeat('s', count($params));
    $stmt->bind_param($types, ...$params);
}

$stmt->execute();
$resultado = $stmt->get_result();
$propuestas_denuncias = $resultado->fetch_all(MYSQLI_ASSOC);
$stmt->close();
$conexion->close();
?>
<script src="buscador.js" defer></script>

<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>Denuncias Ciudadanas</title>
  <style>
    * {
      box-sizing: border-box;
      margin: 0;
      padding: 0;
    }
    body {
      font-family: Arial, sans-serif;
      background-color: #f5f5f5;
    }
    .encabezado1 {
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
    }
    .logo1 img {
      height: 50px;
      margin-right: 10px;
    }
    nav.barra1 {
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
    }
    nav.barra1 a {
      color: #000000;
      text-decoration: none;
      padding: 8px 15px;
      border-radius: 5px;
      transition: 0.3s;
    }
    nav.barra1 a.active {
      background-color: #0d5c9b;
      color: white;
    }
    .redes1 {
      margin-left: 500px;
    }
    .informacion1 {
      margin-right: 15px;
    }
  
    .menu-configuracion {
      position: relative;
      display: inline-block;
      margin-left: 15px;
    }
    
    .icono-configuracion {
      width: 30px;
      height: 30px;
      cursor: pointer;
      transition: transform 0.3s;
    }
    
    .icono-configuracion:hover {
      transform: rotate(30deg);
    }
    
    .menu-desplegable {
      display: none;
      position: absolute;
      right: 0;
      background-color: white;
      min-width: 160px;
      box-shadow: 0 8px 16px rgba(0,0,0,0.2);
      z-index: 1001;
      border-radius: 4px;
    }
    .menu-desplegable a {
      color: #333;
      padding: 12px 16px;
      text-decoration: none;
      display: block;
      transition: background-color 0.3s;
    }
    
    .menu-desplegable a:hover {
      background-color: #f1f1f1;
    }
    
    .menu-configuracion:hover .menu-desplegable {
      display: block;
    }
    .resultados-busqueda {
      margin-bottom: 20px;
      padding: 10px;
      background-color: #f0f0f0;
      border-radius: 4px;
    }
    .contenedor {
      display: flex;
      padding: 150px;
      gap: 40px;
    }
    .denuncia {
      width: 45%;
    }
    a {
      text-decoration: none;
    }
    .boton-publicar { 
      position: fixed; 
      bottom: 30px; right: 
      30px; background-color: #0d5c9b; 
      color: white; 
      border: none; 
      padding: 15px 25px; 
      border-radius: 50px; 
      font-weight: bold; 
      cursor: pointer; 
      box-shadow: 0 4px 8px rgba(0,0,0,0.2); 
      z-index: 1000; 
      text-decoration: none; 
    }
    .contenido-principal { 
      margin-top: 130px;
      padding: 20px; 
    }
    .sin-noticias { 
      text-align: center; 
      padding: 50px; 
      color: #666; 
    }
    .noticia-card { 
      background: white; 
      border-radius: 8px; 
      padding: 20px; 
      margin-bottom: 20px; 
      box-shadow: 0 2px 4px rgba(0,0,0,0.1); 
      position: relative; 
    }
    .dropdown-content { 
      display: none; 
      position: absolute; 
      right: 0; 
      background-color: #ffffff; 
      min-width: 140px; 
      box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15); 
      border-radius: 8px; 
      z-index: 1001; 
      overflow: hidden; 
      transition: all 0.2s ease-in-out;
    }

    .dropdown-content a { 
      color: #333; 
      padding: 10px 16px; 
      text-decoration: none; 
      display: block; 
      font-size: 14px;
      transition: background-color 0.2s ease;
    }

    .dropdown-content a:hover { 
      background-color: #f0f0f0; 
    }

    .dropdown:hover .dropdown-content { 
      display: block; 
    }

    .noticia-titulo { 
      color: #0d5c9b; 
      margin-bottom: 10px; 
    }
    .noticia-meta { 
      color: #666; 
      font-size: 14px; 
      margin-bottom: 15px; 
      display: flex; 
      gap: 15px; 
    }
    .imagen-contenedor { 
      max-width: 100%; 
      overflow: hidden;
       text-align: center; 
       margin-bottom: 15px; 
      }
    .noticia-imagen { 
      max-width: 100%; 
      height: auto; 
      max-height: 400px; 
      object-fit: contain; 
      border-radius: 4px; 
    }
    .noticia-resumen { 
      line-height: 1.6; 
      margin-bottom: 15px;
    }
    .encabezado1.oculto {
      transform: translateY(-100%);
      transition: transform 0.3s ease;
    }
    .barra1.oculto {
      transform: translateY(-130px);
      transition: transform 0.3s ease;
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
    transition: all 0.3 ease;
    white-space: nowrap;
    min-width: auto;
    width: auto; 
}

.btn:hover {
    
}
 
  </style>
</head>
<body>

<?php include 'menu.php'; ?>

  <div class="contenido-principal"> 
    <div id="contenedor-denuncias">
      <?php if (empty($propuestas_denuncias)): ?>
        <div class="sin-noticias">
          <h2>No hay denuncias publicadas aún</h2>
          <p>¡Sé el primero en compartir una denuncia!</p>
        </div>
      <?php else: ?>
        <?php foreach ($propuestas_denuncias as $noticia): ?>
          <article class="noticia-card" style="position: relative;">

            <a href="ver_denuncia.php?id=<?= $noticia['id'] ?>" style="text-decoration: none; color: inherit;">
              <h2 class="noticia-titulo"><?= htmlspecialchars($noticia['titulo']) ?></h2>
            </a>
            <div class="noticia-meta">
              <span><?= htmlspecialchars($noticia['fecha']) ?></span>
            </div>
            <?php if ($noticia['imagen']): ?>
              <div class="imagen-contenedor">
                <img src="imagenes/denuncias/<?= htmlspecialchars($noticia['imagen']) ?>" class="noticia-imagen" alt="<?= htmlspecialchars($noticia['titulo']) ?>">
              </div>
            <?php endif; ?>
            <p class="noticia-resumen"><?= nl2br(htmlspecialchars($noticia['descripcion'])) ?></p>
          </article>
        <?php endforeach; ?>
      <?php endif; ?>
    </div>
  </div>

  <a href="enviar_denuncia_anonima.php" class="boton-publicar">Enviar denuncia</a>
  
    <script>
  // Confirmación de cierre de sesión
      document.getElementById('btnSesion')?.addEventListener('click', function(e) {
        e.preventDefault();

        const confirmBox = document.createElement('div');
        confirmBox.style.position = 'fixed';
        confirmBox.style.top = '0';
        confirmBox.style.left = '0';
        confirmBox.style.width = '100%';
        confirmBox.style.height = '100%';
        confirmBox.style.background = 'rgba(0,0,0,0.5)';
        confirmBox.style.display = 'flex';
        confirmBox.style.alignItems = 'center';
        confirmBox.style.justifyContent = 'center';
        confirmBox.style.zIndex = '9999';

        confirmBox.innerHTML = `
          <div style="background: white; padding: 20px 30px; border-radius: 8px; text-align: center; box-shadow: 0 4px 10px rgba(0,0,0,0.3); max-width: 300px;">
            <h3>¿Cerrar sesión?</h3>
            <p>¿Estás seguro de cerrar sesión?</p>
            <div style="margin-top: 20px; display: flex; justify-content: space-between;">
              <button id="confirmLogout" style="background-color: #d9534f; color: white; padding: 8px 12px; border: none; border-radius: 4px; cursor: pointer;">Cerrar sesión</button>
              <button id="cancelarLogout" style="background-color: #ccc; color: black; padding: 8px 12px; border: none; border-radius: 4px; cursor: pointer;">Cancelar</button>
            </div>
          </div>
        `;

        document.body.appendChild(confirmBox);

        document.getElementById('confirmLogout').onclick = () => {
          window.location.href = "logout.php";
        };

        document.getElementById('cancelarLogout').onclick = () => {
          document.body.removeChild(confirmBox);
        };
      });

      // Ocultar encabezado y barra al hacer scroll hacia abajo
      let lastScroll = 0;
      const encabezado = document.querySelector('.encabezado1');
      const barra = document.querySelector('nav.barra1');
      let timer;

      window.addEventListener('scroll', () => {
        const currentScroll = window.pageYOffset || document.documentElement.scrollTop;

        if (currentScroll > lastScroll && currentScroll > 80) {
          barra?.classList.add('oculto');

          clearTimeout(timer);
          timer = setTimeout(() => {
            encabezado?.classList.add('oculto');
          }, 200);

        } else {

          clearTimeout(timer);
          encabezado?.classList.remove('oculto');
          barra?.classList.remove('oculto');
        }

        lastScroll = currentScroll <= 0 ? 0 : currentScroll;
      });
    </script>

    <?php include 'footer.php'; ?>
</body>
</html>