<?php
session_start();
require "fonctions.php";
$pdo = getDB();

checkSessionTimeout();

if (!isset($_SESSION['user_id']) || ($_SESSION['role_slug'] ?? '') !== 'admin') {
    header("Location: login.php");
    exit;
}

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
if (!$id) {
    header("Location: convocation_admin.php");
    exit;
}

// Fetch the convocation
$stmt = $pdo->prepare("SELECT * FROM convocations WHERE id = ?");
$stmt->execute([$id]);
$convocation = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$convocation) {
    header("Location: convocation_admin.php");
    exit;
}

// Fetch teams
$stmtTeams = $pdo->query("SELECT id, name FROM teams ORDER BY name ASC");
$teams = $stmtTeams->fetchAll(PDO::FETCH_ASSOC);

$dt = date_create(str_replace('T', ' ', $convocation['match_date']));
$date_html = $dt ? $dt->format('Y-m-d\TH:i') : '';

$page_title = "Modifier une convocation";
include "header.php";
?>
<link rel="stylesheet" href="assets/css/convocations_admin.css">
<main>
    <div id="ajout-convocation" class="ajout-convocation">
        <h3>Modifier la convocation #<?= $id ?></h3>
        <form method="POST" action="update_convocation.php" class="add_convocation_form" id="convocationForm" data-conv-id="<?= $id ?>">
            <input type="hidden" name="id" value="<?= $id ?>">
            
            <div class="nom_equipe">
                <label for="team_id">Equipe convoquée</label>
                <select name="team_id" id="team_select" required>
                    <option value="">-- Sélectionnez une équipe --</option>
                    <?php foreach($teams as $t): ?>
                        <option value="<?= $t['id'] ?>" <?= $t['id'] == $convocation['team_id'] ? 'selected' : '' ?>>
                            <?= htmlspecialchars($t['name']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            
            <div class="lieu_match">
                <label for="addressInput">Adresse</label>
                <input id="addressInput" name="addressInput" type="text" autocomplete="off" value="<?= htmlspecialchars($convocation['match_place']) ?>" required>
                <ul id="suggestions"></ul>
            </div>
            
            <div class="date_match">
                <label for="match_date">Date du match</label>
                <input type="datetime-local" name="match_date" value="<?= $date_html ?>" required>
            </div>
            
            <div class="adversaire">
                <label for="opposing_team">Adversaire</label>
                <input type="text" name="opposing_team" value="<?= htmlspecialchars($convocation['opposing_team']) ?>" required>
            </div>

            <div class="players-container" id="playersContainer">
                <h3>Gestion des présences</h3>
                <p>Cochez les joueurs pour les marquer comme <strong>absents</strong>.</p>
                <div class="grid-2-cols">
                    <div class="players-list" id="playersCheckboxList">
                        <!-- Rempli en AJAX -->
                        Chargement des joueurs...
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

            <button type="submit" class="btn" style="margin-top: 15px;">Enregistrer les modifications</button>
            <a href="convocation_admin.php" class="btn" style="background:#555; margin-left:10px;">Annuler</a>
        </form>
    </div>
</main>

<script src="assets/js/adresses.js"></script>
<script src="assets/js/edit_convocation.js"></script>

<?php include "footer.php"; ?>
