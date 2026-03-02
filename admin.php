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
    <main>
    <p>Admin</p>
    <li>
    <ul><a href="manage_users.php">Gérer les utilisateurs</a></ul>
    <ul><a href="convocation_admin.php">Gérer les convocations</a></ul>
    <ul><a href="annonces_popup_admin.php">Gérer les annonces pop-up</a></ul>
    <ul><a href="admin_organigramme.php">Gérer l'organigramme</a></ul>
    <ul><a href="admin_sponsors.php">Gérer les sponsors (carrousel)</a></ul>
    </li>
    </main>
    <footer>
        <?php include 'footer.php'; ?>
    </footer>
</body>
</html>