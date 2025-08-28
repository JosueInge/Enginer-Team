<?php
require_once 'vendor/autoload.php';

$client = new Google_Client();
$client->setClientId('512235154991-hgfl77nhp3qffmqf1schu1smgea7k8q3.apps.googleusercontent.com');
$client->setClientSecret('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');
$client->setRedirectUri('http://localhost/Engine-Team/registro.php');
$client->addScope('email');
$client->addScope('profile');

$auth_url = $client->createAuthUrl();
header('Location: ' . filter_var($auth_url, FILTER_SANITIZE_URL));
exit;