<?php
require 'vendor/autoload.php';
session_start();
require 'conexion.php';

use TheNetworg\OAuth2\Client\Provider\Azure;

$provider = new Azure([
    'clientId'                => 'aca24afd-ef2b-49b0-ac5d-bc393710575b',
    'clientSecret'            => '.Pn8Q~E3K4mAhy-J1dmr05rMyvDdazAbrmtICa1K',
    'redirectUri'             => 'http://localhost/Enginer-Team/outlook_callback.php',
    'urlAuthorize'            => 'https://login.microsoftonline.com/common/oauth2/v2.0/authorize',
    'urlAccessToken'          => 'https://login.microsoftonline.com/common.oauth2/v2.0/token',
    'scopes'                  => ['openid','profile','offline_access','User.Read'],
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

    $nombre = $userData['displayName'] ?? '';
    $correo = null;

    if (!empty($userData['mail'])) {
        $correo = $userData['mail'];
    }

    elseif (!empty($userData['userPrincipalName'])) {
        $correo = $userData['usertPrincipalName'];
    }

    elseif (!empty($userData['preferred_sername'])) {
        $correo = $userData['preferred_username'];
    }

    if (!$correo) {
        exit("No se pudo obtener el correo electronico desde Microsoft. Intenta con otra cuenta.")
    }
    $avatar = "default.png";
    $rol    = "Poblador";

    $stmt = $conexion->prepare("SELECT id, nombre, correo, rol, avatar FROM usuarios WHERE correo = ? ");
    $stmt->bind_param("s", $correo);
    $stmt->execute();
    $resultado = $stmt->get_result();

    if ($resultado->num_rows > 0) {
        $usuario = $resultado->fetch_assoc();
        $_SESSION['usuario_id']     = $usuario['id'];
        $_SESSION['usuario_nombre'] = $usuario['nombre'];
        $_SESSION['usuario_email']  = $usuario['correo'];
        $_SESSION['usuario_rol']    = $usuario['rol'];
        $_SESSION['usuario_avatar'] = $usuario['avatar'];
    } else {
        $stmt = $conexion->prepare("INSERT INTO usuarios (nombre, correo, avatar, rol) VALUES (?, ?, ?, ?)");
        $stmt->bind_param("ssss", $nombre, $correo, $avatar, $rol);
        $stmt->execute();

        $nuevoId = $stmt->insert_id;

        $_SESSION['usuario_id']     = $nuevoId;
        $_SESSION['usuario_nombre'] = $nombre;
        $_SESSION['usuario_email']  = $correo;
        $_SESSION['usuario_rol']    = $rol;
        $_SESSION['usuario_avatar'] = $avatar;
    }

    header("Location: inicio.php");
    exit();

} catch (\League\OAuth2\Client\Provider\Exception\IdentityProviderException $e) {
    exit($e->getMessage());
}