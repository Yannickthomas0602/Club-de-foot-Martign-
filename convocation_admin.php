<?php
session_start();
require "fonctions.php";
$pdo = getDB();

checkSessionTimeout();

if (!isset($_SESSION['user_id']) || ($_SESSION['role_slug'] ?? '') !== 'admin') {
    header("Location: login.php");
    exit;
}

// Récupère les équipes pour le select
$stmtTeams = $pdo->query("SELECT id, name FROM teams ORDER BY name ASC");
$teams = $stmtTeams->fetchAll(PDO::FETCH_ASSOC);

// Nettoyage automatique : supprime les convocations dont la date du match est passée de plus de 30 jours
$pdo->exec("DELETE FROM convocations WHERE match_date < DATE_SUB(NOW(), INTERVAL 30 DAY)");

// Récupère les convocations
$stmt = $pdo->query("
    SELECT c.id, t.name AS team_name, c.team_id, c.match_place, c.match_date, c.opposing_team 
    FROM convocations c 
    JOIN teams t ON c.team_id = t.id
    ORDER BY c.match_date DESC
");
$convocations = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Récupérer les joueurs présents pour chaque convocation
$stmtPlayers = $pdo->prepare("
    SELECT p.first_name, p.initial_name
    FROM players p
    WHERE p.team_id = ? 
      AND p.id NOT IN (
          SELECT player_id FROM convocation_absences WHERE convocation_id = ?
      )
");

foreach ($convocations as &$c) {
    if (isset($c['match_date'])) {
        $dtRaw = str_replace('T', ' ', $c['match_date']);
        $dt = date_create($dtRaw);
        $c['match_date_formatted'] = $dt ? $dt->format('d/m/Y H:i') : htmlspecialchars($c['match_date'], ENT_QUOTES, 'UTF-8');
    }

    $stmtPlayers->execute([$c['team_id'], $c['id']]);
    $present_players = $stmtPlayers->fetchAll(PDO::FETCH_ASSOC);
    
    if ($present_players) {
        $player_strings = array_map(function($p) { return $p['first_name'] . ' ' . $p['initial_name'].'.'; }, $present_players);
        $c['player_name_formatted'] = htmlspecialchars(implode(', ', $player_strings), ENT_QUOTES, 'UTF-8');
    } else {
        $c['player_name_formatted'] = "Aucun joueur présent";
    }
}
unset($c);

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
                <th>Joueurs convoqués (Présents)</th>
                <th>Actions</th>
            </tr>
            <?php foreach ($convocations as $c): ?>
            <tr>
                <td><?= $c['id']?></td>
                <td><?= htmlspecialchars($c['team_name']) ?></td>
                <td><?= htmlspecialchars($c['match_place']) ?></td>
                <td><?= $c['match_date_formatted']?></td>
                <td><?= htmlspecialchars($c['opposing_team']) ?></td>
                <td><?= $c['player_name_formatted']?></td>
                <td>
                    <a class="btn" href="edit_convocation.php?id=<?= $c['id'] ?>">Modifier</a>
                    <form action="delete_convocation.php" method="POST" style="display:inline;" onsubmit="return confirm('Êtes-vous sûr de vouloir supprimer la convocation ?');">
                        <input type="hidden" name="id" value="<?= $c['id'] ?>">
                        <button type="submit" class="btn btn-danger">Supprimer</button>
                    </form>
                </td>
            </tr>
            <?php endforeach; ?>
        </table>
    </div>

    <div id="ajout-convocation" class="ajout-convocation">
        <h3>Ajouter une convocation</h3>
        <form method="POST" action="add_convocations.php" class="add_convocation_form" id="convocationForm">
            <div class="nom_equipe">
                <label for="team_id">Equipe convoquée</label>
                <select name="team_id" id="team_select" required>
                    <option value="">-- Sélectionnez une équipe --</option>
                    <?php foreach($teams as $t): ?>
                        <option value="<?= $t['id'] ?>"><?= htmlspecialchars($t['name']) ?></option>
                    <?php endforeach; ?>
                </select>
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
                <label for="match_date">Date du match</label>
                <input type="datetime-local" name="match_date" required>
            </div>
            
            <div class="adversaire">
                <label for="opposing_team">Adversaire</label>
                <input type="text" name="opposing_team" placeholder="Insérer le nom de l'adversaire" required>
            </div>

            <div class="players-container" id="playersContainer">
                <h3>Gestion des présences</h3>
                <p>Cochez les joueurs pour les marquer comme <strong>absents</strong>.</p>
                <div class="grid-2-cols">
                    <div class="players-list" id="playersCheckboxList">
                        <!-- Rempli en AJAX -->
                    </div>
                    
                    <div class="recap-table">
                        <h4>Tableau récapitulatif</h4>
                        <table>
                            <thead>
                                <tr>
                                    <th>Joueur</th>
                                    <th>Statut</th>
                                </tr>
                            </thead>
                            <tbody id="recapBody">
                                <!-- Rempli en JS -->
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <button type="submit" class="btn" style="margin-top: 15px;">Ajouter</button>
        </form>
    </div>
</main>

<script src="assets/js/adresses.js"></script>
<script src="assets/js/convocation_admin.js"></script>

<?php include "footer.php"; ?>
