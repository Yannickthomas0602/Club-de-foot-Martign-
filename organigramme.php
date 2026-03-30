<?php
// ─── Sécurité & connexion ────────────────────────────────────────────────────
require_once __DIR__ . '/fonctions.php';
if (session_status() !== PHP_SESSION_ACTIVE) { session_start(); }

$pdo = getDB();

// ─── Récupération des membres triés : catégorie → ordre ────────────────────
$stmt = $pdo->query(
    "SELECT id, nom, prenom, role, categorie, photo_url, ordre_affichage
       FROM club_membres
      ORDER BY categorie ASC, ordre_affichage ASC, nom ASC"
);
$membres = $stmt->fetchAll();

// Grouper par catégorie
$parCategorie = [];
foreach ($membres as $m) {
    $parCategorie[$m['categorie']][] = $m;
}

// Icônes par catégorie (fallback = users)
$icones = [
    'Bureau'               => 'fa-solid fa-briefcase',
    'Staff Technique'      => 'fa-solid fa-whistle',
    'Responsables Jeunes'  => 'fa-solid fa-child-reaching',
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

        <?php if (empty($parCategorie)): ?>
            <div class="orga-empty">
                <i class="fa-regular fa-face-sad-tear"></i>
                <p>Aucun membre enregistré pour le moment.</p>
            </div>
        <?php else: ?>

            <?php foreach ($parCategorie as $categorie => $liste): ?>
                <div class="orga-category">

                    <!-- ── Titre de la catégorie ── -->
                    <div class="orga-category-header">
                        <div class="orga-category-icon">
                            <i class="<?= htmlspecialchars(
                                $icones[$categorie] ?? 'fa-solid fa-users',
                                ENT_QUOTES, 'UTF-8'
                            ) ?>"></i>
                        </div>
                        <h2 class="orga-category-name">
                            <?= htmlspecialchars($categorie, ENT_QUOTES, 'UTF-8') ?>
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
