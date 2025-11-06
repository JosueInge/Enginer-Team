<?php
session_start();
include 'conexion.php';
include 'menu.php';
include 'chatbot.php';

// Obtener ID de la noticia a editar
$id = isset($_GET['id']) ? intval($_GET['id']) : 0;
$sql = "SELECT * FROM noticias WHERE id = ?";
$stmt = $conexion->prepare($sql);
$stmt->bind_param("i", $id);
$stmt->execute();
$result = $stmt->get_result();
$noticia = $result->fetch_assoc();

if (!$noticia) {
    echo "<h2>Noticia no encontrada</h2>";
    exit;
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Editar Noticia</title>
<link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600;700&family=Inter:wght@400;600&display=swap" rel="stylesheet">
<style>
body {
  font-family: 'Inter', sans-serif;
  background-color: #f4f6f9;
  margin: 0;
  padding: 0;
}

/* ENCABEZADO */
header {
  background-color: #061F3E;
  display: flex;
  justify-content: space-between;
  align-items: center;
  padding: 15px 40px;
}
header img {
  height: 50px;
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

/* CONTENEDOR FORMULARIO */
.form-container {
  max-width: 900px;
  margin: 50px auto;
  background-color: #fff;
  padding: 40px 60px;
  border-radius: 16px;
  box-shadow: 0 4px 12px rgba(0,0,0,0.1);
}

.form-container h1 {
  text-align: center;
  font-family: 'Poppins', sans-serif;
  font-size: 32px;
  font-weight: 700;
  color: #1661AC;
  margin-bottom: 30px;
}

/* ETIQUETAS */
label {
  font-family: 'Poppins', sans-serif;
  font-size: 16px;
  font-weight: 600;
  color: #403F48;
  display: block;
  margin-bottom: 5px;
}

/* CAMPOS */
select, input[type="text"], textarea, input[type="date"] {
  width: 100%;
  font-family: 'Inter', sans-serif;
  font-size: 16px;
  padding: 10px;
  border-radius: 12px;
  border: 1px solid #B1B1B1;
  margin-bottom: 15px;
  transition: 0.2s;
  color: #061F3E;
  background-color: #fff;
}
select:focus, input:focus, textarea:focus {
  border-color: #2D8EFF;
  outline: none;
  box-shadow: 0 0 6px rgba(45,142,255,0.3);
}
input:hover, textarea:hover, select:hover {
  transform: scale(1.01);
  box-shadow: 0 0 8px rgba(0,0,0,0.05);
}

/* PLACEHOLDER COLOR */
::placeholder { color: #B1B1B1; }

/* ERRORES */
.mensaje-error {
  background-color: #F8D7DA;
  border: 1px solid #F5C2C7;
  color: #842029;
  font-family: 'Inter', sans-serif;
  font-size: 16px;
  border-radius: 8px;
  padding: 5px 10px;
  margin-top: -10px;
  margin-bottom: 10px;
  display: none;
}

/* CONTADOR */
.contador {
  position: relative;
  text-align: right;
  font-family: 'Inter', sans-serif;
  font-size: 14px;
  color: #B1B1B1;
  margin-top: -10px;
  margin-bottom: 10px;
  display: none;
}
.contador.error { color: #E33639; }

/* IMAGEN */
.subir-archivo {
  border: 1px solid #B1B1B1;
  border-radius: 12px;
  padding: 10px;
  display: flex;
  align-items: center;
  gap: 10px;
}
.subir-archivo button {
  background-color: #ADEBFF;
  color: #061F3E;
  border: none;
  padding: 6px 12px;
  border-radius: 8px;
  font-size: 16px;
  cursor: pointer;
}
.subir-archivo button:disabled {
  background-color: #B1B1B1;
  cursor: not-allowed;
}
.subir-archivo span {
  font-size: 14px;
  color: #74737C;
}

/* VISTA PREVIA */
.vista-previa {
  margin-top: 15px;
  display: flex;
  gap: 10px;
  flex-wrap: wrap;
}
.vista-previa img {
  width: 350px;
  height: 200px;
  object-fit: cover;
  border-radius: 12px;
  border: 1px solid #B1B1B1;
  position: relative;
}
.vista-previa button {
  position: absolute;
  top: 5px;
  right: 5px;
  background: none;
  border: none;
  color: #E33629;
  font-size: 20px;
  cursor: pointer;
}

/* BOTONES */
.botones {
  display: flex;
  justify-content: space-between;
  margin-top: 30px;
}
.boton {
  width: 200px;
  height: 50px;
  font-family: 'Poppins', sans-serif;
  font-size: 20px;
  font-weight: 700;
  border-radius: 12px;
  border: none;
  cursor: pointer;
  transition: 0.3s;
}
.boton-cancelar {
  background-color: #E85D5D;
  color: #1B314B;
}
.boton-confirmar {
  background-color: #61C9A8;
  color: #1B314B;
}
.boton:hover {
  transform: scale(1.03);
  box-shadow: 0 0 8px rgba(0,0,0,0.2);
}

/* Modal */
.modal {
    display: none;
    position: fixed;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background: rgba()
}
.modal-contenido {
    background: white;
    padding: 30px;
    border-radius: 12px;
    text-align: center;
    width: 400px;
}
.modal-contenido h3 {
    font-family: 'Poppins', sans-serif;
    color: #403F48;
    font-size: 16px;
    font-weight: 700;
    margin-bottom: 20px;
}
.modal-botones {
    display: flex;
    justify-content: space-between;
}
.modal-botones button {
    font-family: 'Inter', sans-serif;
    font-size: 16px;
    border-radius: 8px;
    border: none;
    cursor: pointer;
    padding: 8px 20px;
}
.btn-cancelar {
    background-color: #EB7373;
    color: #061F373E;
}
.btn-confirmar {
    background-color: #61C9A8;
    color: #fff;
}
</style>
</head>
<body>

<header>
    <img src="imagenes/logo.png" alt="Logo">
    <a href="#" id="volverDetalles">Volver a detalles</a>
</header>

<div class="form-container">
    <h1>Editar Noticia</h1>
    <form id="formEditarNoticia">
        <label for="categoria">Categoria:</labbel>
        <select id="categoria" name="categoria">
            <option value="">Seleccione una categoria</option>
            <option value="Clima" <?= $noticia['categoria']=='Clima'?'selected':''; ?>>Clima</option>
            <option value="Deporte" <?= $noticia['categoria']=='Deporte'?'selected':''; ?>>Deporte</option>
</select>

<label for="titulo">Titulo:</label>
<input ftype="text" id="titulo" name="titulo" maxlength="150" placeholder="Escribe el titulo de la noticia" value="<?= htmlspecialchars($noticia['titulo']) ?>">
<div class="contador" id="contadorTitulo">0/150</div>
<div class="mensaje-error" id="errorTitulo"></div>

<label for="descripcion">Descripcion:</label>
<textarea id="descripcion" name="descripcion" rows="6" maxlength="3000" placeholder="Escribe la descripcion de tu noticia"><?= htmlspecialchars($noticia['descripcion']) ?></textarea>
<div class="contador" id="contadorDescripcion">0/3000</div>
<div class="mensaje-error" id="errorDescripcion"></div>

<label>Carga una imagen (opcional)</label>
<span>Puedes cargar hasta 3 imagenes en formato (.jpeg).</span>
<div class="subir-archivo">
    <button type="button" id="btnArchivo">Subir archivo</button>
    <input type="file" id="archivo" name="archivo[]" accept=".jpeg" multiple style="display:none">
    <span id="textoArchivo">No se ha seleccionado ningun archivo</span>
</div>
<div class="vista-previa" id="vistaPrevia"></div>

<div class="botones">
    <button type="button" class="boton boton-cancelar" id="cancelarBtn">Cancelar</burron>
    <button type="submit" class="boton boton-confirmar" id="confirmarBtn">Confirmar</burron>
    <div>
</form>
</div>

<!-- Modales -->
 <div class="modal" id="modalVolver">
    <div class="modal-contenido">
        <h3>Estas seguro de volver a los dettales de esta noticia?</h3>
        <div class="modal-botones">
            <button class="btn-cancelar" onclick="cerrarModal('modalVolver')">Cancelar</button>
            <button class="btn-confirmar" onclick="location.href='ver_noticia.php?id=<?= $noticia['id'] ?>'">Confirmar</button>
        </div>
    </div>
</div>

<div class="modal" id="modalCancelar">
    <div class="modal-contenido">
        <h3>Estas seguro de cancelar los cmbios realizados de esta noticia?</h3>
        <div class="btn-cancelar" onclick="cerrarModal('modalCancelar')">Cancelar</button>
        <button class="btn-cancelar" onclick="location.href='ver_noticia.php?id=<?= $noticia['id'] ?>'">Confirmar</button>
    </div>
</div>
</div>

<div class="modal" id="modalConfirmar">
    <div class="modal-contenido">
        <h3>Estas seguro de guardar los cambios de esta noticia?</h3>
        <div class="modal-botones">
            <button class="btn-cancelar" onclick="cerrarModal('modalConfirmar')">Cancelar</button>
            <button class="btn-confirmar" onclick="alert('Los cambios fueron guardados correctamente!');location.href='ver_noticia.php?id=<?= $noticia['id'] ?>'">Confirmar</button>
        </div>
    </div>
</div>

<script>
document.getElementById('volverDetalles').onclick = () => {
    document.getElementById('modalVolver').style.display = 'flex';
};
document.getElementById('cancelarBtn').onclick = () => {
    document.getElementById('modalCancelar').style.display = 'flex';
};
document.getElementById('confirmarBtn').onclick = (e) => {
    e.preventDefault();
    document.getElementById('modalConfirmar').style.display = 'flex';
};
function cerrarModal(id) {
    document.getElementById(id).style.display = 'none';
}

// Contadores 
function actualizarContador(idCampo, idContador, max) {
    const campo = document.getElementById(idCampo);
    const contador = document.getElementById(idContador);
    campo.addEvenetListener('input' () => {
        contador.style.display = 'block';
        const length = campo.value.length;
        contador.textContent = `${lenth}/${max}`;
        contador.classList.toggle('error', (idCampo==='titulo' && length<10) || (idCampo==='descripcion' && length<300));
    });
}
actualizarContador('titulo', 'contadorTitulo', 150);
actualizarContador('descripcion', 'contadorDescripcion', 3000);
</script>
</body>
</html>
