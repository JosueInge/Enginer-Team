<?php
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require 'PHPMailer/PHPMailer.php';
require 'PHPMailer/SMTP.php';
require 'PHPMailer/Exception.php';
include 'conexion.php';
session_start();

$error = null;

/* ------------------------------
   Endpoint AJAX interno: verificar correo
   Si se recibe POST con check_email, responder JSON {exists: true/false}
   ------------------------------ */
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['check_email'])) {
    $emailToCheck = trim($_POST['check_email']);
    $response = ['exists' => false];

    if (filter_var($emailToCheck, FILTER_VALIDATE_EMAIL)) {
        $stmt = $conexion->prepare("SELECT id FROM usuarios WHERE correo = ?");
        $stmt->bind_param("s", $emailToCheck);
        $stmt->execute();
        $res = $stmt->get_result();
        if ($res && $res->num_rows > 0) {
            $response['exists'] = true;
        }
    } else {
        $response['error'] = 'invalid_format';
    }

    header('Content-Type: application/json');
    echo json_encode($response);
    exit();
}

/* ------------------------------
   Función de validación de contraseña (server-side)
   ------------------------------ */
function esContraseñaSegura($contraseña) {
    if (strlen($contraseña) < 8) return false;
    if (!preg_match('/[A-Z]/', $contraseña)) return false;
    if (!preg_match('/[a-z]/', $contraseña)) return false;
    if (!preg_match('/[0-9]/', $contraseña)) return false;
    if (!preg_match('/[^A-Za-z0-9]/', $contraseña)) return false;
    return true;
}

/* ------------------------------
   Lógica principal de registro (como tenías)
   ------------------------------ */
if ($_SERVER['REQUEST_METHOD'] === 'POST' && !isset($_POST['check_email'])) {
    $nombre = $_POST['nombre'] ?? '';
    $correo = $_POST['correo'] ?? '';
    $contraseña = $_POST['contraseña'] ?? '';
    $repetir = $_POST['repetir'] ?? '';
    $terminos = $_POST['terminos'] ?? '';
    $politicas = $_POST['politicas'] ?? '';

    if ($contraseña !== $repetir) { 
        $contraseñasnocoinciden = "Las contraseñas no coinciden";
    } elseif (!esContraseñaSegura($contraseña)) {
        $error = "La contraseña debe tener al menos 8 caracteres, incluyendo una mayúscula, una minúscula, un número y un carácter especial";
    } elseif ($terminos !== 'on') {
        $error = "Debes aceptar los términos y condiciones";
    } elseif ($politicas !== 'on') {
        $error = "Debes aceptar las políticas de privacidad";
    } else {
        $contraseña_hash = password_hash($contraseña, PASSWORD_DEFAULT);

        $stmt = $conexion->prepare("SELECT id FROM usuarios WHERE correo = ?");
        $stmt->bind_param("s", $correo);
        $stmt->execute();

        if ($stmt->get_result()->num_rows > 0) {
            $registroexistente = "Este correo ya está registrado, por favor ingresa un correo diferente";
        } else {
            $token = bin2hex(random_bytes(16)); // genera el Token aleatorio.
            $email_verificado = 0;
            $rol = "Poblador";
            $stmt = $conexion->prepare("INSERT INTO usuarios (nombre, correo, contraseña, token_verificacion, email_verificacion, rol) VALUES (?, ?, ?, ?, ?, ?)");
            $stmt->bind_param("ssssss", $nombre, $correo, $contraseña_hash, $token, $email_verificado, $rol);

            if ($stmt->execute()) {
                // Envía al correo solo si el insert fue exitoso
                $mail = new PHPMailer(true);
                try {
                    $mail->isSMTP();
                    $mail->Host       = 'smtp.gmail.com';
                    $mail->SMTPAuth   = true;
                    $mail->Username   = 'd4660140@gmail.com'; // tu correo
                    $mail->Password   = 'emar lypn ivdw bcwn'; // tu contraseña de aplicación de Gmail
                    $mail->SMTPSecure = 'tls';
                    $mail->Port       = 587;

                    $mail->setFrom('TUCORREO@gmail.com', 'Comunicado Digital');
                    $mail->addAddress($correo);

                    $verificar_url = "http://192.168.48.211/Engine-Team/verificar.php?token=" . $token;//link que permite validar el registro

                    $mail->isHTML(true);
                    $mail->Subject = 'Verifica tu cuenta';
                    $mail->Body    = "Hola <b>$nombre</b>,<br><br>Gracias por registrarte. Por favor haz clic en el siguiente enlace para verificar tu cuenta:<br><br>
                                     <a href='$verificar_url'>$verificar_url</a><br><br>Si no te registraste, ignora este mensaje.";

                    $mail->send();

                    $_SESSION['correo'] = "Registro exitoso. Verifica tu correo para activar tu cuenta.";
                    header("Location: login.php");
                    exit();
                } catch (Exception $e) {
                    $error = "Error al enviar el correo de verificación: {$mail->ErrorInfo}";
                }
            } else {
                $error = "Error al registrar: " . $conexion->error;
            }
        }
    }
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Crear Cuenta</title>
    <!-- Fuentes de Google -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@700&family=Inter:wght@400;500;600&display=swap" rel="stylesheet">
    <!-- Iconos -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Inter', sans-serif;
            background-color: #f5f5f5;
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 100vh;
            padding: 20px;
        }

        .container {
            background-color: white;
            border-radius: 12px;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.1);
            padding: 30px;
            width: 100%;
            max-width: 600px;
        }

        h1 {
            font-family: 'Poppins', sans-serif;
            font-size: 20px;
            font-weight: 700;
            color: #1661AC; /* Cambiado a azul como solicitado */
            text-align: center;
            margin-bottom: 10px;
        }

        .subtitle {
            font-family: 'Inter', sans-serif;
            font-size: 14px;
            color: #555555;
            text-align: center;
            margin-bottom: 25px;
        }

        .input-group {
            position: relative;
            margin-bottom: 20px;
        }

        .input-icon {
            position: absolute;
            left: 16px;
            top: 50%;
            transform: translateY(-50%);
            color: #B1B1B1;
            font-size: 18px;
            z-index: 1;
        }

        .input-icon-right {
            position: absolute;
            right: 16px;
            top: 50%;
            transform: translateY(-50%);
            color: #B1B1B1;
            font-size: 18px;
            cursor: pointer;
            z-index: 1;
        }

        input[type="text"],
        input[type="email"],
        input[type="password"] {
            width: 100%;
            padding: 12px 16px 12px 50px;
            border: 1px solid #B1B1B1;
            border-radius: 8px;
            font-family: 'Inter', sans-serif;
            font-size: 16px;
            color: #403F48;
            transition: all 0.3s ease;
        }

        input[type="text"]:focus,
        input[type="email"]:focus,
        input[type="password"]:focus {
            outline: none;
            border-color: #61C9A8;
            box-shadow: 0 0 0 3px rgba(97, 201, 168, 0.2);
            transform: scale(1.01);
        }

        input.error {
            border-color: #E85D5D;
            animation: shake 0.5s linear;
        }

        @keyframes shake {
            0%, 100% {transform: translateX(0);}
            25% {transform: translateX(-5px);}
            75% {transform: translateX(5px);}
        }

        .password-requirements {
            display: none;
            margin-bottom: 20px;
            padding: 15px;
            background-color: #f9f9f9;
            border-radius: 12px; /* Más redondeado como solicitado */
            border: 1px solid #eee;
        }

        .requirement {
            display: flex;
            align-items: center;
            margin-bottom: 8px;
            font-size: 16px;
            color: #403F48;
        }

        .requirement i {
            margin-right: 10px;
            font-size: 14px;
            width: 20px;
            height: 20px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            background-color: #B1B1B1;
        }

        .requirement.valid i {
            background-color: #61C9A8;
        }

        .checkbox-group {
            display: flex;
            align-items: center;
            margin-bottom: 15px;
        }

        input[type="checkbox"] {
            margin-right: 8px;
            width: 18px;
            height: 18px;
            accent-color: #61C9A8;
        }

        .checkbox-group label {
            font-size: 16px;
            color: #403F48;
        }

        .checkbox-group a {
            color: #1661AC;
            text-decoration: none;
        }

        .checkbox-group a:hover {
            text-decoration: underline;
        }

        .btn-continue {
            display: block;
            width: auto; /* Cambiado para que no ocupe todo el ancho */
            margin: 20px auto; /* Centrado */
            background-color: #61C9A8;
            color: #1B314B;
            border: none;
            border-radius: 16px;
            padding: 10px 30px; /* Más pequeño */
            font-family: 'Poppins', sans-serif;
            font-size: 18px; /* Un poco más pequeño */
            font-weight: bold;
            cursor: pointer;
            transition: all 0.3s ease;
        }

        .btn-continue:hover {
            background-color: #4fb598;
            box-shadow: 0 4px 12px rgba(97, 201, 168, 0.3);
        }

        .btn-continue:active {
            transform: scale(0.98);
        }

        .login-link {
            text-align: center;
            margin-bottom: 20px;
            font-size: 16px;
            color: #403F48;
        }

        .login-link a {
            color: #1661AC;
            font-weight: bold;
            text-decoration: none;
        }

        .login-link a:hover {
            text-decoration: underline;
        }

        .separator {
            display: flex;
            align-items: center;
            text-align: center;
            margin: 20px 0;
            color: #403F48;
            font-size: 20px;
        }

        .separator::before,
        .separator::after {
            content: '';
            flex: 1;
            border-bottom: 1px solid #B1B1B1;
        }

        .separator:not(:empty)::before {
            margin-right: .25em;
        }

        .separator:not(:empty)::after {
            margin-left: .25em;
        }

        .social-buttons {
            display: flex;
            gap: 15px;
        }

        .btn-social {
            flex: 1;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            padding: 8px 10px; /* Más pequeño */
            background-color: #FFFFFF;
            border: 1px solid #1661AC;
            border-radius: 8px;
            font-family: 'Inter', sans-serif;
            font-size: 13px; /* Tamaño de fuente más pequeño */
            color: #403F48;
            cursor: pointer;
            transition: all 0.3s ease;
            height: 40px; /* Altura fija para uniformidad */
        }

        .btn-social:hover {
            background-color: #f5f5f5;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
        }

        .btn-social:active {
            transform: scale(0.98);
        }

        .social-icon {
            width: 18px; /* Iconos más pequeños */
            height: 18px;
        }

        .error-message {
            display: none;
            background-color: #F8D7DA;
            color: #842029;
            border: 1px solid #F5C2C7;
            border-radius: 4px;
            padding: 8px 12px;
            margin-top: 5px;
            font-size: 16px;
            text-align: left;
        }

        @media (max-width: 768px) {
            .social-buttons {
                flex-direction: column;
            }
            
            .container {
                padding: 20px;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <form id="registerForm" method="POST" action="">
            <h1>Crea tu cuenta</h1>
            <p class="subtitle">Únete a nuestra comunidad y mantente informado al instante</p>

            <!-- Campo Nombre -->
            <div class="input-group">
                <span class="input-icon"><i class="fas fa-user"></i></span>
                <input type="text" id="nombre" name="nombre" placeholder="Nombre" value="<?= isset($_POST['nombre']) ? htmlspecialchars($_POST['nombre']) : '' ?>" required>
                <div id="nameError" class="error-message"></div>
            </div>

            <!-- Campo Correo Electrónico -->
            <div class="input-group">
                <span class="input-icon"><i class="fas fa-envelope"></i></span>
                <input type="email" id="correo" name="correo" placeholder="Correo electrónico" value="<?= isset($_POST['correo']) ? htmlspecialchars($_POST['correo']) : '' ?>" required>
                <div id="emailError" class="error-message"></div>
            </div>

            <!-- Campo Contraseña -->
            <div class="input-group">
                <span class="input-icon"><i class="fas fa-lock"></i></span>
                <input type="password" id="contraseña" name="contraseña" placeholder="Contraseña" required>
                <span class="input-icon-right" id="togglePassword">
                    <i class="fas fa-eye"></i>
                </span>
                <div id="passwordError" class="error-message"></div>
            </div>

            <!-- Indicadores de fuerza de contraseña -->
            <div id="passwordRequirements" class="password-requirements">
                <div class="requirement" id="lengthReq">
                    <i class="fas fa-times"></i>
                    <span>Mínimo 8 caracteres</span>
                </div>
                <div class="requirement" id="uppercaseReq">
                    <i class="fas fa-times"></i>
                    <span>Al menos una mayúscula</span>
                </div>
                <div class="requirement" id="lowercaseReq">
                    <i class="fas fa-times"></i>
                    <span>Al menos una minúscula</span>
                </div>
                <div class="requirement" id="numberReq">
                    <i class="fas fa-times"></i>
                    <span>Al menos un número</span>
                </div>
                <div class="requirement" id="specialcharReq">
                    <i class="fas fa-times"></i>
                    <span>Al menos un carácter especial</span>
                </div>
            </div>

            <!-- Campo Confirmar Contraseña -->
            <div class="input-group">
                <span class="input-icon"><i class="fas fa-lock"></i></span>
                <input type="password" id="repetir" name="repetir" placeholder="Confirmar contraseña" required>
                <span class="input-icon-right" id="toggleConfirmPassword">
                    <i class="fas fa-eye"></i>
                </span>
                <div id="confirmPasswordError" class="error-message"></div>
            </div>

            <!-- Checkbox Términos y Condiciones -->
            <div class="checkbox-group">
                <input type="checkbox" id="terminos" name="terminos" <?= isset($_POST['terminos']) ? 'checked' : '' ?>>
                <label for="terminos">He leído y acepto los <a href="terminos.php">Términos y Condiciones</a></label>
            </div>
            <div id="termsError" class="error-message"></div>

            <!-- Checkbox Políticas de Privacidad -->
            <div class="checkbox-group">
                <input type="checkbox" id="politicas" name="politicas" <?= isset($_POST['politicas']) ? 'checked' : '' ?>>
                <label for="politicas">He leído y acepto las <a href="politicas.php">Políticas de Privacidad</a></label>
            </div>
            <div id="privacyError" class="error-message"></div>

            <!-- Botón Continuar -->
            <button type="submit" class="btn-continue">Continuar</button>

            <!-- Enlace para iniciar sesión -->
            <div class="login-link">
                ¿Ya tienes una cuenta? <a href="login.php">Inicia sesión</a>
            </div>

            <!-- Separador -->
            <div class="separator">O</div>

            <!-- Botones de redes sociales -->
            <div class="social-buttons">
                <button type="button" class="btn-social" onclick="location.href='google_login.php'">
                    <img src="data:image/svg+xml;base64,PHN2ZyB3aWR0aD0iMTgiIGhlaWdodD0iMTgiIHhtbG5zPSJodHRwOi8vd3d3LnczLm9yZy8yMDAwL3N2ZyI+PGcgZmlsbD0ibm9uZSIgZmlsbC1ydWxlPSJldmVub2RkIj48cGF0aCBkPSJNMTcuNiA5LjJsLS4xLTEuOEg5djMuNGg0LjhDMTMuNiAxMiAxMyAxMyAxMiAxMy42djIuMmgzYTguOCA4LjggMCAwIDAgMi42LTYuNnoiIGZpbGw9IiM0Mjg1RjQiIGZpbGwtcnVsZT0ibm9uemVybyIvPjxwYXRoIGQ9Ik05IDE4YzIuNCAwIDQuNS0uOCA2LTIuMmwtMy0yLjJhNS40IDUuNCAwIDAgMS04LTIuOUgxVjEzYTkgOSAwIDAgMCA4IDV6IiBmaWxsPSIjMzRBODUzIiBmaWxsLXJ1bGU9Im5vbnplcm8iLz48cGF0aCBkPSJNNCAxMC43YTUuNCA1LjQgMCAwIDEgMC0zLjRWNUgxYTkgOSAwIDAgMCAwIDhsMy0yLjN6IiBmaWxsPSIjRkJCQzA1IiBmaWxsLXJ1bGU9Im5vbnplcm8iLz48cGF0aCBkPSJNOSAzLjZjMS4zIDAgMi41LjQgMy40IDEuM0wxNSAyLjNBOSA5IDAgMCAwIDEgNWwzIDIuNGE1LjQgNS40IDAgMCAxIDUtMy43eiIgZmlsbD0iI0VBNDMzNSIgZmlsbC1ydWxlPSJub256ZXJvIi8+PHBhdGggZD0iTTAgMGgxOHYxOEgweiIvPjwvZz48L3N2Zz4=" alt="Google" class="social-icon">
                    Continuar con Google
                </button>
                <button type="button" class="btn-social" onclick="location.href='outlook_login.php'">
                    <img src="data:image/svg+xml;base64,PHN2ZyB4bWxucz0iaHR0cDovL3d3dy53My5vcmcvMjAwMC9zdmciIHZpZXdCb3g9IjAgMCAyNCAyNCI+PHBhdGggZD0iTTQuMDMgMTBIMjB2OS4wMmEuOTguOTggMCAwIDEtLjk4Ljk4SDQuMDNhMy4wMyAzLjAzIDAgMCAxLTMuMDMtM1YxM2MwLTEuNjYgMS4zNy0zIDMuMDMtM3ptMTUuOTktNGgtOXY5aDEwVjZhLjk4Ljk4IDAgMCAwLS45OC0uOTh6TTQgMTloMTZ2LTZINFYxOXoiIGZpbGw9IiMwMDc4ZDQiLz48cGF0aCBkPSJNMTIgMTVWN2w1IDRsLTUgNHoiIGZpbGw9IiM1ZWI2ZmYiLz48L3N2Zz4=" alt="Outlook" class="social-icon">
                    Continuar con Outlook
                </button>
            </div>

            <!-- Mostrar errores de PHP -->
            <?php if (isset($error)): ?>
                <div class="error-message" style="display: block; margin-top: 20px;"><?= htmlspecialchars($error) ?></div>
            <?php endif; ?>

            <?php if (isset($contraseñasnocoinciden)): ?>
                <div class="error-message" style="display: block; margin-top: 20px;"><?= htmlspecialchars($contraseñasnocoinciden) ?></div>
            <?php endif; ?>

            <?php if (isset($registroexistente)): ?>
                <div class="error-message" style="display: block; margin-top: 20px;"><?= htmlspecialchars($registroexistente) ?></div>
            <?php endif; ?>
        </form>
    </div>

    <script>
    document.addEventListener('DOMContentLoaded', function() {
        const form = document.getElementById('registerForm');
        const passwordInput = document.getElementById('contraseña');
        const confirmPasswordInput = document.getElementById('repetir');
        const togglePasswordButton = document.getElementById('togglePassword');
        const toggleConfirmPasswordButton = document.getElementById('toggleConfirmPassword');
        const passwordRequirements = document.getElementById('passwordRequirements');
        const terminosCheckbox = document.getElementById('terminos');
        const politicasCheckbox = document.getElementById('politicas');

        // Mostrar/ocultar contraseña
        togglePasswordButton.addEventListener('click', function() {
            const type = passwordInput.getAttribute('type') === 'password' ? 'text' : 'password';
            passwordInput.setAttribute('type', type);
            this.querySelector('i').classList.toggle('fa-eye');
            this.querySelector('i').classList.toggle('fa-eye-slash');
        });

        // Mostrar/ocultar confirmación de contraseña
        toggleConfirmPasswordButton.addEventListener('click', function() {
            const type = confirmPasswordInput.getAttribute('type') === 'password' ? 'text' : 'password';
            confirmPasswordInput.setAttribute('type', type);
            this.querySelector('i').classList.toggle('fa-eye');
            this.querySelector('i').classList.toggle('fa-eye-slash');
        });

        // Mostrar requisitos de contraseña al enfocar
        passwordInput.addEventListener('focus', function() {
            passwordRequirements.style.display = 'block';
        });

        // Ocultar requisitos de contraseña al quitar el foco (si no hay contenido)
        passwordInput.addEventListener('blur', function() {
            if (!this.value) {
                passwordRequirements.style.display = 'none';
            }
        });

        // Validar contraseña en tiempo real
        passwordInput.addEventListener('input', function() {
            validatePassword(this.value);
        });

        // Validar formulario al enviar
        form.addEventListener('submit', function(e) {
            e.preventDefault();

            if (validateForm()) {
                form.submit();
            }
        });

        function validatePassword(password) {
            const lengthValid = password.length >= 8;
            toggleRequirement('lengthReq', lengthValid);

            const uppercaseValid = /[A-Z]/.test(password);
            toggleRequirement('uppercaseReq', uppercaseValid);

            const lowercaseValid = /[a-z]/.test(password);
            toggleRequirement('lowercaseReq', lowercaseValid);

            const numberValid = /[0-9]/.test(password);
            toggleRequirement('numberReq', numberValid);

            const specialCharValid = /[^A-Za-z0-9]/.test(password);
            toggleRequirement('specialcharReq', specialCharValid);

            return lengthValid && uppercaseValid && lowercaseValid && numberValid && specialCharValid;
        }

        function toggleRequirement(elementId, isValid) {
            const element = document.getElementById(elementId);
            const icon = element.querySelector('i');

            if (isValid) {
                element.classList.add('valid');
                icon.classList.remove('fa-times');
                icon.classList.add('fa-check');
            } else {
                element.classList.remove('valid');
                icon.classList.remove('fa-check');
                icon.classList.add('fa-times');
            }
        }

        function validateForm() {
            let isValid = true;
            const name = document.getElementById('nombre').value.trim();
            const email = document.getElementById('correo').value.trim();
            const password = document.getElementById('contraseña').value;
            const confirmPassword = document.getElementById('repetir').value;
            const terms = document.getElementById('terminos').checked;
            const privacy = document.getElementById('politicas').checked;

            // Validar nombre
            if (name === '') {
                showError('nameError', 'El nombre es obligatorio');
                document.getElementById('nombre').classList.add('error');
                isValid = false;
            } else {
                hideError('nameError');
                document.getElementById('nombre').classList.remove('error');
            }

            // Validar email
            const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
            if (email === '') {
                showError('emailError', 'El correo electrónico es obligatorio');
                document.getElementById('correo').classList.add('error');
                isValid = false;
            } else if (!emailRegex.test(email)) {
                showError('emailError', 'Formato de correo incorrecto');
                document.getElementById('correo').classList.add('error');
                isValid = false;
            } else {
                hideError('emailError');
                document.getElementById('correo').classList.remove('error');
            }

            // Validar contraseña
            if (password === '') {
                showError('passwordError', 'La contraseña es obligatoria');
                document.getElementById('contraseña').classList.add('error');
                isValid = false;
            } else if (!validatePassword(password)) {
                showError('passwordError', 'La contraseña no cumple con los requisitos');
                document.getElementById('contraseña').classList.add('error');
                isValid = false;
            } else {
                hideError('passwordError');
                document.getElementById('contraseña').classList.remove('error');
            }

            // Validar confirmación de contraseña
            if (confirmPassword === '') {
                showError('confirmPasswordError', 'La confirmación de contraseña es obligatoria');
                document.getElementById('repetir').classList.add('error');
                isValid = false;
            } else if (password !== confirmPassword) {
                showError('confirmPasswordError', 'Las contraseñas no coinciden');
                document.getElementById('repetir').classList.add('error');
                isValid = false;
            } else {
                hideError('confirmPasswordError');
                document.getElementById('repetir').classList.remove('error');
            }

            // Validar términos y condiciones
            if (!terms) {
                showError('termsError', 'Debes aceptar nuestros Términos y condiciones');
                isValid = false;
            } else {
                hideError('termsError');
            }

            // Validar políticas de privacidad
            if (!privacy) {
                showError('privacyError', 'Debes aceptar nuestras Políticas de privacidad');
                isValid = false;
            } else {
                hideError('privacyError');
            }

            return isValid;
        }

        // 🔥 Mostrar error con autodesaparición a los 5s
        function showError(elementId, message) {
            const errorElement = document.getElementById(elementId);
            errorElement.textContent = message;
            errorElement.style.display = 'block';

            setTimeout(() => {
                if (errorElement && errorElement.style.display === 'block') {
                    errorElement.style.display = 'none';
                }
            }, 5000);
        }

        function hideError(elementId) {
            const errorElement = document.getElementById(elementId);
            if (errorElement) {
                errorElement.style.display = 'none';
            }
        }
    });
</script>

</body>
</html>