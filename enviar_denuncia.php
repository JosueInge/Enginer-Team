<?php
session_start();
?>
<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Formulario de Denuncia Anónima</title>
<link href="https://fonts.googleapis.com/css2?family=Poppins:wght@600&display=swap" rel="stylesheet">
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600&display=swap" rel="stylesheet">
<link href="https://fonts.googleapis.com/icon?family=Material+Icons" rel="stylesheet">
<style>
body { margin:0; font-family:'Poppins', sans-serif; background:#F5F5F5; }
header{ background:#061F3E; color:#fff; padding:16px 24px; display:flex; justify-content:space-between; align-items:center; }
header h1{ margin:0; font-size:24px; font-weight:600; }
header a.volver{
    font-size:24px;
    font-weight:600;
    color:#FFFFFF;
    text-decoration:none;
    transition:all 0.2s;
}
header a.volver:hover{
    color:#1661AC;
    text-decoration:underline;
}

/* Formulario */
form { max-width:800px; margin:24px auto; background:#fff; padding:24px; border-radius:12px; box-shadow:0 2px 8px rgba(0,0,0,0.1); }
form h2 { font-size:20px; color:#1661AC; text-align:center; margin-bottom:16px; }
.form-group { margin-bottom:16px; position:relative; }
label { display:block; margin-bottom:8px; font-weight:600; }
input[type="text"], input[type="date"], textarea, input[type="file"] {
    width:100%; padding:12px 40px 12px 12px; border:1px solid #ccc; border-radius:8px; font-family:'Inter', sans-serif;
    transition:0.2s all;
}
input[type="text"]:focus, input[type="date"]:focus, textarea:focus, input[type="file"]:focus { transform: scale(1.02); box-shadow:0 2px 8px rgba(0,0,0,0.1); }
.error { color:#E33629; font-family:'Inter', sans-serif; font-size:16px; text-align:center; margin-top:4px; }

/* Icono Security */
.material-icons.security-icon { 
    position:absolute; 
    right:12px; 
    top:36px; 
    color:#1661AC; 
    pointer-events:none;
}

/* Botón enviar */
button#enviar { 
    width:200px; height:50px; background:#61C9A8; color:#1B314B; font-size:20px; font-weight:bold;
    border:none; border-radius:12px; cursor:pointer; transition:0.2s all; display:block; margin:16px auto; min-height:48px;
}
button#enviar:hover{ background:#4CA88C; }

/* Modal general */
.modal-backdrop{
    display:none;
    position:fixed; top:0; left:0;
    width:100%; height:100%;
    background:rgba(0,0,0,0.45);
    justify-content:center; align-items:center;
    z-index:1000;
}
.modal{
    background:#fff;
    padding:20px 28px;
    border-radius:12px;
    text-align:center;
    max-width:400px;
    width:90%;
}
.modal h3{ font-size:18px; margin-bottom:12px; color:#403F48; font-weight:600; }
.modal p{ font-size:16px; margin-bottom:20px; color:#403F48; font-family:'Inter',sans-serif; }
.modal button{ padding:10px 20px; border:none; border-radius:8px; font-size:16px; cursor:pointer; margin:0 6px; }
.modal .btn-confirm{ background:#61C9A8; color:#061F3E; font-weight:600; }
.modal .btn-cancel{ background:#ADEBFF; color:#061F3E; font-weight:600; }
</style>
</head>
<body>

<header>
    <h1>Comunicado Digital</h1>
    <a href="javascript:void(0);" class="volver" id="btnVolver">Volver a Denuncias</a>
</header>

<form id="denunciaForm" enctype="multipart/form-data">
    <h2>Formulario de Denuncia Anónima</h2>

    <div class="form-group">
        <label for="titulo">Título</label>
        <input type="text" id="titulo" name="titulo" placeholder="Ingresa el título">
        <span class="error" id="errorTitulo"></span>
    </div>

    <div class="form-group">
        <label for="descripcion">Descripción</label>
        <textarea id="descripcion" name="descripcion" rows="6" placeholder="Ingresa la descripción"></textarea>
        <span class="error" id="errorDescripcion"></span>
    </div>

    <div class="form-group">
        <label for="fecha_evento">Fecha del Evento</label>
        <input type="date" id="fecha_evento" name="fecha_evento">
        <span class="error" id="errorFecha"></span>
    </div>

    <div class="form-group">
        <label for="imagen">Cargar Imagen (Solo JPEG)</label>
        <input type="file" id="imagen" name="imagen" accept=".jpeg">
        <span class="material-icons security-icon">security</span>
        <span class="error" id="errorImagen"></span>
    </div>

    <button type="button" id="enviar">Enviar Denuncia</button>
</form>

<!-- Modal Volver -->
<div class="modal-backdrop" id="modalVolver">
    <div class="modal">
        <h3>¿Deseas volver a la vista de denuncias?</h3>
        <p>Si confirmas, regresarás a la pantalla de denuncias.</p>
        <button class="btn-cancel" id="cancelVolver">Cancelar</button>
        <button class="btn-confirm" id="confirmVolver">Confirmar</button>
    </div>
</div>

<!-- Modal Enviar -->
<div class="modal-backdrop" id="modalEnviar">
    <div class="modal">
        <h3>¿Estás seguro de enviar tu denuncia?</h3>
        <p>Esta acción enviará tu denuncia para que los administradores puedan revisarla.</p>
        <button class="btn-cancel" id="cancelEnviar">Cancelar</button>
        <button class="btn-confirm" id="confirmEnviar">Confirmar</button>
    </div>
</div>

<!-- Modal error imagen -->
<div class="modal-backdrop" id="modalErrorImagen">
    <div class="modal">
        <h3>El formato de tu archivo no es admitido.</h3>
        <p>Intenta cargar un archivo (.jpeg).</p>
        <button class="btn-confirm" id="okImagen">De acuerdo</button>
    </div>
</div>

<script>
const btnVolver = document.getElementById('btnVolver');
const modalVolver = document.getElementById('modalVolver');
const cancelVolver = document.getElementById('cancelVolver');
const confirmVolver = document.getElementById('confirmVolver');

const btnEnviar = document.getElementById('enviar');
const modalEnviar = document.getElementById('modalEnviar');
const cancelEnviar = document.getElementById('cancelEnviar');
const confirmEnviar = document.getElementById('confirmEnviar');

const modalErrorImagen = document.getElementById('modalErrorImagen');
const okImagen = document.getElementById('okImagen');

btnVolver.addEventListener('click', ()=>{ modalVolver.style.display='flex'; });
cancelVolver.addEventListener('click', ()=>{ modalVolver.style.display='none'; });
confirmVolver.addEventListener('click', ()=>{ window.location.href='denuncias.php'; });

btnEnviar.addEventListener('click', ()=>{
    let titulo = document.getElementById('titulo').value.trim();
    let descripcion = document.getElementById('descripcion').value.trim();
    let fecha = document.getElementById('fecha_evento').value;
    let imagen = document.getElementById('imagen').files[0];

    let error = false;

    document.getElementById('errorTitulo').textContent='';
    document.getElementById('errorDescripcion').textContent='';
    document.getElementById('errorFecha').textContent='';
    document.getElementById('errorImagen').textContent='';

    if(titulo===''){ document.getElementById('errorTitulo').textContent='El titulo es obligatorio'; error=true;}
    else if(titulo.length<10){ document.getElementById('errorTitulo').textContent='La descripción debe tener al menos 10 caracteres.'; error=true;}
    else if(titulo.length>150){ document.getElementById('errorTitulo').textContent='Haz alcanzado el límite de 150 caracteres.'; error=true;}

    if(descripcion===''){ document.getElementById('errorDescripcion').textContent='La descripción es obligatoria.'; error=true;}
    else if(descripcion.length<300){ document.getElementById('errorDescripcion').textContent='La descripción debe tener al menos 300 caracteres.'; error=true;}
    else if(descripcion.length>3000){ document.getElementById('errorDescripcion').textContent='Haz alcanzado el límite de 3000 caracteres.'; error=true;}

    if(fecha===''){ document.getElementById('errorFecha').textContent='La fecha es obligatoria'; error=true;}

    if(imagen && !imagen.name.endsWith('.jpeg')){ modalErrorImagen.style.display='flex'; error=true; }

    if(!error){ modalEnviar.style.display='flex'; }
});

cancelEnviar.addEventListener('click', ()=>{ modalEnviar.style.display='none'; });
confirmEnviar.addEventListener('click', ()=>{
    modalEnviar.style.display='none';
    alert('¡Tu denuncia fue enviada a los administradores!');
    window.location.href='denuncias.php';
});

okImagen.addEventListener('click', ()=>{ modalErrorImagen.style.display='none'; });
</script>

</body>
</html>
