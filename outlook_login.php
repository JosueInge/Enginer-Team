<?php
require_once 'vendor/autoload.php';

session_start();

// Configuración de tu app registrada en Azure AD
$clientID = "d3017f43-525d-48ea-b29e-f520364ae153"; 
$clientSecret = "WQF8Q~QZ9UArljHR70SNBWgmCtxv~e.O631foaxt"; 
$redirectUri = "http://localhost/Enginer-Team/outlook_callback.php";
$tenant = "common"; // puede ser "common", "organizations" o "consumers"

// URL de autorización Microsoft
$authorizeUrl = "https://login.microsoftonline.com/$tenant/oauth2/v2.0/authorize";

// Scopes que pedimos (email, perfil básico)
$scope = "openid profile email User.Read";

// Generar estado aleatorio para seguridad
$_SESSION['oauth2state'] = bin2hex(random_bytes(16));

$params = [
    "client_id" => $clientID,
    "response_type" => "code",
    "redirect_uri" => $redirectUri,
    "response_mode" => "query",
    "scope" => $scope,
    "state" => $_SESSION['oauth2state']
];

// Redirigir a Microsoft
header("Location: " . $authorizeUrl . "?" . http_build_query($params));
exit;
