<?php
session_start();

$_SESSION = [];

if (ini_get("session.use_cookies")) {
    $params = session_get_cookie_params();
    setcookie(session_name(), '', time() - 42000, 
        $params["path"], $params["domain"],
        $params["secure"], $params["httponly"]
    );
}
// session_unset();
session_destroy();

if (isset($_SESSION['google_token'])) {
    unset($_SESSION['google_token']);
}

if (isset($_SESSION['outlook_token'])) {
    unset($_SESSION['outlook_token']);
}


header("Location: login.php");
exit();