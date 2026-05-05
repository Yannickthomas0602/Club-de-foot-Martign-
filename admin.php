<?php
session_start();
require_once "fonctions.php";
checkSessionTimeout();

if (!isset($_SESSION['user_id']) || $_SESSION['role_slug'] !== 'admin') {
    header("Location: login.php");
    exit;
}
$page_title = "Tableau de Bord Admin";

$pdo = getDB();

// Le nom d'utilisateur est stocké dans la session
$displayName = $_SESSION['username'] ?? 'Admin';

// Fetch KPIs
try {
    $countTeams = $pdo->query("SELECT COUNT(*) FROM teams")->fetchColumn();
} catch (Throwable $e) {
    $countTeams = 0;
}

try {
    $countAnnonces = $pdo->query("SELECT COUNT(*) FROM annonces_popup WHERE actif = 1 AND date_fin > NOW()")->fetchColumn();
} catch (Throwable $e) {
    $countAnnonces = 0;
}

try {
    $countPef = $pdo->query("SELECT COUNT(*) FROM pef_articles")->fetchColumn();
} catch (Throwable $e) {
    $countPef = 0;
}
?>
<?php include 'header.php'; ?>
<link rel="stylesheet" href="assets/css/admin.css">

<main class="admin-dashboard-container">
    <!-- En-tête du Dashboard -->
    <header class="dashboard-header">
        <div class="header-text">
            <h1>Bonjour, <span class="highlight"><?= htmlspecialchars($displayName) ?></span> 👋</h1>
            <p>Voici un aperçu de l'activité du club Cadets Chelun Martigné.</p>
        </div>
        <div class="header-action">
            <a href="annonces_popup_admin.php" class="btn-create-annonce">
                <i class="fa-solid fa-bullhorn"></i> Nouvelle Annonce
            </a>
        </div>
    </header>

    <!-- KPIs Section -->
    <section class="kpi-section">
        <div class="kpi-card">
            <div class="kpi-icon icon-teams">
                <i class="fa-solid fa-shirt"></i>
            </div>
            <div class="kpi-info">
                <h3><?= (int)$countTeams ?></h3>
                <p>Équipes gérées</p>
            </div>
        </div>
        
        <div class="kpi-card">
            <div class="kpi-icon icon-annonces">
                <i class="fa-solid fa-bell"></i>
            </div>
            <div class="kpi-info">
                <h3><?= (int)$countAnnonces ?></h3>
                <p>Annonces actives</p>
            </div>
        </div>
        
        <div class="kpi-card">
            <div class="kpi-icon icon-pef">
                <i class="fa-solid fa-graduation-cap"></i>
            </div>
            <div class="kpi-info">
                <h3><?= (int)$countPef ?></h3>
                <p>Articles PEF</p>
            </div>
        </div>
    </section>

    <!-- Grille d'actions rapides -->
    <section class="actions-section">
        <h2><i class="fa-solid fa-bolt"></i> Accès Rapides</h2>
        
        <div class="actions-grid">
            <!-- Utilisateurs -->
            <a href="manage_users.php" class="action-card">
                <div class="action-icon">
                    <i class="fa-solid fa-user-gear"></i>
                </div>
                <div class="action-content">
                    <h4>Utilisateurs</h4>
                    <p>Gérer les coachs, joueurs et accès</p>
                </div>
                <div class="action-arrow"><i class="fa-solid fa-chevron-right"></i></div>
            </a>

            <!-- Équipes -->
            <a href="manage_teams.php" class="action-card">
                <div class="action-icon">
                    <i class="fa-solid fa-people-group"></i>
                </div>
                <div class="action-content">
                    <h4>Équipes</h4>
                    <p>Gérer la liste et les affectations</p>
                </div>
                <div class="action-arrow"><i class="fa-solid fa-chevron-right"></i></div>
            </a>

            <!-- Convocations -->
            <a href="convocation_admin.php" class="action-card">
                <div class="action-icon">
                    <i class="fa-solid fa-calendar-check"></i>
                </div>
                <div class="action-content">
                    <h4>Convocations</h4>
                    <p>Planifier les matchs et présences</p>
                </div>
                <div class="action-arrow"><i class="fa-solid fa-chevron-right"></i></div>
            </a>

            <!-- Annonces -->
            <a href="annonces_popup_admin.php" class="action-card">
                <div class="action-icon">
                    <i class="fa-solid fa-message"></i>
                </div>
                <div class="action-content">
                    <h4>Annonces Pop-up</h4>
                    <p>Diffuser des alertes sur le site</p>
                </div>
                <div class="action-arrow"><i class="fa-solid fa-chevron-right"></i></div>
            </a>

            <!-- Organigramme -->
            <a href="admin_organigramme.php" class="action-card">
                <div class="action-icon">
                    <i class="fa-solid fa-sitemap"></i>
                </div>
                <div class="action-content">
                    <h4>Organigramme</h4>
                    <p>Mettre à jour le bureau du club</p>
                </div>
                <div class="action-arrow"><i class="fa-solid fa-chevron-right"></i></div>
            </a>

            <!-- PEF -->
            <a href="admin_pef.php" class="action-card">
                <div class="action-icon">
                    <i class="fa-solid fa-book-open-reader"></i>
                </div>
                <div class="action-content">
                    <h4>Programme PEF</h4>
                    <p>Rédiger et publier des articles</p>
                </div>
                <div class="action-arrow"><i class="fa-solid fa-chevron-right"></i></div>
            </a>

            <!-- Sponsors -->
            <a href="admin_sponsors.php" class="action-card">
                <div class="action-icon">
                    <i class="fa-solid fa-handshake-angle"></i>
                </div>
                <div class="action-content">
                    <h4>Sponsors</h4>
                    <p>Gérer le carrousel des partenaires</p>
                </div>
                <div class="action-arrow"><i class="fa-solid fa-chevron-right"></i></div>
            </a>
        </div>
    </section>
</main>

<footer>
    <?php include 'footer.php'; ?>
</footer>
</body>
</html>