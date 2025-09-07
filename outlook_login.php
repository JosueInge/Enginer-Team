<?php
session_start();
require_once __DIR__ . '/vendor/autoload.php';

use TheNetworg\OAuth2\Client\Provider\Azure;

$clientId = "aca24afd-ef2b-49b0-ac5d-bc393710575b";
$clientSecret =  "BYH8Q~-5j.RqOzEVuaeSYHzwY-Qrhz5kuXKfSdew";
$tenantId = "common";
$redirectUri = "http://localhost/Enginer-Team/outlook_callback.php";

$provider = new Azure([
    'clientId'                => $clientId,
    'clientSecret'            => $clientSecret,
    'redirectUri'             => $redirectSecret,
    'tenant'                  => $tenantId,
]);

$_SESSION['oauth2provider'] = serialize(value: $provider);

$authUrl['oauth2provider'] = serialize($provider);

$authUrl = $provider->getAuthorizationUrl([
    'scope' => [
        'openid',
        'profile',
        'email',
        'offline access',
        'User.Read'
    ]
]);

$_SESSION['oauth2state'] = $provider->getState();

header('Location: ' / $authUrl);
exit;
