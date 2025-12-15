<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>Sobre Nosotros - Comunicado Digital</title>
  <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet">
  <style>
    :root {
      --primary-blue: #1661AC;
      --dark-blue: #061F3E;
      --text-color: #403F48;
      --accent-green: #61C9A8;
      --white: #FFFFFF;
    }
    
    * {
      margin: 0;
      padding: 0;
      box-sizing: border-box;
    }
    
    body {
      font-family: 'Poppins', sans-serif;
      background-color: var(--white);
      color: var(--text-color);
      line-height: 1.6;
    }
    
    /* Header Styles */
    header {
      background-color: var(--dark-blue);
      padding: 15px 30px;
      display: flex;
      justify-content: space-between;
      align-items: center;
    }
    
    .logo img {
      height: 50px;
    }
    
    .back-link {
      font-family: 'Poppins', sans-serif;
      font-size: 24px;
      font-weight: 600;
      color: var(--white);
      text-decoration: none;
      transition: all 0.3s ease;
    }
    
    .back-link:hover {
      color: var(--primary-blue);
      text-decoration: underline;
    }
    
    /* Main Content Styles */
    main {
      max-width: 1250px;
      margin: 0 auto;
      padding: 40px 20px;
    }
    
    h1 {
      font-family: 'Poppins', sans-serif;
      font-size: 32px;
      font-weight: 700;
      color: var(--primary-blue);
      margin-bottom: 10px;
    }
    
    .divider {
      height: 2px;
      background-color: var(--dark-blue);
      width: 100%;
      margin-bottom: 30px;
    }
    
    .intro-section {
      display: flex;
      align-items: flex-start;
      margin-bottom: 40px;
    }
    
    .icon-container {
      margin-right: 20px;
      flex-shrink: 0;
    }
    
    .people-icon {
      width: 40px;
      height: 40px;
      display: flex;
      align-items: center;
      justify-content: center;
    }
    
    .people-icon-svg {
      width: 40px;
      height: 40px;
    }
    
    .intro-text {
      font-family: 'Poppins', sans-serif;
      font-size: 16px;
      font-weight: 600;
      color: var(--text-color);
    }
    
    .description {
      font-family: 'Poppins', sans-serif;
      font-size: 16px;
      color: var(--text-color);
      margin-bottom: 40px;
      text-align: left;
    }
    
    h2 {
      font-family: 'Poppins', sans-serif;
      font-size: 20px;
      font-weight: 700;
      color: var(--primary-blue);
      margin-bottom: 15px;
    }
    
    /* How It Works Section */
    .how-it-works {
      margin-bottom: 40px;
    }
    
    .categories {
      display: flex;
      flex-direction: column;
      gap: 20px;
    }
    
    .category-item {
      display: flex;
      align-items: center;
      gap: 20px;
    }
    
    .category-btn {
      width: 200px;
      height: 50px;
      background: var(--accent-green);
      border-radius: 12px;
      display: flex;
      align-items: center;
      justify-content: center;
      font-family: 'Poppins', sans-serif;
      font-size: 20px;
      font-weight: 700;
      color: var(--primary-blue);
      flex-shrink: 0;
    }
    
    .category-text {
      font-family: 'Poppins', sans-serif;
      font-size: 16px;
      color: var(--text-color);
    }
    
    /* Contact Section */
    .contact {
      margin-bottom: 40px;
    }
    
    .contact-text {
      font-family: 'Poppins', sans-serif;
      font-size: 16px;
      color: var(--text-color);
    }
    
    .email {
      color: var(--primary-blue);
      font-weight: 600;
    }
    
    /* Final Quote */
    .final-quote {
      text-align: center;
      font-family: 'Poppins', sans-serif;
      font-size: 16px;
      font-weight: 600;
      color: var(--primary-blue);
      margin-top: 60px;
      padding: 20px 0;
    }
    
    /* Responsive Styles */
    @media (max-width: 768px) {
      header {
        padding: 15px 20px;
      }
      
      .back-link {
        font-size: 20px;
      }
      
      main {
        padding: 30px 15px;
      }
      
      h1 {
        font-size: 28px;
      }
      
      .category-item {
        flex-direction: column;
        align-items: flex-start;
        gap: 10px;
      }
      
      .category-btn {
        width: 100%;
        max-width: 200px;
      }
    }
    
    @media (max-width: 480px) {
      header {
        flex-direction: column;
        gap: 15px;
      }
      
      .back-link {
        font-size: 18px;
      }
      
      h1 {
        font-size: 24px;
      }
      
      .intro-section {
        flex-direction: column;
        gap: 15px;
      }
      
      .icon-container {
        margin-right: 0;
      }
    }
  </style>
</head>
<body>
  <header>
    <div class="logo">
      <!-- Logo de Comunicado Digital -->
      <img src="imagenes/logo.png" alt="Comunicado Digital">
    </div>
    <a href="home.php" class="back-link">Volver</a>
  </header>

  <main>
    <h1>Sobre nosotros</h1>
    <div class="divider"></div>
    
    <div class="intro-section">
      <div class="icon-container">
        <div class="people-icon">
          <svg class="people-icon-svg" viewBox="0 0 40 40" fill="none" xmlns="http://www.w3.org/2000/svg">
            <!-- Persona 1 (izquierda) -->
            <circle cx="12" cy="15" r="4" fill="#1661AC"/>
            <path d="M12 20C8 20 4 22 4 26V30H20V26C20 22 16 20 12 20Z" fill="#1661AC"/>
            
            <!-- Persona 2 (centro) -->
            <circle cx="20" cy="12" r="4" fill="#1661AC"/>
            <path d="M20 17C16 17 12 19 12 23V27H28V23C28 19 24 17 20 17Z" fill="#1661AC"/>
            
            <!-- Persona 3 (derecha) -->
            <circle cx="28" cy="15" r="4" fill="#1661AC"/>
            <path d="M28 20C24 20 20 22 20 26V30H36V26C36 22 32 20 28 20Z" fill="#1661AC"/>
          </svg>
        </div>
      </div>
      <p class="intro-text">Comunicado Digital es un espacio creado para que la comunidad informe, comparta y se escuche.</p>
    </div>
    
    <div class="description">
      <p>Creemos que la información local tiene poder.<br>
      Por eso, diseñamos esta app para que cualquier persona pueda publicar noticias, avisos o denuncias que ayuden a mantener informada a su comunidad.</p>
    </div>
    
    <section class="purpose">
      <h2>Nuestro propósito</h2>
      <p class="description">Facilitar la comunicación entre vecinos, promover la transparencia y fortalecer el sentido de comunidad a través de la información compartida por todos.</p>
    </section>
    
    <section class="how-it-works">
      <h2>Cómo funciona</h2>
      <div class="categories">
        <div class="category-item">
          <div class="category-btn">Publica</div>
          <p class="category-text">Comparte noticias o denuncias relevantes de tu zona.</p>
        </div>
        <div class="category-item">
          <div class="category-btn">Informa</div>
          <p class="category-text">Ayuda a otros a conocer lo que ocurre en tu comunidad.</p>
        </div>
        <div class="category-item">
          <div class="category-btn">Conecta</div>
          <p class="category-text">Interactúa para compartir información, sugerencias o apoyo entre vecinos.</p>
        </div>
      </div>
    </section>
    
    <section class="contact">
      <h2>Contacto</h2>
      <p class="contact-text">¿Tienes una sugerencia o encontraste un problema?<br>
      Escríbenos a: <span class="email">comunicadoddigital@gmail.com</span></p>
    </section>
    
    <div class="final-quote">
      <p>La información es de todos, y todos tenemos algo que comunicar.</p>
    </div>
  </main>

  <script>
    // Script para hacer que el enlace "Volver" funcione
    document.querySelector('.back-link').addEventListener('click', function(e) {
      e.preventDefault();
      // Redirigir a la página home.php
      window.location.href = 'home.php';
    });
  </script>
</body>
</html>