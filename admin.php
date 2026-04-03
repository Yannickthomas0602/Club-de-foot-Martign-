<?php
session_start();
require_once "fonctions.php";
checkSessionTimeout();

if (!isset($_SESSION['user_id']) || $_SESSION['role_slug'] !== 'admin') {
    header("Location: login.php");
    exit;
}
$page_title = "Administration";
?>
    <?php include 'header.php'; ?>
    <link rel="stylesheet" href="assets/css/admin.css">
    <main class="admin-main">
        <h1>Panneau d'administration</h1>
        <div class="admin-dashboard">
            <a href="manage_users.php" class="admin-card">
                <h3>Utilisateurs</h3>
                <p>Gérer les coachs, équipes et administrateurs</p>
            </a>
            <a href="manage_teams.php" class="admin-card">
                <h3>Équipes</h3>
                <p>Gérer la liste des équipes et leurs joueurs</p>
            </a>
            <a href="convocation_admin.php" class="admin-card">
                <h3>Convocations</h3>
                <p>Gérer et planifier les convocations des joueurs</p>
            </a>
            <a href="annonces_popup_admin.php" class="admin-card">
                <h3>Annonces</h3>
                <p>Gérer les annonces d'événements et les alertes pop-up</p>
            </a>
            <a href="admin_organigramme.php" class="admin-card">
                <h3>Organigramme</h3>
                <p>Modifier la structure et le bureau du club</p>
            </a>
            <a href="admin_sponsors.php" class="admin-card">
                <h3>Sponsors</h3>
                <p>Ajouter ou modifier les partenaires dans le carrousel</p>
            </a>
            <a href="admin_pef.php" class="admin-card">
                <h3>PEF</h3>
                <p>Gérer le Programme Éducatif Fédéral (articles et ressources)</p>
            </a>
        </div>
    </main>
    <footer>
        <?php include 'footer.php'; ?>
    </footer>
</body>
</html>