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
   Lógica principal de registro
   ------------------------------ */
if ($_SERVER['REQUEST_METHOD'] === 'POST' && !isset($_POST['check_email'])) {
    $nombre = trim($_POST['nombre'] ?? '');
    $correo = trim($_POST['correo'] ?? '');
    $contraseña = $_POST['contraseña'] ?? '';
    $repetir = $_POST['repetir'] ?? '';
    $terminos = $_POST['terminos'] ?? '';
    $politicas = $_POST['politicas'] ?? '';

    // Validaciones básicas
    if ($contraseña !== $repetir) {
        $contraseñasnocoinciden = "Las contraseñas no coinciden";
    } elseif (!esContraseñaSegura($contraseña)) {
        $error = "La contraseña debe tener al menos 8 caracteres, incluyendo una mayúscula, una minúscula, un número y un carácter especial";
    } elseif ($terminos !== 'on') {
        $error = "Debes aceptar los términos y condiciones";
    } elseif ($politicas !== 'on') {
        $error = "Debes aceptar las políticas de privacidad";
    } else {
        // Hasheamos la contraseña
        $contraseña_hash = password_hash($contraseña, PASSWORD_DEFAULT);

        // Comprobar si el correo ya existe
        $stmt = $conexion->prepare("SELECT id FROM usuarios WHERE correo = ?");
        $stmt->bind_param("s", $correo);
        $stmt->execute();
        $res = $stmt->get_result();

        if ($res && $res->num_rows > 0) {
            $registroexistente = "Este correo ya está registrado, por favor ingresa un correo diferente";
        } else {
            // Insert nuevo usuario
            $token = bin2hex(random_bytes(16)); // token verificación
            $email_verificado = 0;
            $rol = "Poblador";

            $stmt = $conexion->prepare("INSERT INTO usuarios (nombre, correo, contraseña, token_verificacion, email_verificacion, rol) VALUES (?, ?, ?, ?, ?, ?)");
            $stmt->bind_param("ssssss", $nombre, $correo, $contraseña_hash, $token, $email_verificado, $rol);

            if ($stmt->execute()) {
                // Envío de correo con PHPMailer
                $mail = new PHPMailer(true);
                try {
                    $mail->isSMTP();
                    $mail->Host       = 'smtp.gmail.com';
                    $mail->SMTPAuth   = true;
                    $mail->Username   = 'd4660140@gmail.com'; // tu correo
                    $mail->Password   = 'emar lypn ivdw bcwn'; // contraseña de aplicación
                    $mail->SMTPSecure = 'tls';
                    $mail->Port       = 587;

                    $mail->setFrom('TUCORREO@gmail.com', 'Comunicado Digital');
                    $mail->addAddress($correo);

                    $verificar_url = "http://192.168.48.211/Engine-Team/verificar.php?token=" . $token;

                    $mail->isHTML(true);
                    $mail->Subject = 'Verifica tu cuenta';
                    $mail->Body    = "Hola <b>" . htmlspecialchars($nombre) . "</b>,<br><br>Gracias por registrarte. Por favor haz clic en el siguiente enlace para verificar tu cuenta:<br><br>
                                     <a href='$verificar_url'>$verificar_url</a><br><br>Si no te registraste, ignora este mensaje.";

                    $mail->send();

                    // Mensaje de confirmación y redirección
                    $_SESSION['registro_exitoso'] = "Usuario registrado con éxito. Revisa tu correo para verificar la cuenta.";
                    header("Location: login.php");
                    exit();
                } catch (Exception $e) {
                    $error = "Error al enviar el correo de verificación: " . $mail->ErrorInfo;
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
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width,initial-scale=1" />
    <title>Crear Cuenta</title>

    <!-- Fonts + Icons -->
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@700&family=Inter:wght@400;500;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

    <style>
        :root{
            --blue: #1661AC;
            --muted: #555555;
            --input-border: #B1B1B1;
            --text: #403F48;
            --success: #61C9A8;
            --error-bg: #F8D7DA;
            --error-border: #F5C2C7;
            --error-text: #842029;
            --error-borde-color: #E85D5D;
            --btn-bg: #61C9A8;
            --btn-text: #1B314B;
        }

        *{box-sizing:border-box;margin:0;padding:0}
        body{
            font-family:'Inter',sans-serif;
            background:#f5f5f5;
            padding:24px;
            display:flex;
            justify-content:center;
            align-items:flex-start;
            min-height:100vh;
        }

        .container{
            width:100%;
            max-width:640px;
            background:#fff;
            border-radius:12px;
            padding:28px;
            box-shadow:0 6px 22px rgba(0,0,0,0.08);
        }

        h1{
            font-family:'Poppins',sans-serif;
            font-size:20px;
            font-weight:700;
            color:#333333;
            text-align:center;
            margin-bottom:8px;
        }
        .subtitle{
            font-family:'Inter',sans-serif;
            font-size:14px;
            color:var(--muted);
            text-align:center;
            margin-bottom:20px;
        }

        .input-group{
            position:relative;
            margin-bottom:12px;
            transition:transform .12s ease, box-shadow .12s ease;
        }

        .input-group:hover,
        .input-group:focus-within{
            transform:scale(1.01);
            box-shadow:0 6px 18px rgba(22,97,172,0.06);
        }

        /* left icon (lock/user/email) */
        .input-icon{
            position:absolute;
            left:12px;
            top:50%;
            transform:translateY(-50%);
            font-size:18px;
            color:var(--input-border);
            width:31px;
            height:24px;
            display:flex;
            align-items:center;
            justify-content:center;
            pointer-events:none;
        }

        /* right icon (eye) - placed inside input */
        .input-icon-right{
            position:absolute;
            right:12px;
            top:50%;
            transform:translateY(-50%);
            font-size:18px;
            color:var(--input-border);
            width:100px;
            height:24px;
            display:flex;
            align-items:center;
            justify-content:center;
            cursor:pointer;
            background:transparent;
            border: none;
        }
        .input-icon-right:focus{ outline: none; }

        input[type="text"], input[type="email"], input[type="password"]{
            width:100%;
            max-width:550px;
            padding:12px 16px 12px 54px; /* left padding for icon */
            border:1px solid var(--input-border);
            border-radius:8px;
            font-family:'Inter',sans-serif;
            font-size:16px;
            color:var(--text);
            transition:border-color .18s ease, box-shadow .18s ease, transform .12s ease;
            outline:none;
        }

        /* when there is right icon reserve space */
        input.with-right{
            padding-right:54px;
        }

        input:focus{
            border-color:var(--success);
            box-shadow:0 0 0 6px rgba(97,201,168,0.06);
        }

        /* error state */
        .shake {
            animation:shake .45s linear;
        }
        @keyframes shake {
            0%,100%{transform:translateX(0)}
            25%{transform:translateX(-6px)}
            75%{transform:translateX(6px)}
        }
        .error-border{
            border-color: var(--error-borde-color) !important;
        }

        /* error message style */
        .error-message {
            display:none;
            background: var(--error-bg);
            color: var(--error-text);
            border: 1px solid var(--error-border);
            border-radius:6px;
            padding:8px 12px;
            margin-top:8px;
            font-family:'Inter',sans-serif;
            font-size:16px;
            text-align:left;
            width:200%;
            max-width:550px;
        }

        /* password requirements container */
        .pw-reqs{
            display:none;
            margin-top:8px;
            max-width:550px;
            padding:12px;
            border-radius:8px;
            background:#fafafa;
            border:1px solid #eee;
        }
        .pw-req{
            display:flex;
            align-items:center;
            gap:12px;
            margin-bottom:10px;
            font-family:'Inter',sans-serif;
            font-size:16px;
            color:var(--text);
        }
        .pw-dot{
            width:18px;
            height:18px;
            border-radius:50%;
            display:inline-flex;
            align-items:center;
            justify-content:center;
            font-size:12px;
            color:#fff;
            background:var(--input-border);
        }
        .pw-dot.met{ background: var(--success); }

        /* checkbox groups */
        .checkbox-group{
            display:flex;
            align-items:center;
            gap:8px;
            margin-bottom:12px;
            font-family:'Inter',sans-serif;
            font-size:16px;
            color:var(--text);
        }
        .checkbox-group a{ color:var(--blue); text-decoration:none; font-weight:500; }
        .checkbox-group a:hover{ text-decoration:underline; }

        /* Button styles */
        .btn-row{
            display:flex;
            justify-content:center;
            margin-top:6px;
            margin-bottom:14px;
        }
        .btn-continue{
            background:var(--btn-bg);
            color:var(--btn-text);
            border:none;
            border-radius:16px;
            padding:.5rem 1.5rem;
            font-family:'Poppins',sans-serif;
            font-weight:700;
            font-size:20px;
            cursor:pointer;
            transition:all .18s ease;
        }
        .btn-continue:hover{ box-shadow:0 8px 20px rgba(97,201,168,0.18); transform:translateY(-3px); }
        .btn-continue:active{ animation:wave .28s; }
        @keyframes wave{ 0%{transform:scale(1)}50%{transform:scale(.98)}100%{transform:scale(1)} }

        .login-link{
            text-align:center;
            margin-top:14px;
            font-family:'Inter',sans-serif;
            font-size:16px;
            color:var(--text);
        }
        .login-link a{ color:var(--blue); font-weight:700; text-decoration:none; }
        .login-link a:hover{text-decoration:underline;}

        .separator{
            text-align:center;
            font-family:'Inter',sans-serif;
            color:var(--text);
            font-size:20px;
            margin:18px 0;
        }

        .socials{
            display:flex;
            justify-content:space-between;
            gap:12px;
            margin-top:6px;
        }
        .social-btn{
            display:flex;
            align-items:center;
            gap:12px;
            padding:12px 16px;
            background:#FFFFFF;
            border:1px solid var(--blue);
            border-radius:8px;
            font-family:'Inter',sans-serif;
            font-size:20px;
            color:var(--text);
            cursor:pointer;
            width:48%;
            justify-content:flex-start;
            transition:all .14s ease;
        }
        .social-btn:hover{ box-shadow:0 6px 18px rgba(22,97,172,0.06); transform:translateY(-2px); }
        .social-icon{ width:24px; height:24px; display:inline-block; }

        /* responsive */
        @media(max-width:640px){
            .container{ padding:18px; }
            input[type="text"],input[type="email"],input[type="password"],.error-message,.pw-reqs{ max-width:100%; }
            .socials{ flex-direction:column; }
            .social-btn{ width:100%; justify-content:center; }
        }
    </style>
</head>
<body>
    <div class="container">
        <form id="registerForm" method="POST" action="">
            <h1>Crea tu cuenta</h1>
            <p class="subtitle">Únete a nuestra comunidad y mantente informado al instante</p>

            <!-- Nombre -->
            <div class="input-group">
                <span class="input-icon" aria-hidden="true"><i class="fas fa-user" style="color:var(--input-border); width:18px;"></i></span>
                <input type="text" id="nombre" name="nombre" placeholder="Nombre" value="<?= isset($_POST['nombre']) ? htmlspecialchars($_POST['nombre']) : '' ?>" autocomplete="name" required>
            </div>
            <div id="nameError" class="error-message" aria-live="polite"></div>

            <!-- Correo electrónico -->
            <div class="input-group">
                <span class="input-icon" aria-hidden="true"><i class="fas fa-envelope" style="color:var(--input-border); width:18px;"></i></span>
                <input type="email" id="correo" name="correo" placeholder="Correo electrónico" value="<?= isset($_POST['correo']) ? htmlspecialchars($_POST['correo']) : '' ?>" autocomplete="email" required>
            </div>
            <div id="emailError" class="error-message" aria-live="polite"></div>

            <!-- Contraseña -->
            <div class="input-group">
                <span class="input-icon" aria-hidden="true"><i class="fas fa-lock" style="color:var(--input-border); width:18px;"></i></span>
                <input type="password" id="contrasena" name="contraseña" placeholder="Contraseña" class="with-right" autocomplete="new-password" aria-describedby="passwordHelp" required>
                <button type="button" class="input-icon-right" id="togglePassword" aria-label="Mostrar contraseña" title="Mostrar contraseña">
                    <i class="fas fa-eye" style="color:var(--input-border);"></i>
                </button>
            </div>
            <div id="passwordError" class="error-message" aria-live="polite"></div>

            <!-- Indicadores de fuerza -->
            <div id="passwordRequirements" class="pw-reqs" aria-hidden="true" role="status" aria-live="polite">
                <div class="pw-req"><span class="pw-dot" id="dotLength"></span><span style="font-family:Inter, sans-serif; font-size:16px; color:var(--text)">Mínimo 8 caracteres</span></div>
                <div class="pw-req"><span class="pw-dot" id="dotUpper"></span><span style="font-family:Inter, sans-serif; font-size:16px; color:var(--text)">Al menos una mayúscula</span></div>
                <div class="pw-req"><span class="pw-dot" id="dotLower"></span><span style="font-family:Inter, sans-serif; font-size:16px; color:var(--text)">Al menos una minúscula</span></div>
                <div class="pw-req"><span class="pw-dot" id="dotSpecial"></span><span style="font-family:Inter, sans-serif; font-size:16px; color:var(--text)">Al menos un carácter especial</span></div>
            </div>

            <!-- Confirmar contraseña -->
            <div class="input-group" style="margin-top:12px;">
                <span class="input-icon" aria-hidden="true"><i class="fas fa-lock" style="color:var(--input-border); width:18px;"></i></span>
                <input type="password" id="repetir" name="repetir" placeholder="Confirmar contraseña" class="with-right" autocomplete="new-password" required>
                <button type="button" class="input-icon-right" id="toggleConfirm" aria-label="Mostrar confirmación" title="Mostrar confirmación">
                    <i class="fas fa-eye" style="color:var(--input-border);"></i>
                </button>
            </div>
            <div id="confirmPasswordError" class="error-message" aria-live="polite"></div>

            <!-- Términos -->
            <div class="checkbox-group" style="margin-top:8px;">
                <input type="checkbox" id="terminos" name="terminos" <?= isset($_POST['terminos']) ? 'checked' : '' ?>>
                <label for="terminos" style="font-family:Inter, sans-serif; font-size:16px; color:var(--text)">
                    He leído y acepto los <a href="terminos.php" target="_blank">Términos y condiciones</a>
                </label>
            </div>
            <div id="termsError" class="error-message" aria-live="polite"></div>

            <!-- Políticas -->
            <div class="checkbox-group">
                <input type="checkbox" id="politicas" name="politicas" <?= isset($_POST['politicas']) ? 'checked' : '' ?>>
                <label for="politicas" style="font-family:Inter, sans-serif; font-size:16px; color:var(--text)">
                    He leído y acepto las <a href="politicas.php" target="_blank">Políticas de Privacidad</a>
                </label>
            </div>
            <div id="privacyError" class="error-message" aria-live="polite"></div>

            <!-- Botón continuar -->
            <div class="btn-row">
                <button type="submit" class="btn-continue">Continuar</button>
            </div>

            <!-- Login link -->
            <div class="login-link">
                <span style="font-family:Inter, sans-serif; font-size:16px; color:var(--text)">¿Ya tienes una cuenta? </span>
                <a href="login.php" style="font-family:Inter, sans-serif; font-size:16px; color:var(--blue); font-weight:700; margin-left:6px;">Inicia sesión</a>
            </div>

            <div class="separator">O</div>

            <!-- Social buttons (Google left, Outlook right) -->
            <div class="socials" role="group" aria-label="Iniciar sesión con">
                <!-- Google -->
                <button type="button" class="social-btn" id="btnGoogle" onclick="location.href='google_login.php'">
                    <span class="social-icon" aria-hidden="true">
                        <!-- Google SVG icon (inline) -->
                        <svg width="20" height="20" viewBox="0 0 533.5 544.3" xmlns="http://www.w3.org/2000/svg" preserveAspectRatio="xMidYMid">
                            <path fill="#4285f4" d="M533.5 278.4c0-17.4-1.6-34.2-4.7-50.5H272v95.6h147.1c-6.3 33.9-25 62.6-53.1 81.8v67h85.7c50.2-46.2 81.8-114.4 81.8-194z"/>
                            <path fill="#34a853" d="M272 544.3c72 0 132.5-23.8 176.7-64.6l-85.7-67c-23.9 16-54.4 25.5-91 25.5-69.8 0-128.9-47.1-150-110.3H34.5v69.4C78.9 492.4 169 544.3 272 544.3z"/>
                            <path fill="#fbbc04" d="M122 325.9c-6.8-20.4-10.7-42.1-10.7-64.4s3.9-44 10.7-64.4V127.6H34.5C12.7 171.9 0 217.9 0 261.5s12.7 89.6 34.5 133.9L122 325.9z"/>
                            <path fill="#ea4335" d="M272 108.3c39.5 0 75 13.6 103 40.5l77.2-77.2C404.5 25.7 345.9 0 272 0 169 0 78.9 51.9 34.5 127.6l87.5 69.4c21.1-63.2 80.2-110.3 150-110.3z"/>
                        </svg>
                    </span>
                    <span style="font-family:Inter, sans-serif; font-size:20px; color:var(--text);">Continuar con Google</span>
                </button>

               <!-- Outlook -->
<button type="button" class="social-btn" id="btnOutlook" onclick="location.href='outlook_login.php'">
    <span class="social-icon" aria-hidden="true">
        <!-- Outlook official SVG -->
        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
            <path d="M22 6V18C22 19.1046 21.1046 20 20 20H4C2.89543 20 2 19.1046 2 18V6C2 4.89543 2.89543 4 4 4H20C21.1046 4 22 4.89543 22 6Z" fill="#0078D4"/>
            <path d="M4 4L12 8V16L4 20V4Z" fill="white"/>
            <path d="M12 8H20V16H12V8Z" fill="#00BCF2"/>
        </svg>
    </span>
    <span style="font-family:Inter, sans-serif; font-size:20px; color:var(--text);">Continuar con Outlook</span>
</button>
            <!-- PHP error blocks (server-side) -->
            <?php if (isset($error)): ?>
                <div class="error-message" style="display:block; margin-top:16px;"><?= htmlspecialchars($error) ?></div>
            <?php endif; ?>

            <?php if (isset($contraseñasnocoinciden)): ?>
                <div class="error-message" style="display:block; margin-top:16px;"><?= htmlspecialchars($contraseñasnocoinciden) ?></div>
            <?php endif; ?>

            <?php if (isset($registroexistente)): ?>
                <div class="error-message" style="display:block; margin-top:16px;"><?= htmlspecialchars($registroexistente) ?></div>
            <?php endif; ?>

            <?php if (isset($_SESSION['registro_exitoso'])): ?>
                <div class="pw-reqs" style="display:block; border-color:<?= htmlspecialchars('--success') ?>; margin-top:12px;">
                    <?= htmlspecialchars($_SESSION['registro_exitoso']); unset($_SESSION['registro_exitoso']); ?>
                </div>
            <?php endif; ?>

        </form>
    </div>

    <script>
    (function(){
        // Elements
        const nombre = document.getElementById('nombre');
        const correo = document.getElementById('correo');
        const contrasena = document.getElementById('contrasena');
        const repetir = document.getElementById('repetir');
        const terminos = document.getElementById('terminos');
        const politicas = document.getElementById('politicas');

        const nameError = document.getElementById('nameError');
        const emailError = document.getElementById('emailError');
        const passwordError = document.getElementById('passwordError');
        const confirmPasswordError = document.getElementById('confirmPasswordError');
        const termsError = document.getElementById('termsError');
        const privacyError = document.getElementById('privacyError');

        const pwReqs = document.getElementById('passwordRequirements');
        const dotLength = document.getElementById('dotLength');
        const dotUpper  = document.getElementById('dotUpper');
        const dotLower  = document.getElementById('dotLower');
        const dotSpecial= document.getElementById('dotSpecial');

        const togglePassword = document.getElementById('togglePassword');
        const toggleConfirm  = document.getElementById('toggleConfirm');
        const registerForm = document.getElementById('registerForm');

        // helper: show/hide error with auto hide 5s
        function showError(el, msg){
            if(!el) return;
            el.textContent = msg;
            el.style.display = 'block';
            // mark related input red
            const input = adjacentInput(el);
            if(input){
                input.classList.add('error-border','shake');
                setTimeout(()=> input.classList.remove('shake'), 500);
            }
            clearTimeout(el._hideTimer);
            el._hideTimer = setTimeout(()=>{
                if(el && el.textContent === msg){
                    el.style.display = 'none';
                }
            },5000);
        }

        function hideError(el){
            if(!el) return;
            el.style.display = 'none';
            const input = adjacentInput(el);
            if(input){
                input.classList.remove('error-border');
            }
            clearTimeout(el._hideTimer);
        }

        // find input just above the error message (structure fixed)
        function adjacentInput(errorEl){
            if(!errorEl) return null;
            const prev = errorEl.previousElementSibling;
            if(!prev) return null;
            if(prev.classList && prev.classList.contains('input-group')){
                const input = prev.querySelector('input');
                return input || null;
            }
            return null;
        }

        // Name validation: required + only letters
        nombre.addEventListener('input', function(){
            const val = nombre.value.trim();
            const regex = /^[A-Za-zÁÉÍÓÚáéíóúÑñ\s]+$/;
            if(val === ''){
                showError(nameError, 'El nombre es obligatorio');
            } else if(!regex.test(val)){
                showError(nameError, 'El nombre solo debe contener letras');
            } else {
                hideError(nameError);
            }
        });

        // Email validation: required, format, ajax check
        let emailCheckTimer = null;
        correo.addEventListener('input', function(){
            clearTimeout(emailCheckTimer);
            const val = correo.value.trim();
            const regex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;

            if(val === ''){
                showError(emailError, 'El correo electrónico es obligatorio');
                return;
            }
            if(!regex.test(val)){
                showError(emailError, 'Formato de correo incorrecto');
                return;
            }

            emailCheckTimer = setTimeout(()=>{ 
                fetch('', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                    body: 'check_email=' + encodeURIComponent(val)
                })
                .then(r => r.json())
                .then(data => {
                    if(data && data.exists){
                        showError(emailError, 'Este correo ya esta registrado');
                    } else {
                        hideError(emailError);
                    }
                })
                .catch(()=> {
                    hideError(emailError);
                });
            }, 450);
        });

        // Password show/hide
        togglePassword && togglePassword.addEventListener('click', function(){
            const icon = this.querySelector('i');
            if(contrasena.type === 'password'){
                contrasena.type = 'text';
                if(icon){ icon.classList.remove('fa-eye'); icon.classList.add('fa-eye-slash'); }
            } else {
                contrasena.type = 'password';
                if(icon){ icon.classList.remove('fa-eye-slash'); icon.classList.add('fa-eye'); }
            }
        });

        toggleConfirm && toggleConfirm.addEventListener('click', function(){
            const icon = this.querySelector('i');
            if(repetir.type === 'password'){
                repetir.type = 'text';
                if(icon){ icon.classList.remove('fa-eye'); icon.classList.add('fa-eye-slash'); }
            } else {
                repetir.type = 'password';
                if(icon){ icon.classList.remove('fa-eye-slash'); icon.classList.add('fa-eye'); }
            }
        });

        // Show password requirements on focus
        contrasena.addEventListener('focus', function(){
            pwReqs.style.display = 'block';
            pwReqs.setAttribute('aria-hidden','false');
        });
        contrasena.addEventListener('blur', function(){
            if(!contrasena.value) {
                pwReqs.style.display = 'none';
                pwReqs.setAttribute('aria-hidden','true');
            }
        });

        // Password validation live + update dots
        contrasena.addEventListener('input', function(){
            const val = contrasena.value;
            const lengthOk = val.length >= 8;
            const upperOk = /[A-Z]/.test(val);
            const lowerOk = /[a-z]/.test(val);
            const specialOk = /[^A-Za-z0-9]/.test(val);
            const numberOk = /[0-9]/.test(val);

            toggleDot(dotLength, lengthOk);
            toggleDot(dotUpper, upperOk);
            toggleDot(dotLower, lowerOk);
            toggleDot(dotSpecial, specialOk);

            if(val === ''){
                showError(passwordError, 'La contraseña es obligatoria.');
            } else if(!(lengthOk && upperOk && lowerOk && specialOk && numberOk)){
                showError(passwordError, 'La contraseña no cumple con los requisitos');
            } else {
                hideError(passwordError);
            }

            if(repetir.value){
                if(repetir.value !== val){
                    showError(confirmPasswordError, 'Las contraseñas no coinciden.');
                } else {
                    hideError(confirmPasswordError);
                }
            }
        });

        function toggleDot(dotEl, ok){
            if(ok){
                dotEl.classList.add('met');
                dotEl.innerHTML = '<i class="fas fa-check"></i>';
            } else {
                dotEl.classList.remove('met');
                dotEl.innerHTML = '';
            }
        }

        // Confirm password live check
        repetir.addEventListener('input', function(){
            if(repetir.value === ''){
                showError(confirmPasswordError, 'La confirmación de contraseña es obligatoria.');
            } else if(repetir.value !== contrasena.value){
                showError(confirmPasswordError, 'Las contraseñas no coinciden.');
            } else {
                hideError(confirmPasswordError);
            }
        });

        // On submit: ensure checkboxes accepted and fields valid; if not, show messages (and prevent submit)
        registerForm.addEventListener('submit', function(e){
            let valid = true;

            // Trigger input events to set messages
            nombre.dispatchEvent(new Event('input'));
            correo.dispatchEvent(new Event('input'));
            contrasena.dispatchEvent(new Event('input'));
            repetir.dispatchEvent(new Event('input'));

            if(nombre.value.trim() === '' || !/^[A-Za-zÁÉÍÓÚáéíóúÑñ\s]+$/.test(nombre.value.trim())){
                valid = false;
            }
            if(emailError.style.display === 'block') valid = false;
            if(passwordError.style.display === 'block') valid = false;
            if(confirmPasswordError.style.display === 'block') valid = false;

            if(!terminos.checked){
                showError(termsError, 'Debes aceptar nuestros Términos y condiciones');
                valid = false;
            } else {
                hideError(termsError);
            }
            if(!politicas.checked){
                showError(privacyError, 'Debes aceptar nuestras Políticas de privacidad');
                valid = false;
            } else {
                hideError(privacyError);
            }

            if(!valid){
                e.preventDefault();
                const first = document.querySelector('.error-border') || document.querySelector('.error-message[style*="block"]');
                if(first){
                    if(first.tagName && first.tagName.toLowerCase() === 'div' && first.previousElementSibling){
                        const inputToFocus = first.previousElementSibling.querySelector('input');
                        inputToFocus && inputToFocus.focus();
                    }
                }
            }
        });

    })();
    </script>
</body>
</html>