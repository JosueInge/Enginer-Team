<?php
require_once '../vendor/autoload.php';
require_once '../config.php';
require_once '../conexion.php';

$provider = new TheNetworg\OAuth2\Client\Provider\Azure([
    'clientId'          => MICROSOFT_CLIENT_ID,
    'clientSecret'      => MICROSOFT_CLIENT_SECRET,
    'redirectUri'       => MICROSOFT_REDIRECT_URI,
    'defaultEndPointVersion' => '2.0',
    'scopes'            => ['openid', 'profile', 'email', 'User.Read'],
]);

$sessionState = $_SESSION['oauth2state'] ?? null;
$cookieState = $_COOKIE['oauth2state_backup'] ?? null;
$requestState = $_GET['state'] ?? null;

$expectedState = $sessionState ?? $cookieState;

if (empty($requestState) || empty($expectedState) || ($requestState !== $expectedState)) {
    error_log("STATE MISMATCH: Request=$requestState, Session=$sessionState, Cookie=$cookieState");
    exit('Error de seguridad. Por favor, <a href="../registro.php">intenta nuevamente</a>.');
}

if (isset($_SESSION['code_processed']) && $_SESSION['code_processed'] === true){
    header("Location: ../inicio.php");
    exit();
}

if (isset($_GET['code'])) {
    try {
        $_SESSION['code_processed'] = true;

        $token = $provider->getAccessToken('authorization_code', [
            'code' => $_GET['code']
        ]);

        if (!$token || !$token->getToken()) {
            throw new Exception('No se pudo obtener el token de acceso');
        }

        $user = $provider->get('me', $token);

        if (!isset($user['id'])) {
            throw new Exception('No se pudo obtener la informacion del usuario'); 
        }

        $outlook_id = $user['id'];
        $correo = $user['mail'] ?? $user['userPrincipalName'];
        $nombre = $user['displayName'] ?? 'Usuario Outlook';

        if (!$correo) {
            throw new Exception('No se pudo obtener el correo electronico.');
        }

        $stmt = $conexion->prepare("SELECT * FROM usuarios_outlook WHERE outlook_id = ? OR correo = ?");
        $stmt->bind_param("ss", $outlook_id, $correo);
        $stmt->execute();
        $resultado = $stmt->get_result();

        if ($resultado->num_rows > 0) {
            $usuario = $resultado->fetch_assoc();
            $_SESSION['usuario_id'] = $usuario['id'];
            $_SESSION['usuario_correo'] = $usuario['correo'];
            $_SESSION['usuario_nombre'] = $usuario['nombre'];
            $_SESSION['usuario_rol'] = $usuario['rol'];
            $_SESSION['usuario_outlook'] = true;

        } else {
            $rol = "Poblador";
            $stmt =  $conexion->prepare("INSERT INTO usuarios_outlook (outlook_id, nombre, correo, rol) VALUES (?, ?, ?, ?)");
            $stmt->bind_param("ssss", $outlook_id, $nombre, $correo, $rol);

            if ($stmt->execute()) {
                $_SESSION['usuario_id'] = $stmt->insert_id;
                $_SESSION['usuario_correo'] = $correo;
                $_SESSION['usuario_nombre'] = $nombre;
                $_SESSION['usuario_rol'] = $rol;
                $_SESSION['login_outlook'] = true;
                $_SESSION['mensaje_bienvenida'] = "¡Bienvenido $nombre! Tu cuenta con Outlook ha sido creada.";
            } else {
                throw new Exception('Error al registrarusuario: ' . $conexion->error);
            }
        }

        unset($_SESSION['code_processed']);
        unset($_SESSION['oauth2state']);
        setcookie('oauth2state_backup', '', time() - 3600, '/');

        header("Location: ../inicio.php");
        exit();

    } catch (Exception $e) {
        unset($_SESSION['code_processed']);
        unset($_SESSION['oauth2state']);
        setcookie('oauth2state_backup', '', time() - 3600, '/');

        echo "<h3>Error en la autenticacion</h3>";
        echo "<p><strong>Mensaje:</strong> " . $e->getMessage() . "</p>";
        echo "<p>Por favor, <a href='../login.php'>intenta nuevamente</a></p>";
        exit;
    }
} else {
    exit('Codigo de autorizacion no recibido.');
}
// $clientId = getenv('5d87fbaa-789a-4d64-8b32-f0d13f4f68a7');
//$clientSecret = getenv('Adn8Q~HaTI1CYh8VLOal6at1jLipaTdggy~.6aHw');
//$redirectUri = "http://localhost/Enginer-Team/outlook/callback.php";
?>
