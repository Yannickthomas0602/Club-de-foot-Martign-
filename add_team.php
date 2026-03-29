<?php
session_start();
require "fonctions.php";
$pdo = getDB();

checkSessionTimeout();

if (!isset($_SESSION['user_id']) || $_SESSION['role_slug'] !== 'admin') {
    header("Location: login.php");
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $teamName = trim($_POST['team_name'] ?? '');
    $players = $_POST['players'] ?? [];

    if (empty($teamName)) {
        die("Nom de l'équipe requis.");
    }

    // Créer l'équipe
    $teamId = createTeam($pdo, $teamName);

    // Ajouter les joueurs
    foreach ($players as $player) {
        $firstName = trim($player['first_name'] ?? '');
        $initialName = trim($player['initial_name'] ?? '');
        if (!empty($firstName) && !empty($initialName)) {
            createPlayer($pdo, $teamId, $firstName, $initialName);
        }
    }

    // Rediriger vers manage_teams.php
    header("Location: manage_teams.php");
    exit;
} else {
    header("Location: manage_teams.php");
    exit;
}
?>