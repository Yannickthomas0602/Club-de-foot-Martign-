<?php
require_once __DIR__ . "/fonctions.php";

if (session_status() !== PHP_SESSION_ACTIVE) {
    session_start();
}

$base_href = isset($base_href) ? (string)$base_href : '';

?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <?php if ($base_href !== ''): ?>
    <base href="<?= htmlspecialchars($base_href, ENT_QUOTES, 'UTF-8') ?>">
    <?php endif; ?>
    <link rel="stylesheet" href="assets/css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    
    <title>
        <?php
        if (!isset($page_title) || $page_title === "") {
            // Page d’accueil → titre simple
            echo "Cadets Chelun Martigné";
        } else {
            // Toutes les autres pages
            echo $page_title . " | Cadets Chelun Martigné";
        }
        ?>
    </title>


    <!-- Logo du club devant le titre de l'onglet -->
    <link rel="icon" type="image/png" href="assets/img/Logo_club/logo_rogne.png"> 
    <link rel="stylesheet" href="assets/css/header.css">
    <style>
    
@import url('https://fonts.googleapis.com/css2?family=Roboto:ital,wght@0,100..900;1,100..900&display=swap');
</style>
</head>
<body>  
    <header class="navbar-modern">
        <div class="nav-container">
            <!-- 1. GAUCHE -->
            <div class="nav-left">
                <button id="mobile-menu-btn" class="burger-menu mobile-only" aria-label="Menu">
                    <span></span><span></span><span></span>
                </button>
                <nav class="desktop-only desktop-links">
                    <a href="index.php" class="nav-link">Accueil</a>
                    <a href="equipes.php" class="nav-link">&Eacute;quipes</a>
                    <a href="resultats.php" class="nav-link">R&eacute;sultats</a>
                </nav>
            </div>

            <!-- 2. CENTRE : LOGO -->
            <div class="nav-center">
                <a href="index.php" class="logo-wrapper">
                    <img src="assets/img/Logo_club/logo_rogne.png" alt="Logo club" class="nav-logo">
                </a>
            </div>

            <!-- 3. DROITE -->
            <div class="nav-right">
                <nav class="desktop-only desktop-links">
                    <a href="photos.php" class="nav-link">Photos</a>
                    <div class="dropdown">
                        <span class="nav-link dropdown-toggle">Le Club <i class="fa-solid fa-chevron-down dropdown-icon"></i></span>
                        <div class="dropdown-menu">
                            <a href="organigramme.php">Organigramme</a>
                            <a href="pef.php">PEF</a>
                            <a href="#">Convocations</a>
                        </div>
                    </div>
                    <a href="https://cadets-chelun-martigne.kalisport.com/" target="_blank" rel="noopener noreferrer" class="nav-link btn-boutique">Boutique</a>
                </nav>

                <div class="auth-buttons">
                    <?php if (isset($_SESSION['user_id'])): ?>
                        <a href="admin.php" class="btn-icon desktop-only" title="Mon Compte"><i class="fa-solid fa-user"></i></a>
                        <a href="logout.php" class="btn-logout desktop-only" title="Se déconnecter"><i class="fa-solid fa-right-from-bracket"></i></a>
                        <a href="admin.php" class="btn-icon mobile-only"><i class="fa-solid fa-circle-user"></i></a>
                    <?php else: ?>
                        <a href="login.php" class="btn-login desktop-only">Se connecter</a>
                        <a href="login.php" class="btn-icon mobile-only"><i class="fa-solid fa-user"></i></a>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </header>

    <div id="mobile-drawer" class="mobile-drawer">
        <div class="drawer-header">
            <h2 class="drawer-title">Menu</h2>
            <button id="close-drawer-btn" class="close-btn">&times;</button>
        </div>
        <div class="drawer-content">
            <ul class="drawer-links">
                <li><a href="index.php">Accueil</a></li>
                <li><a href="equipes.php">&Eacute;quipes</a></li>
                <li><a href="resultats.php">R&eacute;sultats</a></li>
                <li><a href="calendrier.php">Calendrier</a></li>
                <li><a href="photos.php">Photos</a></li>
                <li><a href="organigramme.php">Organigramme</a></li>
                <li><a href="pef.php">PEF</a></li>
                <li><a href="https://cadets-chelun-martigne.kalisport.com/" target="_blank" rel="noopener noreferrer" class="boutique-text">Boutique du club</a></li>
            </ul>
            <div class="drawer-footer">
                <?php if (isset($_SESSION['user_id'])): ?>
                    <a href="admin.php" class="btn-register w-100 mb-2">Mon Tableau de bord</a>
                    <a href="logout.php" class="btn-login w-100">Déconnexion</a>
                <?php else: ?>
                    <a href="login.php" class="btn-login w-100 mb-2">Se connecter</a>
                <?php endif; ?>
            </div>
        </div>
    </div>
    <div id="drawer-overlay" class="drawer-overlay"></div>
    <script src="assets/js/menu_burger.js" defer></script>
