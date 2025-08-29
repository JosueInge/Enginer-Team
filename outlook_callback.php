<?php
require 'vendor/autoload.php';
include 'conexion.php';
session_start();

$clientId = "493826cc-aa37-4e71-81c2-456a1b369fca";
$clientSecret = "ad9b6a4a-6687-4219-96b2-c7e021b219d1";
$redirectUri = "http://localhost/Enginer-Team/outlook_callback.php";
$tenantId = "common";

$provider = new TheNetworg\OAuth2\Client\Provider\Azure([
    'clientId'          => $clientId,
    'clientSecret'      => $clientSecret,
    'redirectUri'       => $redirectUri,
    'urlAuthorize'      => "http://login.microsoftonline.com/$tenantId/oauth2/v2.0/authorize",
    'urlAccessToken'    => "http://login.microsoftonline.com/$tenantId/oauth2/v2.0/token",
    'scopas'            => ['openid', 'profile', 'email']
]);

if (isset($_GET['code'])) {
    $token = $provider->getAccessToken('authorization_code', [
        'code' => $_GET['code']
    ]);

    $user =  $provider->get("https://graph.microsoft.com/v1.0/me", $token);

    $correo = $user['mail'] ?? $user['userPrincipalName'];
    $nombre = $user['displayName'];
    $avatar = "imagenes/avatar-defacult.png";

    $stmt = $conexion->prepare("SELECT id, nombre, correo, avatar, rol FROM usuarios WHERE correo = ?");
    $stmt->bind_param("s", $correo);
    $stmt->execute();
    $resultado = $stmt->get_result();

    if ($resultado->num_rows > 0) {
        $usuario = $resultado->fetch_assoc();
        $_SESSION['usuario_id'] = $usuario['id'];
        $_SESSION['usuario_nombre'] = $usuario['nombre'];
        $_SESSION['usuario_correo'] = $usuario['correo'];
        $_SESSION['usuario_imagen'] = $usuario['avatar'];
        $_SESSION['usuario_imagen'] = $usuario['avatar'];
        $_SESSION['usuario_rol'] = $usuario['rol'];

        header("Location: inicio.php");
        exit();
    } else {
        $rol = "usuario";
        $email_verificacion = 1;

        $stmt = $conexion->prepare("INSERT INTO usuarios (nombre, correo, avatar, rol, email_verificacion) VALUES (?, ?, ?, ?, ?)");
        $stmt->bind_param("ssssi", $nombre, $correo, $rol, $email_verificacion);
        $stmt->execute();

        $nuevoId = $stmt->insert_id;

        $_SESSION['usuario_id'] = $nuevoId;
        $_SESSION['usuario_nombre'] = $nombre;
        $_SESSION['usuario_correo'] = $correo;
        $_SESSION['usuario_imagen'] = $avatar;
        $_SESSION['usuario_rol'] = $rol;

        header("Location: inicio.php");
        exit();
    }
}
