<?php
session_start();
require_once 'vendor/autoload.php';
include 'conexion.php';

use TheNetworg\OAuth2\Client\Provider\Azure;

// Configuración de la app registrada en Azure
$clientId = "TU_CLIENT_ID";
$clientSecret = "TU_CLIENT_SECRET";  // ⚠️ EL MISMO QUE EN outlook_login.php
$tenantId = "common";
$redirectUri = "http://localhost/Engine-Team/outlook_callback.php";

// Crear el proveedor
$provider = new Azure([
    'clientId'     => $clientId,
    'clientSecret' => $clientSecret,
    'redirectUri'  => $redirectUri,
    'tenant'       => $tenantId,
]);

// Validar el estado
if (empty($_GET['state']) || ($_GET['state'] !== $_SESSION['oauth2state'])) {
    unset($_SESSION['oauth2state']);
    exit('Estado invalido, intenta de nuevo');
}

// Intercambiar el código por el token
$token = $provider->getAccessToken('authorization_code', [
    'code' => $_GET['code']
]);

// Obtener datos del usuario
$user = $provider->get('me');

// Extraer datos
$correo = $user['mail'] ?? $user['userPrincipalName'];
$nombre = $user['displayName'] ?? 'Usuario';

// Guardar en BD con rol Poblador por defecto
$stmt = $conexion->prepare("INSERT INTO usuarios (nombre, correo, rol) VALUES (?, ?, 'Poblador')
                            ON DUPLICATE KEY UPDATE nombre=VALUES(nombre)");
$stmt->bind_param("ss", $nombre, $correo);
$stmt->execute();

// Guardar sesión
$_SESSION['usuario_id'] = $conexion->insert_id ?: $conexion->query("SELECT id FROM usuarios WHERE correo='$correo'")->fetch_assoc()['id'];
$_SESSION['usuario_nombre'] = $nombre;
$_SESSION['usuario_correo'] = $correo;
$_SESSION['usuario_rol'] = 'Poblador';

// Redirigir
header("Location: inicio.php");
exit;
