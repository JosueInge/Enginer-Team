<?php 
session_start();
include 'conexion.php'; 

// Validar si el usuario no está logueado
if (!isset($_SESSION['usuario_id'])) { 
  header("Location: login.php"); 
  exit(); 
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Enviar Denuncia - Comunicado Digital</title>
  <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600;700&family=Inter:wght@400;600&display=swap" rel="stylesheet">
  <style>
    * { box-sizing: border-box; margin: 0; padding: 0; }
    body { font-family: 'Inter', sans-serif; background-color: #f7f7f7; color: #403F48; }

    /* ----- ENCABEZADO ----- */
    header {
      background-color: #061F3E;
      color: #fff;
      padding: 15px 30px;
      display: flex;
      justify-content: space-between;
      align-items: center;
    }
    header img {
      height: 50px;
    }
    header a {
      font-family: 'Poppins', sans-serif;
      font-size: 20px;
      font-weight: 600;
      color: #fff;
      text-decoration: none;
      transition: all 0.3s ease;
    }
    header a:hover {
      color: #1661AC;
      text-decoration: underline;
    }

    /* ----- FORMULARIO ----- */
    .contenedor-principal {
      max-width: 900px;
      margin: 40px auto;
      padding: 30px;
      background: #fff;
      border-radius: 12px;
      box-shadow: 0 4px 10px rgba(0,0,0,0.1);
    }
    h1 {
      font-family: 'Poppins', sans-serif;
      font-size: 28px;
      font-weight: 700;
      color: #1661AC;
      text-align: center;
      margin-bottom: 8px;
    }
    h2 {
      font-family: 'Inter', sans-serif;
      font-size: 16px;
      text-align: center;
      margin-bottom: 25px;
      color: #555;
    }
    .campo {
      margin-bottom: 20px;
    }
    .campo label {
      display: block;
      font-family: 'Poppins', sans-serif;
      font-size: 15px;
      font-weight: 600;
      margin-bottom: 6px;
      color: #403F48;
    }
    .campo input, 
    .campo textarea {
      width: 100%;
      font-family: 'Inter', sans-serif;
      font-size: 15px;
      padding: 12px 14px;
      border: 1px solid #B1B1B1;
      border-radius: 10px;
      outline: none;
      transition: all 0.3s ease;
    }
    .campo input:focus, 
    .campo textarea:focus {
      border-color: #2D8EFF;
      box-shadow: 0 0 5px rgba(45,142,255,0.4);
    }
    .campo textarea {
      resize: vertical;
      min-height: 120px;
    }

    /* ----- BOTÓN ----- */
    .boton-publicar {
      display: block;
      width: 200px;
      height: 50px;
      margin: 25px auto 0;
      background-color: #61C9A8;
      color: #1B314B;
      font-family: 'Poppins', sans-serif;
      font-size: 18px;
      font-weight: 700;
      border: none;
      border-radius: 12px;
      cursor: pointer;
      transition: all 0.3s ease;
    }
    .boton-publicar:hover {
      background-color: #4ABF97;
      transform: scale(1.05);
    }
  </style>
</head>
<body>
  <header>
    <!-- Logo blanco corregido -->
    <img src="imagenes/logo-blanco.png" alt="Logo Comunicado Digital">
    <a href="denuncias.php">Volver a denuncias</a>
  </header>

  <div class="contenedor-principal">
    <h1>Enviar Denuncia</h1>
    <h2>Completa el formulario para enviar tu denuncia</h2>

    <form action="procesar_denuncia.php" method="POST" enctype="multipart/form-data">
      <!-- Campo título -->
      <div class="campo">
        <label for="titulo">Título de la denuncia</label>
        <input type="text" id="titulo" name="titulo" maxlength="100" required>
      </div>

      <!-- Campo descripción -->
      <div class="campo">
        <label for="descripcion">Descripción</label>
        <textarea id="descripcion" name="descripcion" maxlength="500" required></textarea>
      </div>

      <!-- Campo fecha -->
      <div class="campo">
        <label for="fecha">Fecha del incidente</label>
        <input type="date" id="fecha" name="fecha" required>
      </div>

      <!-- Campo imágenes -->
      <div class="campo">
        <label for="imagenes">Cargar imágenes (máx. 3, solo .jpeg)</label>
        <input type="file" id="imagenes" name="imagenes[]" accept="image/jpeg" multiple>
      </div>

      <button type="submit" class="boton-publicar">Enviar Denuncia</button>
    </form>
  </div>
</body>
</html>
