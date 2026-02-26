<?php
session_start();
require "fonctions.php";

$pdo = getDB();
checkSessionTimeout();

if (!isset($_SESSION['user_id']) || ($_SESSION['role_slug'] ?? null) !== 'admin') {
    header("Location: login.php");
    exit;
}

// si la requête n'est pas en POST, on redirige vers la page de gestion des convocations
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    $_SESSION['flash_error'] = "Méthode invalide.";
    header("Location: Convocation_admin.php");
    exit;
}

// récupération des données du formulaire
$team_name    = trim($_POST['team_name'] ?? '');
$match_place  = trim($_POST['match_place'] ?? ($_POST['addressInput'] ?? '')); 
$match_date   = trim($_POST['match_date'] ?? '');
$opponent     = trim($_POST['opposing_team'] ?? '');
$players_raw  = trim($_POST['player_name'] ?? '');

// permet de normaliser la date pour la bdd et converti la liste des joueurs en JSON 
$match_date_norm = str_replace('T', ' ', $match_date); 
$players = array_values(array_filter(array_map('trim', explode(',', $players_raw)), fn($v) => $v !== ''));
$players_json = json_encode($players, JSON_UNESCAPED_UNICODE);

// ajout de la convocation dans la bdd
try {
    $pdo->beginTransaction();

    $stmt = $pdo->prepare("SELECT id FROM teams WHERE name = ? LIMIT 1");
    $stmt->execute([$team_name]);
    $team_id = $stmt->fetchColumn();

    if (!$team_id) {
        $stmt = $pdo->prepare("INSERT INTO teams (name) VALUES (?)");
        $stmt->execute([$team_name]);
        $team_id = (int)$pdo->lastInsertId();
    }

    $stmt = $pdo->prepare(
        "INSERT INTO convocations (team_id, match_place, match_date, opposing_team, player_name)
         VALUES (?, ?, ?, ?, ?)"
    );
    $stmt->execute([
        $team_id,
        $match_place,
        $match_date_norm,
        $opponent,
        $players_json
    ]);

    $pdo->commit();
    $_SESSION['flash_success'] = "Convocation ajoutée avec succès.";
} catch (PDOException $e) {
    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }
    $_SESSION['flash_error'] = "Erreur lors de l'ajout : " . $e->getMessage();
}

header("Location: Convocation_admin.php");
exit;