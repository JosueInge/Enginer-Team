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

$reporte_id = isset($_GET['id']) ? intval($_GET['id']) : null;


if ($reporte_id === null) {
    echo "No se proporcionó ID de noticia.";
    exit();
}

$query = "SELECT r.id, n.titulo, r.fecha_reporte, u.nombre as reportero, 
          r.motivo, r.comentario, r.estado, n.id as propuestas_denuncias_id
          FROM reportesdenuncias r
          JOIN propuestas_denuncias n ON r.propuestas_denuncias_id = n.id
          JOIN usuarios u ON r.usuario_id = u.id
          WHERE r.propuestas_denuncias_id = ?";

$stmt = $conexion->prepare($query);
$stmt->bind_param("i", $reporte_id);
$stmt->execute();
$resultado = $stmt->get_result();
$reportes = $resultado->fetch_all(MYSQLI_ASSOC);
$stmt->close();

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['cambiar_estado'])) {
    $reporte_id = intval($_POST['reporte_id']);
    $nuevo_estado = $conexion->real_escape_string($_POST['nuevo_estado']);
    
    $stmt = $conexion->prepare("UPDATE reportesdenuncias SET estado = ? WHERE id = ?");
    $stmt->bind_param("si", $nuevo_estado, $reporte_id);
    $stmt->execute();
    $stmt->close();
    
    $redirigir_id = isset($_GET['id']) ? intval($_GET['id']) : 0;
    header("Location: notificacion_reporte_denuncia.php?id=$redirigir_id");
    exit();
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <title>Notificación de Reportes</title>
  <style>
    body {
      font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
      margin: 0;
      background-color: #f8f9fa;
    }

    header {
      background-color: #0d5c9b;
      color: white;
      padding: 15px 30px;
      display: flex;
      justify-content: space-between;
      align-items: center;
      box-shadow: 0 2px 5px rgba(0,0,0,0.1);
    }

    header .logo img {
      height: 50px;
    }

    header nav a {
      color: white;
      text-decoration: none;
      margin-left: 20px;
      font-size: 15px;
      transition: color 0.3s;
    }

    header nav a:hover {
      color: #e0e0e0;
    }

    .container {
      max-width: 1100px;
      margin: 40px auto;
      background-color: white;
      padding: 40px;
      border-radius: 10px;
      box-shadow: 0 0 10px rgba(0,0,0,0.05);
    }

    .container h2 {
      margin-bottom: 10px;
      font-size: 26px;
      color: #333;
    }

    .container p {
      color: #555;
      margin-bottom: 25px;
    }

    hr {
      margin-bottom: 30px;
      border: none;
      height: 2px;
      background-color: #0d5c9b;
    }

    table {
      width: 100%;
      border-collapse: collapse;
      border-radius: 8px;
      overflow: hidden;
      box-shadow: 0 0 8px rgba(0,0,0,0.05);
    }

    th, td {
      padding: 14px;
      text-align: left;
      border-bottom: 1px solid #ddd;
    }

    th {
      background-color: #f1f1f1;
      color: #333;
      font-size: 14px;
    }

    tr:hover {
      background-color: #f9f9f9;
    }

    .estado-pendiente {
      color: #e67e22;
      font-weight: bold;
    }

    .estado-revisado {
      color: #2980b9;
      font-weight: bold;
    }

    .estado-resuelto {
      color: #27ae60;
      font-weight: bold;
    }

    select {
      padding: 6px 10px;
      border-radius: 6px;
      border: 1px solid #ccc;
      background-color: #fff;
      font-size: 14px;
      transition: border-color 0.3s;
    }

    select:focus {
      outline: none;
      border-color: #0d5c9b;
    }

    .btn-accion {
      padding: 8px 14px;
      border: none;
      border-radius: 6px;
      cursor: pointer;
      color: white;
      font-weight: 600;
      font-size: 14px;
      transition: background-color 0.3s ease;
      text-decoration: none;
      display: inline-block;
    }

    .btn-ver {
      background-color: #0d5c9b;
    }

    .btn-ver:hover {
      background-color: #0a4a7a;
    }

    .mensaje-exito {
      padding: 15px;
      background-color: #dff0d8;
      color: #3c763d;
      border: 1px solid #d6e9c6;
      border-radius: 6px;
      margin-bottom: 20px;
      font-size: 15px;
    }

    td[colspan="7"] {
      text-align: center;
      font-style: italic;
      color: #888;
      padding: 30px 0;
    }
  </style>
</head>
<body>

  <header>
    <div class="logo">
      <img src="imagenes/logo.png" alt="Logo">
    </div>
    <nav>
      <a href="revision_reportes_denuncias.php">Volver</a>
      <a href="logout.php">Cerrar Sesión</a>
    </nav>
  </header>

  <div class="container">
    <h2>Notificación de reportes de denuncias</h2>
    <hr>

    <?php if (isset($_GET['exito'])): ?>
      <div class="mensaje-exito">
        <?= htmlspecialchars($_GET['exito']) ?>
      </div>
    <?php endif; ?>

    <p>Listado de denuncias reportadas por los usuarios</p>

    <table>
      <thead>
        <tr>
          <th>Título de la denuncia</th>
          <th>Fecha del reporte</th>
          <th>Reportado por</th>
          <th>Motivo</th>
          <th>Comentario</th>
          <th>Estado</th>
          <th>Acciones</th>
        </tr>
      </thead>
      <tbody>
        <?php if (empty($reportes)): ?>
          <tr>
            <td colspan="7">No hay reportes disponibles.</td>
          </tr>
        <?php else: ?>
          <?php foreach ($reportes as $reporte): ?>
            <tr>
              <td><?= htmlspecialchars($reporte['titulo']) ?></td>
              <td><?= htmlspecialchars($reporte['fecha_reporte']) ?></td>
              <td><?= htmlspecialchars($reporte['reportero']) ?></td>
              <td><?= htmlspecialchars($reporte['motivo']) ?></td>
              <td><?= htmlspecialchars($reporte['comentario']) ?></td>
              <td class="estado-<?= htmlspecialchars($reporte['estado']) ?>">
                <form method="POST" style="display: inline;">
                  <input type="hidden" name="reporte_id" value="<?= $reporte['id'] ?>">
                  <select name="nuevo_estado" onchange="this.form.submit()">
                    <option value="pendiente" <?= $reporte['estado'] == 'pendiente' ? 'selected' : '' ?>>Pendiente</option>
                    <option value="revisado" <?= $reporte['estado'] == 'revisado' ? 'selected' : '' ?>>Revisado</option>
                    <option value="resuelto" <?= $reporte['estado'] == 'resuelto' ? 'selected' : '' ?>>Resuelto</option>
                  </select>
                  <input type="hidden" name="cambiar_estado" value="1">
                </form>
              </td>
              <td>
                <a href="ver_denuncia.php?id=<?= $reporte['propuestas_denuncias_id'] ?>" class="btn-accion btn-ver">Ver Denuncia</a>
              </td>
            </tr>
          <?php endforeach; ?>
        <?php endif; ?>
      </tbody>
    </table>
  </div>

</body>
</html>