<?php
session_start();
include 'conexion.php';
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Terminos y Condiciones - Periodico Digital Comunitario</title>
    <style>
        body {
            font-family: Arial,sans-serif;
            line-height: 1.6;
            margin: 0;
            padding: 20px;
            background-color: #f9f9f9;
            color: #333;
        }
        .container {
            max-width: 800px;
            margin: auto;
            background: white;
            padding: 30px;
            border-radius: 8px;
            box-shadow: 0 0 10px rgba(0,0,0,0.1);
        }
        h1 {
            color: #0d5c9b;
            border-bottom: 2px solid #0d5c9b;
            padding-bottom: 10px;
        }
        h2 {
            color: #0d5c9b;
            margin-top: #0d5c9b;
        }
        h3 {
            color: #0d5c9b;
        }
        .date {
            font-style: italic;
            color: #666;
            margin-bottom: 20px;
        }
        .back-buttom {
            display:inline-block;
            margin-top: 20px;
            padding: 10px 15px;
            background-color: #0d5c9b;
            color: white;
            text-decoration: none;
            border-radius: 5px;
            font-weight: bold;
        }
        .back-buttom:hover {
            background-color: #0d5c9b;
        }
        header {
            background-color: #0d5c9b;
            color: white;
            padding: 15px 30px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 30px;
        }
        .logo {
            font-size: 24px;
            font-weight: bold;
        }
        .logo img {
            height: 50px;
            margin-right: 10px;
        }
        .informacion a {
            color: white;
            margin-left: 20px;
            text-decoration: none;
            font-size: 14px;
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
            <a href="login.php">Iniciar Sesion</a>
        </div>
    </header>

    <div class="container">
        <h1>Terminos y condiciones de uso</h1>
        <h2>Periodico Digital Comunitario</h2>
        <div class="date">Ultima actualización: <?php echo date('d/m/Y');?></div>

        <h3>1. ACEPTACIÓN DE LOS TÉRMINOS</h3>
        <P>Al acceder y utilizar la aplicación web Periódico Digital Comunitario, el usuario acepta estar sujeto a estos Términos y Condiciones, así como a nuestra Política de Privacidad. Si no está de acuerdo, debe abstenerse de utilizar la aplicación.</P>

        <h3>2. DESCRIPCIÓN DEL SERVICIO</h3>
        <p>Periódico Digital Comunitario es una plataforma digital destinada a la difusión de noticias y contenido informativo de carácter comunitario en El Salvador. Los usuarios pueden registrarse para acceder a funciones adicionales, como comentar o interactuar con el contenido.</p>

        <h3>3. REGISTRO DE USUARIOS</h3>
        <p>Para acceder a ciertas funcionalidades, el usuario debe registrarse proporcionando información personal como nombre, apellidos y correo electrónico. Es responsabilidad del usuario proporcionar datos verídicos y mantener la confidencialidad de su cuenta.</p>

        <h3>4. PRIVACIDAD Y PROTECCIÓN DE DATOS</h3>
        <p>Los datos personales recopilados durante el registro serán tratados conforme a la legislación vigente en El Salvador y nuestra Política de Privacidad. La información es almacenada y gestionada mediante los servicios de Firebase, ofrecidos por Google LLC, lo cual puede implicar transferencias internacionales de datos</p>

        <h3>5. USO ADECUADO</h3>
        <p>El usuario se compromete a utilizar la plataforma de forma lícita y respetuosa, absteniéndose de publicar contenido ofensivo, difamatorio, ilegal o que viole derechos de terceros. Periódico Digital Comunitario se reserva el derecho de suspender o eliminar cuentas que incumplan estas normas.</p>

        <h3>6. EDAD MÍNIMA</h3>
        <p>El uso de la aplicación está dirigido a personas mayores de 13 años. Al registrarse, el usuario declara cumplir con este requisito.</p>

        <h3>7. SERVICIOS DE TERCEROS</h3>
        <p>La plataforma utiliza servicios de terceros como Firebase para el almacenamiento y autenticación de usuarios. El uso de dichos servicios está sujeto a sus propios términos y condiciones, los cuales el usuario también acepta al utilizar nuestra aplicación.</p>

        <h3>8. MODIFICACIONES</h3>
        <p>Periódico Digital Comunitario se reserva el derecho de modificar estos Términos y Condiciones en cualquier momento. Las modificaciones serán publicadas en la plataforma y entrarán en vigor desde su publicación.</p>

        <h3>9. RESPONSABILIDAD</h3>
        <p>La plataforma no se hace responsable por las opiniones emitidas por los usuarios, ni por interrupciones del servicio causadas por problemas técnicos o ajenos a nuestro control.</p>

        <h3>10. LEGISLACIÓN APLICABLE</h3>
        <p>Estos Términos y Condiciones se rigen por las leyes de la República de El Salvador. Cualquier disputa será resuelta en los tribunales competentes del país.</p>

        <a href="javascript:history.back()" class="back-buttom">Volver al Registro</a>
    </div>
</body>
</html>