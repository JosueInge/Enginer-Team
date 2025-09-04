<?php
require 'vendor/autoload.php';
session_start();

use TheNetworg\OAuth2\Client\Provider\Azure;

$provider = new Azure([
    'clientId'                => 'aca24afd-ef2b-49b0-ac5d-bc393710575b',
    'clientSecret'            => '.Pn8Q~E3K4mAhy-J1dmr05rMyvDdazAbrmtICa1K',
    'redirectUri'             => 'http://localhost/Enginer-Team/outlook_callback.php',
    'urlAuthorize'            => 'https://login.microsoftonline.com/common/oauth2/v2.0/authorize',
    'urlAccessToken'          => 'https://login.microsoftonline.com/common.oauth2/v2.0/token',
    'scopes'                  => ['openid', 'profile', 'offline_access', 'User.Read'],
]);

$authUrl = $provider->getAuthorizationUrl();
$_SESSION['oauth2state'] = $provider->getState();

header('Location: ' . $authUrl);
exit;
    