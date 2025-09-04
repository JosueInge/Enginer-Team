<?php
session_start();
require __DIR__ . '/vendor/autoload.php';

use TheNetworg\OAuth2\Client\Provider\Azure;

if (isset($_GET['error'])) {
    exit('error de Microsoft: ' .htmlspecialchars($_GET['error_description'] ?? $_GET['error']));
}

if (
    !isset($_GET['state']) ||
    !isset($_SESSION['oauth2state']) ||
    $_GET['state'] !== $_SESSION['oauth2state']
) {
    unset($_SESSION['oauth2state']);
    exit('Estado invalido, intenta de nuevo');
}

$provider = new Azure([
    'clientId'                => 'aca24afd-ef2b-49b0-ac5d-bc393710575b',
    'clientSecret'            => '.Pn8Q~E3K4mAhy-J1dmr05rMyvDdazAbrmtICa1K',
    'redirectUri'             => 'http://localhost/Enginer-Team/outlook_callback.php',
    'urlAuthorize'            => 'https://login.microsoftonline.com/common/oauth2/v2.0/authorize',
    'urlAccessToken'          => 'https://login.microsoftonline.com/common.oauth2/v2.0/token',
]);

if (!isset($_GET['code'])) {
    exit("No se recibio el codido de autenticacion.");
}


$token = $provider->getAccessToken('authorization_code', [
    'code' => $_GET['code']
]);

$user = json_decode($me->getBody()->getContents(), true);
