<?php
require_once 'vendor/autoload.php';

session_start();

$clientID = "512235154991-hgfl77nhp3qffmqf1schu1smgea7k8q3.apps.googleusercontent.com";
$clientSecret = "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";
$redirectUri = "http://localhost:8080/Enginer-Team/google_callback.php";

$client = new Google_Client();
$client->setClientId($clientID);
$client->setClientSecret($clientSecret);
$client->setRedirectUri($redirectUri);
$client->addScope("email");
$client->addScope("profile");

$login_url = $client->createAuthUrl();
header("Location: " . $login_url);
exit();