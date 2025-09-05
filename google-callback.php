<?php
require_once 'vendor/autoload.php';
require_once 'conexion.php';
session_start();

$client = new Google_Client();
$client->setClientId('512235154991-hgfl77nhp3qffmqf1schu1smgea7k8q3.apps.googleusercontent.com');
$client->setClientSecret('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');
$client->setRedirectUri('http://localhost/Enginer-Team/registro.php');

if (isset($_GET['code'])) {
    $client->authenticate($_GET['code']);
    $token = $client->getAccessToken();
    $client->setAccessToken($token);

    $oauth = new Google_Service_Oauth2($client);
    $userInfo = $oauth->userinfo->get();

    $correo = $userInfo->email;
    $nombre = $userInfo->name;

    $stmt = $conexion->prepare("SELECT id FROM usuarios WHERE correo = ?");
    $stmt->bind_param("s", $correo);
    $stmt->execute();
    $resultado = $stmt->get_result();

    if ($resultado->num_rows > 0) {
        $usuario = $resultado->fetch_assoc();
        $_SESSION['usuario_id'] = $usuario['id'];
    } else {
        $rol = "Poblador";
        $email_verificacion = 1;
        $token_verificacion = '';
        $stmt = $conexion->prepare("INSERT INTO usuarios (nombre, correo, contraseña, token_verificacion, email_veroficacion, rol) VALUES (?, ?, ''. ?, ?, ?");
        $stmt->bind_param("sssis", $nombre, $correo, $token_verificacion, $email_verificacion, $rol);
        $stmt->execute();
        $_SESSION['usuarios_id'] = $stmt->insert_id;
    }

    $_SESSION['usuario_nombre'] = $nombre;
    header("Location: home.php");
    exit();
}
?>