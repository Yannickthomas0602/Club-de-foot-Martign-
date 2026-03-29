<?php
session_start();
require "fonctions.php";
$pdo = getDB();

checkSessionTimeout();

if (!isset($_SESSION['user_id']) || $_SESSION['role_slug'] !== 'admin') {
    header("Location: login.php");
    exit;
}

$id = (int)($_GET['id'] ?? 0);
$team = getTeamById($pdo, $id);
if (!$team) {
    header("Location: manage_teams.php");
    exit;
}

$players = getPlayersByTeam($pdo, $id);

$page_title = "Modifier l'équipe";
?>

<?php include 'header.php'; ?>
<link rel="stylesheet" href="assets/css/edit_team.css">
<main>
    <h1>Modifier l'équipe</h1>
    <form method="POST" action="update_team.php">
        <input type="hidden" name="id" value="<?= $team['id'] ?>">
        <div>
            <label for="name">Nom de l'équipe</label>
            <input type="text" name="name" value="<?= htmlspecialchars($team['name']) ?>" required>
        </div>
        <h3>Joueurs</h3>
        <div id="players">
            <?php foreach ($players as $index => $player): ?>
            <div class="player">
                <input type="hidden" name="existing_players[<?= $index ?>][id]" value="<?= $player['id'] ?>">
                <label>Prénom</label>
                <input type="text" name="existing_players[<?= $index ?>][first_name]" value="<?= htmlspecialchars($player['first_name']) ?>">
                <label>Initiale du nom</label>
                <input type="text" name="existing_players[<?= $index ?>][initial_name]" value="<?= htmlspecialchars($player['initial_name']) ?>" maxlength="1">
                <button type="button" onclick="removePlayer(this)">Supprimer</button>
            </div>
            <?php endforeach; ?>
        </div>
        <button type="button" onclick="addPlayer()">Ajouter un joueur</button>
        <br><br>
        <button type="submit">Mettre à jour</button>
    </form>
    <a href="manage_teams.php">Retour</a>
</main>
<script>
let playerIndex = 0; // Pour les nouveaux joueurs

function addPlayer() {
    const playersDiv = document.getElementById('players');
    const newPlayerDiv = document.createElement('div');
    newPlayerDiv.className = 'player';
    newPlayerDiv.innerHTML = `
        <label>Prénom</label>
        <input type="text" name="players[${playerIndex}][first_name]">
        <label>Initiale du nom</label>
        <input type="text" name="players[${playerIndex}][initial_name]" maxlength="1">
        <button type="button" onclick="removePlayer(this)">Supprimer</button>
    `;
    playersDiv.appendChild(newPlayerDiv);
    playerIndex++;
}

function removePlayer(button) {
    if (confirm('Êtes-vous sûr de vouloir supprimer ce joueur ?')) {
        const playerDiv = button.parentElement;
        playerDiv.remove();
    }
}
</script>
<?php include 'footer.php'; ?>