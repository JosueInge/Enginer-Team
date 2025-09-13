<?php
session_start();
include 'conexion.php';

if (!isset($_SESSION['usuario_id'])) {
    header("Location: login.php");
    exit();
}

$error = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $categoria = $_POST['categoria'] ?? '';
    $titulo = $_POST['titulo'] ?? '';
    $descripcion = $_POST['descripcion'] ?? '';
    $autor = $_POST['autor'] ?? '';
    $fecha = date('Y-m-d H:i:s');
    $usuario_id = $_SESSION['usuario_id'] ?? null;

    $imagen_nombre = null;

    if (isset($_FILES['imagen']) && $_FILES['imagen']['error'] === UPLOAD_ERR_OK) {
        $extension = pathinfo($_FILES['imagen']['name'], PATHINFO_EXTENSION);
        $imagen_nombre = uniqid() . '.' . $extension;
        $ruta_destino = 'imagenes/noticias/' . $imagen_nombre;

        // Crear la carpeta si no existe
        if (!file_exists('imagenes/noticias')) {
            mkdir('imagenes/noticias', 0777, true);
        }

        // Mover la imagen al destino
        if (!move_uploaded_file($_FILES['imagen']['tmp_name'], $ruta_destino)) {
            $errorimagen = "Error al subir la imagen.";
            $imagen_nombre = null;
        }
    }

    $stmt = $conexion->prepare("INSERT INTO propuestas_noticias (categoria, titulo, descripcion, imagen, autor, fecha, usuario_id, estado, bloquear_comentarios) VALUES (?, ?, ?, ?, ?, ?, ?, 'pendiente', ?)");
    $bloquear_comentarios = isset($_POST['bloquear_comentarios']) ? 1 : 0;
    $stmt->bind_param("sssssssi", $categoria, $titulo, $descripcion, $imagen_nombre, $autor, $fecha, $usuario_id, $bloquear_comentarios);

    if ($stmt->execute()) {
        $envioexitoso = "Denuncia enviada correctamente. Será revisada por un administrador.";
    } else {
        $errorenviar = "Error al enviar la denuncia: " . $conexion->error;
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Enviar Denuncia Anónima - Comunicado Digital</title>

<!-- Fuentes -->
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600&family=Poppins:wght@400;600;700&display=swap" rel="stylesheet">

<style>
* { box-sizing: border-box; margin: 0; padding: 0; }
body { font-family: 'Inter', sans-serif; background-color: #f5f5f5; line-height: 1.5; color: #403F48; }
h1,h3 { font-family: 'Poppins', sans-serif; }
a { text-decoration: none; cursor: pointer; }
button { cursor: pointer; border: none; }

header { 
    background-color: #061F3E; 
    color: white; 
    padding: 10px 20px; 
    display: flex; 
    justify-content: space-between; 
    align-items: center; 
}

.logo img { height: 50px; }
nav { display: flex; align-items: center; gap: 20px; }

.volver {
    font-family: 'Poppins', sans-serif;
    font-weight: 600;
    font-size: 24px;
    color: #FFFFFF;
    transition: 0.3s;
}
.volver:hover { color: #1661AC; text-decoration: underline; }

.menu-configuracion { position: relative; display: inline-block; }
.icono-configuracion { width: 30px; height: 30px; cursor: pointer; transition: transform 0.3s; }
.icono-configuracion:hover { transform: rotate(30deg); }
.menu-desplegable {
    display: none; position: absolute; right: 0;
    background-color: white; min-width: 160px;
    box-shadow: 0 8px 16px rgba(0,0,0,0.2); border-radius: 4px;
    z-index: 1001;
}
.menu-desplegable a { 
    color: #333; padding: 12px 16px; display: block; transition: background-color 0.3s; 
}
.menu-desplegable a:hover { background-color: #f1f1f1; }
.menu-configuracion:hover .menu-desplegable { display: block; }

.contenedor-principal {
    max-width: 800px; margin: 40px auto; padding: 20px;
    background-color: #fff; border-radius: 12px; box-shadow: 0 4px 10px rgba(0,0,0,0.1);
}

h1 { font-size: 32px; font-weight: 700; color: #1661AC; text-align: center; margin-bottom: 8px; }
h3 { font-size: 16px; font-weight: 400; text-align: center; margin-bottom: 20px; }

.info-anonimo {
    display: flex; align-items: center; gap: 12px;
    background-color: #ADEBFF; border-radius: 12px; padding: 12px 16px; margin-bottom: 20px;
    font-family: 'Inter', sans-serif; font-size: 16px;
}
.info-anonimo img { width: 20px; height: 20px; flex-shrink: 0; }

.campo { margin-bottom: 20px; position: relative; }
.campo label { display: inline-block; font-family: 'Poppins', sans-serif; font-weight: 600; font-size: 16px; color: #403F48; }
.campo small { font-family:'Inter'; font-size:16px; color:#403F48; margin-left:8px; }
.campo input[type="text"], .campo input[type="date"], .campo textarea, .campo select, .campo input[type="file"] {
    width: 100%; padding: 12px 16px; border: 1px solid #B1B1B1; border-radius: 12px;
    font-family: 'Inter', sans-serif; font-size: 16px; transition: all 0.3s;
}
.campo input:focus, .campo textarea:focus, .campo select:focus { border-color: #2D8EFF; outline: none; box-shadow: 0 0 8px rgba(45,142,255,0.2); }
.campo input:hover, .campo textarea:hover, .campo select:hover { transform: scale(1.02); }

.campo textarea { min-height: 150px; resize: vertical; }

#preview-imagen {
    width: 350px; height: 200px; border: 1px solid #B1B1B1; border-radius: 12px; margin-top: 10px;
    display: none; object-fit: cover; position: relative;
}

.contador {
    position: absolute; right: 16px; bottom: -22px; font-family: 'Inter', sans-serif; font-size: 16px; color: #B1B1B1;
}
.contador.error { color: #E33639; }

.boton-publicar {
    width: 200px; height: 50px;
    background-color: #61C9A8; color: #1B314B;
    font-family: 'Poppins', sans-serif; font-size: 20px; font-weight: 700;
    border-radius: 12px; display: block; margin: 20px auto 0;
    transition: 0.3s;
}
.boton-publicar:hover { background-color: #4CA88C; box-shadow: 0 4px 8px rgba(0,0,0,0.2); }

.error { color: #E33629; text-align: center; margin-top: 5px; font-size: 16px; font-family: 'Inter', sans-serif; }

/* Modal */
.modal-fondo {
    display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%;
    background: rgba(0,0,0,0.5); justify-content: center; align-items: center; z-index: 2000;
}
.modal {
    background: #fff; padding: 24px 32px; border-radius: 12px; max-width: 400px; text-align: center;
}
.modal h2 { font-family: 'Poppins', sans-serif; font-size: 16px; font-weight: 700; color: #403F48; margin-bottom: 12px; }
.modal p { font-family: 'Inter', sans-serif; font-size: 16px; color: #403F48; margin-bottom: 24px; }
.modal button {
    font-family: 'Inter', sans-serif; font-size: 16px; padding: 8px 20px; border-radius: 8px; margin: 0 10px;
}
.btn-confirm { background: #61C9A8; color: #fff; }
.btn-cancel { background: #EB7373; color: #061F3E; }
</style>
</head>

<body>

<header>
    <div class="logo">
        <img src="imagenes/logo.png" alt="logo">
    </div>
    <nav>
        <a class="volver" id="btn-volver">Volver a denuncias</a>
        <div class="menu-configuracion">
            <img src="imagenes/configurar.png" class="icono-configuracion" alt="Configuración">
            <div class="menu-desplegable">
                <a href="actualizar_perfil.php">Configurar Perfil</a>
                <a href="logout.php">Cerrar Sesión</a>
            </div>
        </div>
    </nav>
</header>

<div class="contenedor-principal">
    <h1>Enviar denuncia anónima</h1>
    <h3>Tu denuncia será revisada por los administradores</h3>

    <div class="info-anonimo">
        <img src="imagenes/seguridad.png" alt="seguridad">
        <span>Tu denuncia es 100 % anónima. No pediremos datos personales ni podremos rastrear tu identidad.</span>
    </div>

    <?php if ($error) echo "<div class='error'>" . htmlspecialchars($error) . "</div>"; ?>

    <form action="enviar_noticia.php" method="POST" enctype="multipart/form-data" id="form-denuncia">
        <div class="campo">
            <label for="titulo" class="requerido">Título:</label>
            <input type="text" id="titulo" name="titulo" required placeholder="Escribe el título de la denuncia" maxlength="150">
            <span class="contador" id="contador-titulo">0/150</span>
            <div class="error" id="error-titulo"></div>
        </div>

        <div class="campo">
            <label for="descripcion" class="requerido">Descripción:</label>
            <textarea id="descripcion" name="descripcion" required placeholder="Escribe la descripción de la denuncia" maxlength="3000"></textarea>
            <span class="contador" id="contador-descripcion">0/3000</span>
            <div class="error" id="error-descripcion"></div>
        </div>

        <div class="campo">
            <label for="fecha" class="requerido">Fecha del evento denunciado:</label>
            <input type="date" id="fecha" name="fecha" required value="<?php echo date('Y-m-d'); ?>">
            <div class="error" id="error-fecha"></div>
        </div>

        <div class="campo">
            <label for="imagen">Carga una imagen (opcional)</label>
            <small>Puedes cargar hasta 3 imágenes en formato (.jpeg)</small>
            <input type="file" id="imagen" name="imagen" accept="image/jpeg">
            <img id="preview-imagen" src="#" alt="Vista previa">
        </div>

        <button type="submit" class="boton-publicar">Enviar Denuncia</button>
    </form>
</div>

<!-- Modales -->
<div class="modal-fondo" id="modal-volver">
    <div class="modal">
        <h2>¿Estás seguro de volver a la vista de denuncias?</h2>
        <p>Esta acción cancelará los cambios hechos en el formulario.</p>
        <button class="btn-confirm" id="confirm-volver">Confirmar</button>
        <button class="btn-cancel" id="cancel-volver">Cancelar</button>
    </div>
</div>

<div class="modal-fondo" id="modal-enviar">
    <div class="modal">
        <h2>¿Estás seguro de enviar tu denuncia?</h2>
        <p>Un administrador la revisará y publicará posteriormente.</p>
        <button class="btn-confirm" id="confirm-enviar">Confirmar</button>
        <button class="btn-cancel" id="cancel-enviar">Cancelar</button>
    </div>
</div>

<script>
// Contadores de caracteres
const titulo = document.getElementById('titulo');
const descripcion = document.getElementById('descripcion');
const contadorTitulo = document.getElementById('contador-titulo');
const contadorDescripcion = document.getElementById('contador-descripcion');
const errorTitulo = document.getElementById('error-titulo');
const errorDescripcion = document.getElementById('error-descripcion');

titulo.addEventListener('input', () => {
    contadorTitulo.textContent = ${titulo.value.length}/150;
    errorTitulo.textContent = (titulo.value.length < 10) ? 'La descripción debe tener al menos 10 caracteres.' : '';
    contadorTitulo.classList.toggle('error', titulo.value.length < 10);
});

descripcion.addEventListener('input', () => {
    contadorDescripcion.textContent = ${descripcion.value.length}/3000;
    errorDescripcion.textContent = (descripcion.value.length < 300) ? 'La descripción debe tener al menos 300 caracteres.' : '';
    contadorDescripcion.classList.toggle('error', descripcion.value.length < 300);
});

// Vista previa imagen
document.getElementById('imagen').addEventListener('change', function(e) {
    const file = e.target.files[0];
    if (file && file.type === 'image/jpeg') {
        const reader = new FileReader();
        reader.onload = function(event) {
            const preview = document.getElementById('preview-imagen');
            preview.src = event.target.result;
            preview.style.display = 'block';
        }
        reader.readAsDataURL(file);
    } else if(file) {
        alert('El formato de tu archivo no es admitido. Intenta cargar un archivo (.jpeg).');
        e.target.value = '';
    }
});

// Modales volver y enviar
const modalVolver = document.getElementById('modal-volver');
const modalEnviar = document.getElementById('modal-enviar');

document.getElementById('btn-volver').addEventListener('click', () => modalVolver.style.display = 'flex');
document.getElementById('cancel-volver').addEventListener('click', () => modalVolver.style.display = 'none');
document.getElementById('confirm-volver').addEventListener('click', () => window.location.href = 'vista_denuncias.php');

document.getElementById('form-denuncia').addEventListener('submit', (e)=>{
    e.preventDefault();
    modalEnviar.style.display = 'flex';
});

document.getElementById('cancel-enviar').addEventListener('click', () => modalEnviar.style.display = 'none');
document.getElementById('confirm-enviar').addEventListener('click', () => {
    modalEnviar.style.display = 'none';
    document.getElementById('form-denuncia').submit();
});
</script>
</body>
</html>