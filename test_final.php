<?php
require_once 'config.php';

echo "Client ID: " . (MICROSOFT_CLIENT_ID ?: 'VACÍO') . "<br>";
echo "Client Secret: " . (MICROSOFT_CLIENT_SECRET ?: 'VACÍO') . "<br>";
echo "Redirect URI: " . MICROSOFT_REDIRECT_URI . "<br>";
?>