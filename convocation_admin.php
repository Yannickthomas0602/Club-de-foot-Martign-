<?php

session_start();
require "fonctions.php";
$pdo = getDB();

checkSessionTimeout();

if (!isset($_SESSION['user_id']) || $_SESSION['role_slug'] !== 'admin') {
    header("Location: login.php");
    exit;
}
// récupère les convocations avec les noms des équipes
$stmt = $pdo->query("SELECT c.id, t.name, c.match_place, c.match_date, c.opposing_team, c.player_name FROM convocations c JOIN teams t ON c.team_id = t.id");
$convocations = $stmt->fetchAll();

foreach ($convocations as &$c) {
    $dtRaw = str_replace('T', ' ', $c['match_date']);
    $dt = date_create($dtRaw);
    $c['match_date_formatted'] = $dt ? $dt->format('d/m/Y H:i') : htmlspecialchars($c['match_date'], ENT_QUOTES, 'UTF-8');

    $players = json_decode($c['player_name'], true);
    if (json_last_error() === JSON_ERROR_NONE && is_array($players)) {
        $c['player_name_formatted'] = htmlspecialchars(implode(', ', $players), ENT_QUOTES, 'UTF-8');
    } else {
        $c['player_name_formatted'] = htmlspecialchars((string)$c['player_name'], ENT_QUOTES, 'UTF-8');
    }
}
unset($c);
?>

<?php
$page_title = "Gestion des convocations";
include "header.php";
?>
<link rel="stylesheet" href="assets/css/convocations_admin.css">
<main>
    <div id="liste-convocations" class="liste-convocations">
        <h2>Liste des convocations : </h2>
        <table border="2">
            <tr>
                <th>ID</th>
                <th>Equipe convoquée</th>
                <th>Lieu</th>
                <th>Date</th>
                <th>Adversaire</th>
                <th>Joueurs convoqués</th>
                <th>Actions</th>
            </tr>
            <?php foreach ($convocations as $c): ?>
            <tr>
                <td><?= $c['id']?></td>
                <td><?= $c['name']?></td>
                <td><?= $c['match_place']?></td>
                <td><?= $c['match_date_formatted']?></td>
                <td><?= $c['opposing_team']?></td>
                <td><?= $c['player_name_formatted']?></td>
                <td>
                    <a class="btn" href="edit_convocation.php?id=<?= $c['id'] ?>">Modifier</a>
                    <form action="delete_convocation.php" method="POST" style="display:inline;" onsubmit="return confirm('Êtes-vous sûr de vouloir supprimer la convocation');">
                        <input type="hidden" name="id" value="<?= $c['id'] ?>">
                        <button type="submit" class="btn btn-danger">Supprimer</button>
                    </form>
                </td>
            </tr>
            <?php
                endforeach; ?>
        </table>
    </div>
    <div id="ajout-convocation" class="ajout-convocation">
        <h3>Ajouter une convocation</h3>
            <form method="POST" action="add_convocations.php" class="add_convocation_form">
                <div class="nom_equipe">
                    <label for="team_name">Equipe convoquée</label>
                    <input type="text" name="team_name" placeholder="Insérer le nom de l'équipe convoquée" required>
                </div>
                <div class="lieu_match">
                    
                    
                    <label for="addressInput">Adresse</label>
                    <input
                        id="addressInput"
                        name="addressInput"
                        type="text"
                        autocomplete="off"
                        placeholder="Tapez une adresse..."
                        required
                    >

                    <ul id="suggestions"></ul>

                </div>
                <div class="date_match">
                    <!-- date et heure -->
                    <label for="match_date">Date du match</label>
                    <input type="datetime-local" name="match_date" required>
                </div>
                <div class="adversaire">
                    <label for="opposing_team">Adversaire</label>
                    <input type="text" name="opposing_team" placeholder="Insérer le nom de l'adversaire" required>
                </div>
                <div class="joueurs_convoques">
                    <label for="player_name">Joueurs convoqués</label>
                    <input type="text" name="player_name" placeholder="Insérer les noms des joueurs convoqués (séparés par des virgules)" required>
                </div>
                <button type="submit" class="btn">Ajouter</button>
            </form>
    </div>
</main>
<script src="assets/js/adresses.js"></script>
<?php include "footer.php"; ?>

