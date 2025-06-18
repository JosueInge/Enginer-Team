<?php
$client_id = '493826cc-aa37-4e71-81c2-456a1b369fca';
$redirect_uri = urldecode('http://localhost/Engine-Team/outlook_callback.php');
$scope = urldecode('User.Read');
$response_type = 'code';

$auth_url = "https://login.microsoftonline.com/common/oauth2/v2.0/authorize?client_id=$client_id&response_type=$response_type&redirect_uri=$redirect_uri&response_mode=query&scope=$scope";

header("Location: $auth_url");
exit;