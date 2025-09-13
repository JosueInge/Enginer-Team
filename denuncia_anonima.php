<?php
$categoria_actual = 'denuncias';
include 'menu.php';
?>
<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8" />
<meta name="viewport" content="width=device-width, initial-scale=1.0"/>
<title>Enviar Denuncia Anónima</title>
<style>
@import url('https://fonts.googleapis.com/css2?family=Inter:wght@400;600&family=Poppins:wght@400;600;700&display=swap');

body {
    font-family: 'Inter', sans-serif;
    background-color: #f5f5f5;
    margin: 0;
    padding: 0;
}

/* ===== Encabezado ===== */
header {
    background-color: #061F3E;
    color: white;
    padding: 15px 20px;
    display: flex;
    justify-content: space-between;
    align-items: center;
}
header .logo {
    display: flex;
    align-items: center;
}
header .logo img {
    height: 40px;
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
    color: #1661AC;
    text-decoration: underline;
}

/* ===== Formulario ===== */
.form-container {
    max-width: 700px;
    margin: 40px auto 60px auto;
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
    margin-bottom: 10px;
}

h2.subtitulo {
    text-align: center;
    font-family: 'Inter', sans-serif;
    font-size: 16px;
    color: #403F48;
    margin-bottom: 20px;
}

.mensaje-anonimo {
    display: flex;
    align-items: center;
    background: #ADEBFF;
    padding: 12px 16px;
    border-radius: 12px;
    font-family: 'Inter', sans-serif;
    color: #403F48;
    margin-bottom: 20px;
    gap: 10px;
}

.mensaje-anonimo i {
    font-size: 20px;
}

/* ===== Etiquetas y campos ===== */
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
    padding: 12px 16px;
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
input::placeholder, textarea::placeholder, input[type="date"]::placeholder {
    color: #B1B1B1;
}
input:focus, textarea:focus, select:focus {
    border-color: #2D8EFF;
    box-shadow: 0 0 5px rgba(45,142,255,0.3);
    transform: scale(1.01);
}

textarea {
    resize: vertical;
    min-height: 120px;
}

.contador {
    text-align: right;
    font-size: 16px;
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

/* ===== Carga de archivos ===== */
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

/* ===== Checkbox ===== */
.checkbox {
    margin-top: 20px;
    display: flex;
    align-items: center;
    gap: 8px;
    font-size: 16px;
    color: #403F48;
}

/* ===== Botón ===== */
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

/* ===== Modal ===== */
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

/* ===== Alerta ===== */
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
  <div class="logo">
    <img src="imagenes/logo-blanco.png" alt="Logo">
  </div>
  <a href="#" id="volverNoticias">Volver a denuncias</a>
</header>

<div class="form-container">
  <h1>Enviar denuncia anónima</h1>
  <h2 class="subtitulo">Tu denuncia será revisada por los administradores</h2>
  <div class="mensaje-anonimo">
    <i>🔒</i>
    Tu denuncia es 100 % anónima. No pediremos datos personales ni podremos rastrear tu identidad.
  </div>
  <form id="noticiaForm">
    <label for="titulo">Título:</label>
    <input type="text" id="titulo" placeholder="Escribe el título de la denuncia" maxlength="150"/>
    <div id="contadorTitulo" class="contador"></div>
    <div id="errorTitulo" class="error-msg"></div>

    <label for="descripcion">Descripción:</label>
    <textarea id="descripcion" placeholder="Escribe la descripción de la denuncia" maxlength="3000"></textarea>
    <div id="contadorDescripcion" class="contador"></div>
    <div id="errorDescripcion" class="error-msg"></div>

    <label for="fecha">Fecha del evento denunciado:</label>
    <input type="date" id="fecha"/>
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

    <button type="submit" class="btn-submit">Enviar Denuncia</button>
  </form>
</div>

<div class="alert" id="alertaExito">¡Tu denuncia fue enviada a los administradores!</div>

<!-- Modal Volver -->
<div class="modal" id="modalVolver">
  <div class="modal-content">
    <h3>¿Estás seguro de volver a la vista de denuncias?</h3>
    <p><b>Esta acción cancelará los cambios hechos en el formulario</b></p>
    <button class="btn-cancel" id="cancelVolver">Cancelar</button>
    <button class="btn-confirm" onclick="window.location.href='denuncias.php'">Confirmar</button>
  </div>
</div>

<!-- Modal Confirmar Publicación -->
<div class="modal" id="modalPublicar">
  <div class="modal-content">
    <h3>¿Estás seguro de enviar tu denuncia?</h3>
    <p>Un administrador la revisará
