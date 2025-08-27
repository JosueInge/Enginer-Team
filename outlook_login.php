<?php

session_start();

$clientID = "493826cc-aa37-4e71-81c2-456a1b369fca";
$tenantID = "common";
$redirectUri = "http://localhost/Engine-Team/outlook_callback.php";
// 554ad9e4-78b2-494d-bc83-9759b2c39ea8 (inquilino)
$scope = "User.Read ofline_access openid email profile"; 

$authUrl = "https://login.microsoftonline.com/$tenantID/oauth2/v2.0/authorize?" . http_build_query([
    'client_id' => $clientID,
    'client_type' => 'code',
    'redirectUri' => $redirectUri,
    'response_mode' => 'query',
    'scope' => $scope,
    'state' => bin2hex(random_bytes(16))
]);

header("Location: $authUrl");
exit;
    