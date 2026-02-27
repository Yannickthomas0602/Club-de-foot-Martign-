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
    <a href="manage_users.php">Gérer les utilisateurs</a>
    <br>
    <a href="convocation_admin.php">accès convocation</a>
    <br>
    <a href="annonces_popup_admin.php">Gérer les annonces pop-up</a>
    </main>
    <footer>
        <?php include 'footer.php'; ?>
    </footer>
</body>
</html>