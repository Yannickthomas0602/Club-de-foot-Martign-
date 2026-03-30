    <footer class="footer-wrapper">
        <link rel="stylesheet" href="assets/css/carousel.css">
        <link rel="stylesheet" href="assets/css/footer.css">
        <script src="assets/js/burger_menu.js"></script>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Bebas+Neue&family=Great+Vibes&family=Pacifico&family=Roboto:ital,wght@0,100..900;1,100..900&display=swap" rel="stylesheet">
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Montserrat:ital,wght@0,100..900;1,100..900&display=swap" rel="stylesheet">

        <?php include __DIR__ . '/carousel.php'; ?>
        <script src="assets/js/carousel.js"></script>
        <script src="assets/js/script.js"></script>

        <div class="footer-container">
            <!-- Colonne 1 : Logo & Réseaux -->
            <div class="footer-col footer-about">
                <img class="footer-club-logo" src="assets/img/Logo_club/logo%20full%20blanc.png" alt="Logo Cadets Chelun Martigné">
                <p class="devise">
                  Jouer Ensemble,<span class="devise-break"> Vivre Ensemble,</span><span class="devise-break"> Grandir Ensemble</span>
                </p>
                <div class="social-links mt-3">
                    <a class="social-link" href="https://www.facebook.com/p/Cadets-Chelun-Martign%C3%A9-Ferchaud-100057405613179/" target="_blank" rel="noopener noreferrer">
                        <img class="social-img" src="assets/img/Logo_club/icons8-facebook-100 (1).png" alt="logo facebook">
                    </a>
                    <a class="social-link" href="https://www.instagram.com/cadetschelunmartigne/" target="_blank" rel="noopener noreferrer">
                        <img class="social-img" src="assets/img/Logo_club/icons8-instagram-100 (1).png" alt="logo instagram">
                    </a>
                </div>
            </div>

            <!-- Colonne 2 : Navigation (Divisée en deux pour un meilleur équilibre) -->
            <div class="footer-col footer-nav-col">
                <p class="footer-title">Navigation</p>
                <div class="footer-nav-split">
                    <ul class="footer-nav-list">
                        <li><a href="index.php">Accueil</a></li>
                        <li><a href="calendrier.php">Calendrier</a></li>
                        <li><a href="equipes.php">Équipes</a></li>
                        <li><a href="#">Convocations</a></li>
                        <li><a href="photos.php">Album photos</a></li>
                    </ul>
                    <ul class="footer-nav-list">
                        <li><a href="pef.php">PEF</a></li>
                        <li><a class="boutique-link" href="https://cadets-chelun-martigne.kalisport.com/" target="_blank" rel="noopener noreferrer">Boutique</a></li>
                        <li><a href="organigramme.php">Organigramme</a></li>
                        <li><a href="resultats.php">Résultats</a></li>
                        <li><a href="coach.php">Espace coach</a></li>
                    </ul>
                </div>
            </div>

            <!-- Colonne 3 : Légal, Contact & Support -->
            <div class="footer-col footer-legal-col">
                <p class="footer-title">Informations & Support</p>
                <ul class="footer-contact-list">
                    <li>
                        <a href="tel:+33000000000"><i class="fa-solid fa-phone"></i> 06 31 07 99 01</a>
                    </li>
                    <li>
                        <a href="mailto:contact@cadets-chelun-martigne.fr"><i class="fa-solid fa-envelope"></i> CCMreseauxsociaux@gmail.com</a>
                    </li>
                </ul>
                <ul class="footer-legal-list mt-2">
                    <li><a href="#">Mentions légales</a></li>
                    <li><a href="#">Informations légales</a></li>
                    <li><a href="#">Gestion des cookies</a></li>
                </ul>
            </div>
        </div>
        
        <div class="footer-bottom">
            <p>&copy; <?php echo date("Y"); ?> Cadets Chelun Martigné-Ferchaud. Tous droits réservés.</p>
        </div>
     </footer>
    
</body>
</html>