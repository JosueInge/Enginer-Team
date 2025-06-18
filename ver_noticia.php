<?php
session_start();
include 'conexion.php';

if (!isset($_GET['id'])) {
    header("Location: noticias.php");
    exit();
}

$id_noticia = intval($_GET['id']);
$usuario_id = $_SESSION['usuario_id'] ?? null;
$es_admin = ($_SESSION['usuario_rol'] ?? '') === 'Administrador';

$stmt = $conexion->prepare("SELECT * FROM propuestas_noticias WHERE id = ?");
$stmt->bind_param("i", $id_noticia);
$stmt->execute();
$noticia = $stmt->get_result()->fetch_assoc();
$stmt->close();

if (!$noticia) {
    header("Location: noticias.php");
    exit();
}

if (isset($_POST['eliminar_id'])) {
    $id_comentario = intval($_POST['eliminar_id']);
    $stmt = $conexion->prepare("DELETE FROM comentarios WHERE id = ? AND (usuario_id = ? OR ? = 1)");
    $es_admin_flag = $es_admin ? 1 : 0;
    $stmt->bind_param("iii", $id_comentario, $usuario_id, $es_admin_flag);
    $stmt->execute();
    $stmt->close();
    echo json_encode(["success" => true]);
    exit();
}

if (isset($_POST["editar_id"], $_POST['editar_texto'])) {
    $id_comentario = intval($_POST['editar_id']);
    $texto = trim($_POST['editar_texto']);
    $stmt = $conexion->prepare("UPDATE comentarios SET texto = ?, editado = 1 WHERE id = ? AND usuario_id = ?");
    $stmt->bind_param("sii", $texto, $id_comentario, $usuario_id);
    $stmt->execute();
    $stmt->close();
    echo json_encode(["success"=> true]);
    exit();
}

if (isset($_POST['nuevo_comentario'])) {
    $texto = trim($_POST['nuevo_comentario']);
    if (!empty($texto)) {
        $stmt = $conexion->prepare("INSERT INTO comentarios ( propuestas_noticias_id, usuario_id, texto, fecha) VALUES (?, ?, ?, NOW())");
        $stmt->bind_param("iis", $id_noticia, $usuario_id, $texto);
        $stmt->execute();
        $stmt->close();
    }
    echo json_encode(["success" => true]);
    exit();
}

$stmt = $conexion->prepare("SELECT c.*, u.nombre, u.avatar FROM comentarios c JOIN usuarios u ON c.usuario_id = u.id WHERE c.propuestas_noticias_id = ? ORDER BY c.fecha DESC");
$stmt->bind_param("i", $id_noticia);
$stmt->execute();
$comentarios = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
$stmt->close();
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($noticia['titulo']) ?> - Comunicado Digital</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f5f5f5;
            padding-top: 130px;
        }
        header {
            background-color: #0d5c9b;
            color: white;
            padding: 10px 20px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            z-index: 1000;
        }
        .logo img {
            height: 50px;
        }
        .informacion a {
            color: white;
            text-decoration: none;
            margin-left: 15px;
        }
        .contenido-principal {
            max-width: 800px;
            margin: auto;
            background: white;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
            padding: 20px;
        }
        .noticia-meta {
            color: #666;
            font-size: 14px;
            margin-bottom: 10px;
        }
        .noticia-imagen {
            max-width: 100%;
            margin: 20px 0;
            border-radius: 6px;
        }
        .formulario-comentario {
            margin-top: 30px;
        }
        .formulario-comentario textarea {
            width: 100%;
            padding: 10px;
            border-radius: 5px;
            border: 1px solid #ccc;
        }
        .formulario-comentario button {
            margin-top: 10px;
            background-color: #0d5c9b;
            color: white;
            padding: 10px 15px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
        }
        .comentario {
            display: flex;
            gap: 15px;
            margin-top: 20px;
            padding: 15px;
            background: #ffffff;
            border-radius: 10px;
            border: 1px solid #ddd;
            box-shadow: 0 2px 4px rgba(0,0,0,0.05);
            align-items: flex-start;
        }
        .comentario strong {
            color: #0d5c9b;
        }
        .mensaje {
            color: green;
            margin-bottom: 10px;
        }
        .comentario-avatar {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            object-fit: cover;
        }
        .comentario-cuerpo {
            flex: 1;
            font-size: 15px;
            line-height: 1.5;
        }
        .comentario-cuerpo strong {
            color: #0d5c9b;
        }
        .comentario-cuerpo small {
            margin-top: 2px;
            font-size: 12px;
            color: #888;
        }
        .acciones {
            margin-top: 8px;
        }
        .acciones button {
            font-size: 12px;
            background: none;
            border: none;
            color: #0d5c9b;
            cursor: pointer;
        }
        .acciones button:hover {
            text-decoration: underline;
        }
        .formulario-comentario textarea {
            width: 100%;
            padding: 10px;
            margin-top: 10px;
            border-radius: 10px;
            border: 1px solid #ccc;
        }
        .formulario-comentario button {
            background-color: #0d5c9b;
            color: white;
            padding: 8px 14px;
            border: none;
            border-radius: 4px;
            margin-top: 10px;
            cursor: pointer;
        }
        .tooltip {
            position: relative;
            display: inline-block;
        }

        .tooltip .tooltip-text {
            visibility: hidden;
            width: 110px;
            background-color: #555;
            color: #fff;
            font-size: 14px;
            text-align: center;
            border-radius: 6px;
            padding: 5px 8px;
            position: absolute;
            z-index: 1;
            bottom: 115%;
            left: 50%;
            transform: translateX(-50%);
            opacity: 0;
            transition: opacity 0.3s;
        }

        .tooltip:hover .tooltip-text {
            visibility: visible;
            opacity: 1;
        }
    </style>
</head>
<body>
    <header>
        <div class="logo">
            <img src="imagenes/logo.png" alt="Logo">
        </div>
        <nav class="informacion">
            <a href="javascript:history.back()" class="back-buttom">Volver</a>
            <a href="logout.php" id="btnSesion">Cerrar Sesión</a>
        </nav>
    </header>

    <div class="contenido-principal">
        <h1><?= htmlspecialchars($noticia['titulo']) ?></h1>
        <p><strong><?= htmlspecialchars($noticia['categoria']) ?></strong> - <?= htmlspecialchars($noticia['autor']) ?> | <?= $noticia['fecha'] ?></p>
        <?php if ($noticia['imagen']): ?>
            <img src="imagenes/noticias/<?= htmlspecialchars($noticia['imagen']) ?>" class="noticia-imagen" alt="Imagen">
        <?php endif; ?>
        <p><?= nl2br(htmlspecialchars($noticia['descripcion'])) ?></p>
            <div style="text-align: right; margin-top: 10px; margin-right: 10px;">
            <div class="tooltip">
                <a href="reportar.php?id=<?= $noticia['id'] ?>">
                <img src="imagenes/reportar.png" alt="Reportar" style="width: 30px; height: 30px; cursor: pointer;">
                </a>
                <div class="tooltip-text">Reportar Noticia</div>
            </div>
            </div>
        <h3>Comentarios</h3>
        <?php if ($noticia['bloquear_comentarios']): ?>
            <p style="color: #777; font-style: italic;">Los comentarios estan bloqueados para esta noticia.</p>
        <?php else: ?>
            <?php if (isset($_SESSION['usuario_id'])): ?>
                <form class="formulario-comentario" onsubmit="enviarComentario(event)">
                    <textarea name="nuevo_comentario" id="nuevo_comentario" required placeholder="Escribe un comentario..."></textarea>
                    <button type="submit">Comentar</button>
                </form>
            <?php else: ?>
                <p><em>Inicia sesión para dejar un comentario.</em></p>
            <?php endif; ?>

            <div id="comentarios">
                <?php if (empty($comentarios)): ?>
                    <p style="margin-top: 10px; color: #555;"> No hay comentarios aun.</p>
                <?php else: ?>
                    <?php foreach ($comentarios as $comentario): ?>
                        <div class="comentario" id="comentario-<?= $comentario['id'] ?>">
                            <img class="comentario-avatar" src="<?= htmlspecialchars(!empty($comentario['avatar']) ? $comentario['avatar'] : 'imagenes/avatar-default.png') ?>" alt="avatar">
                            <div class="comentario-cuerpo">
                                <strong><?= htmlspecialchars($comentario['nombre']) ?></strong>
                                <small><?= date('d/m/Y H:i', strtotime($comentario['fecha'])) ?><?= $comentario['editado'] ? ' (editado)' : '' ?></small>
                                <div id="texto-<?= $comentario['id'] ?>"><?= nl2br(htmlspecialchars($comentario['texto'])) ?></div>

                                <?php if ($comentario['usuario_id'] == $usuario_id || $es_admin): ?>
                                    <div class="acciones">
                                        <?php if ($comentario['usuario_id'] == $usuario_id): ?>
                                            <button onclick="editarComentario(<?= $comentario['id'] ?>, '<?= htmlspecialchars($comentario['texto'], ENT_QUOTES) ?>')">Editar</button>
                                            <button onclick="eliminarComentario(<?= $comentario['id'] ?>)">Eliminar</button>
                                        <?php elseif ($es_admin): ?>
                                            <button onclick="eliminarComentario(<?= $comentario['id'] ?>)">Eliminar</button>
                                        <?php endif; ?>
                                    </div>
                                <?php endif; ?>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        <?php endif; ?>
    </div>
       
    <script>
        function enviarComentario(e) {
            e.preventDefault();
            const texto = document.getElementById('nuevo_comentario').value;
            fetch(location.href, {
                method: "POST",
                headers: { 'Content-Type': 'application/x-www-form-urlencoded'},
                body: new URLSearchParams({ nuevo_comentario: texto }) 
            }).then(() => location.reload());
        }

        function eliminarComentario(id) {
            if (confirm('Eliminar este comentario?')) {
                fetch(location.href, {
                    method:'POST',
                    headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                    body: new URLSearchParams({ eliminar_id: id })
                }).then(() => location.reload());
            }
        }

        function editarComentario(id, texto) {
            const contenedor = document.getElementById('texto-' + id);
            contenedor.innerHTML = `
                <textarea id="editar-${id}" style="width:100%;">${texto}</textarea>
                <button onclick="guardarEdicion(${id})">Guardar</button>
            `;

        }

        function guardarEdicion(id) {
            const nuevoTexto = document.getElementById('editar-' + id).value;
            fetch(location.href, {
                method: 'POST',
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                body: new URLSearchParams({ editar_id: id, editar_texto: nuevoTexto })
            }).then(() => location.reload());
        }
        </script>
</body>
</html>