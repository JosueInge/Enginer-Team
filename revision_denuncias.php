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

$stmt = $conexion->prepare("SELECT * FROM propuestas_denuncias WHERE estado = 'pendiente' ORDER BY fecha DESC");
$stmt->execute();
$noticias = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
?>

<!DOCTYPE html>
<html charset="UTF-8">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Revision de Denuncias - Periódico Digital Comunitario</title>
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
            background-color: #28a745;
            color: white;
        }
        .btn-aprobar:hover {
            background-color: #28a745;
        }
        .btn-rechazar {
            background-color: #dc3545;
            color: white;
        }
        .btn-rechazar:hover {
            background-color: #dc3545;
        }
        .sin-noticias {
            text-align: center;
            padding: 50px;
            color: #666;
            font-size: 18px;
            grid-column: 1 / -1;
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
            <h1 class="titulo-session">Revision de Denuncias Pendientes</h1>
            <div style="margin-top: 20px; display: flex; align-items: center; gap: 10px;">
                <input type="text" id="buscador" placeholder="Buscar por titulo, autor o categoria..."
                    style="padding: 8px 12px; width: 60%; max-width: 400px; border: 1px solid #ccc; border-radius: 4px;">
                <button onclick="filtrarNoticias()" style="background-color: #0d5c9b; color: white; border: none; border-radius: 4px; padding: 8px 12px; cursor: pointer;">
                    Buscar
                </button>
            </div>

            <?php if (empty($noticias)): ?>
                <div class="sin-noticias">
                    No hay denuncias pendientes de revision en este momento.
                </div>
            <?php else: ?>
                <div class="noticias-pendientes" id="lista-noticias">
                    <?php foreach ($noticias as $noticia): ?>
                        <div class="noticia-card">
                            <?php if (!empty($noticia['imagen'])): ?>
                                <img src="imagenes/denuncias/<?php echo htmlspecialchars($noticia['imagen']); ?>"
                                alt="<?php echo htmlspecialchars($noticia['titulo']); ?>"
                                class="noticia-imagen">
                            <?php else: ?>
                                <div class="noticia-imagen" style="background-color: #ddd; display: flex; align-items: center; justify-content: center;">
                                    <span style="color: #888;">Sin imagen</span>
                                </div> 
                            <?php endif; ?>

                            <div class="noticia-contenido">
                                <h3 class="noticia-titulo"><?php echo htmlspecialchars($noticia['titulo']); ?></h3>
                                <p class="noticia-descripcion"><?php echo htmlspecialchars($noticia['descripcion']); ?></p>

                                <div class="noticia-meta">
                                    <span><?php echo date('d/m/Y', strtotime($noticia['fecha'])); ?></span>
                                </div>

                                <div class="noticia-acciones">
                                    <form method="POST" action="aprobar_denuncia.php" style="flex: 1; margin-rigth: 10px;">
                                        <input type="hidden" name="id" value="<?php echo htmlspecialchars($noticia['id']); ?>">
                                        <button type="submit" class="btn-accion btn-aprobar">Aprobar</button>
                                    </form>
                                    <form method="POST" action="rechazar_denuncias.php" style="flex: 1;">
                                        <input type="hidden" name="id" value="<?php echo htmlspecialchars($noticia['id']); ?>">
                                        <button type="submit" class="btn-accion btn-rechazar">Rechazar</button>
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