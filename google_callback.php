<?php
session_start();
require_once 'conexion.php';
require_once "vendor/autoload.php";

$client_id = "512235154991-hgfl77nhp3qffmqf1schu1smgea7k8q3.apps.googleusercontent.com";
$client_secret = "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";
$redirect_uri = "http://localhost:8080/Enginer-Team/google_callback.php";


$client = new Google_Client();
$client->setClientId($client_id);
$client->setClientSecret($client_secret);
$client->setRedirectUri($redirect_uri);
$client->addScope("email");
$client->addScope("profile");

if (isset($_GET['code'])) {
    $token = $client->fetchAccessTokenWithAuthCode($_GET['code']);

    if (isset($token['error'])) {
        die("Error al obtener token de acceso: " . $token['error_description']);
    }

    $client->setAccessToken($token);

    $google_oauth = new Google_Service_Oauth2($client);
    $google_account_info = $google_oauth->userinfo->get();

    $nombre = $google_account_info->name;
    $email = $google_account_info->email;
    $foto = $google_account_info->picture;

    $stmt = $conexion->prepare("SELECT * FROM usuarios WHERE correo = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();
    $usuario = $result->fetch_assoc();

    if (!$usuario) {
        $rol = "Poblador";
        $stmt = $conexion->prepare("INSERT INTO usuarios (nombre, correo, avatar, rol) VALUES (?, ?, ?, ?)");
        $stmt->bind_param("ssss", $nombre, $email, $foto, $rol);
        $stmt->execute();

        $usuario_id = $conexion->insert_id;
    } else {
        $usuario_id = $usuario['id'];
        $nombre = $usuario['nombre'];
        $rol = $usuario['rol'];
    }

    $_SESSION['usuario_id'] = $usuario_id;
    $_SESSION['usuario_nombre'] = $nombre;
    $_SESSION['usuario_correo'] = $email;
    $_SESSION['usuario_imagen'] = $foto;
    $_SESSION['usuario_rol'] = $rol;

    header("Location: inicio.php");
    exit();

} else {
    echo "No se recibio el codigo de autenticacion.";
}