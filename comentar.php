<?php
session_start();
include 'conexion.php';

if (!isset($_SESSION['usuario_id'])) {
    $_SESSION['comentario'] = "Para poder comentar una noticia, debes iniciar sesión primero.";
    header("Location: login.php");
    exit();
}

if (!isset($_GET['id'])) {
    header("Location: noticias.php");
    exit();
}

$id_noticia = intval($_GET['id']);
$error = null;
$exito = null;

$stmt_noticia = $conexion->prepare("SELECT titulo FROM propuestas_noticias WHERE id = ?");
$stmt_noticia->bind_param("i", $id_noticia);
$stmt_noticia->execute();
$resultado_noticia = $stmt_noticia->get_result();
$noticia = $resultado_noticia->fetch_assoc();
$stmt_noticia->close();

if (isset($_GET['eliminar_comentario'])) {
    $id_comentario = intval($_GET['eliminar_comentario']);

    $stmt = $conexion->prepare("DELETE FROM comentarios WHERE id = ? AND (usuario_id = ? OR ? = 1)");
    $es_admin = $_SESSION['es_admin'] ?? 0;
    $stmt->bind_param("iii", $id_comentario, $_SESSION['usuario_id'], $es_admin);

    if ($stmt->execute()) {
        $exito = "Comentario eliminado correctamente";
    } else {
        $error = "Error al eliminar el comentario";
    }
    $stmt->close();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['comentario_id'])) {
        $comentario_id = intval($_POST['comentario_id']);
        $texto = trim($_POST['comentario']);
        
        if (empty($texto)) {
            $error = "El comentario no puede estar vacío";
        } else {
            $stmt = $conexion->prepare("UPDATE comentarios SET texto = ?, editado = 1 WHERE id = ? AND usuario_id = ?");
            $stmt->bind_param("sii", $texto, $comentario_id, $_SESSION['usuario_id']);
            
            if ($stmt->execute()) {
                $exito = "Comentario actualizado correctamente";
            } else {
                $error = "Error al actualizar el comentario";
            }
            $stmt->close();
        }
    } else {
        $comentario = trim($_POST['comentario']);
        
        if (empty($comentario)) {
            $error = "El comentario no puede estar vacío";
        } else {
            $stmt = $conexion->prepare("INSERT INTO comentarios (propuestas_noticias_id, usuario_id, texto, fecha) VALUES (?, ?, ?, NOW())");
            $stmt->bind_param("iis", $id_noticia, $_SESSION['usuario_id'], $comentario);
            
            if ($stmt->execute()) {
                $exito = "Comentario enviado correctamente";
            } else {
                $error = "Error al guardar el comentario: " . $conexion->error;
            }
            $stmt->close();
        }
    }
}

$stmt_comentarios = $conexion->prepare("
    SELECT c.*, u.nombre as usuario, u.id as usuario_id, u.avatar,
           (u.id = ? OR ? = 1) as puede_editar
    FROM comentarios c
    JOIN usuarios u ON c.usuario_id = u.id
    WHERE c.propuestas_noticias_id = ?
    ORDER BY c.fecha DESC
");
$es_admin = $_SESSION['es_admin'] ?? 0;
$stmt_comentarios->bind_param("iii", $_SESSION['usuario_id'], $es_admin, $id_noticia);
$stmt_comentarios->execute();
$comentarios = $stmt_comentarios->get_result()->fetch_all(MYSQLI_ASSOC);
$stmt_comentarios->close();
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Comentar Noticia - Comunicado Digital</title>
    <style>
        * 
        {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }
        body {
            font-family: Arial, sans-serif;
            background-color: #f5f5f5;
            line-height: 1.6;
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
        .barra-navegacion {
            background-color: #bebaba;
            display: flex;
            justify-content: space-around;
            padding: 10px;
            position: fixed;
            top: 70px;
            left: 0;
            right: 0;
            z-index: 999;
        }
        .contenido-principal {
            margin-top: 130px;
            padding: 20px;
            max-width: 800px;
            margin-left: auto;
            margin-right: auto;
            background: white;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }
        .titulo-noticia {
            color: #0d5c9b;
            margin-bottom: 20px;
            font-size: 24px;
        }
        .formulario-comentario {
            margin-bottom: 30px;
            padding: 20px;
            background: #f9f9f9;
            border-radius: 8px;
            border: 1px solid #ddd;
        }
        .campo-comentario {
            margin-bottom: 15px;
        }
        .campo-comentario textarea {
            width: 100%;
            padding: 12px;
            border: 1px solid #ccc;
            border-radius: 4px;
            min-height: 100px;
            resize: vertical;
        }
        .boton-enviar {
            background-color: #0d5c9b;
            color: white;
            border: none;
            padding: 10px 20px;
            border-radius: 4px;
            cursor: pointer;
            font-size: 16px;
            transition: background-color 0.3s;
        }
        .boton-enviar:hover {
            background-color: #0a4a7a;
        }
        .lista-comentarios {
            margin-top: 20px;
        }
        .comentario {
            padding: 15px;
            margin-bottom: 15px;
            background: #fff;
            border-radius: 8px;
            border: 1px solid #eee;
        }
        .comentario-usuario {
            font-weight: bold;
            color: #0d5c9b;
            margin-bottom: 5px;
        }
        .comentario-fecha {
            font-size: 12px;
            color: #666;
            margin-bottom: 10px;
        }
        .comentario-texto {
            line-height: 1.5;
        }
        .error {
            color: #d9534f;
            padding: 10px;
            margin-bottom: 15px;
            background-color: #f2dede;
            border: 1px solid #ebccd1;
            border-radius: 4px;
        }
        .exito {
            color: #3c763d;
            padding: 10px;
            margin-bottom: 15px;
            background-color: #dff0d8;
            border: 1px solid #d6e9c6;
            border-radius: 4px;
        }
        .informacion a {
            color: white;
            text-decoration: none;
            margin-left: 15px;
        }
        .Buscador {
            display: flex;
            align-items: center;
            gap: 8px;
        }
        .Buscador img {
            width: 20px;
            height: 20px;
        }
        .Buscador input {
            padding: 8px 12px;
            border: 1px solid #ccc;
            border-radius: 4px;
            font-size: 14px;
        }
        .barra-navegacion a {
            color: #333;
            text-decoration: none;
            padding: 5px 10px;
        }
        .barra-navegacion a:hover {
            color: #0d5c9b;
        }
        .comentario {
            position: relative;
            padding: 15px;
            margin-bottom: 15px;
            background: #f9f9f9;
            border-radius: 8px;
            border: 1px solid #eee;
        }
        
        .comentario-header {
            display: flex;
            align-items: center;
            margin-bottom: 10px;
        }
        
        .comentario-avatar {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            margin-right: 10px;
            object-fit: cover;
        }
        
        .comentario-usuario {
            font-weight: bold;
            color: #0d5c9b;
        }
        
        .comentario-fecha {
            font-size: 12px;
            color: #666;
            margin-left: auto;
        }
        
        .comentario-texto {
            margin-left: 50px;
            line-height: 1.5;
        }
        
        .comentario-editado {
            font-size: 12px;
            color: #999;
            font-style: italic;
        }
        
        .comentario-acciones {
            margin-top: 10px;
            margin-left: 50px;
            display: flex;
            gap: 10px;
        }
        
        .accion-comentario {
            font-size: 13px;
            color: #666;
            text-decoration: none;
            cursor: pointer;
        }
        
        .accion-comentario:hover {
            text-decoration: underline;
        }
        
        .form-editar-comentario {
            margin-top: 10px;
            margin-left: 50px;
        }
        
        .form-editar-comentario textarea {
            width: 100%;
            padding: 10px;
            border: 1px solid #ddd;
            border-radius: 4px;
            margin-bottom: 5px;
        }
        
        .form-editar-comentario button {
            padding: 5px 10px;
            background: #0d5c9b;
            color: white;
            border: none;
            border-radius: 4px;
            cursor: pointer;
        }
        .toast {
            visibility: hidden;
            min-width: 250px;
            background-color: #0d5c9b;
            color: white;
            text-align: center;
            border-radius: 5px;
            padding: 12px 20px;
            position: fixed;
            z-index: 9999;
            left: 50%;
            bottom: 30px;
            transform: translateX(-50%);
            font-weight: bold;
            opacity: 0;
            transition: opacity 0.5s ease-in-out, visibility 0s linear 0.5s;
        }

        .toast.visible {
            visibility: visible;
            opacity: 1;
            transition-delay: 0s;
        }

    </style>
</head>
<body>
    <header>
        <div class="logo">
            <img src="imagenes/logo.png" alt="Logo">
        </div>
        <nav class="informacion">
            <a href="noticias.php">Volver a Noticias</a>
            <a href="logout.php" id="btnSesion">Cerrar Sesión</a>
        </nav>
    </header>

    <div class="contenido-principal">
        <h1>Comentarios: <?= htmlspecialchars($noticia['titulo']) ?></h1>
        
        <?php if ($error): ?>
            <div id="toast" class="toast"><?= htmlspecialchars( $exito) ?></div>
        <?php endif; ?>
        
        <?php if ($exito): ?>
            <div id="toast" class="toast"><?= htmlspecialchars($exito) ?></div>
            <script>
                setTimeout(() => {
                    const toast = document.getElementById('toast');
                    toast.classList.add('visible');
                    setTimeout(() => {
                        toast.classList.remove('visible');
                    }, 3000);
                }, 100);
            </script>
        <?php endif; ?>
        
        <form method="POST" class="formulario-comentario">
            <div class="campo-comentario">
                <textarea name="comentario" placeholder="Escribe tu comentario aquí..." required></textarea>
            </div>
            <button type="submit" class="boton-enviar">Enviar Comentario</button>
        </form>
        
        <div class="lista-comentarios">
            <h2>Comentarios</h2>
            
            <?php if (empty($comentarios)): ?>
                <p>No hay comentarios aún. ¡Sé el primero en comentar!</p>
            <?php else: ?>
                <?php foreach ($comentarios as $comentario): ?>
                    <div class="comentario" id="comentario-<?= $comentario['id'] ?>">
                        <div class="comentario-header">
                            <img src="<?= htmlspecialchars(!empty($comentario['avatar']) ? $comentario['avatar'] : 'imagenes/avatar-default.png') ?>"  
                                 class="comentario-avatar" 
                                 alt="<?= htmlspecialchars($comentario['usuario']) ?>">
                            <span class="comentario-usuario"><?= htmlspecialchars($comentario['usuario']) ?></span>
                            <span class="comentario-fecha">
                                <?= date('d/m/Y H:i', strtotime($comentario['fecha'])) ?>
                                <?= $comentario['editado'] ? '(editado)' : '' ?>
                            </span>
                        </div>
                        
                        <?php if (isset($_GET['editar']) && $_GET['editar'] == $comentario['id'] && $comentario['puede_editar']): ?>
                            <form method="POST" class="form-editar-comentario">
                                <input type="hidden" name="comentario_id" value="<?= $comentario['id'] ?>">
                                <textarea name="comentario"><?= htmlspecialchars($comentario['texto']) ?></textarea>
                                <button type="submit">Guardar</button>
                                <a href="comentar.php?id=<?= $id_noticia ?>" class="accion-comentario">Cancelar</a>
                            </form>
                        <?php else: ?>
                            <div class="comentario-texto">
                                <?= nl2br(htmlspecialchars($comentario['texto'])) ?>
                            </div>
                            
                            <?php if ($comentario['puede_editar']): ?>
                                <div class="comentario-acciones">
                                    <a href="comentar.php?id=<?= $id_noticia ?>&editar=<?= $comentario['id'] ?>#comentario-<?= $comentario['id'] ?>" 
                                       class="accion-comentario">Editar</a>
                                    <a href="comentar.php?id=<?= $id_noticia ?>&eliminar_comentario=<?= $comentario['id'] ?>" 
                                       class="accion-comentario" 
                                       onclick="return confirm('¿Estás seguro de eliminar este comentario?')">Eliminar</a>
                                </div>
                            <?php endif; ?>
                        <?php endif; ?>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </div>
</body>
</html>