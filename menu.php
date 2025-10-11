<?php 

?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Comunicado Digital</title>
  <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;700&family=Montserrat:wght@500;600;700&family=Inter&family=Open+Sans&display=swap" rel="stylesheet">
  <script src="https://kit.fontawesome.com/3d3e3e3d3e.js" crossorigin="anonymous"></script>
  <style>
    *{margin:0;padding:0;box-sizing:border-box;}
    body{font-family:'Poppins',sans-serif;background:#f9f9f9;}

    /* Encabezado */
    header{
      background:#061F3E;
      padding:10px 30px;
      display:flex;
      justify-content:space-between;
      align-items:center;
    }
    .logo img{height:50px;}
    .botones{display:flex;gap:20px;}
    .btn{
      width:150px;height:40px;
      border-radius:25px;
      font-size:16px;font-weight:bold;
      text-align:center;line-height:40px;
      text-decoration:none;
      color:#fff;transition:.3s;
      position:relative;overflow:hidden;
    }
    .btn-login{background:#4C00DA;}
    .btn-register{background:#4C00DA;}
    .btn:hover{background:#3B00AD;}
    .btn:active::after{
      content:"";position:absolute;
      width:300%;height:300%;
      top:50%;left:50%;
      transform:translate(-50%,-50%);
      background:rgba(255,255,255,0.3);
      border-radius:50%;animation:ripple .6s linear;
    }
    @keyframes ripple{to{width:0;height:0;opacity:0;}}

    /* Barra navegación */
    nav{
      background:#fff;
      border-bottom:2px solid #EFEFF0;
      height:90px;
      display:flex;align-items:center;
      padding:0 20px;justify-content:space-between;
    }
    .nav-left{display:flex;align-items:center;gap:95px;}
    .nav-links{display:flex;gap:20px;}
    .nav-links a{
      font-family:'Poppins',sans-serif;
      font-size:20px;font-weight:bold;
      color:#403F48;text-decoration:none;
      padding:8px 12px;border-radius:8px;
      transition:.3s;
    }
    .nav-links a:hover{
      background:linear-gradient(90deg,#61C9A8,#61C9A880);
      color:#061F3E;
    }

    /* Hamburguesa */
    .menu-toggle {
      font-size:32px;
      cursor:pointer;
      color:#061F3E;
    }

    /* Buscador */
    .contenedor-buscador {
      display: flex;
      align-items: center;
      gap: 10px;
    }
    .icono-lupa-externo {
      font-size: 20px;
      color: #403F48;
      cursor: pointer;
      transition: color 0.3s;
    }
    .icono-lupa-externo:hover {
      color: #2D8EFF;
    }
    /* Buscador */
    .buscador{
      display:flex;
      align-items:center;
      border:1px solid #438DCB;
      border-radius:20px;
      padding:8px 15px;
      background:none;
      transition:.2s;
      min-width: 200px;
    }
    .buscador:hover{transform:scale(1.05);box-shadow:0 2px 6px rgba(0,0,0,0.15);}
    .buscador i{font-size:20px;color:#403F48;margin-right:8px;}
    .buscador input{
      border:none;outline:none;font-size:16px;
      font-family:'Open Sans',sans-serif;
      color:#8A8991;background:none;
    }
    .buscador input::placeholder{color:#8A8991;}

    #no-results{
      display:none;text-align:center;
      margin-top:30px;font-family:'Poppins',sans-serif;
      font-size:24px;color:#403F48;
    }

    /* Menú lateral */
    .menu-lateral{
      position:fixed;
      top: 0;
      left: -525px;
      width: 525px;
      max-width: 100%;
      height: 100%;
      background: #ffffff;
      transition: left 0.3s ease;
      z-index: 2000;
      padding:25px 40px;
      overflow-y: auto;
      box-shadow:-2px 0 6px rgba(0,0,0,0.2);
      font-family: 'Montserrat', sans-serif;
      border: 3px solid #2D8EFF;
      border-radius: 15px;
    }

    .menu-lateral.open {
      left:0;
    }
    .menu-header {
      display:flex;
      justify-content:space-between;
      align-items:center;
    }

    /* .session de redes sociales */
    .menu-section.redes {
      display: flex;
      align-items: center;
      justify-content: space-between;
      margin-top: 25px;
    }
    .menu-section.redes h3 {
      font-size: 20px;
      font-weight: 700;
      color: #061F3E;
      margin: 0;
    }
    .social-icons {
      display: flex;
      gap: 12px;
    }
    .social-icons a {
      font-size: 24px;
      color: #061F3E;
      transition: color 0.3s;
    }
    .social-icons a:hover {
      color: #2D8eFF;
    }
    
    /* Divisores */
    .divider {
      border: none;
      height: 2px;
      background-color: #2F8EFF;
      margin: 25px 0;
    }

    /* Categorias */
    .menu.links.categorias a {
      display: block;
      text-align: center;
      font-size: 24px;
      font-weight: 600;
      color: #061F3E;
      padding: 12px 0;
      text-decoration: none;
      transition: background 0.3s;
    }
    

    .menu-header h2 {
      font-size:24px;
      font-weight: 700;
      color: #061F3E;
      margin:0 auto;
      text-align: center;
    }
    .menu-close{font-size:32px;cursor:pointer;color:#061F3E;}

    .menu-section h3{
      font-family:'Montserrat',sans-serif;
      font-size:20px;font-weight:bold;
      color:#061F3E;margin:20px 0 10px;
    }
    .social-icons{display:flex;gap:12px;margin-bottom:20px;}
    .social-icons a{font-size:24px;color:#061F3E;transition:.3s;}
    .social-icons a:hover{color:#2D8EFF;}

    .separator{border-bottom:2px solid #2F8EFF;margin:15px 0;}

    .menu-links a{
      display:block;text-align:left;
      font-family:'Montserrat',sans-serif;
      font-size:24px;font-weight:600;
      color:#061F3E;padding:10px 0;
      text-decoration:none;transition:.3s;
    }
    .menu-links a:hover{background:#C7F1FF;}

    .menu-footer{
      text-align:center;margin-top:20px;
      font-family:'Inter',sans-serif;
      font-size:20px;color:#2D8EFF;
    }
    
    /* Responsive */
    @media(max-width:768px){
      .nav-links{display:none;} /* se ocultan enlaces en móvil */
      .contenedor-buscador {
        margin-left: auto;
        gap: 5px;
      }
      .buscador {
        min-width: 140px;
        padding: 6px 12px; 
      }
    }

    @media(max-width:480px){
      .buscador {
        min-width: 120px;
      }
      .buscador input {
        font-size: 14px;
      }
    }
  </style>
</head>
<body>

  <!-- Encabezado -->
  <header>
    <div class="logo"><img src="imagenes/logo.png" alt="Logo"></div>
    <div class="botones">
      <a href="login.php" class="btn btn-login">Iniciar Sesión</a>
      <a href="registro.php" class="btn btn-register">Regístrate</a>
    </div>
  </header>

  <!-- Barra de navegación -->
  <nav>
    <div class="nav-left">
      <span class="menu-toggle" onclick="openMenu()">&#9776;</span>
     <div class="nav-links">
    <a href="home.php">Inicio</a>
    <a href="clima1.php">Clima</a>
    <a href="deporte1.php">Deportes</a>
    <a href="educacion1.php">Educación</a>
    <a href="turismo1.php">Turismo</a>
    <a href="denuncia_anonima.php">Denuncias</a>
</div>

    </div>

  <!-- Buscador -->
    <div class="contenedor-buscador">
      <i class="fas fa-search icono-lupa-externo" onclick="focusBuscador()"></i>
      <div class="buscador">
       <input type="text" id="search" placeholder="Buscar" onkeyup="buscar()">
    </div>
  </div>
</nav>

  <!-- Menú lateral desplegable -->
  <div id="menuLateral" class="menu-lateral">
    <div class="menu-header">
      <h2>Comunicado digital</h2>
      <span class="menu-close" onclick="closeMenu()">&times;</span>
    </div>

    <div class="menu-section" style="display: flex; align-items: center; justify-content: space-between; margin-top: 15px;">
      <h3 style="margin: 0; font-family:'Montserrat',sans-serif; font-size:20px; font-weight:bold; color:#061F3E;">Síguenos</h3>
      <div class="social-icons" style="display: flex; gap: 10px; align-items: left;">
        <a href="#"><i class="fab fa-facebook-f"></i></a>
        <a href="#"><i class="fab fa-instagram"></i></a>
        <a href="#"><i class="fab fa-x-twitter"></i></a>
      </div>
    </div>

    <div class="separator"></div>
    <div class="menu-links">
      <a href="politica.php">Política</a>
      <a href="cultura.php">Cultura</a>
      <a href="entretenimiento.php">Entretenimiento</a>
      <a href="social.php">Social</a>
      <a href="salud.php">Salud</a>
      <a href="medioambiente.php">Medio ambiente</a>
      <a href="tendencia.php">Tendencia</a>
    </div>

    <div class="separator"></div>
    <div class="menu-links">
      <a href="publicidad.php">Contratar publicidad</a>
      <a href="terminos.php">Términos y condiciones</a>
      <a href="privacidad.php">Políticas de privacidad</a>
    </div>

    <div class="menu-footer">
      © 2025 Comunicado Digital. Todos los derechos reservados
    </div>
  </div>

  <div id="no-results">No se encontraron coincidencias</div>

  <script>
    function openMenu(){
      document.getElementById("menuLateral").classList.add("open");
    }
    function closeMenu(){
      document.getElementById("menuLateral").classList.remove("open");
    }

    function buscar(){
      let input=document.getElementById("search").value.toLowerCase();
      let links=document.querySelectorAll(".nav-links a, .menu-links a");
      let found=false;
      links.forEach(l=>{
        if(l.textContent.toLowerCase().includes(input)){l.style.display="block";found=true;}
        else{l.style.display="none";}
      });
      document.getElementById("no-results").style.display=found?"none":"block";
    }
  </script>
</body>
</html>
