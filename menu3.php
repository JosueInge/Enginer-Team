<?php
$currentPage = basename($_SERVER['PHP_SELF']);
?>
<style>

.encabezado {
    background-color: #061F3E; 
    color: white; 
    padding: 10px 20px; 
    display: flex; 
    justify-content: space-between; 
    align-items: center; 
    position: fixed; 
    top: 0; 
    left: 0; 
    right: 0; 
    z-index: 1000; 
    height: 70px; 
}

.logo-box {
    display: flex;
    align-items: center;
}

.logo-box img {
    height: 45px;
    margin-top: 3px; 
}

/* enlaces */
.nav-links {
    display: flex;
    gap: 50px; /* Mayor separación entre cada enlace */
    align-items: center;
    margin-right: 20px;
}

.nav-link {
    color: #ffffff;
    font-weight: 600;
    text-decoration: none;
    position: relative;
    padding-bottom: 3px;
    transition: 0.3s;
    font-size: 15px; /* Tamaño similar al de tu captura */
    font-family: 'Open Sans', sans-serif;
}

.nav-link.active::after {
    content: "";
    position: absolute;
    left: 0;
    bottom: -1px;
    height: 2px; /* Línea más delgada */
    width: 100%;
    background-color: #4ca2ff;
    border-radius: 2px;
}

/* Enlace activo */
.nav-link.active {
    color: #4ca2ff;
}

/* responsivo */
@media (max-width: 768px) {
    .encabezado {
        flex-wrap: wrap;
        height: auto;
        padding: 10px;
    }
    
    .logo-box {
        flex: 0 0 100%;
        margin-bottom: 10px;
    }
    
    .logo-box img {
        height: 38px;
    }
    
    .nav-links {
        flex: 0 0 100%;
        gap: 20px;
        justify-content: center;
    }
}
</style>

<header class="encabezado">
    <div class="logo-box">
        <img src="imagenes/logo.png" alt="Comunicado Digital">
    </div>

    <nav class="nav-links">
        <a href="inicio.php" class="nav-link <?= $currentPage === 'inicio.php' ? 'active' : '' ?>">Inicio</a>
        <a href="revision_noticias.php" class="nav-link <?= $currentPage === 'revision_noticias.php' ? 'active' : '' ?>">Noticias</a>
        <a href="revision_denuncias.php" class="nav-link <?= $currentPage === 'revision_denuncias.php' ? 'active' : '' ?>">Denuncias</a>
        <a href="revision_reportes.php" class="nav-link <?= ($currentPage === 'revision_reportes.php' || $currentPage === 'revision_reportes_denuncias.php') ? 'active' : '' ?>">Reportes</a>
    </nav>
</header>