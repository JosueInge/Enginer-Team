<?php
session_start();
include 'conexion.php';

// Obtener ID de denuncia y tabla de origen
$id_noticia = isset($_GET['id']) ? intval($_GET['id']) : 0;
$tabla_origen = isset($_GET['tabla']) ? $_GET['tabla'] : 'propuestas_denuncias';

// Validar que la tabla sea una de las permitidas
if (!in_array($tabla_origen, ['propuestas_denuncias', 'propuestas_denuncias_anonima'])) {
    $tabla_origen = 'propuestas_denuncias';
}

// Obtener datos de la denuncia de la tabla correspondiente
$sql = "SELECT * FROM " . $tabla_origen . " WHERE id = ?";

$stmt = $conexion->prepare($sql);
$stmt->bind_param("i", $id_noticia);
$stmt->execute();
$resultado = $stmt->get_result();
$noticia = $resultado->fetch_assoc();

// Si no existe la denuncia
if (!$noticia) {
    echo "<h2>Denuncia no encontrada</h2>";
    exit;
}

// Manejar acciones AJAX
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Publicar denuncia
    if (isset($_POST['publicar']) && isset($_POST['id_noticia']) && isset($_POST['tabla_origen'])) {
        $id_publicar = intval($_POST['id_noticia']);
        $tabla_publicar = $_POST['tabla_origen'];
        
        // Validar que la tabla sea una de las permitidas
        if (!in_array($tabla_publicar, ['propuestas_denuncias', 'propuestas_denuncias_anonima'])) {
            echo json_encode(["success" => false, "error" => "Tabla no válida"]);
            exit();
        }
        
        // Obtener datos de la propuesta
        $stmt = $conexion->prepare("SELECT * FROM " . $tabla_publicar . " WHERE id = ?");
        $stmt->bind_param("i", $id_publicar);
        $stmt->execute();
        $propuesta = $stmt->get_result()->fetch_assoc();
        $stmt->close();
        
        if ($propuesta) {
            // Actualizar estado en la tabla correspondiente
            $stmt = $conexion->prepare("UPDATE " . $tabla_publicar . " SET estado = 'aprobada' WHERE id = ?");
            $stmt->bind_param("i", $id_publicar);
            $stmt->execute();
            $stmt->close();
            
            echo json_encode(["success" => true]);
        } else {
            echo json_encode(["success" => false, "error" => "Denuncia no encontrada"]);
        }
        exit();
    }
}

// Obtener imágenes
$imagenes = [];
foreach (['imagen', 'imagen2', 'imagen3'] as $campo) {
    if (!empty($noticia[$campo]) && file_exists('imagenes/denuncias/' . $noticia[$campo])) {
        $imagenes[] = $noticia[$campo];
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title><?= htmlspecialchars($noticia['titulo']) ?> - Comunicado Digital</title>
<link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600;700&family=Inter:wght@400;600&family=Open+Sans:wght@700&display=swap" rel="stylesheet">
<style>
body {
    font-family: 'Inter', sans-serif;
    background-color: #f9f9f9;
    margin: 0;
    padding: 0;
}

/* ENCABEZADO */
.header {
    position: fixed;
    top: 0;
    left: 0;
    width: 100%;
    height: 80px;
    background-color: #061F3E;
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 0 40px;
    box-shadow: 0 2px 8px rgba(0,0,0,0.1);
    z-index: 1000;
}

.header-logo {
    height: 60px;
    cursor: pointer;
}

.btn-volver {
    font-family: 'Poppins', sans-serif;
    font-size: 18px;
    font-weight: 600;
    color: #FFFFFF;
    text-decoration: none;
    margin-right: 70px;
    transition: text-decoration 0.3s ease;
}

.btn-volver:hover {
    text-decoration: underline;
}

/* CONTENEDOR PRINCIPAL */
.contenedor-detalle {
    display: flex;
    justify-content: center;
    align-items: flex-start;
    gap: 30px;
    max-width: 1300px;
    margin: 100px auto;
    padding: 30px;
}

/* COLUMNA DE NOTICIA */
.columna-noticia {
    flex: 1;
    max-width: 1000px;
}

/* TÍTULO */
.titulo-noticia {
    font-family: 'Poppins', sans-serif;
    font-size: 28px;
    font-weight: 700;
    color: #1661AC;
    text-align: left;
    margin: 20px 0 10px 0;
    line-height: 1.3;
}

/* Título + Iconos alineados */
.contenedor-titulo-acciones {
    display: flex;
    justify-content: space-between;
    align-items: center;
    width: 100%;
}

.titulo-noticia {
    font-family: 'Poppins', sans-serif;
    font-weight: bold;
    font-size: 28px;
    color: #1661AC;
    margin: 0;
}

/* META INFO */
.meta-noticia {
    font-family: 'Poppins', sans-serif;
    font-size: 16px;
    color: #74737C;
    display: flex;
    flex-wrap: wrap;
    align-items: center;
    gap: 10px;
    margin-bottom: 20px;
    padding-bottom: 15px;
    border-bottom: 2px solid #e0e0e0;
}

/* GALERÍA */
.galeria {
    text-align: center;
    margin-bottom: 30px;
}
.galeria-principal {
    width: 1000px;
    height: 500px;
    object-fit: cover;
    border-radius: 8px;
    margin-bottom: 10px;
    box-shadow: 0 4px 8px rgba(0,0,0,0.1);
}
.miniaturas {
    display: flex;
    justify-content: center;
    gap: 8px;
    margin-top: 10px;
}
.miniaturas img {
    width: 200px;
    height: 125px;
    object-fit: cover;
    border-radius: 8px;
    cursor: pointer;
    transition: all 0.2s ease;
    border: 2px solid transparent;
}
.miniaturas img:hover,
.miniaturas img.activa {
    border: 2px solid #2D8EFF;
}

/* FECHA DEL HECHO + DESCRIPCIÓN */
.detalle-descripcion {
    margin-top: 30px;
}

.detalle-descripcion span {
    display: inline;
}

.fecha-hecho {
    font-family: 'Inter', sans-serif;
    font-weight: 700;
    font-size: 20px;
    color: #403F48;
}

.descripcion {
    font-family: 'Inter', sans-serif;
    font-size: 18px;
    color: #403F48;
    line-height: 1.8;
    text-align: justify;
    text-justify: inter-word;
}
/* MENSAJE PARA USUARIOS NO REGISTRADOS */
.mensaje-iniciar-sesion {
    font-family: 'Poppins', sans-serif;
    font-size: 16px;
    font-style: italic;
    color: #403F48;
    margin-bottom: 30px;
    text-align: left;
}
/* MODAL ELIMINAR */
.modal-overlay {
    position: fixed;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background-color: rgba(0,0,0,0.5);
    display: none;
    justify-content: center;
    align-items: center;
    z-index: 1000;
}
.modal-eliminar {
    background-color: #FFFFFF;
    padding: 30px;
    border-radius: 12px;
    width: 400px;
    text-align: center;
    box-shadow: 0 8px 24px rgba(0,0,0,0.2);
}
.modal-titulo {
    font-family: 'Poppins', sans-serif;
    font-size: 16px;
    font-weight: bold;
    color: #403F48;
    margin-bottom: 20px;
    line-height: 1.4;
}
.modal-botones {
    display: flex;
    justify-content: space-between;
    gap: 20px;
}
.btn-cancelar {
    flex: 1;
    height: 40px;
    background-color: #EB7373;
    color: #061F3E;
    font-family: 'Inter', sans-serif;
    font-size: 16px;
    border: none;
    border-radius: 8px;
    cursor: pointer;
    transition: background 0.3s ease;
}
.btn-cancelar:hover {
    background-color: #d45c5c;
}
.btn-confirmar {
    flex: 1;
    height: 40px;
    background-color: #61C9A8;
    color: #FFFFFF;
    font-family: 'Inter', sans-serif;
    font-size: 16px;
    border: none;
    border-radius: 8px;
    cursor: pointer;
    transition: background 0.3s ease;
}
.btn-confirmar:hover {
    background-color: #4ab894;
}

/* BOTÓN "VER MÁS COMENTARIOS" */
.btn-ver-mas {
    display: block;
    width: 275px;
    height: 50px;
    margin: 25px auto;
    background-color: #1661AC;
    color: #FFFFFF;
    font-family: 'Open Sans', sans-serif;
    font-weight: 700;
    font-size: 16px;
    border: none;
    border-radius: 6px;
    cursor: pointer;
    transition: background 0.3s ease;
}
.btn-ver-mas:hover {
    background-color: #2D8EFF;
}
/* MODAL ELIMINACIÓN - ESTILOS PERSONALIZADOS */
.modal-overlay {
    position: fixed;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background-color: rgba(0, 0, 0, 0.5);
    display: none;
    justify-content: center;
    align-items: center;
    z-index: 1000;
}

.modal-overlay.activo {
    display: flex;
}

.modal-contenedor {
    background-color: #FFFFFF;
    padding: 40px;
    border-radius: 12px;
    width: 400px;
    box-shadow: 0 8px 24px rgba(0, 0, 0, 0.2);
    animation: slideIn 0.3s ease;
}

@keyframes slideIn {
    from {
        opacity: 0;
        transform: scale(0.95);
    }
    to {
        opacity: 1;
        transform: scale(1);
    }
}

.modal-titulo {
    font-family: 'Poppins', sans-serif;
    font-size: 16px;
    font-weight: bold;
    color: #403F48;
    text-align: center;
    margin: 0 0 30px 0;
    line-height: 1.5;
}

.modal-botones {
    display: flex;
    justify-content: center;
    gap: 100px;
}

.btn-modal {
    width: 125px;
    height: 40px;
    font-family: 'Inter', sans-serif;
    font-size: 16px;
    border: none;
    border-radius: 12px;
    cursor: pointer;
    transition: all 0.3s ease;
    font-weight: 500;
    position: relative;
    overflow: hidden;
}

.btn-cancelar {
    background-color: #EB7373;
    color: #FFFFFF;
}

.btn-cancelar:hover {
    background-color: #d45c5c;
    transform: translateY(-2px);
    box-shadow: 0 4px 8px rgba(235, 115, 115, 0.3);
}

.btn-cancelar:active {
    transform: translateY(0);
    box-shadow: 0 2px 4px rgba(235, 115, 115, 0.2);
}

.btn-cancelar::after {
    content: '';
    position: absolute;
    top: 50%;
    left: 50%;
    width: 0;
    height: 0;
    border-radius: 50%;
    background-color: rgba(255, 255, 255, 0.4);
    transform: translate(-50%, -50%);
    transition: width 0.6s, height 0.6s;
}

.btn-cancelar:active::after {
    width: 300px;
    height: 300px;
}

.btn-confirmar {
    background-color: #61C9A8;
    color: #061F3E;
}

.btn-confirmar:hover {
    background-color: #4ab894;
    transform: translateY(-2px);
    box-shadow: 0 4px 8px rgba(97, 201, 168, 0.3);
}

.btn-confirmar:active {
    transform: translateY(0);
    box-shadow: 0 2px 4px rgba(97, 201, 168, 0.2);
}

.btn-confirmar::after {
    content: '';
    position: absolute;
    top: 50%;
    left: 50%;
    width: 0;
    height: 0;
    border-radius: 50%;
    background-color: rgba(255, 255, 255, 0.4);
    transform: translate(-50%, -50%);
    transition: width 0.6s, height 0.6s;
}

.btn-confirmar:active::after {
    width: 300px;
    height: 300px;
}

/* MODAL VOLVER */
.modal-volver {
    position: fixed;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background-color: rgba(0, 0, 0, 0.5);
    display: none;
    justify-content: center;
    align-items: center;
    z-index: 10000;
}

.modal-volver.activo {
    display: flex;
}

.modal-volver-contenedor {
    background-color: #FFFFFF;
    padding: 40px;
    border-radius: 12px;
    width: 400px;
    box-shadow: 0 8px 24px rgba(0, 0, 0, 0.2);
    animation: slideIn 0.3s ease;
}

.modal-volver-titulo {
    font-family: 'Poppins', sans-serif;
    font-size: 16px;
    font-weight: bold;
    color: #403F48;
    text-align: center;
    margin: 0 0 30px 0;
    line-height: 1.5;
}

.modal-volver-botones {
    display: flex;
    justify-content: center;
    gap: 100px;
}

.btn-volver-cancelar {
    width: 145px;
    height: 40px;
    font-family: 'Inter', sans-serif;
    font-size: 16px;
    color: #FFFFFF;
    background-color: #EB7373;
    border: none;
    border-radius: 12px;
    cursor: pointer;
    transition: all 0.3s ease;
    font-weight: 500;
    position: relative;
    overflow: hidden;
}

.btn-volver-cancelar:hover {
    background-color: #d45c5c;
    transform: translateY(-2px);
    box-shadow: 0 4px 8px rgba(235, 115, 115, 0.3);
}

.btn-volver-cancelar:active {
    transform: translateY(0);
    box-shadow: 0 2px 4px rgba(235, 115, 115, 0.2);
}

.btn-volver-cancelar::after {
    content: '';
    position: absolute;
    top: 50%;
    left: 50%;
    width: 0;
    height: 0;
    border-radius: 50%;
    background-color: rgba(255, 255, 255, 0.4);
    transform: translate(-50%, -50%);
    transition: width 0.6s, height 0.6s;
}

.btn-volver-cancelar:active::after {
    width: 300px;
    height: 300px;
}

.btn-volver-confirmar {
    width: 145px;
    height: 40px;
    font-family: 'Inter', sans-serif;
    font-size: 16px;
    color: #061F3E;
    background-color: #61C9A8;
    border: none;
    border-radius: 12px;
    cursor: pointer;
    transition: all 0.3s ease;
    font-weight: 500;
    position: relative;
    overflow: hidden;
}

.btn-volver-confirmar:hover {
    background-color: #4ab894;
    transform: translateY(-2px);
    box-shadow: 0 4px 8px rgba(97, 201, 168, 0.3);
}

.btn-volver-confirmar:active {
    transform: translateY(0);
    box-shadow: 0 2px 4px rgba(97, 201, 168, 0.2);
}

.btn-volver-confirmar::after {
    content: '';
    position: absolute;
    top: 50%;
    left: 50%;
    width: 0;
    height: 0;
    border-radius: 50%;
    background-color: rgba(255, 255, 255, 0.4);
    transform: translate(-50%, -50%);
    transition: width 0.6s, height 0.6s;
}

.btn-volver-confirmar:active::after {
    width: 300px;
    height: 300px;
}

/* BOTONES ELIMINAR Y PUBLICAR */
.contenedor-botones {
    display: flex;
    gap: 150px;
    margin-top: 50px;
    justify-content: center;
}

.btn-eliminar {
    width: 150px;
    height: 50px;
    background-color: #E85D5D;
    color: #FFFFFF;
    font-family: 'Poppins', sans-serif;
    font-size: 20px;
    font-weight: bold;
    border: none;
    border-radius: 20px;
    cursor: pointer;
    transition: all 0.3s ease;
    position: relative;
    overflow: hidden;
}

.btn-eliminar:hover {
    background-color: #EB7373;
    transform: translateY(-2px);
    box-shadow: 0 6px 20px rgba(232, 93, 93, 0.4);
}

.btn-eliminar:active {
    transform: translateY(0);
    box-shadow: 0 2px 8px rgba(232, 93, 93, 0.3);
}

.btn-eliminar::after {
    content: '';
    position: absolute;
    top: 50%;
    left: 50%;
    width: 0;
    height: 0;
    border-radius: 50%;
    background-color: rgba(255, 255, 255, 0.3);
    transform: translate(-50%, -50%);
    transition: width 0.6s, height 0.6s;
}

.btn-eliminar:active::after {
    width: 300px;
    height: 300px;
}

.btn-publicar-noticia {
    width: 150px;
    height: 50px;
    background-color: #61C9A8;
    color: #1B314B;
    font-family: 'Poppins', sans-serif;
    font-size: 20px;
    font-weight: bold;
    border: none;
    border-radius: 20px;
    cursor: pointer;
    transition: all 0.3s ease;
    position: relative;
    overflow: hidden;
}

.btn-publicar-noticia:hover {
    background-color: #4CA88C;
    transform: translateY(-2px);
    box-shadow: 0 6px 20px rgba(97, 201, 168, 0.4);
}

.btn-publicar-noticia:active {
    transform: translateY(0);
    box-shadow: 0 2px 8px rgba(97, 201, 168, 0.3);
}

.btn-publicar-noticia::after {
    content: '';
    position: absolute;
    top: 50%;
    left: 50%;
    width: 0;
    height: 0;
    border-radius: 50%;
    background-color: rgba(255, 255, 255, 0.3);
    transform: translate(-50%, -50%);
    transition: width 0.6s, height 0.6s;
}

.btn-publicar-noticia:active::after {
    width: 300px;
    height: 300px;
}

/* MODAL PUBLICAR */
.modal-publicar {
    position: fixed;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background-color: rgba(0, 0, 0, 0.5);
    display: none;
    justify-content: center;
    align-items: center;
    z-index: 10000;
}

.modal-publicar.activo {
    display: flex;
}

.modal-publicar-contenedor {
    background-color: #FFFFFF;
    padding: 40px;
    border-radius: 12px;
    width: 450px;
    box-shadow: 0 8px 24px rgba(0, 0, 0, 0.2);
    animation: slideIn 0.3s ease;
}

.modal-publicar-titulo {
    font-family: 'Inter', sans-serif;
    font-size: 16px;
    font-weight: bold;
    color: #403F48;
    text-align: center;
    margin: 0 0 20px 0;
    line-height: 1.5;
}

.modal-publicar-texto {
    font-family: 'Inter', sans-serif;
    font-size: 16px;
    color: #403F48;
    text-align: center;
    margin: 0 0 30px 0;
    line-height: 1.6;
}

.modal-publicar-botones {
    display: flex;
    justify-content: center;
    gap: 100px;
}

.btn-publicar-cancelar {
    width: 145px;
    height: 40px;
    font-family: 'Inter', sans-serif;
    font-size: 16px;
    color: #FFFFFF;
    background-color: #EB7373;
    border: none;
    border-radius: 12px;
    cursor: pointer;
    transition: all 0.3s ease;
    font-weight: 500;
    position: relative;
    overflow: hidden;
}

.btn-publicar-cancelar:hover {
    background-color: #d45c5c;
    transform: translateY(-2px);
    box-shadow: 0 4px 8px rgba(235, 115, 115, 0.3);
}

.btn-publicar-cancelar:active {
    transform: translateY(0);
    box-shadow: 0 2px 4px rgba(235, 115, 115, 0.2);
}

.btn-publicar-cancelar::after {
    content: '';
    position: absolute;
    top: 50%;
    left: 50%;
    width: 0;
    height: 0;
    border-radius: 50%;
    background-color: rgba(255, 255, 255, 0.4);
    transform: translate(-50%, -50%);
    transition: width 0.6s, height 0.6s;
}

.btn-publicar-cancelar:active::after {
    width: 300px;
    height: 300px;
}

.btn-publicar-confirmar {
    width: 145px;
    height: 40px;
    font-family: 'Inter', sans-serif;
    font-size: 16px;
    color: #061F3E;
    background-color: #61C9A8;
    border: none;
    border-radius: 12px;
    cursor: pointer;
    transition: all 0.3s ease;
    font-weight: 500;
    position: relative;
    overflow: hidden;
}

.btn-publicar-confirmar:hover {
    background-color: #4ab894;
    transform: translateY(-2px);
    box-shadow: 0 4px 8px rgba(97, 201, 168, 0.3);
}

.btn-publicar-confirmar:active {
    transform: translateY(0);
    box-shadow: 0 2px 4px rgba(97, 201, 168, 0.2);
}

.btn-publicar-confirmar::after {
    content: '';
    position: absolute;
    top: 50%;
    left: 50%;
    width: 0;
    height: 0;
    border-radius: 50%;
    background-color: rgba(255, 255, 255, 0.4);
    transform: translate(-50%, -50%);
    transition: width 0.6s, height 0.6s;
}

.btn-publicar-confirmar:active::after {
    width: 300px;
    height: 300px;
}

/* ALERTA DE ÉXITO */
.alerta-exito {
    position: fixed;
    top: 50%;
    left: 50%;
    transform: translate(-50%, -50%);
    background-color: #28a745;
    color: white;
    padding: 20px 40px;
    border-radius: 8px;
    font-family: 'Poppins', sans-serif;
    font-size: 16px;
    font-weight: 600;
    z-index: 10000;
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.3);
    text-align: center;
    display: none;
    opacity: 0;
    transition: opacity 0.3s ease;
}

.alerta-exito.mostrar {
    display: block;
    opacity: 1;
}

/* RESPONSIVO */
@media (max-width: 1024px) {
    .contenedor-detalle {
        flex-direction: column;
        align-items: center;
    }
    .galeria-principal {
        width: 100%;
        height: auto;
        max-height: 400px;
    }
    .detalle-descripcion {
        flex-direction: column;
    }
    .miniaturas {
        flex-wrap: wrap;
    }
    .miniaturas img {
        width: 150px;
        height: 100px;
    }
    .btn-publicar {
        position: relative;
        float: right;
        margin-top: 10px;
    }
}
</style>
</head>
<body>

<!-- Encabezado -->
<header class="header">
    <img src="imagenes/logo.png" alt="Logo" class="header-logo" onclick="window.location.href='inicio.php'">
    <a href="javascript:void(0)" class="btn-volver" onclick="abrirModalVolver()">Volver</a>
</header>

<div class="contenedor-detalle">
    <!-- Columna izquierda: contenido -->
    <div class="columna-noticia">
        <div class="contenedor-titulo-acciones">
            <h1 class="titulo-noticia"><?= htmlspecialchars($noticia['titulo']) ?></h1>
        </div>

        <div class="meta-noticia">
            <span>Anónimo</span>
            <span>|</span>
            <span><?= date('d-m-Y', strtotime($noticia['fecha'])) ?></span>
            <span><?= date('H:i', strtotime($noticia['fecha'])) ?></span>
        </div>

        <!-- Galería -->
        <div class="galeria">
            <img id="imagenPrincipal" src="imagenes/denuncias/<?= $imagenes[0] ?? 'default.jpg' ?>" alt="Imagen principal" class="galeria-principal">
            <?php if(count($imagenes) > 1): ?>
            <div class="miniaturas">
                <?php foreach ($imagenes as $index => $img): ?>
                    <img src="imagenes/denuncias/<?= $img ?>" 
                         alt="Miniatura" 
                         onclick="cambiarImagen('<?= $img ?>', this)"
                         <?= $index === 0 ? 'class="activa"' : '' ?>>
                <?php endforeach; ?>
            </div>
            <?php endif; ?>
        </div>

        <!-- Fecha del hecho y Descripción -->
        <div class="detalle-descripcion">
            <span class="fecha-hecho">
                <?php
                setlocale(LC_TIME, 'es_ES.UTF-8', 'es_ES', 'Spanish_Spain');
                $fecha = strftime("%d de %B de %Y", strtotime($noticia['fecha_hecho'] ?? $noticia['fecha']));
                echo ucfirst($fecha) . ". -";
                ?>
            </span>

            <span class="descripcion">
                <?= nl2br(htmlspecialchars($noticia['descripcion'])) ?>
            </span>
        </div>

        <!-- Botones Eliminar y Publicar -->
        <div class="contenedor-botones">
            <button class="btn-eliminar" onclick="abrirModalEliminarNoticia()">Eliminar</button>
            <button class="btn-publicar-noticia" onclick="publicarNoticia()">Publicar</button>
        </div>
    </div>

<!-- Modal Eliminación de Noticia -->
<div class="modal-overlay" id="modalEliminarNoticia">
    <div class="modal-contenedor">
        <h2 class="modal-titulo">¿Estás seguro de eliminar esta denuncia?</h2>
        <div class="modal-botones">
            <button class="btn-modal btn-cancelar" onclick="cerrarModalEliminarNoticia()">Cancelar</button>
            <button class="btn-modal btn-confirmar" onclick="confirmarEliminarNoticia()">Confirmar</button>
        </div>
    </div>
</div>

<!-- Modal Volver -->
<div class="modal-volver" id="modalVolver">
    <div class="modal-volver-contenedor">
        <h2 class="modal-volver-titulo">¿Estás seguro de regresar a las denuncias pendientes?</h2>
        <div class="modal-volver-botones">
            <button class="btn-volver-cancelar" onclick="cerrarModalVolver()">Cancelar</button>
            <button class="btn-volver-confirmar" onclick="confirmarVolver()">Confirmar</button>
        </div>
    </div>
</div>

<!-- Modal Publicar -->
<div class="modal-publicar" id="modalPublicar">
    <div class="modal-publicar-contenedor">
        <h2 class="modal-publicar-titulo">¿Estas seguro de publicar esta denuncia?</h2>
        <p class="modal-publicar-texto">Estás a punto de publicar esta denuncia. Una vez publicada, estará disponible para todos los lectores.</p>
        <div class="modal-publicar-botones">
            <button class="btn-publicar-cancelar" onclick="cerrarModalPublicar()">Cancelar</button>
            <button class="btn-publicar-confirmar" onclick="confirmarPublicarNoticia()">Confirmar</button>
        </div>
    </div>
</div>

<!-- Alerta de Éxito -->
<div id="alertaExito" class="alerta-exito">
    Denuncia eliminada correctamente
</div>

<!-- Alerta de Publicación -->
<div id="alertaPublicacion" class="alerta-exito">
    Denuncia publicada con exito, disponible para los lectores.
</div>

<script>
// Funciones para modal de Volver
function abrirModalVolver() {
    document.getElementById('modalVolver').classList.add('activo');
}

function cerrarModalVolver() {
    document.getElementById('modalVolver').classList.remove('activo');
}

function confirmarVolver() {
    window.location.href = 'revision_denuncias.php';
}

// Función para publicar noticia (abrir modal)
function publicarNoticia() {
    document.getElementById('modalPublicar').classList.add('activo');
}

function cerrarModalPublicar() {
    document.getElementById('modalPublicar').classList.remove('activo');
}

function confirmarPublicarNoticia() {
    const idNoticia = <?= $id_noticia ?>;
    const tablaOrigen = "<?= $tabla_origen ?>";
    
    // Cerrar el modal
    cerrarModalPublicar();
    
    // Enviar solicitud para publicar la denuncia
    fetch('', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
        },
        body: 'publicar=1&id_noticia=' + idNoticia + '&tabla_origen=' + encodeURIComponent(tablaOrigen)
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            // Mostrar alerta de éxito
            const alerta = document.getElementById('alertaPublicacion');
            alerta.classList.add('mostrar');
            
            // Redirigir a revision_denuncias.php después de 2 segundos
            setTimeout(() => {
                window.location.href = 'revision_denuncias.php';
            }, 2000);
        } else {
            alert('Error al publicar la noticia: ' + (data.error || 'Error desconocido'));
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('Error al publicar la denuncia');
    });
}

// Función para abrir modal de eliminación de noticia
function abrirModalEliminarNoticia() {
    document.getElementById('modalEliminarNoticia').classList.add('activo');
}

// Función para cerrar modal de eliminación de noticia
function cerrarModalEliminarNoticia() {
    document.getElementById('modalEliminarNoticia').classList.remove('activo');
}

// Función para confirmar eliminación de noticia
function confirmarEliminarNoticia() {
    const idNoticia = <?= $id_noticia ?>;
    const tablaOrigen = "<?= $tabla_origen ?>";
    
    // Cerrar el modal
    cerrarModalEliminarNoticia();
    
    // Crear formulario oculto para enviar datos a rechazar_denuncias.php
    const form = document.createElement('form');
    form.method = 'POST';
    form.action = 'rechazar_denuncias.php';
    
    const inputId = document.createElement('input');
    inputId.type = 'hidden';
    inputId.name = 'id';
    inputId.value = idNoticia;
    
    const inputTabla = document.createElement('input');
    inputTabla.type = 'hidden';
    inputTabla.name = 'tabla_origen';
    inputTabla.value = tablaOrigen;
    
    form.appendChild(inputId);
    form.appendChild(inputTabla);
    document.body.appendChild(form);
    
    // Mostrar alerta de éxito
    const alerta = document.getElementById('alertaExito');
    alerta.classList.add('mostrar');
    
    // Esperar 1 segundo y enviar el formulario
    setTimeout(() => {
        form.submit();
    }, 1000);
}

// Galería de imágenes
function cambiarImagen(imagen, elemento) {
    document.getElementById('imagenPrincipal').src = 'imagenes/denuncias/' + imagen;
    
    // Remover clase activa de todas las miniaturas
    document.querySelectorAll('.miniaturas img').forEach(img => {
        img.classList.remove('activa');
    });
    
    // Agregar clase activa a la miniatura clickeada
    elemento.classList.add('activa');
}

// Cerrar modales al hacer click fuera
document.addEventListener('DOMContentLoaded', function() {
    const modalPublicar = document.getElementById('modalPublicar');
    if (modalPublicar) {
        modalPublicar.addEventListener('click', function(e) {
            if (e.target === this) {
                cerrarModalPublicar();
            }
        });
    }
    
    const modalVolver = document.getElementById('modalVolver');
    if (modalVolver) {
        modalVolver.addEventListener('click', function(e) {
            if (e.target === this) {
                cerrarModalVolver();
            }
        });
    }
    
    const modalEliminar = document.getElementById('modalEliminarNoticia');
    if (modalEliminar) {
        modalEliminar.addEventListener('click', function(e) {
            if (e.target === this) {
                cerrarModalEliminarNoticia();
            }
        });
    }
});
</script>

</body>
</html>