<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Mover las configuraciones de sesión ANTES de session_start()
if (session_status() === PHP_SESSION_NONE) {
    ini_set('session.cookie_lifetime', 3600);
    ini_set('session.gc_maxlifetime', 3600);
    session_start();
}

function getEnvVariable($key, $default = null){
    $envFile = __DIR__ . '/.env';

    if (file_exists($envFile)) {
        $lines = file($envFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
        foreach ($lines as $line) {
            if (strpos(trim($line), '#') === 0) continue;

            if (strpos($line, '=') !== false) {
                list($name, $value) = explode('=', $line, 2);
                $name = trim($name);
                $value = trim($value);

                if ($name === $key) {
                    return $value;
                }
            }
        }
    }
    return getenv($key) ?: $default;
}

define('MICROSOFT_CLIENT_ID', getEnvVariable('MICROSOFT_CLIENT_ID'));
define('MICROSOFT_CLIENT_SECRET', getEnvVariable('MICROSOFT_CLIENT_SECRET'));
define('MICROSOFT_REDIRECT_URI', getEnvVariable('MICROSOFT_REDIRECT_URI'));
define('MICROSOFT_TENANT', getEnvVariable('MICROSOFT_TENANT', 'common'));

if (MICROSOFT_CLIENT_ID === '') {
    die("ERROR: Configura MICROSOFT_CLIENT_ID en el archivo .env");
}

error_reporting(E_ALL);
ini_set('display_errors', 1);
date_default_timezone_set('America/Lima');
?>