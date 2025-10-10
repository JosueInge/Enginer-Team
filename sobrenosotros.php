<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>Acerca de Código Digital</title>
  <style>
    body {
      margin: 0;
      font-family: Arial, sans-serif;
      background-color: #fff; 
    }

    header { 
      background-color: #0d5c9b;
      color: white;
      padding: 15px 30px;
      display: flex;
      justify-content: space-between;
      align-items: center;
    }

    .logo {
      font-size: 20px;
      font-weight: bold;
    }
    .logo img {
      height: 50px;
      margin-right: 10px;
    }

    .redes {
      margin-left: 500px;
    }
    .informacion {
      margin-right: 45px;
      font-family: 'Open Sans Regular';
    }

    main {
      padding: 40px;
    }

    h2, h4 {
      margin-bottom: 10px;
      font-family: 'Open Sans Bold';
    }

    hr {
      border: 1px solid black;
      width: 900px;
      margin-bottom: 20px;
      margin-left: 5px;
    }

    .descripcion p {
      max-width: 600px;
      color: #555;
      margin-bottom: 60px;
      font-family: 'Open Sans Regular';
    }

    .contactos {
      margin-top: 40px;
    }

    .contactos h3 {
      margin-bottom: 10px;
      font-family: 'Open Sans Bold';
    }

    .contactos img {
      width: 32px;
      height: 32px;
      margin-right: 10px;
      vertical-align: middle;
    }
    a {
  text-decoration: none;
  }
  </style>
</head>
<body>

  <header>
      <div class="logo">
        <img src="imagenes/logo.png" alt="logo">
      </div>
      <div class="redes">
        <a target="_blank" href="https://www.instagram.com/"><img src="imagenes/instagram.png" height="35"/></a>
        <a target="_blank" href="https://www.facebook.com/"><img src="imagenes/facebook.png" height="35" style="margin-left: 12px;"/></a>
        <a target="_blank" href="https://x.com/?lang=es"><img src="imagenes/X.png" height="35" style="margin-left: 10px;"/></a>
     </div>  
      <div class="informacion">
        <a href="#" style="margin-left: 15px; color: #ffffff;">Contacto</a>
        <a href="sobrenosotros.php" style="margin-left: 15px; color: #ffffff;">Sobre Nosotros</a>
        <a href="login.php" style="margin-left: 15px; color: #ffffff;">Iniciar Sesión</a>
      </div>
  </header>

  <main>
    <h2>Acerca de Código Digital</h2>
    <hr />
    <div class="descripcion">
      <h4>¿Quiénes somos?</h4>
      <p>Código Digital es una plataforma en donde los pobladores pueden difundir noticias de su localidad.</p>
    </div>

    <a class="contactos">
      <h3>Contactos</h3>
      <a target="_blank" href="https://www.instagram.com/"><img src="https://cdn-icons-png.flaticon.com/512/2111/2111463.png"height="35"/></a>
      <a target="_blank" href="https://www.facebook.com/"><img src="https://cdn-icons-png.flaticon.com/512/733/733547.png"height="35"/></a>
      <a <a target="_blank" href="https://x.com/?lang=es"><img src="https://cdn-icons-png.flaticon.com/512/5968/5968958.png"height="35"/></a>
    </div>
  </main>

</body>
</html>
