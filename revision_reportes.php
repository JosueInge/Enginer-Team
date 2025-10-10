<?php
session_start();
include 'conexion.php';

if (!isset($_SESSION['usuario_id'])) {
    header("Location: login.php");
    exit();
}
if ($_SESSION['usuario_rol'] !== 'Administrador') {
    header("Location: noticias.php");
    exit();
}

$reportes_pendientes = 0;
$query = "SELECT COUNT(DISTINCT propuestas_denuncias_id) AS total FROM reportesdenuncias WHERE estado = 'pendiente'";
$result = $conexion->query($query);
if ($result && $row = $result->fetch_assoc()) {
    $reportes_pendientes = $row['total'];
}

$stmt = $conexion->prepare("SELECT * FROM reportes WHERE estado = 'pendiente' GROUP BY propuestas_noticias_id");
$stmt->execute();
$reportes = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
$stmtRepetidos = $conexion->prepare("SELECT propuestas_noticias_id, COUNT(*) as cantidad FROM reportes WHERE estado = 'pendiente' GROUP BY propuestas_noticias_id");
$stmtRepetidos->execute();
$resultadoRepetidos = $stmtRepetidos->get_result()->fetch_all(MYSQLI_ASSOC);

$conteoReportes = [];
foreach ($resultadoRepetidos as $item) {
    $conteoReportes[$item['propuestas_noticias_id']] = $item['cantidad'];
}
?>

<!DOCTYPE html>
<html charset="UTF-8">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Revision de Reportes - Periódico Digital Comunitario</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #fff;
            margin: 0;
            color: #333;
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
            font-size: 24px;
            font-weight: bold;
        }
        .logo img {
            height: 50px;
            margin-right: 10px;
        }
        .informacion a{
            color: white;
            margin-left: 20px;
            text-decoration: none;
            font-size: 14px;
        }
        .contenedor {
            max-width: 1200px;
            margin: 30px auto;
            padding: 0 20px;
        }
        .titulo-seccion {
            color: #0d5c9b;
            border-bottom: 2px solid #0d5c9b;
            padding-bottom: 10px;
            margin-bottom: 20px;
        }
        .noticias-pendientes {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(350px, 1fr));
            gap: 25px;
            margin-top: 30px;
        }
        .noticia-card {
            position: relative;
            background-color: #fff;
            border-radius: 10px;
            overflow: hidden;
            box-shadow: 0 3px 10px rgba(0,0,0,0.1);
            transition: transform 0.3s ease, box-shadow 0.3s ease;
        }
        .noticia-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 5px 15px rgba(0,0,0,0.2);
        }
        .noticia-imagen {
            width: 100%;
            height: 200;
            object-fit: cover;
        }
        .noticia-contenido {
            padding: 20px;
        }
        .noticia-categoria {
            display: inline-block;
            background-color: #0d5c9b;
            color: white;
            padding: 5px 10px;
            border-radius: 5px;
            font-size: 12px;
            margin-bottom: 10px;
        }
        .noticia-titulo {
            font-size: 18px;
            margin: 10px 0;
            color: #0d5c9b;
        }
        .noticia-descripcion {
            color: gray;
            font-size: 14px;
            margin-bottom: 15px;
            display: -webkit-box;
            -webkit-line-clamp: 3;
            -webkit-box-orient: vertical;
            overflow: hidden;
        }
        .noticia-meta {
            display: flex;
            justify-content: space-between;
            font-size: 12px;
            color: #888;
            margin-top: 15px;
            border-top: 1px solid #eee;
            padding-top: 10px;
        }
        .noticia-acciones {
            display: flex;
            justify-content: space-between;
            margin-top: 15px;
        }
        .btn-accion {
            padding: 8px 15px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            font-weight: bold;
            transition: all 0.3s ease;
        }
        .btn-aprobar {
            background-color: #dc3545;
            color: white;
        }
        .btn-aprobar:hover {
            background-color: #dc3545;
        }
        .btn-rechazar {
            margin-left: -4px;
            background-color: #dc3545;
            color: white;
        }
        .btn-rechazar:hover {
            background-color: #dc3545;
        }
        .btn-ver_noticia {
            background-color: #0d5c9b;
            color: white;
        }
        .btn-ver_noticia:hover {
            background-color: #0d5c9b;
        }
        .sin-noticias {
            text-align: center;
            padding: 50px;
            color: #666;
            font-size: 18px;
            grid-column: 1 / -1;
        }
        .tooltip {
            position: relative;
            display: inline-block;
        }
        .tooltip-text {
            visibility: hidden;
            width: 120px;
            background-color: #555;
            color: #fff;
            text-align: center;
            border-radius: 6px;
            padding: 5px;
            position: absolute;
            z-index: 1;
            bottom: 125%;
            left: 50%;
            transform: translateX(-50%);
            opacity: 0;
            transition: opacity 0.3s;
            font-size: 14px;
        }
        .tooltip:hover .tooltip-text {
            visibility: visible;
            opacity: 1;
        }
        .notificacion {
            position: absolute;
            top: -5px;
            right: -5px;
            background-color: red;
            color: white;
            border-radius: 50%;
            padding: 3px 6px;
            font-size: 12px;
            font-weight: bold;
            z-index: 10;
        }
        .icono-con-notificacion {
            position: relative;
            display: inline-block;
        }
    </style>
</head>
<body>
    <header>
        <div class="logo">
            <img src="imagenes/logo.png" alt="logo">
        </div>
        <div class="informacion">
            <a href="inicio.php">Inicio</a>
            <a href="revision_noticias.php">Revision</a>
            <a href="perfil.php">Mi perfil</a>
            <a href="logout.php">Cerrar Sesion</a>
        </div>
    </header>

        <div class="contenedor">
            <div style="display: flex; justify-content: space-between; align-items: center;">
                <h1 class="titulo-session">Revisión de Reportes Pendientes de Noticias</h1>
                <div class="tooltip" style="margin-right: 10px;">
                    <a href="revision_reportes_denuncias.php" class="icono-con-notificacion">
                        <img src="imagenes/derecha.png" alt="Reportar" style="width: 50px; height: 50px; cursor: pointer;">
                        <?php if ($reportes_pendientes > 0): ?>
                            <span class="notificacion"><?= $reportes_pendientes ?></span>
                        <?php endif; ?>
                    </a>
                    <div class="tooltip-text">Reportes Denuncia</div>
                </div>
            </div>

            <div style="margin-top: 20px; display: flex; align-items: center; gap: 10px;">
                <input type="text" id="buscador" placeholder="Buscar por titulo, autor o categoria..."
                    style="padding: 8px 12px; width: 60%; max-width: 400px; border: 1px solid #ccc; border-radius: 4px;">
                <button onclick="filtrarNoticias()" style="background-color: #0d5c9b; color: white; border: none; border-radius: 4px; padding: 8px 12px; cursor: pointer;">
                    Buscar
                </button>
            </div>

            <!-- MENSAJE SI NO HAY REPORTES -->
            <?php if (empty($reportes)): ?>
                <div class="sin-noticias">
                    No hay reportes pendientes de revisión en este momento.
                </div>
            <?php else: ?>
                <div class="noticias-pendientes" id="lista-noticias">
                    <?php foreach ($reportes as $reporte): 
                        $noticia_id = $reporte['propuestas_noticias_id'];
                        $repeticiones = isset($conteoReportes[$noticia_id]) ? $conteoReportes[$noticia_id] - 1 : 0; ?>
                        <div class="noticia-card">
                            <?php if ($repeticiones > 0): ?>
                                <div style="position: absolute; top: 10px; right: 10px; background-color: red; color: white; border-radius: 50%; width: 25px; height: 25px; display: flex; align-items: center; justify-content: center; font-size: 14px; font-weight: bold;">
                                    <?php echo $repeticiones; ?>
                                </div>
                            <?php endif; ?>
                            <div class="noticia-contenido">
                                <h3 class="noticia-titulo"><?php echo htmlspecialchars($reporte['motivo']); ?></h3>
                                <p class="noticia-descripcion"><?php echo htmlspecialchars($reporte['comentario']); ?></p>
                                <div class="noticia-meta">
                                    <span><?php echo date('d/m/Y', strtotime($reporte['fecha_reporte'])); ?></span>
                                </div>
                                <div class="noticia-acciones">
                                    <form action="eliminar_reporte.php" method="GET" style="flex: 1; margin-right: 10px;" onsubmit="return confirm('¿Deseas eliminar esta noticia?')">
                                        <input type="hidden" name="id" value="<?php echo htmlspecialchars($reporte['id']); ?>">
                                        <button type="submit" class="btn-accion btn-aprobar">Eliminar</button>
                                    </form>
                                    <form method="POST" action="rechazar_reportes.php" style="flex: 1;">
                                        <input type="hidden" name="id" value="<?php echo htmlspecialchars($reporte['id']); ?>">
                                        <button type="submit" class="btn-accion btn-rechazar">Rechazar</button>
                                    </form>
                                    <form method="GET" action="notificacion_reporte.php">
                                        <input type="hidden" name="id" value="<?= htmlspecialchars($reporte['propuestas_noticias_id']) ?>">
                                        <button type="submit" class="btn-accion btn-ver_noticia">Detalles</button>
                                    </form>
                                </div> 
                            </div>
                            <script>
                            function filtrarNoticias() {
                                const termino = document.getElementById('buscador').value.toLowerCase().trim();
                                const noticias = document.querySelectorAll('#lista-noticias .noticia-card');

                                noticias.forEach(noticia => {
                                    const titulo = noticia.querySelector('.noticia-titulo')?.textContent.toLowerCase() || '';

                                    if (titulo.includes(termino)) {
                                        noticia.style.display = 'block';
                                    } else {
                                        noticia.style.display = 'none';
                                    }
                                });
                            }
                            </script>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>
</body>
</html>