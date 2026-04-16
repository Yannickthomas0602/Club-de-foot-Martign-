<?php
session_start();
require_once "../fonctions.php";
$pdo = getDB();

if (!isset($_SESSION['user_id']) || ($_SESSION['role_slug'] ?? '') !== 'admin') {
    http_response_code(403);
    echo json_encode(['error' => 'Accès refusé']);
    exit;
}

if (!isset($_GET['team_id'])) {
    http_response_code(400);
    echo json_encode(['error' => 'Team ID manquant']);
    exit;
}

$team_id = (int)$_GET['team_id'];
$convocation_id = isset($_GET['convocation_id']) ? (int)$_GET['convocation_id'] : null;

// On récupère les joueurs de l'équipe
$stmt = $pdo->prepare("SELECT id, first_name, initial_name FROM players WHERE team_id = ? ORDER BY first_name ASC");
$stmt->execute([$team_id]);
$players = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Si une convocation est en édition, on récupère les absents
$absentIds = [];
if ($convocation_id) {
    $stmtAbs = $pdo->prepare("SELECT player_id FROM convocation_absences WHERE convocation_id = ?");
    $stmtAbs->execute([$convocation_id]);
    $absentIds = $stmtAbs->fetchAll(PDO::FETCH_COLUMN);
}

// On fusionne les données
foreach ($players as &$player) {
    $player['is_absent'] = in_array($player['id'], $absentIds);
}

header('Content-Type: application/json');
echo json_encode(['status' => 'success', 'players' => $players]);
