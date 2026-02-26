<?php
session_start();
require "fonctions.php";

$pdo = getDB();
checkSessionTimeout();

if (!isset($_SESSION['user_id']) || ($_SESSION['role_slug'] ?? null) !== 'admin') {
    header("Location: login.php");
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    $_SESSION['flash_error'] = "Méthode invalide.";
    header("Location: Convocation_admin.php");
    exit;
}

$id = intval($_POST['id'] ?? 0);
// si l'id n'est pas un entier positif, on redirige vers la page de gestion des convocations
if ($id <= 0) {
    $_SESSION['flash_error'] = "ID de convocation invalide.";
    header("Location: Convocation_admin.php");
    exit;
}


try {
    $stmt = $pdo->prepare("DELETE FROM convocations WHERE id = ?");
    $stmt->execute([$id]);
// si la suppression a affecté au moins une ligne, on considère que c'était un succès, sinon on affiche une erreur
    if ($stmt->rowCount() > 0) {
        $_SESSION['flash_success'] = "Convocation supprimée avec succès.";
    } else {
        $_SESSION['flash_error'] = "Convocation introuvable ou déjà supprimée.";
    }

} catch (PDOException $e) {
    $_SESSION['flash_error'] = "Erreur lors de la suppression : " . $e->getMessage();
}

header("Location: Convocation_admin.php");
exit;