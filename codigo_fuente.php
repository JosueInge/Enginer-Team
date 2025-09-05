<?php
session_start();
require_once __DIR__ . '/vendor/autoload.php';

use TheNetworg\OAuth2\Client\Provider\Azure;

$provider = new Azure([
    'clientId'                => 'TU_CLIENT_ID',
    'clientSecret'            => 'TU_CLIENT_SECRET',
    'redirectUri'             => 'http://localhost/Enginer-Team/outlook_callback.php',
    'urlAuthorize'            => 'https://login.microsoftonline.com/common/oauth2/v2.0/authorize',
    'urlAccessToken'          => 'https://login.microsoftonline.com/common/oauth2/v2.0/token',
    'urlResourceOwnerDetails' => 'https://graph.microsoft.com/v1.0/me',
]);

// Si no hay "code", redirigimos a Microsoft
if (!isset($_GET['code'])) {
    $authUrl = $provider->getAuthorizationUrl([
        'scope' => ['openid', 'profile', 'email', 'User.Read']
    ]);
    $_SESSION['oauth2state'] = $provider->getState();
    header('Location: ' . $authUrl);
    exit;
}
