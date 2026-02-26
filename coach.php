<?php

session_start();
require "fonctions.php";
$pdo = getDB();

checkSessionTimeout();

if (!isset($_SESSION['user_id']) || ($_SESSION['role_slug'] !== 'coach' && $_SESSION['role_slug'] !== 'admin')) {
    header("Location: login.php");
    exit;
}

$page_title = "Espace Coach";
?>

<?php include 'header.php'; ?>
<main>
    <h1>Espace coach</h1>
</main>
<?php include 'footer.php'; ?>