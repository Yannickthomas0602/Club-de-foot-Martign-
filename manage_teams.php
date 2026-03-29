<?php
session_start();
require "fonctions.php";
$pdo = getDB();

checkSessionTimeout();

if (!isset($_SESSION['user_id']) || $_SESSION['role_slug'] !== 'admin') {
    header("Location: login.php");
    exit;
}

// Récupérer toutes les équipes
$teams = getAllTeams($pdo);

$page_title = "Gestion des équipes";
?>

<?php include 'header.php'; ?>
<script src="assets/js/check_password.js"></script>
<main>
    <h1>Gestion des équipes</h1>
    <div id="liste-equipes">
        <h2>Liste des équipes</h2>
        <table border="2">
            <tr>
                <th>ID</th>
                <th>Nom de l'équipe</th>
                <th>Joueurs</th>
                <th>Actions</th>
            </tr>
            <?php foreach ($teams as $team): ?>
            <tr>
                <td><?= $team['id'] ?></td>
                <td><?= htmlspecialchars($team['name']) ?></td>
                <td>
                    <?php
                    $players = getPlayersByTeam($pdo, $team['id']);
                    if (count($players) > 0) {
                        echo '<ul>';
                        foreach ($players as $player) {
                            echo '<li>' . htmlspecialchars($player['first_name']) . ' ' . htmlspecialchars($player['initial_name']) . '.</li>';
                        }
                        echo '</ul>';
                    } else {
                        echo 'Aucun joueur';
                    }
                    ?>
                </td>
                <td>
                    <a href="edit_team.php?id=<?= $team['id'] ?>">Modifier</a>
                    <form action="delete_team.php" method="POST" style="display:inline;" onsubmit="return confirm('Êtes-vous sûr de vouloir supprimer l\'équipe <?= htmlspecialchars($team['name']) ?> ?');">
                        <input type="hidden" name="id" value="<?= $team['id'] ?>">
                        <button type="submit">Supprimer</button>
                    </form>
                </td>
            </tr>
            <?php endforeach; ?>
        </table>
            <button type="button" onclick="addPlayer()">Ajouter un joueur</button>
    <div id="ajout-equipe">
        <h2>Ajouter une équipe</h2>
        <form method="POST" action="add_team.php">
            <div>
                <label for="team_name">Nom de l'équipe</label>
                <input type="text" name="team_name" required>
            </div>
            <h3>Joueurs</h3>
            <div id="players">
                <div class="player">
                    <label>Prénom</label>
                    <input type="text" name="players[0][first_name]">
                    <label>Initiale du nom</label>
                    <input type="text" name="players[0][initial_name]" maxlength="1">
                    <button type="button" onclick="removePlayer(this)">Supprimer</button>
                </div>
            </div>
            <button type="button" onclick="addPlayer()">Ajouter un joueur</button>
            <button type="submit">Ajouter l'équipe</button>
        </form>
    </div>
</main>
<script>
let playerIndex = 1; // Commence à 1 car le premier est 0

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
    const playerDiv = button.parentElement;
    playerDiv.remove();
    // Optionnel : réindexer les noms, mais pas nécessaire car PHP gère les arrays avec clés numériques
}
</script>
<?php include 'footer.php'; ?>