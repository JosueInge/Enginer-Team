<?php
require_once '../vendor/autoload.php';
require_once '../config.php';

$provider = new TheNetworg\OAuth2\Client\Provider\Azure([
    'clientId'          => MICROSOFT_CLIENT_ID,
    'clientSecret'      => MICROSOFT_CLIENT_SECRET,
    'redirectUri'       => MICROSOFT_REDIRECT_URI,
    'tenant'            => MICROSOFT_TENANT,
    'defaultEndPointVersion' => '2.0',
    'scope'             => ['openid', 'profile', 'email', 'User.Read'], 
]);

$authUrl = $provider->getAuthorizationUrl([
    'scope' => ['openid', 'profile', 'email', 'User.Read'],
    'response_type' => 'code',
    'prompt' => 'select_account',
]);

$_SESSION['oauth2state'] = $provider->getState();
setcookie('oauth2state_backup', $provider->getState(), time() + 300, '/');

error_log("Session ID: " . session_id());
error_log("OAUTH2 STATE: " . $_SESSION['oauth2state']);

header('Location: ' . $authUrl);
exit;
?>