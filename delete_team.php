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
    $id = (int)($_POST['id'] ?? 0);
    if ($id > 0) {
        // Supprimer les joueurs d'abord
        $players = getPlayersByTeam($pdo, $id);
        foreach ($players as $player) {
            deletePlayer($pdo, $player['id']);
        }
        // Supprimer l'équipe
        deleteTeam($pdo, $id);
    }
}

header("Location: manage_teams.php");
exit;
?>