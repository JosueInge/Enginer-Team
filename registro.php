<?php
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require 'PHPMailer/PHPMailer.php';
require 'PHPMailer/SMTP.php';
require 'PHPMailer/Exception.php';
include 'conexion.php';
session_start();

$error = null;

function esContraseñaSegura($contraseña) {
    if (strlen($contraseña) < 8) {
        return false;
    }
    
    if (!preg_match('/[A-Z]/', $contraseña)) {
        return false;
    }
    
    if (!preg_match('/[a-z]/', $contraseña)) {
        return false;
    }
    
    if (!preg_match('/[^A-Za-z0-9]/', $contraseña)) {
        return false;
    }
    
    return true;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nombre = $_POST['nombre'] ?? '';
    $correo = $_POST['correo'] ?? '';
    $contraseña = $_POST['contraseña'] ?? '';
    $repetir = $_POST['repetir'] ?? '';
    $terminos = $_POST['terminos'] ?? '';

    if ($contraseña !== $repetir) { 
        $contraseñasnocoinciden = "Las contraseñas no coinciden";
    } elseif (!esContraseñaSegura($contraseña)) {
        $error = "La contraseña debe tener al menos 8 caracteres, incluyendo una mayúscula, una minúscula y un carácter especial";
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
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
    <title>Registro</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #fff;
            margin: 0;
        }
        header {
            background-color: #0d5c9b;
            color: white;
            padding: 15px 30px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        .logo {
            font-size: 24px;
            font-weight: bold;
        }
        .logo img{
            height: 50px;
            margin-right: 10px;
        }
        .informacion a {
            color: white;
            margin-left: 20px;
            text-decoration: none;
            font-size: 14px;
            font-family: 'Open Sans Regular';
        }
        .contenedor {
            display: flex;
            justify-content: center;
            align-items: start;
            gap: 40px;
            padding: 40px;
            margin-top: 20px;
        }
        .formulario, .admin{
            border: 2px solid #000;
            padding: 25px;
            border-radius: 10px;
            width: 420px;
        }
        .formulario h2 {
            text-align: center;
            margin-bottom: 10px;
            font-family: 'Open Sans Bold';
        }
        .formulario p{
            text-align: center;
            margin-bottom: 20px;
            font-family: 'Open Sans Regular';
        }
        .formulario label{
            font-family: 'Open Sans Bold';
        }
        input[type="text"],
        input[type="email"],
        input[type="password"] {
            width: 100%;
            padding: 10px;
            margin-top: 8px;
            background-color: #d9d9d9;
            border: none;
            border-radius: 5px;
        }
        .botones {
            display: flex;
            justify-content: center;
            gap: 15px;
            margin-top: 20px;
        }
        .botones button {
            display: flex;
            align-items: center;
            padding: 8px 12px;
            border: none;
            cursor: pointer;
            background-color: #f0f0f0;
            border-radius: 5px;
            font-weight: hold;
            font-family: 'Open Sans Regular';
        }
        .botones img {
            height: 20px;
            margin-right: 8px;
        }
        .admin h4 {
            margin-bottom: 15px;
        }
        .admin input {
            width: 100%;
            padding: 10px;
            background-color: #d9d9d9;
            border: none;
            border-radius: 5px;
        }
        .admin button {
            width: 100%;
            margin-top: 20px;
            padding: 10px;
            background-color: #0d5c9b;
            color: white;
            border: none;
            border-radius: 6px;
            font-weight: bold;
        }
        .terminos {
            display: flex;
            align-items: center;
            margin-top: 20px;
        }
        .terminos input {
            margin-right: 8px;
        }
        .terminos label {
            font-family: 'Open Sans Regular';
        }
        .formulario .continuar {
            width: 100%;
            background-color: #0d5c9b;
            color: white;
            padding: 10px;
            border: none;
            border-radius: 6px;
            margin-top: 20px;
            font-weight: bold;
            cursor: pointer;
            font-family: 'Open Sans Regular';
        }
        .formulario .continuar:disabled {
            background-color: #cccccc;
            cursor: not-allowed;
        }

        .password-strength {
            margin-top: 5px;
            height: 5px;
            width: 100%;
            background-color: #ddd;
            border-radius: 5px;
            overflow: hidden;
        }

        .password-strength-bar {
            height: 100%;
            width: 0%;
            transition: width 0.3s, background-color 0.3s;
        }

        .strength-weak {
            background-color: #ff4d4d;
            width: 33%;
        }

        .strength-medium {
            background-color: #ffcc00;
            width: 66%;
        }

        .strength-strong {
            background-color: #00cc66;
            width: 100%;
        }

        .strength-text {
            font-size: 12px;
            margin-top: 3px;
            text-align: right;
            font-family: 'Open Sans Regular';
        }
        
        .password-requirements {
            margin-top: 5px;
            font-size: 12px;
            color: #666;
        }
        
        .requirement {
            display: flex;
            align-items: center;
            margin-bottom: 3px;
        }
        
        .requirement-icon {
            margin-right: 5px;
            font-size: 14px;
            width: 15px;
            display: inline-block;
        }
        
        .requirement.valid {
            color: #00cc66;
        }
        .link {
            margin-left: 5px;
        }
    </style>
</head>
<body>
    <header>
        <div class="logo">
            <img src="imagenes/logo.png" alt="logo">
        </div>
        <div class="informacion">
            <a href="#">Contacto</a>
            <a href="sobrenosotros.php">Sobre Nosotros</a>
            <a href="login.php">Iniciar Sesión</a>
        </div>
    </header>

    <div class="contenedor">
        <form method='POST' action="registro.php" class="formulario"  id="formRegistro">
            <h2>Registro</h2>
            <p>Ingresa los siguientes datos para completar el registro</p>

            <label for="nombre">Nombre y Apellido</label>
            <input type="text" name="nombre" value="<?php echo isset($_POST['nombre']) ? htmlspecialchars($_POST['nombre']) : ''; ?>" required>

            <label for="correo">Correo Electronico</label>
            <input type="email" name="correo" value="<?php echo isset($_POST['correo']) ? htmlspecialchars($_POST['correo']) : ''; ?>" required>

            <label for="contraseña">Contraseña</label>
            <div style="position: relative;">
                <input type="password" id="contraseña" name="contraseña" required onkeyup="checkPasswordStrength(this.value)">
                <img src="imagenes/ojoAbierto.webp" id="togglePassword1" onclick="togglePasswordVisibility('contraseña', 'togglePassword1')"
                style="position: absolute; top: 56%; right: 10px; transform: translateY(-50%); cursor: pointer; width: 24px;">
            </div>

            <div class="password-strength">
                <div class="password-strength-bar" id="passwordStrengthBar"></div>
            </div>
            <div class="strength-text" id="strengthText"></div>

            <div class="password-requirements" id="passwordRequirements">
                <div class="requirement" id="reqLength"><span class="requirement-icon"></span> Mínimo 8 caracteres</div>
                <div class="requirement" id="reqLower"><span class="requirement-icon"></span> Al menos una minúscula</div>
                <div class="requirement" id="reqUpper"><span class="requirement-icon"></span> Al menos una mayúscula</div>
                <div class="requirement" id="reqSpecial"><span class="requirement-icon"></span> Al menos un carácter especial</div>
            </div>

            <label for="repetir">Repetir Contraseña</label>
            <div style="position: relative;">
                <input type="password" id="repetir" name="repetir" required>
                <img src="imagenes/ojoAbierto.webp" id="togglePassword2" onclick="togglePasswordVisibility('repetir', 'togglePassword2')"
                style="position: absolute; top: 55%; right: 10px; transform: translateY(-50%); cursor: pointer; width: 24px;">
            </div>

            <?php if (isset($error)): ?>
                <div style="color:red; margin:10px 0;"><?= htmlspecialchars ($error) ?></div>
            <?php endif; ?>

            <div class="botones">
                <a href="google-login.php" style="text-decoration: none;">
                    <button type="button"><img src="imagenes/google.png" alt="Google">Continuar con Google</button>
                </a>
                <a href="outlook-login.php" style="text-decoration: none;">
                    <button type="button"><img src="imagenes/outlook.png" alt="Outlook">Continuar con Outlook</button>
                </a>
            </div>

            <div class="terminos">
                <input type="checkbox" id="termino" name="terminos" required 
                    <?php if (isset($_POST['terminos'])) echo 'checked'; ?>>
                <label for="termino">He leído y acepto los</label><a class="link" href="terminos.php">términos y condiciones</a>
            </div>

            <button type="submit" class="continuar" id="btnContinuar" disabled>Continuar</button>
        </form>

        <!-- Las contraseñas no coinciden -->
        <?php if (isset($contraseñasnocoinciden)): ?>
        <div class="position-fixed bottom-0 end-0 p-3" style="z-index: 1055;">
            <div class="toast align-items-center text-bg-danger border-0 show" role="alert" aria-live="assertive" aria-atomic="true">
                <div class="d-flex">
                    <div class="toast-body">
                        <?= htmlspecialchars($contraseñasnocoinciden) ?>
                    </div>
                    <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast" aria-label="Cerrar"></button>
                </div>
            </div>
        </div>
        <?php unset($contraseñasnocoinciden); ?>
        <?php endif; ?>

        <!-- registro existente -->
        <?php if (isset($registroexistente)): ?>
        <div class="position-fixed bottom-0 end-0 p-3" style="z-index: 1055;">
            <div class="toast align-items-center text-bg-danger border-0 show" role="alert" aria-live="assertive" aria-atomic="true">
                <div class="d-flex">
                    <div class="toast-body">
                        <?= htmlspecialchars($registroexistente) ?>
                    </div>
                    <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast" aria-label="Cerrar"></button>
                </div>
            </div>
        </div>
        <?php unset($registroexistente); ?>
        <?php endif; ?>

        <script>
            function checkPasswordStrength(password) {
                const strengthBar = document.getElementById('passwordStrengthBar');
                const strengthText = document.getElementById('strengthText');
                const btnContinuar = document.getElementById('btnContinuar');
                
                const reqLength = document.getElementById('reqLength');
                const reqLower = document.getElementById('reqLower');
                const reqUpper = document.getElementById('reqUpper');
                const reqSpecial = document.getElementById('reqSpecial');
                
                strengthBar.className = 'password-strength-bar';
                strengthText.textContent = '';
                strengthBar.style.width = '0%';
                
                if (password.length === 0) {
                    resetRequirements();
                    btnContinuar.disabled = true;
                    return;
                }
                
                const hasLength = password.length >= 8;
                const hasLower = /[a-z]/.test(password);
                const hasUpper = /[A-Z]/.test(password);
                const hasSpecial = /[^A-Za-z0-9]/.test(password);
                const hasNumber = /\d/.test(password);
                
                updateRequirement(reqLength, hasLength);
                updateRequirement(reqLower, hasLower);
                updateRequirement(reqUpper, hasUpper);
                updateRequirement(reqSpecial, hasSpecial);
                
                const cumpleRequisitos = hasLength && hasLower && hasUpper && hasSpecial;
                
                let strength = 0;
                
                if (password.length >= 12) strength += 2;
                else if (password.length >= 8) strength += 1;
                
                if (hasLower) strength += 1;
                if (hasUpper) strength += 1;
                if (hasNumber) strength += 1;
                if (hasSpecial) strength += 2;
                
                if (!cumpleRequisitos) {
                    strengthBar.classList.add('strength-weak');
                    strengthBar.style.width = '33%';
                    strengthText.textContent = 'Debil';
                    strengthText.style.color = '#ff4d4d';
                    btnContinuar.disabled = true;
                } else if (strength <= 5) {
                    strengthBar.classList.add('strength-medium');
                    strengthBar.style.width = '66%';
                    strengthText.textContent = 'Media';
                    strengthText.style.color = '#ffcc00';
                    btnContinuar.disabled = false;
                } else {
                    strengthBar.classList.add('strength-strong');
                    strengthBar.style.width = '100%';
                    strengthText.textContent = 'Fuerte';
                    strengthText.style.color = '#00cc66';
                    btnContinuar.disabled = false;
                }
            }
            
            function updateRequirement(element, isValid) {
                const icon = element.querySelector('.requirement-icon');
                if (isValid) {
                    element.classList.add('valid');
                    icon.textContent = '';
                } else {
                    element.classList.remove('valid');
                    icon.textContent = '';
                }
            }
            
            function resetRequirements() {
                document.querySelectorAll('.requirement').forEach(req => {
                    req.classList.remove('valid');
                    const icon = req.querySelector('.requirement-icon');
                    icon.textContent = '';
                });
            }
            document.getElementById('formRegistro').addEventListener('submit', function(e) {
                const contraseña = document.getElementById('contraseña').value;
                const repetir = document.getElementById('repetir').value;
                const terminos = document.getElementById('terminos').checked;

                if (!terminos) {
                    e.preventDefault();
                    alert("Debes aceptar los términos y condiciones");
                    return;
                }

                const hasLower = /[a-z]/.test(contraseña);
                const hasUpper = /[A-Z]/.test(contraseña);
                const hasSpecial = /[^A-Za-z0-9]/.test(contraseña);

                if (contraseña.length < 8 || !hasLower || !hasUpper || !hasSpecial) {
                    e.preventDefault();
                    alert("La contraseña debe tener:\n- Mínimo 8 caracteres\n- Al menos una mayúscula\n- Al menos una minúscula\n- Al menos un carácter especial");
                    return;
                }
            });
            // Codigo para mostrar y ocultar la contraseña
            function togglePasswordVisibility(inputId, iconId) {
                const input = document.getElementById(inputId);
                const icon = document.getElementById(iconId);

                if (input.type === "password") {
                    input.type = "text";
                    icon.src = "imagenes/ojoCerrado.webp";
                } else {
                    input.type = "password";
                    icon.src = "imagenes/ojoAbierto.webp";
                }
            }
            document.addEventListener('DOMContentLoaded', function () {
            const toastEl = document.querySelector('.toast');
                if (toastEl) {
                    const bsToast = new bootstrap.Toast(toastEl, {
                        autohide: true,
                        delay: 7000
                    });
                    bsToast.show();
                }
            });
        </script>     
</body>     
</html>