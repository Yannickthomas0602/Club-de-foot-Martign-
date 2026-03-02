<?php
session_start();
require_once "fonctions.php";
require_once "sponsors_store.php";

checkSessionTimeout();

if (!isset($_SESSION['user_id']) || ($_SESSION['role_slug'] ?? '') !== 'admin') {
    header("Location: login.php");
    exit;
}

$page_title = "Gestion des sponsors";

if (empty($_SESSION['csrf_sponsors'])) {
    $_SESSION['csrf_sponsors'] = bin2hex(random_bytes(32));
}

$message = '';
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $token = $_POST['csrf_sponsors'] ?? '';
    $action = $_POST['action'] ?? '';

    if (!hash_equals($_SESSION['csrf_sponsors'], $token)) {
        $error = 'Jeton de sécurité invalide.';
    } elseif ($action === 'add') {
        $url = $_POST['sponsor_url'] ?? '';
        $file = $_FILES['sponsor_image'] ?? [];
        $result = addSponsor($file, (string)$url);
        if ($result['ok']) {
            $message = $result['message'];
        } else {
            $error = $result['message'];
        }
    } elseif ($action === 'delete') {
        $id = (string)($_POST['sponsor_id'] ?? '');
        $result = deleteSponsor($id);
        if ($result['ok']) {
            $message = $result['message'];
        } else {
            $error = $result['message'];
        }
    } elseif ($action === 'migrate_webp') {
        $result = migrateSponsorsToWebp();
        $details = ' Converties: ' . (int)($result['converted'] ?? 0)
            . ' | Déjà en webp: ' . (int)($result['skipped'] ?? 0)
            . ' | Échecs: ' . (int)($result['failed'] ?? 0);

        if ($result['ok']) {
            $message = $result['message'] . $details;
        } else {
            $error = $result['message'] . $details;
        }
    }
}

$sponsors = loadSponsors();
?>

<?php include 'header.php'; ?>
<main style="max-width:1000px;margin:28px auto;padding:0 16px;">
    <h1>Gestion des sponsors (carrousel)</h1>

    <?php if ($message !== ''): ?>
        <p style="color:#147d14;"><?= htmlspecialchars($message, ENT_QUOTES, 'UTF-8') ?></p>
    <?php endif; ?>

    <?php if ($error !== ''): ?>
        <p style="color:#b00020;"><?= htmlspecialchars($error, ENT_QUOTES, 'UTF-8') ?></p>
    <?php endif; ?>

    <section style="margin:20px 0;padding:14px;border:1px solid #ddd;border-radius:8px;">
        <h2 style="margin-top:0;">Ajouter un sponsor</h2>
        <form method="post" enctype="multipart/form-data" action="" style="display:flex;flex-wrap:wrap;gap:12px;align-items:end;">
            <input type="hidden" name="csrf_sponsors" value="<?= htmlspecialchars($_SESSION['csrf_sponsors'], ENT_QUOTES, 'UTF-8') ?>">
            <input type="hidden" name="action" value="add">

            <div>
                <label for="sponsor_image">Image du sponsor</label><br>
                <input id="sponsor_image" type="file" name="sponsor_image" accept="image/png,image/jpeg,image/webp,image/gif" required>
            </div>

            <div style="min-width:280px;flex:1;">
                <label for="sponsor_url">URL (optionnel)</label><br>
                <input id="sponsor_url" type="text" name="sponsor_url" placeholder="https://example.com" style="width:100%;padding:8px;">
            </div>

            <button type="submit" style="padding:8px 14px;">Ajouter</button>
        </form>
    </section>

    <section style="margin-top:18px;">
        <form method="post" action="" style="margin:0 0 12px;">
            <input type="hidden" name="csrf_sponsors" value="<?= htmlspecialchars($_SESSION['csrf_sponsors'], ENT_QUOTES, 'UTF-8') ?>">
            <input type="hidden" name="action" value="migrate_webp">
            <button type="submit" onclick="return confirm('Convertir toutes les images sponsors existantes en WebP ?');">
                Convertir les sponsors existants en WebP
            </button>
        </form>

        <h2>Sponsors existants</h2>
        <table border="1" cellpadding="8" cellspacing="0" style="width:100%;border-collapse:collapse;">
            <tr>
                <th>Aperçu</th>
                <th>URL</th>
                <th>Actions</th>
            </tr>
            <?php if (empty($sponsors)): ?>
                <tr>
                    <td colspan="3">Aucun sponsor pour le moment.</td>
                </tr>
            <?php else: ?>
                <?php foreach ($sponsors as $sponsor): ?>
                    <tr>
                        <td style="width:180px;">
                            <img
                                src="<?= htmlspecialchars($sponsor['image'], ENT_QUOTES, 'UTF-8') ?>"
                                alt="Sponsor"
                                style="max-width:160px;max-height:70px;object-fit:contain;display:block;"
                            >
                        </td>
                        <td>
                            <?php if (($sponsor['url'] ?? '') !== ''): ?>
                                <a href="<?= htmlspecialchars($sponsor['url'], ENT_QUOTES, 'UTF-8') ?>" target="_blank" rel="noopener noreferrer">
                                    <?= htmlspecialchars($sponsor['url'], ENT_QUOTES, 'UTF-8') ?>
                                </a>
                            <?php else: ?>
                                —
                            <?php endif; ?>
                        </td>
                        <td style="width:130px;">
                            <form method="post" action="" onsubmit="return confirm('Supprimer ce sponsor ?');">
                                <input type="hidden" name="csrf_sponsors" value="<?= htmlspecialchars($_SESSION['csrf_sponsors'], ENT_QUOTES, 'UTF-8') ?>">
                                <input type="hidden" name="action" value="delete">
                                <input type="hidden" name="sponsor_id" value="<?= htmlspecialchars($sponsor['id'], ENT_QUOTES, 'UTF-8') ?>">
                                <button type="submit">Supprimer</button>
                            </form>
                        </td>
                    </tr>
                <?php endforeach; ?>
            <?php endif; ?>
        </table>
    </section>
</main>

<?php include 'footer.php'; ?>
