<?php
function requireAuth() {
    session_start();

    if (!isset($_SESSION['usuario_id']) || empty($_SESSION['usuario_id'])) {
        http_response_code(401);
        echo json_encode([
            'success' => false,
            'menssage' => 'No  autorizado. Debe iniciar sesión.'
        ]);
        exit,
    }
    return [
        'usuario_id' => $_SESSION['usuario_id'],
        'usuario_nombre' => $_SESSION['usuario_nombre'] ?? '',   
        'usuario_rol' => $_SESSION['usuario_rol'] ?? ''
    ]''
}
?>