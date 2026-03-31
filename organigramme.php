<?php
// ─── Sécurité & connexion ────────────────────────────────────────────────────
require_once __DIR__ . '/fonctions.php';
if (session_status() !== PHP_SESSION_ACTIVE) { session_start(); }

$pdo = getDB();

// ─── 1. Récupération des catégories triées par priorité ─────────────────────
$categories = $pdo->query(
    'SELECT id, nom_categorie
       FROM club_categories_organigramme
      ORDER BY ordre_priorite ASC, nom_categorie ASC'
)->fetchAll();

// ─── 2. Récupération de tous les membres avec leur catégorie ─────────────────
$stmt = $pdo->query(
    'SELECT m.id, m.nom, m.prenom, m.role, m.categorie_id, m.photo_url, m.ordre_affichage
       FROM club_membres m
      ORDER BY m.ordre_affichage ASC, m.nom ASC'
);
$tousLesMembres = $stmt->fetchAll();

// ─── 3. Grouper les membres par categorie_id ─────────────────────────────────
$membresByCat = [];
foreach ($tousLesMembres as $m) {
    $membresByCat[$m['categorie_id']][] = $m;
}

// ─── Icônes par nom de catégorie (fallback = users) ──────────────────────────
// Vous pouvez enrichir ce tableau au fur et à mesure
$icones = [
    'Bureau'               => 'fa-solid fa-briefcase',
    'Staff Technique'      => 'fa-solid fa-whistle',
    'Responsables Jeunes'  => 'fa-solid fa-child-reaching',
    'Arbitres'             => 'fa-solid fa-flag',
    'Bénévoles'            => 'fa-solid fa-hands-helping',
    'Médical'              => 'fa-solid fa-kit-medical',
];

$page_title = "L'organigramme du club";
?>
<?php include 'header.php'; ?>
<link rel="stylesheet" href="assets/css/organigramme.css">

<main>
    <section class="orga-section">

        <!-- ── Héro ── -->
        <div class="orga-hero" data-aos="fade-up">
            <span class="orga-hero-eyebrow">
                <i class="fa-solid fa-sitemap"></i>&nbsp; Organigramme
            </span>
            <h1 class="orga-hero-title">Notre équipe dirigeante</h1>
            <p class="orga-hero-sub">
                Découvrez les femmes et les hommes qui font vivre notre club au quotidien.
            </p>
            <div class="orga-hero-divider"></div>
        </div>

        <?php
        // Filtrer les catégories qui ont effectivement des membres
        $categoriesAvecMembres = array_filter(
            $categories,
            fn($cat) => !empty($membresByCat[$cat['id']])
        );
        ?>

        <?php if (empty($categoriesAvecMembres)): ?>
            <div class="orga-empty">
                <i class="fa-regular fa-face-sad-tear"></i>
                <p>Aucun membre enregistré pour le moment.</p>
            </div>
        <?php else: ?>

            <?php foreach ($categoriesAvecMembres as $cat): ?>
                <?php
                $liste     = $membresByCat[$cat['id']];
                $nomCat    = $cat['nom_categorie'];
                $iconeClass = $icones[$nomCat] ?? 'fa-solid fa-users';
                ?>
                <div class="orga-category">

                    <!-- ── Titre de la catégorie ── -->
                    <div class="orga-category-header">
                        <div class="orga-category-icon">
                            <i class="<?= htmlspecialchars($iconeClass, ENT_QUOTES, 'UTF-8') ?>"></i>
                        </div>
                        <h2 class="orga-category-name">
                            <?= htmlspecialchars($nomCat, ENT_QUOTES, 'UTF-8') ?>
                        </h2>
                        <div class="orga-category-line"></div>
                        <span class="orga-category-count"><?= count($liste) ?> membre<?= count($liste) > 1 ? 's' : '' ?></span>
                    </div>

                    <!-- ── Grille des cartes ── -->
                    <div class="orga-grid">
                        <?php foreach ($liste as $membre): ?>
                            <article class="orga-card">

                                <!-- Photo ou initiales -->
                                <div class="orga-avatar-wrap">
                                    <?php if (!empty($membre['photo_url'])): ?>
                                        <img
                                            src="<?= htmlspecialchars($membre['photo_url'], ENT_QUOTES, 'UTF-8') ?>"
                                            alt="Photo de <?= htmlspecialchars($membre['prenom'] . ' ' . $membre['nom'], ENT_QUOTES, 'UTF-8') ?>"
                                            class="orga-avatar"
                                            loading="lazy"
                                        >
                                    <?php else: ?>
                                        <div class="orga-avatar-placeholder">
                                            <span><?= htmlspecialchars(
                                                mb_strtoupper(mb_substr($membre['prenom'], 0, 1)) .
                                                mb_strtoupper(mb_substr($membre['nom'],    0, 1)),
                                                ENT_QUOTES, 'UTF-8'
                                            ) ?></span>
                                        </div>
                                    <?php endif; ?>
                                </div>

                                <!-- Nom & rôle -->
                                <p class="orga-card-name">
                                    <?= htmlspecialchars($membre['prenom'] . ' ' . $membre['nom'], ENT_QUOTES, 'UTF-8') ?>
                                </p>
                                <p class="orga-card-role">
                                    <?= htmlspecialchars($membre['role'], ENT_QUOTES, 'UTF-8') ?>
                                </p>

                            </article>
                        <?php endforeach; ?>
                    </div>

                </div>
            <?php endforeach; ?>

        <?php endif; ?>

    </section>
</main>

<footer>
    <?php include 'footer.php'; ?>
</footer>
