<?php
session_start();
include 'conexion.php';

if (!isset($_SESSION['usuario_id']) || $_SESSION['usuario_rol'] !== 'Administrador') {
    header("Location: login.php");
    exit();
}

$error = null;

if (!isset($_GET['id'])) {
    header("Location: noticias.php");
    exit();
}
$id_noticia = intval($_GET["id"]);

$stmt = $conexion->prepare("SELECT * FROM propuestas_noticias WHERE id = ?");
$stmt->bind_param("i", $id_noticia);
$stmt->execute();
$noticia = $stmt->get_result()->fetch_assoc();
$stmt->close();

if (!$noticia) {
    header("Location: noticia.php");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $titulo = $_POST['titulo'] ?? '';
    $descripcion = $_POST['descripcion'] ??'';

    $stmt = $conexion->prepare("UPDATE propuestas_noticias SET titulo = ?, descripcion = ? WHERE id = ?");
    $stmt->bind_param("ssi", $titulo, $descripcion, $id_noticia);
    if ($stmt->execute()) {
        header("Location: noticias.php?actualizada=1");
        exit();
    } else {
        $error = "Error al actualizar: " . $conexion->error;
    }
    $stmt->close();
    
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>Editar Noticia</title>
    <style>
        * {
            box-sizing: border-box;
        }
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: #f9fafb;
            margin: 0;
            color: #333;
            line-height: 1.6;
        }
        a {
            text-decoration: none;
            color: #0d5c9b;
            font-weight: 600;
            transition: color 0.3s ease;
        }
        a:hover {
            color: #074973;
        }

        header {
            background-color: #0d5c9b;
            color: #fff;
            padding: 12px 24px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            box-shadow: 0 2px 6px rgb(13 92 155 / 0.4);
            position: sticky;
            top: 0;
            z-index: 100;
        }
        .logo img {
            height: 48px;
            user-select: none;
        }

        nav.informacion a {
            color: white;
            font-weight: 600;
            font-size: 1rem;
            text-decoration: none;
            transition: color 0.3s ease;
        }

        nav.informacion a:hover,
        nav.informacion a:focus {
            color: #a8d0ff;
            text-decoration: none;
        }

        .contenedor-principal {
            max-width: 720px;
            background: #fff;
            margin: 40px auto 60px;
            padding: 30px 40px;
            border-radius: 10px;
            box-shadow: 0 4px 16px rgb(0 0 0 / 0.1);
        }
        h1 {
            color: #0d5c9b;
            margin-bottom: 25px;
            font-weight: 700;
            font-size: 2rem;
            text-align: center;
        }

        .campo {
            margin-bottom: 22px;
        }
        .campo label {
            display: block;
            font-weight: 600;
            margin-bottom: 8px;
            color: #555;
            font-size: 1rem;
            user-select: none;
        }
        .campo input[type="text"],
        .campo textarea {
            width: 100%;
            padding: 14px 16px;
            border: 1.8px solid #ccc;
            border-radius: 6px;
            font-size: 1rem;
            font-family: inherit;
            transition: border-color 0.3s ease;
            resize: vertical;
            min-height: 100px;
        }
        .campo input[type="text"]:focus,
        .campo textarea:focus {
            outline: none;
            border-color: #0d5c9b;
            box-shadow: 0 0 6px rgba(13, 92, 155, 0.4);
        }

        .boton-publicar {
            background-color: #0d5c9b;
            color: white;
            border: none;
            padding: 15px 0;
            border-radius: 8px;
            cursor: pointer;
            font-size: 1.1rem;
            font-weight: 700;
            width: 100%;
            transition: background-color 0.3s ease, box-shadow 0.3s ease;
            user-select: none;
        }
        .boton-publicar:hover,
        .boton-publicar:focus {
            background-color: #074973;
            box-shadow: 0 4px 12px rgba(7, 73, 115, 0.6);
        }

        .error {
            background-color: #ffe6e6;
            border: 1px solid #ff4d4d;
            color: #b00000;
            padding: 12px 15px;
            border-radius: 6px;
            margin-bottom: 22px;
            font-weight: 600;
            text-align: center;
            user-select: none;
        }
        @media (max-width: 600px) {
            .contenedor-principal {
                margin: 20px 15px 40px;
                padding: 25px 20px;
            }
            h1 {
                font-size: 1.6rem;
            }
            .campo input[type="text"],
            .campo textarea {
                padding: 12px 14px;
            }
            .boton-publicar {
                font-size: 1rem;
                padding: 14px 0;
            }
        }
    </style>
</head>
<body>
<header>
    <div class="logo">
        <img src="imagenes/logo.png" alt="logo" />
    </div>
    <nav class="informacion">
        <a href="inicio.php">Volver a Noticias</a>
    </nav>
</header>

<div class="contenedor-principal">
    <h1>Editar Noticia</h1>

    <?php if ($error): ?>
        <div class="error"><?php echo htmlspecialchars($error); ?></div>
    <?php endif; ?>

    <form method="POST" novalidate>
        <div class="campo">
            <label for="titulo">Título:</label>
            <input
                type="text"
                id="titulo"
                name="titulo"
                value="<?php echo htmlspecialchars($noticia['titulo']); ?>"
                required
                autocomplete="off"
            />
        </div>

        <div class="campo">
            <label for="descripcion">Descripción:</label>
            <textarea
                id="descripcion"
                name="descripcion"
                required
                autocomplete="off"
            ><?php echo htmlspecialchars($noticia['descripcion']); ?></textarea>
        </div>

        <button class="boton-publicar" type="submit">Guardar Cambios</button>
    </form>
</div>
</body>
</html>
