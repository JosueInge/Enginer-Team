<footer class="site-footer">
    <div class="footer-content">

    <div class="footer-logo">
        <img src="imagenes/logoblanco.png" alt="Logo Comunicado Digital">
        <p class="copyright">copyright Comunicado Digital</p>
    </div>

    <div class="footer-links">
        <a href="login.php">INICIAR SESION</a>
        <a href="https://wa.me/" target="_blank">WHATSAPP</a>
        <a href="contacto.php">CONTACTO</a>
        <a href="sobrenosotros.php">SOBRE NOSOTROS</a>
    </div>

    <div class="footer-contact">
        <strong>CONTACTO:</strong><br>
        <a href="mailto:correo@gmail.com">correo@gmail.com</a>
    </div>

    <div class="footer-social">
        <a href="https://facebook.com" target="_blank"><i class="fab fa-facebook"></i></a>
        <a href="https://instagram.com" target="_blank"><i class="fab fa-instagram"></i></a>
        <a href="https://twitter.com" target="_blank"><i class="fab fa-twitter"></i></a>
    </div>

</div>

</footer>

<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">

<style>
    .site-footer {
        background-color: #1b1b1cff;
        color: white;
        padding: 50px 50px;
        font-family: Arial, sans-serif;
    }

     .footer-content {
        display: flex;
        justify-content: space-between;
        align-items: flex-start;
        flex-wrap: wrap;
        border-bottom: 1px solid #555;
        padding-bottom: 20px;
    }

    .footer-logo img {
        height: 90px;
        margin-bottom: 10px;
    }

    .footer-logo .copyright {
        font-size: 12px;
        color: #ccc;
    }

    .footer-links, 
    .footer-contact,
    .footer-social {
        display: flex;
        flex-direction: column;
        gap: 10px;
        margin: 10px 0;
    }

    .footer-links a,
    .footer-contact a {
        text-decoration: none;
        color: white;
        font-weight: bold;
    }

    .footer-contact a {
        color: #ccc
    }

    .footer-links a:hover,
    .footer-contact a:hover {
        color: #320dd7ff;
    }

    .footer-social a {
        font-size: 24px;
        color: white;
        margin-right: 15px;
    }

    .footer-social a:hover {
        color: #a5261d;
    }

    .footer-bottom {
        display: flex;
        align-items: center;
        justify-content: center;
        padding-top: 15px;
        gap: 10px;
        font-weight: bold;
    }

    .footer-bottom img {
        height: 30px;
    }
</style>