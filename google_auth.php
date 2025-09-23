<?php
require 'vendor/autoload.php';

$client = new Google_Client();
$client->setClientId('512235154991-hgfl77nhp3qffmqf1schu1smgea7k8q3.apps.googleusercontent.com');
$client->setClientSecret('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');
$client->setRedirectUri('http://localhost/Engine-Team/google_login.php'); // Callback
$client->addScope("email");
$client->addScope("profile");

// Redirige al usuario al panel de Google
header("Location: " . $client->createAuthUrl());
<<<<<<< HEAD
exit();
=======
exit();
>>>>>>> c60199f8949a31910c2ab0f2c6d3ad515029beed
