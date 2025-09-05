<?php
session_start();
require __DIR__ . '/vendor/autoload.php';
require 'conexion.php';

use TheNetworg\OAuth2\Client\Provider\Azure;

$provider = new Azure([
    'clientId'                => 'aca24afd-ef2b-49b0-ac5d-bc393710575b',
    'clientSecret'            => '2d37aaf7-3e9a-4872-b749-ba852ccc00ad',
    'redirectUri'             => 'http://localhost/Enginer-Team/outlook_callback.php',
    'utlAuthorize'            => 'https://login.microsoftonline.com/common/oauth2/v2.0/authorize',
    'urlAccessToken'          => 'https://login.microsoftonline.com/common/oauth2/v3.0/token',
    'urlResourceOwnerDetails' => 'https://graph.microsoft.com/v1.0/me',             
]);


if (empty($_GET['state']) || ($_GET['state'] !== $_SESSION['oauth2state'])) {
    unset($_SESSION['oauth2state']);
    exit('Estado invalido, intenta de nuevo');
}

try {
    $token = $provider->getAccessToken('authorization_code', [
        'code' => $_GET['code']
    ]);

    $user = $provider->getResourceOwner($token);
    $userData = $user->toArray();

    $nombre = $userData['displayName'] ?? 'Usuario Outlook';
    $correo = $userData['mail'] ?? ($userData['userPrincipalName'] ?? null);

    if (!$correo) {
        exit("No se pudo obtener el correo electrónico desde Microsoft.");
    }

    $stmt = $conexion->prepare("SELECT * FROM usuarios WHERE correo = ?");
    $stmt->bind_param("s", $correo);
    $stmt->execute();
    $resultado = $stmt->get_result();
    $usuario = $resultado->fetch_assoc();

    if ($usuario) {
        $_SESSION['usuario_id'] = $usuario['id'];
        $_SESSION['usuario_nombre'] = $usuario['usuario'];
        $_SESSION['usuario_rol'] = $usuario['rol'];
    } else {
        $rol = 'Poblador';
        $avatar = "imagenes/avatar.png";

        $stmt = $conexion->prepare("INSERT INTO usuarios (nombre, correo, avatar, rol) VALUES (?, ?, ?, ?)");
        $stmt->bind_param("ssss", $nombre, $correo, $avatar, $rol);
        $stmt->execute();
        
        $_SESSION['usuario_id'] = $stmt->insert_id;
        $_SESSION['usuario_nombre'] = $nombre;
        $_SESSION['usuario_rol'] = $rol;
    }

    header("Location: inicio.php");
    exit;


} catch (Exception $e) {
    exit($e->getMessage());
}