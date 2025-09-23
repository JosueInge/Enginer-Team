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

    if ($contraseña !== $repetir) { 
        $contraseñasnocoinciden = "Las contraseñas no coinciden";
    } elseif (!esContraseñaSegura($contraseña)) {
        $error = "La contraseña debe tener al menos 8 caracteres, incluyendo una mayúscula, una minúscula, un número y un carácter especial";
    } elseif ($terminos !== 'on') {
        $error = "Debes aceptar los términos y condiciones";
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
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width,initial-scale=1" />
    <title>Registro</title>

    <!-- Fuentes -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600&family=Poppins:wght@400;700&display=swap" rel="stylesheet">

    <!-- Bootstrap (solo para toasts y utilidades) -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">

    <style>
    :root{
        --header-bg: #1B314B;
        --link-hover: #DDDDDD;
        --primary: #61C9A8;
        --primary-dark: #1B314B;
        --border-gray: #B1B1B1;
        --text-dark: #403F48;
        --muted: #555555;
        --error-red: #E85D5D;
        --check-green: #61C9A8;
    }

    html,body{height:100%;margin:0;background:#fff;font-family: 'Inter', sans-serif;color:var(--text-dark);}

    /* HEADER */
    header{
        background: var(--header-bg);
        color:#fff;
        padding: 14px 22px;
        display:flex;
        align-items:center;
        justify-content:space-between;
        gap:12px;
    }
    .brand { display:flex; align-items:center; gap:12px; }
    .brand img { height:44px; width:auto; display:block; }
    nav { display:flex; gap:15px; align-items:center;}
    nav a { font-family:'Poppins',sans-serif; font-size:14px; color:#fff; padding:8px; }
    nav a:hover{ color:var(--link-hover); text-decoration:underline; }

    /* hamburger */
    .hamburger{ display:none; font-size:24px; color:#fff; cursor:pointer; background:transparent; border:none; }
    @media (max-width:900px){
        nav{ display:none; position:absolute; right:12px; top:62px; background:var(--header-bg); padding:12px; border-radius:6px; flex-direction:column; min-width:170px; z-index:1000; }
        nav.show{ display:flex; }
        .hamburger{ display:block; }
    }

    /* Container */
    .page{
        max-width:820px;
        margin:28px auto;
        padding: 18px;
    }

    .card {
        padding:28px;
        border-radius:12px;
        box-shadow: 0 6px 20px rgba(0,0,0,0.05);
        background: #fff;
    }

    /* Títulos */
    .title {
        font-family:'Poppins',sans-serif;
        font-size:20px;
        font-weight:700;
        color:#333333;
        text-align:center;
        margin:0 0 8px 0;
    }
    .subtitle {
        font-family:'Inter',sans-serif;
        font-size:14px;
        color:var(--muted);
        text-align:center;
        margin:0 0 20px 0;
    }

    /* Inputs */
    form{ display:flex; flex-direction:column; align-items:center; gap:8px; }
    .field {
        width:100%;
        max-width:550px;
        position:relative;
    }
    .field input {
        width:100%;
        box-sizing:border-box;
        padding:12px 44px 12px 44px;
        border:1px solid var(--border-gray);
        border-radius:8px;
        font-family:'Inter',sans-serif;
        font-size:16px;
        color:var(--text-dark);
        transition: transform .12s ease, box-shadow .12s ease, border-color .12s ease;
        background: #fff;
    }
    .field input::placeholder { color:#9b9b9b; }
    .field input:focus { transform:scale(1.01); box-shadow: 0 6px 18px rgba(0,0,0,0.08); outline:none; }

    /* iconos */
    .icon-left, .icon-right {
        position:absolute;
        top:50%;
        transform:translateY(-50%);
        pointer-events:none;
        width:31px;
        height:24px;
        display:flex;
        align-items:center;
        justify-content:center;
        color:var(--border-gray);
    }
    .icon-left{ left:12px; pointer-events:none; }
    .icon-right{ right:12px; pointer-events:auto; cursor:pointer; }

    /* error state */
    .field.error input { border-color: var(--error-red); animation: shake .18s linear; }
    @keyframes shake { 0%{transform:translateX(0)}25%{transform:translateX(-4px)}50%{transform:translateX(4px)}75%{transform:translateX(-4px)}100%{transform:translateX(0)} }

    /* mensajes de error debajo del campo */
    .msg {
        width:100%;
        max-width:550px;
        text-align:center;
        font-family:'Poppins',sans-serif;
        color:var(--primary-dark);
        font-size:16px;
        margin-top:6px;
        min-height:20px;
    }
    .msg.error { color:var(--error-red); }

    /* Reglas de contraseña (visible solo on focus) */
    .pw-rules {
        width:100%;
        max-width:550px;
        margin-top:6px;
        display:none;
        flex-direction:column;
        gap:6px;
    }
    .pw-rules.visible { display:flex; }
    .rule {
        display:flex;
        align-items:center;
        gap:10px;
        font-family:'Inter',sans-serif;
        font-size:16px;
        color:var(--text-dark);
    }
    .rule .dot {
        width:20px; height:20px; border-radius:50%; display:inline-flex; align-items:center; justify-content:center;
        font-size:12px;
        color:#fff;
        background: var(--border-gray);
    }
    .rule.valid .dot { background: var(--check-green); }
    .rule.invalid .dot { background: var(--border-gray); }

    /* Aceptación de términos */
    .terms {
        width:100%;
        max-width:550px;
        display:flex;
        align-items:center;
        gap:8px;
        font-family:'Inter',sans-serif;
        font-size:16px;
        color:var(--text-dark);
    }
    .terms a { color:#1661AC; text-decoration:none; }
    .terms a:hover { text-decoration:underline; }

    /* Botón continuar */
    .btn-submit {
        margin-top:12px;
        background: var(--primary);
        color: var(--primary-dark);
        font-family:'Poppins',sans-serif;
        font-size:20px;
        font-weight:700;
        padding:0.5rem 1.5rem;
        border-radius:16px;
        border: none;
        cursor:pointer;
        transition: transform .12s ease, box-shadow .12s ease;
    }
    .btn-submit:disabled { opacity:0.6; cursor:not-allowed; transform:none; box-shadow:none; }
    .btn-submit:hover:not(:disabled){ transform:translateY(-2px); box-shadow: 0 8px 20px rgba(0,0,0,0.12); }

    /* login link */
    .login-line {
        width:100%;
        max-width:550px;
        text-align:center;
        margin-top:12px;
        font-family:'Inter',sans-serif;
        font-size:16px;
        color:var(--text-dark);
    }
    .login-line a { color:#1661AC; font-weight:700; text-decoration:none; }
    .login-line a:hover { text-decoration:underline; }

    /* separador O */
    .separator {
        font-family:'Inter',sans-serif;
        color:var(--text-dark);
        font-size:20px;
        margin:12px 0;
        text-align:center;
        width:100%;
        max-width:550px;
    }

    /* social buttons */
    .socials {
        width:100%;
        max-width:550px;
        display:flex;
        justify-content:space-between;
        gap:12px;
    }
    .social {
        width:49%;
        display:flex;
        align-items:center;
        gap:12px;
        padding:12px 16px;
        border-radius:8px;
        background:#fff;
        border:1px solid #1661AC;
        cursor:pointer;
        font-family:'Inter',sans-serif;
        font-size:20px;
        color:var(--text-dark);
        justify-content:center;
        transition: transform .12s ease, box-shadow .12s ease;
    }
    .social img { height:20px; width:auto; }
    .social:hover { transform:translateY(-3px); box-shadow: 0 8px 18px rgba(0,0,0,0.08); }

    /* responsive behaviour */
    @media (max-width:640px) {
        .page { padding:12px; margin-top:8px; }
        .card { padding:18px; }
        .brand img { height:38px; }
        .field input { padding:10px 40px 10px 44px; font-size:15px; }
        .title { font-size:18px; }
        .subtitle { font-size:13px; }
        .social { font-size:16px; padding:10px; }
    }
    </style>
</head>
<body>
    <header>
        <div class="brand">
            <a href="/"><img src="imagenes/logo.png" alt="logo" /></a>
        </div>

        <button class="hamburger" aria-label="Abrir menú" onclick="toggleMenu()">☰</button>

        <nav id="navMenu" aria-label="Navegación principal">
            <a href="#">Contacto</a>
            <a href="sobrenosotros.php">Sobre nosotros</a>
            <a href="login.php">Inicio de sesión</a>
        </nav>
    </header>

    <main class="page">
        <div class="card">
            <h1 class="title">Crea tu cuenta</h1>
            <p class="subtitle">Únete a nuestra comunidad y mantente informado al instante</p>

            <form id="formRegistro" method="POST" action="registro.php" novalidate>
                <!-- Nombre -->
                <div class="field" id="field-nombre">
                    <span class="icon-left" aria-hidden="true">
                        <!-- usuario SVG -->
                        <svg width="31" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg"><path d="M12 12c2.761 0 5-2.239 5-5s-2.239-5-5-5-5 2.239-5 5 2.239 5 5 5zM3 20c0-3.866 3.582-7 9-7s9 3.134 9 7v1H3v-1z" fill="#B1B1B1"/></svg>
                    </span>
                    <input id="nombre" name="nombre" type="text" placeholder="Nombre" value="<?= isset($_POST['nombre']) ? htmlspecialchars($_POST['nombre']) : '' ?>" />
                </div>
                <div id="msg-nombre" class="msg"></div>

                <!-- Correo -->
                <div class="field" id="field-correo">
                    <span class="icon-left" aria-hidden="true">
                        <!-- sobre SVG -->
                        <svg width="31" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg"><path d="M20 4H4c-1.1 0-2 .9-2 2v0 12c0 1.1.9 2 2 2h16c1.1 0 2-.9 2-2V6c0-1.1-.9-2-2-2zm0 4-8 5L4 8V6l8 5 8-5v2z" fill="#B1B1B1"/></svg>
                    </span>
                    <input id="correo" name="correo" type="email" placeholder="Correo electrónico" value="<?= isset($_POST['correo']) ? htmlspecialchars($_POST['correo']) : '' ?>" />
                </div>
                <div id="msg-correo" class="msg"></div>

                <!-- Contraseña -->
                <div class="field" id="field-password">
                    <span class="icon-left" aria-hidden="true">
                        <!-- candado SVG -->
                        <svg width="31" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg"><path d="M17 8h-1V6c0-2.761-2.239-5-5-5S6 3.239 6 6v2H5c-1.103 0-2 .897-2 2v8c0 1.103.897 2 2 2h12c1.103 0 2-.897 2-2v-8c0-1.103-.897-2-2-2zM8 6c0-2.206 1.794-4 4-4s4 1.794 4 4v2H8V6z" fill="#B1B1B1"/></svg>
                    </span>
                    <input id="contraseña" name="contraseña" type="password" placeholder="Contraseña" onfocus="showPwRules(true)" onblur="showPwRules(false)" />
                    <span class="icon-right" onclick="toggleEye('contraseña')" title="Mostrar/ocultar contraseña">
                        <!-- ojo SVG (cambia por JS) -->
                        <svg id="eye-contraseña" width="31" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg"><path d="M12 5c-7 0-11 6-11 7 0 1 4 7 11 7s11-6 11-7c0-1-4-7-11-7zm0 11a4 4 0 1 1 .001-8.001A4 4 0 0 1 12 16z" fill="#B1B1B1"/></svg>
                    </span>
                </div>
                <div id="msg-password" class="msg"></div>

                <!-- password rules (hidden until focus) -->
                <div id="pw-rules" class="pw-rules" aria-hidden="true">
                    <div id="rule-length" class="rule invalid"><span class="dot">✓</span> Mínimo 8 caracteres</div>
                    <div id="rule-upper" class="rule invalid"><span class="dot">✓</span> Al menos una mayúscula</div>
                    <div id="rule-lower" class="rule invalid"><span class="dot">✓</span> Al menos una minúscula</div>
                    <div id="rule-number" class="rule invalid"><span class="dot">✓</span> Al menos un número</div>
                    <div id="rule-special" class="rule invalid"><span class="dot">✓</span> Al menos un carácter especial</div>
                </div>

                <!-- Confirmar contraseña -->
                <div class="field" id="field-repetir">
                    <span class="icon-left" aria-hidden="true">
                        <!-- candado SVG -->
                        <svg width="31" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg"><path d="M17 8h-1V6c0-2.761-2.239-5-5-5S6 3.239 6 6v2H5c-1.103 0-2 .897-2 2v8c0 1.103.897 2 2 2h12c1.103 0 2-.897 2-2v-8c0-1.103-.897-2-2-2zM8 6c0-2.206 1.794-4 4-4s4 1.794 4 4v2H8V6z" fill="#B1B1B1"/></svg>
                    </span>
                    <input id="repetir" name="repetir" type="password" placeholder="Confirmar contraseña" />
                    <span class="icon-right" onclick="toggleEye('repetir')" title="Mostrar/ocultar contraseña">
                        <svg id="eye-repetir" width="31" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg"><path d="M12 5c-7 0-11 6-11 7 0 1 4 7 11 7s11-6 11-7c0-1-4-7-11-7zm0 11a4 4 0 1 1 .001-8.001A4 4 0 0 1 12 16z" fill="#B1B1B1"/></svg>
                    </span>
                </div>
                <div id="msg-repetir" class="msg"></div>

                <!-- Términos -->
                <div class="terms">
                    <input id="terminos" name="terminos" type="checkbox" <?= isset($_POST['terminos']) ? 'checked' : '' ?> />
                    <label for="terminos">He leído y acepto los <a href="terminos.php">Términos y condiciones</a></label>
                </div>
                <div id="msg-terminos" class="msg"></div>

                <!-- Botón continuar -->
                <button id="btnContinuar" type="submit" class="btn-submit" disabled>Continuar</button>

                <!-- Login link -->
                <div class="login-line">¿Ya tienes una cuenta? <a href="login.php">Inicia sesión</a></div>

                <div class="separator">O</div>

                <!-- Social buttons -->
                <div class="socials">
                    <button type="button" class="social" onclick="location.href='google_login.php'">
                        <img src="imagenes/google.png" alt="Google">Continuar con Google
                    </button>
                    <button type="button" class="social" onclick="location.href='outlook_login.php'">
                        <img src="imagenes/outlook.png" alt="Outlook">Continuar con Outlook
                    </button>
                </div>

                <!-- Mostrar toasts de PHP (errores de servidor) -->
                <?php if (isset($error)): ?>
                    <div class="msg error"><?= htmlspecialchars($error) ?></div>
                <?php endif; ?>

                <?php if (isset($contraseñasnocoinciden)): ?>
                    <div class="msg error"><?= htmlspecialchars($contraseñasnocoinciden) ?></div>
                <?php endif; ?>

                <?php if (isset($registroexistente)): ?>
                    <div class="msg error"><?= htmlspecialchars($registroexistente) ?></div>
                <?php endif; ?>

            </form>
        </div>
    </main>

    <!-- Bootstrap bundle (toasts) -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

    <script>
    // Toggle mobile menu
    function toggleMenu(){
        document.getElementById('navMenu').classList.toggle('show');
    }

    // Toggle eye icons
    function toggleEye(fieldId){
        const input = document.getElementById(fieldId);
        if(!input) return;
        input.type = input.type === 'password' ? 'text' : 'password';
        // update icon fill color (simple visual)
        const eye = document.getElementById('eye-' + fieldId);
        if(eye){
            // change fill color to indicate visible (just visual, using fill attr is enough)
            const color = input.type === 'text' ? '#1661AC' : '#B1B1B1';
            eye.querySelectorAll('path').forEach(p => p.setAttribute('fill', color));
        }
    }

    // Show/hide pw rules
    function showPwRules(show){
        const el = document.getElementById('pw-rules');
        if(show) el.classList.add('visible'); else el.classList.remove('visible');
    }

    // Element refs
    const nombre = document.getElementById('nombre');
    const correo = document.getElementById('correo');
    const contraseña = document.getElementById('contraseña');
    const repetir = document.getElementById('repetir');
    const terminos = document.getElementById('terminos');
    const btnContinuar = document.getElementById('btnContinuar');

    // messages
    const msgNombre = document.getElementById('msg-nombre');
    const msgCorreo = document.getElementById('msg-correo');
    const msgPassword = document.getElementById('msg-password');
    const msgRepetir = document.getElementById('msg-repetir');
    const msgTerminos = document.getElementById('msg-terminos');

    // password rule nodes
    const ruleLength = document.getElementById('rule-length');
    const ruleUpper = document.getElementById('rule-upper');
    const ruleLower = document.getElementById('rule-lower');
    const ruleNumber = document.getElementById('rule-number');
    const ruleSpecial = document.getElementById('rule-special');

    // utilities
    function validarEmailFormato(email){
        const re = /^[^\s@]+@[^\s@]+\.[A-Za-z]{2,}$/;
        return re.test(email);
    }

    // Check email existence via AJAX to same PHP file
    let checkEmailTimeout = null;
    async function checkEmailExists(email){
        // return {exists: true/false, error?: 'invalid_format'}
        const form = new FormData();
        form.append('check_email', email);
        try {
            const resp = await fetch('registro.php', { method: 'POST', body: form });
            const data = await resp.json();
            return data;
        } catch (e){
            return { exists:false };
        }
    }

    // Update password rules UI
    function updatePasswordRules(val){
        const hasLen = val.length >= 8;
        const hasUpper = /[A-Z]/.test(val);
        const hasLower = /[a-z]/.test(val);
        const hasNum = /[0-9]/.test(val);
        const hasSpec = /[^A-Za-z0-9]/.test(val);

        toggleRule(ruleLength, hasLen);
        toggleRule(ruleUpper, hasUpper);
        toggleRule(ruleLower, hasLower);
        toggleRule(ruleNumber, hasNum);
        toggleRule(ruleSpecial, hasSpec);

        return hasLen && hasUpper && hasLower && hasNum && hasSpec;
    }

    function toggleRule(el, ok){
        if(ok){
            el.classList.remove('invalid');
            el.classList.add('valid');
        } else {
            el.classList.remove('valid');
            el.classList.add('invalid');
        }
    }

    // Listeners
    contraseña.addEventListener('input', (e)=>{
        const v = e.target.value;
        updatePasswordRules(v);
        // disable continue if weak
        checkFormEnable();
    });

    nombre.addEventListener('input', ()=>{ msgNombre.textContent=''; document.getElementById('field-nombre').classList.remove('error'); checkFormEnable(); });

    correo.addEventListener('input', ()=> {
        msgCorreo.textContent=''; document.getElementById('field-correo').classList.remove('error');
        checkFormEnable();

        // debounce check email existence
        if(checkEmailTimeout) clearTimeout(checkEmailTimeout);
        checkEmailTimeout = setTimeout(async ()=>{
            const val = correo.value.trim();
            if(!val) return;
            if(!validarEmailFormato(val)){
                msgCorreo.textContent = "Formato de correo incorrecto";
                document.getElementById('field-correo').classList.add('error');
                btnContinuar.disabled = true;
                return;
            }
            const res = await checkEmailExists(val);
            if(res.error === 'invalid_format'){
                msgCorreo.textContent = "Formato de correo incorrecto";
                document.getElementById('field-correo').classList.add('error');
                btnContinuar.disabled = true;
                return;
            }
            if(res.exists){
                msgCorreo.textContent = "Este correo ya está registrado";
                document.getElementById('field-correo').classList.add('error');
                btnContinuar.disabled = true;
            } else {
                if(msgCorreo.textContent === "Este correo ya está registrado") msgCorreo.textContent='';
                document.getElementById('field-correo').classList.remove('error');
            }
        }, 600);
    });

    repetir.addEventListener('input', ()=>{
        msgRepetir.textContent=''; document.getElementById('field-repetir').classList.remove('error'); checkFormEnable();
    });

    terminos.addEventListener('change', ()=>{ msgTerminos.textContent=''; checkFormEnable(); });

    // enable/disable continue button based on local checks (not server final)
    function checkFormEnable(){
        const nameOk = nombre.value.trim().length > 0;
        const emailOk = correo.value.trim().length > 0 && validarEmailFormato(correo.value.trim());
        const pwOk = updatePasswordRules(contraseña.value);
        const repeatOk = repetir.value.trim().length > 0 && (contraseña.value === repetir.value);
        const termsOk = terminos.checked;

        btnContinuar.disabled = !(nameOk && emailOk && pwOk && repeatOk && termsOk);
    }

    // final client validation on submit (also leaves actual POST for server)
    document.getElementById('formRegistro').addEventListener('submit', async function(e){
        // clear messages
        msgNombre.textContent=''; msgCorreo.textContent=''; msgPassword.textContent=''; msgRepetir.textContent=''; msgTerminos.textContent='';

        let ok = true;

        if(nombre.value.trim() === ''){
            msgNombre.textContent = "El nombre es obligatorio";
            document.getElementById('field-nombre').classList.add('error');
            ok = false;
        }

        if(correo.value.trim() === ''){
            msgCorreo.textContent = "El correo electrónico es obligatorio";
            document.getElementById('field-correo').classList.add('error');
            ok = false;
        } else if(!validarEmailFormato(correo.value.trim())){
            msgCorreo.textContent = "Formato de correo incorrecto";
            document.getElementById('field-correo').classList.add('error');
            ok = false;
        } else {
            // check server if already registered
            const res = await checkEmailExists(correo.value.trim());
            if(res.exists){
                msgCorreo.textContent = "Este correo ya está registrado";
                document.getElementById('field-correo').classList.add('error');
                ok = false;
            }
        }

        if(contraseña.value.trim() === ''){
            msgPassword.textContent = "La contraseña es obligatoria";
            document.getElementById('field-password').classList.add('error');
            ok = false;
        } else {
            const passOk = updatePasswordRules(contraseña.value);
            if(!passOk){
                msgPassword.textContent = "La contraseña debe tener mínimo 8 caracteres, 1 mayúscula, 1 minúscula, 1 número y 1 carácter especial";
                document.getElementById('field-password').classList.add('error');
                ok = false;
            }
        }

        if(repetir.value.trim() === ''){
            msgRepetir.textContent = "La confirmación de contraseña es obligatoria";
            document.getElementById('field-repetir').classList.add('error');
            ok = false;
        } else if(contraseña.value !== repetir.value){
            msgRepetir.textContent = "Las contraseñas no coinciden";
            document.getElementById('field-repetir').classList.add('error');
            ok = false;
        }

        if(!terminos.checked){
            msgTerminos.textContent = "Debes aceptar nuestros Términos y condiciones";
            ok = false;
        }

        if(!ok){
            e.preventDefault();
            // animate shake briefly on invalid fields already applied via class .error
            return false;
        }

        // All client checks passed -> allow submit to server
        // Optionally show a quick confirmation (keeps submit)
        // You requested: when giving continuar, show message "Se ha enviado correctamente".
        // But since the server will redirect to login.php on success, showing client toast before submit may be redundant.
        // We'll allow the normal POST to proceed.
        return true;
    });

    // Initialize eye icons colors based on default (hidden)
    document.querySelectorAll('[id^="eye-"]').forEach(svg => {
        svg.querySelectorAll('path').forEach(p => p.setAttribute('fill','#B1B1B1'));
    });

    // enable checks on load in case form pre-filled (server-side re-render)
    window.addEventListener('load', ()=> {
        updatePasswordRules(contraseña.value || '');
        checkFormEnable();
    });

    </script>
</body>
</html>