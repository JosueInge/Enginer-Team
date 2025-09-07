<?php
session_start();
require_once 'vendor/autoload.php';
require 'conexion.php';

use TheNetworg\OAuth2\Client\Provider\Azure;

$clientId = "aca24afd-ef2b-49b0-ac5d-bc393710575b";
$clientSecret = "2d37aaf7-3e9a-4872-b749-ba852ccc00ad";
$tenantId = "common";
$redirectUri = "http://localhost/Enginer-Team/outlook_callback.php";


$provider = new Azure([
    'clientId'                => $clientId,
    'clientSecret'            => $clientSecret,
    'redirectUri'             => $redirectUri,
    'utlAuthorize'            => $tenantId,           
]);


if (empty($_GET['state']) || ($_GET['state'] !== $_SESSION['oauth2state'])) {
    unset($_SESSION['oauth2state']);
    exit('Estado invalido, intenta de nuevo');
}

$token = $provider->getAccessToken('authorization_code', [
    'code' => $_GET['code']
]);

$user = $provider->get('me');

$correo = $user['mail'] ?? $user['userPrincipalName'];
$nombre = $user['displayName'] ?? 'Usuario';

$stmt = $conexion->prepare("INSERT INTO usuarios (nombre, correo, rol) VALUES (?, ?, 'Poblador')
                            ON DUPLICATE KEY UPDATE nombre=VALUES(nombre)");
$stmt->bind_param("ss", $nombre, $correo);
$stmt->execute();

$_SESSION['usuario_id'] = $conexion->insert_id ?: $conexion->query("SELECT id FROM cusuarios WHERE correo='$correo'")->fetch_assoc()['id'];
$_SESSION['usuario_nombre'] = $nombre;
$_SESSION['usuario_correo'] =  $correo;
$_SESSION['usuario_rol'] = 'Poblador';

header("Location: inicio.php");
exit;

