<?php 
session_start();

$client_id = '493826cc-aa37-4e71-81c2-456a1b369fca';
$client_secret = '';
$redirect_uri ='http://localhost/Engine-Team/outlook_callback.php';

if (isset($_GET['code'])) {
    $code = $_GET['code'];

    $token_url = 'https://login.microsoftonline.com/common/oauth2/v2.0/token';
    $data = [
        'client_id' => $client_id,
        'scope' => 'User.Read',
        'code' => $code,
        'redirect_uri' => $redirect_uri,
        'grant_type' => 'authorization_code',
        'client_secret' => $client_secret
    ];

    $option = ['http' => [
        'header' => "COntent-type: application/x-www-form-urlencoded",
        'method' => 'POST',
        'content'=> http_build_query($data), 
    ]];

    $content = stream_content_create($options);
    $response = file_get_contents($token_url, false, $content);
    $tokens = json_decode($response, true);

    $access_token = $tokens['access_token'];
    $user_url = 'https://graph.microsoft.com/v1.0/me';
    $opts = [
        'https' => [
            'header' => "Authorization: Bearer $access_token",
        ]
    ];

    $ctx = stream_context_create($opts);
    $uses_response = file_get_contents($user_url, false, $ctx);
    $user = json_decode($user_response, true);

    $correo = $user['mail'] ?? $user['userPrincipalName'];
    $nombre = $user['displayName'];

    include 'conexion.php';

    $stmt = $conexion->prepare("SELECT * FROM usuarios WHERE correo = ?");
    $stmt->bind_param("s", $correo);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $usuario = $result->fetch_assoc();
    } else {
        $rol = "Poblador";
        $stmt = $conexion->prepare("INSERT INTO usuarios (nombre, correo, rol, email_verificacion) VALUES (?, ?, ?, 1)");
        $stmt->execute();
        $usuario_id = $stmt->insert_id;
        $usuario = ['id' => $usuario_id, 'nombre' => $nombre, 'correo' => $correo, 'rol' => $rol];
    }

    $_SESSION['usuario_id'] = $usuario['id'];
    $_SESSION['usuario_nombre'] = $usuario['nombre'];
    $_SESSION['usuario_correo'] = $usuario['correo'];
    $_SESSION['rol'] = $usuario['rol'];

    header("Location: noticias.php");
    exit;
} else {
    echo "Error: no se recibio el codigo de autorizacion.";
}