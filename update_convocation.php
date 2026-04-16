<?php
session_start();
require "fonctions.php";

$pdo = getDB();
checkSessionTimeout();

if (!isset($_SESSION['user_id']) || ($_SESSION['role_slug'] ?? '') !== 'admin') {
    header("Location: login.php");
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    $_SESSION['flash_error'] = "Méthode invalide.";
    header("Location: convocation_admin.php");
    exit;
}

$id           = (int)($_POST['id'] ?? 0);
$team_id      = (int)($_POST['team_id'] ?? 0);
$match_place  = trim($_POST['match_place'] ?? ($_POST['addressInput'] ?? '')); 
$match_date   = trim($_POST['match_date'] ?? '');
$opponent     = trim($_POST['opposing_team'] ?? '');
$absences     = $_POST['absent_players'] ?? [];

$match_date_norm = str_replace('T', ' ', $match_date); 

if (!$id || !$team_id) {
    header("Location: convocation_admin.php");
    exit;
}

try {
    $pdo->beginTransaction();

    // Modifier la convocation
    $stmt = $pdo->prepare(
        "UPDATE convocations SET team_id = ?, match_place = ?, match_date = ?, opposing_team = ? WHERE id = ?"
    );
    $stmt->execute([$team_id, $match_place, $match_date_norm, $opponent, $id]);

    // Supprimer les anciennes absences
    $stmtDel = $pdo->prepare("DELETE FROM convocation_absences WHERE convocation_id = ?");
    $stmtDel->execute([$id]);

    // Ajouter les nouvelles absences
    if (!empty($absences)) {
        $stmtAbs = $pdo->prepare("INSERT INTO convocation_absences (convocation_id, player_id) VALUES (?, ?)");
        foreach ($absences as $player_id) {
            $stmtAbs->execute([$id, (int)$player_id]);
        }
    }

    $pdo->commit();
    $_SESSION['flash_success'] = "Convocation modifiée avec succès.";
} catch (Exception $e) {
    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }
    $_SESSION['flash_error'] = "Erreur : " . $e->getMessage();
}

header("Location: convocation_admin.php");
exit;
