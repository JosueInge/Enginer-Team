<?php
session_start();
require_once 'conexion.php';

// Configuración
$clientID = "d3017f43-525d-48ea-b29e-f520364ae153";
$clientSecret = "WQF8Q~QZ9UArljHR70SNBWgmCtxv~e.O631foaxt";
$redirectUri = "http://localhost/Enginer-Team/outlook_callback.php";
$tenant = "common";

// Verificar estado
if (!isset($_GET['state']) || $_GET['state'] !== $_SESSION['oauth2state']) {
    die("Error de validación de estado.");
}

// Intercambiar el "code" por un token
if (isset($_GET['code'])) {
    $tokenUrl = "https://login.microsoftonline.com/$tenant/oauth2/v2.0/token";

    $params = [
        "client_id" => $clientID,
        "client_secret" => $clientSecret,
        "code" => $_GET['code'],
        "redirect_uri" => $redirectUri,
        "grant_type" => "authorization_code"
    ];

    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $tokenUrl);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($params));
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    $response = curl_exec($ch);
    curl_close($ch);

    $tokenData = json_decode($response, true);

    if (isset($tokenData['error'])) {
        die("Error en la autenticación con Outlook: " . $tokenData['error_description']);
    }

    $accessToken = $tokenData['access_token'];

    // Obtener datos del usuario desde Microsoft Graph
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, "https://graph.microsoft.com/v1.0/me");
    curl_setopt($ch, CURLOPT_HTTPHEADER, ["Authorization: Bearer " . $accessToken]);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    $userResponse = curl_exec($ch);
    curl_close($ch);

    $userData = json_decode($userResponse, true);

    $outlook_id = $userData['id'];
    $nombre = $userData['displayName'];
    $email = $userData['userPrincipalName']; // correo
    $foto = null; // Podríamos pedir la foto con otra petición a Graph

    // Buscar usuario en la BD
    $stmt = $conexion->prepare("SELECT * FROM usuarios_outlook WHERE outlook_id = ?");
    $stmt->bind_param("s", $outlook_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $usuario = $result->fetch_assoc();

    if (!$usuario) {
        $rol = "Poblador";
        $stmt = $conexion->prepare("INSERT INTO usuarios_outlook (nombre, correo, outlook_id, avatar, rol) VALUES (?, ?, ?, ?, ?)");
        $stmt->bind_param("sssss", $nombre, $email, $outlook_id, $foto, $rol);
        $stmt->execute();

        $usuario_id = $conexion->insert_id;
    } else {
        $usuario_id = $usuario['id'];
        $nombre = $usuario['nombre'];
        $rol = $usuario['rol'];
        $foto = $usuario['avatar'];
    }

    // Guardar en sesión
    $_SESSION['usuario_id'] = $usuario_id;
    $_SESSION['usuario_nombre'] = $nombre;
    $_SESSION['usuario_correo'] = $email;
    $_SESSION['usuario_imagen'] = $foto;
    $_SESSION['usuario_rol'] = $rol;

    // Redirigir a noticias
    header("Location: noticias.php");
    exit;
} else {
    echo "No se recibió el código de autenticación.";
}
