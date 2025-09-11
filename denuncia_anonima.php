<?php
$categoria_actual = 'denuncias';
include 'menu.php';
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>Publicar Nueva Noticia</title>
  <style>
    @import url('https://fonts.googleapis.com/css2?family=Inter:wght@400;600&family=Poppins:wght@400;600;700&display=swap');

    body {
      font-family: 'Inter', sans-serif;
      background-color: #f5f5f5;
      margin: 0;
      padding: 0;
    }

    header {
      background-color: #061F3E;
      color: white;
      padding: 20px;
      text-align: right;
    }

    header a {
      font-family: 'Poppins', sans-serif;
      font-size: 24px;
      font-weight: 600;
      color: #fff;
      text-decoration: none;
      transition: 0.3s;
    }
    header a:hover {
      text-decoration: underline;
      color: #1661AC;
    }

    .form-container {
      max-width: 700px;
      margin: 40px auto;
      background: #fff;
      padding: 30px;
      border-radius: 12px;
      box-shadow: 0 4px 8px rgba(0,0,0,0.1);
    }

    h1 {
      text-align: center;
      font-family: 'Poppins', sans-serif;
      font-size: 32px;
      font-weight: 700;
      color: #1661AC;
      margin-bottom: 20px;
    }

    label {
      display: block;
      font-family: 'Poppins', sans-serif;
      font-size: 16px;
      font-weight: 600;
      color: #403F48;
      margin-top: 15px;
      margin-bottom: 5px;
    }

    select, input[type="text"], textarea, input[type="date"] {
      width: 100%;
      padding: 10px;
      font-family: 'Inter', sans-serif;
      font-size: 16px;
      border: 1px solid #B1B1B1;
      border-radius: 12px;
      outline: none;
      transition: all 0.3s;
    }
    select {
      background-color: #ADEBFF;
      color: #061F3E;
    }
    input[type="text"]::placeholder,
    textarea::placeholder,
    input[type="date"]::placeholder {
      color: #B1B1B1;
    }
    input:focus, textarea:focus, select:focus {
      border-color: #2D8EFF;
      box-shadow: 0 0 5px rgba(45,142,255,0.3);
      transform: scale(1.01);
    }

    textarea {
      border-radius: 6px;
      resize: vertical;
      min-height: 120px;
    }

    .contador {
      text-align: right;
      font-size: 14px;
      font-family: 'Inter', sans-serif;
      color: #B1B1B1;
      display: none;
    }
    .contador.error {
      color: #E33639;
    }

    .error-msg {
      text-align: center;
      font-family: 'Inter', sans-serif;
      font-size: 16px;
      color: #E33629;
      margin-top: 5px;
      display: none;
    }

    .file-upload {
      border: 1px solid #B1B1B1;
      border-radius: 12px;
      padding: 10px;
      display: flex;
      align-items: center;
      gap: 10px;
      transition: 0.3s;
    }
    .file-upload:hover {
      border-color: #2D8EFF;
    }
    .file-upload button {
      background-color: #ADEBFF;
      border: none;
      border-radius: 8px;
      padding: 6px 12px;
      font-size: 16px;
      color: #061F3E;
      cursor: pointer;
    }
    .file-name {
      font-size: 14px;
      color: #74737C;
    }
    .preview {
      margin-top: 10px;
      display: flex;
      flex-wrap: wrap;
      gap: 10px;
    }
    .preview img {
      width: 350px;
      height: 200px;
      border-radius: 12px;
      border: 1px solid #B1B1B1;
      object-fit: cover;
      position: relative;
    }

    .checkbox {
      margin-top: 20px;
      display: flex;
      align-items: center;
      gap: 8px;
      font-size: 16px;
      color: #403F48;
    }

    .btn-submit {
      display: block;
      width: 200px;
      height: 50px;
      margin: 30px auto 0;
      background: #61C9A8;
      color: #1B314B;
      font-family: 'Poppins', sans-serif;
      font-size: 20px;
      font-weight: 700;
      border: none;
      border-radius: 12px;
      cursor: pointer;
      transition: 0.3s;
    }
    .btn-submit:hover {
      background: #4CA88C;
      box-shadow: 0 4px 8px rgba(0,0,0,0.2);
    }

    /* Modal */
    .modal {
      display: none;
      position: fixed;
      inset: 0;
      background: rgba(0,0,0,0.6);
      display: flex;
      align-items: center;
      justify-content: center;
      z-index: 9999;
    }
    .modal-content {
      background: #fff;
      padding: 20px;
      border-radius: 12px;
      max-width: 400px;
      text-align: center;
      box-shadow: 0 4px 10px rgba(0,0,0,0.3);
    }
    .modal h3 {
      font-family: 'Poppins', sans-serif;
      font-size: 16px;
      color: #403F48;
      font-weight: 700;
      margin-bottom: 10px;
    }
    .modal p {
      font-family: 'Inter', sans-serif;
      font-size: 16px;
      color: #403F48;
      margin-bottom: 20px;
    }
    .modal button {
      border: none;
      border-radius: 8px;
      font-family: 'Inter', sans-serif;
      font-size: 16px;
      padding: 8px 16px;
      margin: 0 10px;
      cursor: pointer;
    }
    .btn-cancel {
      background: #EB7373;
      color: #061F3E;
    }
    .btn-confirm {
      background: #61C9A8;
      color: #fff;
    }
    .alert {
      background: #d4edda;
      color: #155724;
      padding: 10px;
      border-radius: 8px;
      margin: 20px auto;
      text-align: center;
      display: none;
      max-width: 500px;
    }
  </style>
</head>
<body>

<header>
  <a href="#" id="volverNoticias">Volver a noticias</a>
</header>

<div class="form-container">
  <h1>Publicar Nueva Noticia</h1>
  <form id="noticiaForm">
    <label for="categoria">Categoría:</label>
    <select id="categoria" required>
      <option value="">Seleccione una categoría</option>
      <option value="politica">Política</option>
      <option value="sociedad">Sociedad</option>
      <option value="economia">Economía</option>
    </select>

    <label for="titulo">Título:</label>
    <input type="text" id="titulo" placeholder="Escribe el título de la noticia" maxlength="150"/>
    <div id="contadorTitulo" class="contador"></div>
    <div id="errorTitulo" class="error-msg"></div>

    <label for="descripcion">Descripción:</label>
    <textarea id="descripcion" placeholder="Escribe la descripción de tu noticia" maxlength="3000"></textarea>
    <div id="contadorDescripcion" class="contador"></div>
    <div id="errorDescripcion" class="error-msg"></div>

    <label for="fecha">Fecha del hecho:</label>
    <input type="date" id="fecha" placeholder="Selecciona la fecha"/>
    <div id="errorFecha" class="error-msg"></div>

    <label>Carga una imagen (opcional)</label>
    <span style="font-family: Inter; font-size:16px; color:#403F48;">Puedes cargar hasta 3 imágenes en formato (.jpeg).</span>
    <div class="file-upload">
      <button type="button" id="btnUpload">Seleccionar</button>
      <span class="file-name">No se ha seleccionado ningún archivo</span>
      <input type="file" id="imagen" accept="image/jpeg" multiple style="display:none"/>
    </div>
    <div class="preview" id="preview"></div>

    <div class="checkbox">
      <input type="checkbox" id="bloquearComentarios"/> Bloquear comentarios
    </div>

    <button type="submit" class="btn-submit">Publicar noticia</button>
  </form>
</div>

<div class="alert" id="alertaExito">¡Tu denuncia fue enviada a los administradores!</div>

<!-- Modal Volver -->
<div class="modal" id="modalVolver">
  <div class="modal-content">
    <h3>¿Estás seguro de volver a la vista de noticias?</h3>
    <p><b>Esta acción cancelará los cambios hechos en el formulario</b></p>
    <button class="btn-cancel" id="cancelVolver">Cancelar</button>
    <button class="btn-confirm" onclick="window.location.href='denuncias.php'">Confirmar</button>
  </div>
</div>

<!-- Modal Confirmar Publicación -->
<div class="modal" id="modalPublicar">
  <div class="modal-content">
    <h3>¿Estás seguro de publicar tu noticia?</h3>
    <p>Estás a punto de publicar tu noticia. Una vez publicada, estará disponible para todos los lectores.</p>
    <button class="btn-cancel" id="cancelPublicar">Cancelar</button>
    <button class="btn-confirm" id="confirmPublicar">Confirmar</button>
  </div>
</div>

<script>
  const titulo = document.getElementById('titulo');
  const descripcion = document.getElementById('descripcion');
  const contadorTitulo = document.getElementById('contadorTitulo');
  const contadorDescripcion = document.getElementById('contadorDescripcion');
  const errorTitulo = document.getElementById('errorTitulo');
  const errorDescripcion = document.getElementById('errorDescripcion');
  const errorFecha = document.getElementById('errorFecha');
  const form = document.getElementById('noticiaForm');
  const fecha = document.getElementById('fecha');

  titulo.addEventListener('input', () => {
    contadorTitulo.style.display = 'block';
    const length = titulo.value.length;
    contadorTitulo.textContent = `${length}/150`;
    if (length < 10) {
      contadorTitulo.classList.add('error');
      errorTitulo.textContent = "El título debe tener al menos 10 caracteres.";
      errorTitulo.style.display = 'block';
    } else if (length >= 150) {
      errorTitulo.textContent = "Has alcanzado el límite de 150 caracteres.";
      errorTitulo.style.display = 'block';
    } else {
      contadorTitulo.classList.remove('error');
      errorTitulo.style.display = 'none';
    }
  });

  descripcion.addEventListener('input', () => {
    contadorDescripcion.style.display = 'block';
    const length = descripcion.value.length;
    contadorDescripcion.textContent = `${length}/3000`;
    if (length < 300) {
      contadorDescripcion.classList.add('error');
      errorDescripcion.textContent = "La descripción debe tener al menos 300 caracteres.";
      errorDescripcion.style.display = 'block';
    } else if (length >= 3000) {
      errorDescripcion.textContent = "Has alcanzado el límite de 3000 caracteres.";
      errorDescripcion.style.display = 'block';
    } else {
      contadorDescripcion.classList.remove('error');
      errorDescripcion.style.display = 'none';
    }
  });

  document.getElementById('btnUpload').onclick = () => {
    document.getElementById('imagen').click();
  };

  document.getElementById('imagen').addEventListener('change', (e) => {
    const preview = document.getElementById('preview');
    preview.innerHTML = '';
    const files = Array.from(e.target.files).slice(0, 3);
    files.forEach(file => {
      const reader = new FileReader();
      reader.onload = () => {
        const img = document.createElement('img');
        img.src = reader.result;
        preview.appendChild(img);
      };
      reader.readAsDataURL(file);
    });
    document.querySelector('.file-name').textContent = files.length > 0 ? files.map(f => f.name).join(', ') : "No se ha seleccionado ningún archivo";
  });

  // Modal volver
  const modalVolver = document.getElementById('modalVolver');
  document.getElementById('volverNoticias').onclick = (e) => {
    e.preventDefault();
    modalVolver.style.display = 'flex';
  };
  document.getElementById('cancelVolver').onclick = () => {
    modalVolver.style.display = 'none';
  };

  // Modal publicar
  const modalPublicar = document.getElementById('modalPublicar');
  form.addEventListener('submit', (e) => {
    e.preventDefault();
    if (titulo.value.trim() === "") {
      errorTitulo.textContent = "El título es obligatorio.";
      errorTitulo.style.display = 'block';
      return;
    }
    if (descripcion.value.trim() === "") {
      errorDescripcion.textContent = "La descripción es obligatoria.";
      errorDescripcion.style.display = 'block';
      return;
    }
    if (fecha.value === "") {
      errorFecha.textContent = "La fecha es obligatoria.";
      errorFecha.style.display = 'block';
      return;
    }
    modalPublicar.style.display = 'flex';
  });
  document.getElementById('cancelPublicar').onclick = () => {
    modalPublicar.style.display = 'none';
  };
  document.getElementById('confirmPublicar').onclick = () => {
    modalPublicar.style.display = 'none';
    document.getElementById('alertaExito').style.display = 'block';
    setTimeout(() => {
      window.location.href = 'denuncias.php';
    }, 2000);
  };
</script>

</body>
</html>
