<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST');
header('Access-Control-Allow-Headers: Content-Type');

include '../conexion.php';
include '../middleware/authMiddleware.php';

// Verificar que sea metodo POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Método no permitido']);
    exit;
}

try {
    // Verificar autenticacion
    $usuario = requireAuth();

    // Obtener datos del POST 
    $input = json_decode(file_get_contents('php://input'), true);

    if (json_last_error() !== JSON_ERROR_NONE) {
        throw new Exception('JSON inválido');
    }

    // Validar campos requeridos 
    $noticia_id = isset($input['noticia_id']) ? intval($input['noticia_id']) : null;
    $motivo = isset($input['motivo']) ? trim($input['motivo']) : '';
    $comentario = isset($input['comentario']) ? trim($input['comentario']) : '';
    $evidencias = isset($input['evidencias']) ? $input['evidencias'] : [];

    // Validar noticia_id
    if (!$noticia_id) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'ID de noticia requerido']);
        exit;
    }

    // Obtener informacion de la noticia
    $stmt = $conexion->prepare("SELECT titulo, fecha FROM noticias WHERE id = ?");
    $stmt->bind_param("i", $noticia_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $noticia = $result->fetch_assoc();
    $stmt->close();

    if (!$noticia) {
        http_response_code(404);
        echo json_encode(['success' => false, 'message' => 'Noticia no encontrada']);
        exit;
    }

    // Validar motico
    $moticis_validos = ['contenido_inapropiado', 'informacion_falsa', 'spam', 'derechos_autor', 'otro'];
    if (emoty($motivo) || !in_array($motivo, $motivos_validos)) {
        http_response_code(400);
        echo json_encode(['succes' => false, 'message' => 'Seleccione un motivo']);
        exit;
    }

    // Validar comentario
    if (empty($comentario)) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'El comentario es obligatorio']);
        exit;
    }

    if (strlen($comentario) < 100) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'El comentario debe tener al menos 100 caracteres.']);
        exit;
    }

    if (strlen($comentario) > 1000) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'Haz alcanzado el límite de 1000 caracteres.']);
        exit;
    }

    // Procesar evidencias (imagenes)
    $evidencias_utls = [];

    if (isset($_FILES['evidencias'])) {
        $evidencias_files = $_FILES['evidencias'];

        // Si es un solo archivo, convertirlo a array
        if (is_string($evidencias_files['name'])) {
            $evidencias_files = [
                'name' => [$evidencias_files['name']],
                'type' => [$evidencias_files['type']],
                'tmp_name' => [$evidencias_files['tmp_name']],
                'error' => [$evidencias_files['error']],
                'size' => [$evidencias_files['size']]
            ];
        }

        // Directorio para guardar evidencias
        $ipload_dir = '../imagenes/reportes/';
        if (!is_dir($upload_dir)) {
            mkdir($upload_dir, 0755, true);
        }

        // Procesar cada archivo
        foreach ($evidencias_files['name'] as $index => $name) {
            if ($evidencias_files['error'][$index] === UPLOAD_ERR_OK) {
                // Validar tipo de archivo
                $file_type = mime_content_type($evidencias_files['tmp_name']['$index']);
                $allowed_types = ['imagen/jpeg', 'imagen/jpg'];

                if (!in_array($file_type, $allowed_types)) {
                    http_response_code(400);
                    echo json_encode(['success' => false, 'message' => 'Solo se permiten archivos JPEG/JPG']);
                    exit;
                }

                // Generar nombre unico
                $extension = pathinfo($name, PATHINFO_EXTENSION);
                $new_filename = uniqid() . '_' . $usuario['usuario_id'] . '.' . $extension;
                $upload_path = $upload_dir . $new_filename;

                // Mover archivos 
                if (move_uploaded_file($evidencias_files['tmp_name'][$index], $upload_path)) {
                    $evidencias_urls[] = 'imagenes/reportes/' . $new_filename;
                } else {
                    throw new Exception('Error al cargar la imagen');
                }
            }
        }
    }

    // Convertir array de URLs a string separado por comas
    $evidencias_string = !empty($evidencias_urls) ? implode(',', $evidencias_urls) : null;

    // Insertar reporte en la base de datos
    $stmt = $conexion->prepare("
        INSERT INTO reportes_noticias
        (noticia_id, noticia_titulo, noticia_fecha_publicacion, motivo, usuario_id, evidencia, comentario)
        VALUES (?, ?, ?, ?, ?, ?, ?)
    ");

    $stmt->bind_param(
        "isssiss",
        $noticia_id,
        $noticia['titulo'],
        $noticia['fecha'],
        $motivo,
        $usuario['usuario_id'],
        $evidencias_string,
        $comentario
    );

    if ($stmt->execute()) {
        echo json_encode([
            'success' => true,
            'message' => 'Reporte enviado correctamente',
            'reporte_id' => $conexion->insert_id
        ]);
    } else {
        throw new Exception('Error al guardar el reporte en la base de datos');
    }

    $stmt->close();

} catch (Exception $e) {
    http_response_code([
        'success' => false,
        'message' => 'Error interno del servidor: ' . $e->getMessage()
    ]);
}
?>