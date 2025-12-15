<?php
session_start();
include 'conexion.php';
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Términos y Condiciones - Periódico Digital Comunitario</title>
    <!-- Google Fonts para Poppins -->
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet">
    <!-- Font Awesome para iconos -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Poppins', sans-serif;
            line-height: 1.6;
            color: #403F48;
            background-color: #ffffff;
        }

        /* ENCABEZADO */
        .header-container {
            background-color: #061F3E;
            padding: 15px 40px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            width: 100%;
        }

        .logo img {
            height: 50px;
        }

        .back-link {
            font-family: 'Poppins', sans-serif;
            font-size: 24px;
            font-weight: 600;
            color: #FFFFFF;
            text-decoration: none;
            transition: all 0.3s ease;
        }

        .back-link:hover {
            color: #1661AC;
            text-decoration: underline;
        }

        /* CONTENIDO PRINCIPAL */
        .main-container {
            max-width: 1250px;
            margin: 0 auto;
            padding: 40px 20px;
        }

        /* TÍTULO PRINCIPAL */
        .main-title {
            font-family: 'Poppins', sans-serif;
            font-size: 32px;
            font-weight: 700;
            color: #1661AC;
            text-align: left;
            margin-bottom: 15px;
        }

        /* LÍNEA DIVISORA */
        .divider {
            width: 1250px;
            max-width: 100%;
            height: 2px;
            background-color: #061F3E;
            margin-bottom: 20px;
        }

        /* FECHA DE ACTUALIZACIÓN */
        .update-date {
            display: flex;
            align-items: center;
            gap: 10px;
            margin-bottom: 40px;
        }

        .update-date i {
            color: #1661AC;
            font-size: 30px;
        }

        .update-date span {
            font-family: 'Poppins', sans-serif;
            font-size: 16px;
            font-weight: 600;
            color: #403F48;
        }

        /* SECCIONES */
        .section {
            margin-bottom: 40px;
            width: 100%;
            max-width: 1250px;
        }

        .section-title {
            font-family: 'Poppins', sans-serif;
            font-size: 20px;
            font-weight: 700;
            color: #1661AC;
            text-align: left;
            margin-bottom: 15px;
        }

        .section-content {
            font-family: 'Poppins', sans-serif;
            font-size: 16px;
            line-height: 1.8;
            color: #403F48;
            text-align: left;
            margin-bottom: 10px;
        }

        .section-content strong {
            font-weight: 700;
        }

        .section-content ul {
            margin-left: 20px;
            margin-bottom: 15px;
        }

        .section-content li {
            margin-bottom: 8px;
        }

        /* RESPONSIVIDAD */
        @media (max-width: 1300px) {
            .main-container {
                padding: 30px 15px;
            }
            
            .divider {
                width: 100%;
            }
        }

        @media (max-width: 768px) {
            .header-container {
                padding: 15px 20px;
                flex-direction: column;
                gap: 15px;
            }

            .logo {
                width: 100%;
                text-align: center;
            }

            .back-link-container {
                width: 100%;
                text-align: center;
            }

            .main-title {
                font-size: 28px;
                text-align: center;
            }

            .section-title {
                font-size: 18px;
            }

            .section-content {
                font-size: 15px;
            }

            .update-date {
                flex-direction: column;
                text-align: center;
                gap: 5px;
            }
        }

        @media (max-width: 480px) {
            .main-title {
                font-size: 24px;
            }

            .back-link {
                font-size: 20px;
            }

            .section-title {
                font-size: 17px;
            }

            .section-content {
                font-size: 14px;
            }

            .update-date span {
                font-size: 14px;
            }
        }
    </style>
</head>
<body>
    <div class="header-container">
        <div class="logo">
            <img src="imagenes/logo.png" alt="Logo Periódico Digital Comunitario">
        </div>
        <div class="back-link-container">
            <a href="javascript:history.back();" class="back-link">Volver</a>
        </div>
    </div>

    <div class="main-container">
        <h1 class="main-title">Términos y condiciones</h1>
        
        <div class="divider"></div>

        <!-- Fecha de actualización -->
        <div class="update-date">
            <i class="fas fa-clock"></i>
            <span>Última actualización: 14 de noviembre de 2025</span>
        </div>

        <!-- Sección 1: Denuncias anónimas -->
        <div class="section">
            <h2 class="section-title">Denuncias anónimas.</h2>
            <h2 class="section-title">Definición jurídica de denuncia anónima.</h2>
            <p class="section-content">
                  Se entiende por <strong>denuncia anónima</strong> toda información enviada a Comunicado Digital sin que el sistema solicite datos identificables del denunciante, tales como nombre, correo electrónico, número telefónico u otros identificadores directos.
                </p>
                <p class="section-content">
                    El usuario decide voluntariamente el contenido que envía, asumiendo la responsabilidad legal del mismo.
                </p>
                <h2 class="section-title">Tratamiento técnico del anonimato.</h2>
                <ul class="section-content">
                    <li>Comunicado Digital <strong>no utiliza mecanismos activos de identificación del denunciante.</strong></li>
                    <li>Los registros técnicos del servidor (logs) se utilizan únicamente para fines de seguridad informática y prevención de abusos, y <strong>no se emplean para identificar al denunciante,</strong> salvo requerimiento legal expreso.</li>
                    <li>Los logs se conservan por un plazo limitado y razonable conforme a estándares de seguridad y luego son eliminados.</li>
                </ul>
        </div>
    </div>

    <script>
        // Script para manejar el hover del enlace "Volver"
        document.addEventListener('DOMContentLoaded', function() {
            const backLink = document.querySelector('.back-link');
            
            backLink.addEventListener('mouseenter', function() {
                this.style.color = '#1661AC';
                this.style.textDecoration = 'underline';
            });
            
            backLink.addEventListener('mouseleave', function() {
                this.style.color = '#FFFFFF';
                this.style.textDecoration = 'none';
            });
        });
    </script>
</body>
</html>