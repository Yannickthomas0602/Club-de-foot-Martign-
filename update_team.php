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
    $name = trim($_POST['name'] ?? '');
    if ($id > 0 && !empty($name)) {
        updateTeam($pdo, $id, ['name' => $name]);
    }

    // Traiter les joueurs existants
    $existingPlayers = $_POST['existing_players'] ?? [];
    foreach ($existingPlayers as $playerData) {
        $playerId = (int)($playerData['id'] ?? 0);
        $firstName = trim($playerData['first_name'] ?? '');
        $initialName = trim($playerData['initial_name'] ?? '');
        if ($playerId > 0) {
            if (!empty($firstName) && !empty($initialName)) {
                // Update
                updatePlayer($pdo, $playerId, ['first_name' => $firstName, 'initial_name' => $initialName]);
            } else {
                // Delete si champs vides
                deletePlayer($pdo, $playerId);
            }
        }
    }

    // Traiter les nouveaux joueurs
    $newPlayers = $_POST['players'] ?? [];
    foreach ($newPlayers as $playerData) {
        $firstName = trim($playerData['first_name'] ?? '');
        $initialName = trim($playerData['initial_name'] ?? '');
        if (!empty($firstName) && !empty($initialName)) {
            createPlayer($pdo, $id, $firstName, $initialName);
        }
    }
}

header("Location: manage_teams.php");
exit;
?>