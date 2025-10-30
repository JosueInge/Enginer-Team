<?php
session_start();

// Configuración de errores (solo en desarrollo)
ini_set('display_errors', 1);
error_reporting(E_ALL);

// Incluir conexión a la base de datos
require_once 'config/database.php';

// Función para verificar si el usuario puede comentar
function puedeComentar() {
    return isset($_SESSION['tipo_usuario']) && 
           ($_SESSION['tipo_usuario'] == 'administrador' || 
            $_SESSION['tipo_usuario'] == 'poblador');
}

// Función para obtener el tipo de usuario de forma segura
function getTipoUsuario() {
    return isset($_SESSION['tipo_usuario']) ? $_SESSION['tipo_usuario'] : 'invitado';
}

// Función para obtener comentarios de la noticia
function obtenerComentariosNoticia($noticia_id, $limite = 5) {
    global $pdo;
    
    try {
        $sql = "SELECT c.*, u.nombre as nombre_usuario 
                FROM comentarios c 
                JOIN usuarios u ON c.usuario_id = u.id 
                WHERE c.noticia_id = :noticia_id 
                ORDER BY c.fecha_creacion DESC 
                LIMIT :limite";
        
        $stmt = $pdo->prepare($sql);
        $stmt->bindValue(':noticia_id', $noticia_id, PDO::PARAM_INT);
        $stmt->bindValue(':limite', $limite, PDO::PARAM_INT);
        $stmt->execute();
        
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        error_log("Error al obtener comentarios: " . $e->getMessage());
        return [];
    }
}

// Obtener ID de la noticia desde la URL
$noticia_id = isset($_GET['id']) ? intval($_GET['id']) : 0;

// Obtener información de la noticia
$noticia = null;
if ($noticia_id > 0) {
    try {
        $sql = "SELECT n.*, u.nombre as autor_nombre 
                FROM noticias n 
                JOIN usuarios u ON n.autor_id = u.id 
                WHERE n.id = :id AND n.estado = 'publicada'";
        
        $stmt = $pdo->prepare($sql);
        $stmt->bindValue(':id', $noticia_id, PDO::PARAM_INT);
        $stmt->execute();
        
        $noticia = $stmt->fetch(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        error_log("Error al obtener noticia: " . $e->getMessage());
    }
}

// Si no se encuentra la noticia, redirigir
if (!$noticia) {
    header("Location: noticias.php");
    exit();
}

// Procesar nuevo comentario (si se envió el formulario)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['comentario'])) {
    if (puedeComentar()) {
        $comentario = trim($_POST['comentario']);
        
        if (!empty($comentario)) {
            try {
                $sql = "INSERT INTO comentarios (noticia_id, usuario_id, contenido, fecha_creacion) 
                        VALUES (:noticia_id, :usuario_id, :contenido, NOW())";
                
                $stmt = $pdo->prepare($sql);
                $stmt->bindValue(':noticia_id', $noticia_id, PDO::PARAM_INT);
                $stmt->bindValue(':usuario_id', $_SESSION['usuario_id'], PDO::PARAM_INT);
                $stmt->bindValue(':contenido', $comentario, PDO::PARAM_STR);
                $stmt->execute();
                
                // Redirigir para evitar reenvío del formulario
                header("Location: ver_noticia.php?id=" . $noticia_id);
                exit();
                
            } catch (PDOException $e) {
                error_log("Error al insertar comentario: " . $e->getMessage());
                $error_comentario = "Error al publicar el comentario. Intenta nuevamente.";
            }
        } else {
            $error_comentario = "El comentario no puede estar vacío.";
        }
    } else {
        $error_comentario = "Debes iniciar sesión para comentar.";
    }
}

// Obtener comentarios para mostrar
$comentarios = obtenerComentariosNoticia($noticia_id, 5);
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($noticia['titulo']) ?> - Enginer Team</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            line-height: 1.6;
            color: #333;
            background-color: #f8f9fa;
        }

        .container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 20px;
        }

        .noticia-header {
            background: white;
            padding: 30px;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            margin-bottom: 30px;
        }

        .noticia-titulo {
            font-size: 2.5em;
            color: #2c3e50;
            margin-bottom: 15px;
            line-height: 1.2;
        }

        .noticia-meta {
            color: #7f8c8d;
            font-size: 0.9em;
            margin-bottom: 20px;
            padding-bottom: 15px;
            border-bottom: 1px solid #ecf0f1;
        }

        .noticia-contenido {
            background: white;
            padding: 30px;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            margin-bottom: 30px;
            line-height: 1.8;
            font-size: 1.1em;
        }

        .noticia-contenido img {
            max-width: 100%;
            height: auto;
            border-radius: 8px;
            margin: 20px 0;
        }

        .comentarios-section {
            background: white;
            padding: 30px;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }

        .comentarios-titulo {
            font-size: 1.5em;
            color: #2c3e50;
            margin-bottom: 20px;
            padding-bottom: 10px;
            border-bottom: 2px solid #3498db;
        }

        .alert {
            padding: 15px 20px;
            border-radius: 8px;
            margin-bottom: 20px;
            border-left: 4px solid;
        }

        .alert-info {
            background-color: #d1ecf1;
            border-color: #bee5eb;
            color: #0c5460;
        }

        .alert-warning {
            background-color: #fff3cd;
            border-color: #ffeaa7;
            color: #856404;
        }

        .login-links {
            margin-top: 15px;
        }

        .login-links a {
            display: inline-block;
            margin-right: 15px;
            padding: 8px 16px;
            background: #3498db;
            color: white;
            text-decoration: none;
            border-radius: 5px;
            transition: background 0.3s;
        }

        .login-links a:hover {
            background: #2980b9;
        }

        .comentario-form {
            margin-bottom: 30px;
        }

        .comentario-textarea {
            width: 100%;
            padding: 15px;
            border: 1px solid #ddd;
            border-radius: 8px;
            resize: vertical;
            min-height: 100px;
            font-family: inherit;
            font-size: 1em;
            margin-bottom: 15px;
        }

        .comentario-submit {
            background: #27ae60;
            color: white;
            padding: 12px 25px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            font-size: 1em;
            transition: background 0.3s;
        }

        .comentario-submit:hover {
            background: #219a52;
        }

        .comentario-item {
            border-bottom: 1px solid #ecf0f1;
            padding: 20px 0;
        }

        .comentario-item:last-child {
            border-bottom: none;
        }

        .usuario-info {
            font-weight: bold;
            color: #2c3e50;
            margin-bottom: 5px;
        }

        .fecha-comentario {
            color: #7f8c8d;
            font-size: 0.85em;
        }

        .comentario-texto {
            color: #555;
            line-height: 1.6;
            margin-top: 10px;
        }

        .ver-mas-comentarios {
            text-align: center;
            margin-top: 20px;
            padding-top: 20px;
            border-top: 1px solid #ecf0f1;
        }

        .ver-mas-link {
            display: inline-block;
            padding: 10px 20px;
            background: #3498db;
            color: white;
            text-decoration: none;
            border-radius: 5px;
            transition: background 0.3s;
        }

        .ver-mas-link:hover {
            background: #2980b9;
        }

        .error-message {
            color: #e74c3c;
            background: #fdf2f2;
            padding: 10px 15px;
            border-radius: 5px;
            margin-bottom: 15px;
            border-left: 4px solid #e74c3c;
        }

        @media (max-width: 768px) {
            .container {
                padding: 10px;
            }
            
            .noticia-titulo {
                font-size: 2em;
            }
            
            .noticia-header,
            .noticia-contenido,
            .comentarios-section {
                padding: 20px;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <!-- Encabezado de la noticia -->
        <div class="noticia-header">
            <h1 class="noticia-titulo"><?= htmlspecialchars($noticia['titulo']) ?></h1>
            <div class="noticia-meta">
                <strong>Autor:</strong> <?= htmlspecialchars($noticia['autor_nombre']) ?> | 
                <strong>Fecha:</strong> <?= date('d/m/Y', strtotime($noticia['fecha_publicacion'])) ?> | 
                <strong>Categoría:</strong> <?= htmlspecialchars($noticia['categoria'] ?? 'General') ?>
            </div>
        </div>

        <!-- Contenido de la noticia -->
        <div class="noticia-contenido">
            <?php 
            // Mostrar imagen si existe
            if (!empty($noticia['imagen'])): 
            ?>
                <img src="uploads/noticias/<?= htmlspecialchars($noticia['imagen']) ?>" 
                     alt="<?= htmlspecialchars($noticia['titulo']) ?>">
            <?php endif; ?>
            
            <div class="contenido-texto">
                <?= nl2br(htmlspecialchars($noticia['contenido'])) ?>
            </div>
        </div>

        <!-- Sección de comentarios -->
        <div class="comentarios-section">
            <h2 class="comentarios-titulo">Comentarios</h2>

            <!-- Formulario de comentarios -->
            <?php if (puedeComentar()): ?>
                <div class="comentario-form">
                    <?php if (isset($error_comentario)): ?>
                        <div class="error-message"><?= htmlspecialchars($error_comentario) ?></div>
                    <?php endif; ?>
                    
                    <form action="ver_noticia.php?id=<?= $noticia_id ?>" method="POST">
                        <textarea name="comentario" 
                                  class="comentario-textarea" 
                                  placeholder="Escribe tu comentario..." 
                                  required></textarea>
                        <button type="submit" class="comentario-submit">Publicar comentario</button>
                    </form>
                </div>
            <?php else: ?>
                <div class="alert alert-info">
                    <strong>Inicia sesión</strong> como poblador o administrador para dejar un comentario
                    <div class="login-links">
                        <a href="login.php?return=<?= urlencode($_SERVER['REQUEST_URI']) ?>">Iniciar Sesión</a>
                        <a href="registro.php">Registrarse</a>
                    </div>
                </div>
            <?php endif; ?>

            <!-- Lista de comentarios -->
            <div class="comentarios-lista">
                <?php if (!empty($comentarios)): ?>
                    <?php foreach ($comentarios as $comentario): ?>
                        <div class="comentario-item">
                            <div class="usuario-info">
                                <?= htmlspecialchars($comentario['nombre_usuario']) ?>
                                <span class="fecha-comentario">
                                    - <?= date('d/m/Y H:i', strtotime($comentario['fecha_creacion'])) ?>
                                </span>
                            </div>
                            <div class="comentario-texto">
                                <?= nl2br(htmlspecialchars($comentario['contenido'])) ?>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <div class="alert alert-info">
                        No hay comentarios aún. Sé el primero en comentar.
                    </div>
                <?php endif; ?>
            </div>

            <!-- Enlace para ver más comentarios -->
            <?php if (count($comentarios) >= 5): ?>
                <div class="ver-mas-comentarios">
                    <a href="comentarios.php?noticia=<?= $noticia_id ?>" class="ver-mas-link">
                        Ver más comentarios
                    </a>
                </div>
            <?php endif; ?>
        </div>
    </div>
</body>
</html>