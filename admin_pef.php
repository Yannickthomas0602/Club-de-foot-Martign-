<?php
session_start();
require_once "fonctions.php";
checkSessionTimeout();

if (!isset($_SESSION['user_id']) || ($_SESSION['role_slug'] ?? '') !== 'admin') {
    header("Location: login.php");
    exit;
}

date_default_timezone_set('Europe/Paris');

$pdo = getDB();
$page_title = "Gestion PEF";

// ── Constantes ──────────────────────────────────────────────────────────────
const UPLOAD_DIR   = __DIR__ . '/uploads/pef_images/';
const UPLOAD_URL   = 'uploads/pef_images/';
const MAX_SIZE     = 5 * 1024 * 1024; // 5 Mo
const ALLOW_TYPES  = ['image/jpeg','image/png','image/webp','image/gif'];
const THEMES       = ['Santé','Engagement Citoyen','Environnement','Fair-Play','Règles du Jeu','Culture Foot'];

// ── CSRF ────────────────────────────────────────────────────────────────────
if (empty($_SESSION['csrf_pef'])) {
    $_SESSION['csrf_pef'] = bin2hex(random_bytes(32));
}

$success = $error = '';
$editArticle = null;

// ═══════════════════════════════════════════════════════════════════════════
//  TRAITEMENT POST
// ═══════════════════════════════════════════════════════════════════════════
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $token  = $_POST['csrf_pef'] ?? '';
    $action = $_POST['action']   ?? '';

    if (!hash_equals($_SESSION['csrf_pef'], $token)) {
        $error = "Jeton de sécurité invalide.";
    } else {

        // ── SUPPRIMER ──────────────────────────────────────────────────────
        if ($action === 'delete') {
            $id = (int)($_POST['del_id'] ?? 0);
            if ($id > 0) {
                $row = $pdo->prepare("SELECT image_couverture_url FROM pef_articles WHERE id=?");
                $row->execute([$id]);
                $row = $row->fetch();
                if ($row && $row['image_couverture_url']) {
                    $file = __DIR__ . '/' . ltrim($row['image_couverture_url'], '/');
                    if (is_file($file)) @unlink($file);
                }
                $pdo->prepare("DELETE FROM pef_articles WHERE id=?")->execute([$id]);
                $success = "Article supprimé.";
            }

        // ── CRÉER ──────────────────────────────────────────────────────────
        } elseif ($action === 'create') {
            $titre  = trim($_POST['titre']  ?? '');
            $theme  = $_POST['theme']       ?? '';
            $contenu= trim($_POST['contenu_html'] ?? '');
            $imgUrl = '';

            if ($titre === '' || !in_array($theme, THEMES, true) || $contenu === '') {
                $error = "Merci de remplir tous les champs obligatoires.";
            } else {
                // Upload image couverture
                if (!empty($_FILES['image_couverture']['name'])) {
                    $file    = $_FILES['image_couverture'];
                    $mime    = mime_content_type($file['tmp_name']);
                    if (!in_array($mime, ALLOW_TYPES, true)) {
                        $error = "Type d'image non autorisé (JPEG, PNG, WEBP, GIF uniquement).";
                    } elseif ($file['size'] > MAX_SIZE) {
                        $error = "L'image ne doit pas dépasser 5 Mo.";
                    } else {
                        $ext    = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
                        $fname  = uniqid('pef_', true) . '.' . $ext;
                        if (!is_dir(UPLOAD_DIR)) mkdir(UPLOAD_DIR, 0755, true);
                        if (move_uploaded_file($file['tmp_name'], UPLOAD_DIR . $fname)) {
                            $imgUrl = UPLOAD_URL . $fname;
                        } else {
                            $error = "Erreur lors de l'enregistrement de l'image.";
                        }
                    }
                }

                if ($error === '') {
                    $pdo->prepare(
                        "INSERT INTO pef_articles (titre, theme, image_couverture_url, contenu_html, date_publication)
                         VALUES (:titre, :theme, :img, :contenu, NOW())"
                    )->execute([
                        ':titre'   => $titre,
                        ':theme'   => $theme,
                        ':img'     => $imgUrl,
                        ':contenu' => $contenu,
                    ]);
                    $success = "Article publié avec succès !";
                }
            }

        // ── MODIFIER (save) ────────────────────────────────────────────────
        } elseif ($action === 'update') {
            $id     = (int)($_POST['edit_id'] ?? 0);
            $titre  = trim($_POST['titre']       ?? '');
            $theme  = $_POST['theme']            ?? '';
            $contenu= trim($_POST['contenu_html'] ?? '');
            $imgUrl = trim($_POST['img_actuelle'] ?? '');

            if ($id <= 0 || $titre === '' || !in_array($theme, THEMES, true) || $contenu === '') {
                $error = "Merci de remplir tous les champs obligatoires.";
            } else {
                // Nouvelle image ?
                if (!empty($_FILES['image_couverture']['name'])) {
                    $file = $_FILES['image_couverture'];
                    $mime = mime_content_type($file['tmp_name']);
                    if (!in_array($mime, ALLOW_TYPES, true)) {
                        $error = "Type d'image non autorisé.";
                    } elseif ($file['size'] > MAX_SIZE) {
                        $error = "L'image ne doit pas dépasser 5 Mo.";
                    } else {
                        $ext   = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
                        $fname = uniqid('pef_', true) . '.' . $ext;
                        if (!is_dir(UPLOAD_DIR)) mkdir(UPLOAD_DIR, 0755, true);
                        if (move_uploaded_file($file['tmp_name'], UPLOAD_DIR . $fname)) {
                            // Supprimer l'ancienne
                            if ($imgUrl) { $f = __DIR__ . '/' . ltrim($imgUrl, '/'); if (is_file($f)) @unlink($f); }
                            $imgUrl = UPLOAD_URL . $fname;
                        } else {
                            $error = "Erreur d'upload.";
                        }
                    }
                }

                if ($error === '') {
                    $pdo->prepare(
                        "UPDATE pef_articles SET titre=:titre, theme=:theme, image_couverture_url=:img, contenu_html=:contenu WHERE id=:id"
                    )->execute([':titre'=>$titre,':theme'=>$theme,':img'=>$imgUrl,':contenu'=>$contenu,':id'=>$id]);
                    $success = "Article mis à jour.";
                }
            }
        }
    }
}

// ── Charger article en mode édition ─────────────────────────────────────────
if (isset($_GET['action'], $_GET['id']) && $_GET['action'] === 'edit') {
    $stmt = $pdo->prepare("SELECT * FROM pef_articles WHERE id=? LIMIT 1");
    $stmt->execute([(int)$_GET['id']]);
    $editArticle = $stmt->fetch();
}

// ── Liste des articles ───────────────────────────────────────────────────────
$liste = $pdo->query("SELECT * FROM pef_articles ORDER BY date_publication DESC")->fetchAll();
?>
<?php include 'header.php'; ?>
<link rel="stylesheet" href="assets/css/pop_up_admin.css">
<!-- TinyMCE 7 CDN (no-api-key) -->
<script src="https://cdn.tiny.cloud/1/px6s5xjuah2l3ong05g30gghmow9bquclepnuhz2gawdro3l/tinymce/7/tinymce.min.js" referrerpolicy="origin"></script>
<script>
tinymce.init({
  selector: '#contenu_html',
  language: 'fr_FR',
  plugins: 'anchor autolink charmap codesample emoticons image link lists media searchreplace table visualblocks wordcount',
  toolbar: 'undo redo | blocks fontfamily fontsize | bold italic underline | link image media | align lineheight | numlist bullist indent outdent | removeformat',
  height: 420,
  images_upload_url: null,
  automatic_uploads: false,
  file_picker_types: 'image',
  setup: function(editor) {
    editor.on('change', function() { editor.save(); });
  }
});
</script>
<style>
select { padding:10px; border:1px solid #d0d0d0; border-radius:6px; background:#fff; font-size:15px; width:100%; max-width:600px; box-sizing:border-box; }
select:focus { outline:none; border-color:#F0322B; box-shadow:0 0 0 3px #ffb3b3; }
input[type="text"], input[type="date"], textarea { width:100%; max-width:600px; box-sizing:border-box; }
.img-preview { max-width:200px; max-height:120px; object-fit:cover; border-radius:8px; margin-top:8px; display:block; }
.tox-tinymce { max-width:100% !important; border-radius:6px !important; }
.badge-theme { display:inline-block; padding:2px 10px; border-radius:20px; font-size:.75rem; font-weight:700; text-transform:uppercase; }
.thumb-table { width:60px; height:44px; object-fit:cover; border-radius:6px; }
.edit-btn { background:#1E457B; }
.edit-btn:hover { background:#0f2f59; }
</style>

<main>
  <h2><?= $editArticle ? 'Modifier l\'article' : 'Gestion des articles PEF' ?></h2>

  <?php if ($success): ?><p class="alert alert--success"><?= htmlspecialchars($success) ?></p><?php endif; ?>
  <?php if ($error):   ?><p class="alert alert--error"><?= htmlspecialchars($error) ?></p><?php endif; ?>

  <!-- ── FORMULAIRE CRÉATION / ÉDITION ────────────────────────────────── -->
  <section class="bloc bloc--form">
    <h3><?= $editArticle ? 'Modifier' : 'Nouvel article' ?></h3>

    <form method="post" action="admin_pef.php" enctype="multipart/form-data" class="form-popup" style="display:grid">
      <input type="hidden" name="csrf_pef" value="<?= htmlspecialchars($_SESSION['csrf_pef']) ?>">
      <input type="hidden" name="action"   value="<?= $editArticle ? 'update' : 'create' ?>">
      <?php if ($editArticle): ?>
      <input type="hidden" name="edit_id"     value="<?= (int)$editArticle['id'] ?>">
      <input type="hidden" name="img_actuelle" value="<?= htmlspecialchars($editArticle['image_couverture_url']) ?>">
      <?php endif; ?>

      <!-- Titre -->
      <div class="form-row">
        <label for="titre">Titre *</label>
        <input type="text" id="titre" name="titre" maxlength="255" required
               value="<?= htmlspecialchars($editArticle['titre'] ?? '') ?>">
      </div>

      <!-- Thème -->
      <div class="form-row form-row--inline" style="display:flex;gap:20px;flex-wrap:wrap">
        <div class="form-row" style="flex:1;min-width:200px">
          <label for="theme">Thème PEF *</label>
          <select id="theme" name="theme" required>
            <option value="">-- Choisir --</option>
            <?php foreach (THEMES as $t): ?>
            <option value="<?= $t ?>" <?= (($editArticle['theme'] ?? '') === $t) ? 'selected' : '' ?>><?= $t ?></option>
            <?php endforeach; ?>
          </select>
        </div>
      </div>

      <!-- Image couverture -->
      <div class="form-row">
        <label for="image_couverture">Image de couverture <?= $editArticle ? '(laisser vide pour conserver)' : '*' ?></label>
        <input type="file" id="image_couverture" name="image_couverture" accept="image/*" <?= $editArticle ? '' : 'required' ?>>
        <small style="color:#888">JPEG, PNG, WEBP, GIF – max 5 Mo</small>
        <?php if (!empty($editArticle['image_couverture_url'])): ?>
        <img src="<?= htmlspecialchars($editArticle['image_couverture_url']) ?>" alt="Image actuelle" class="img-preview">
        <?php endif; ?>
      </div>

      <!-- Contenu WYSIWYG -->
      <div class="form-row" style="max-width:100%">
        <label for="contenu_html">Contenu de l'article *</label>
        <textarea id="contenu_html" name="contenu_html"><?= htmlspecialchars($editArticle['contenu_html'] ?? '') ?></textarea>
      </div>

      <div class="form-actions" style="display:flex;gap:12px;align-items:center">
        <button type="submit" class="btn"><?= $editArticle ? 'Enregistrer les modifications' : 'Publier l\'article' ?></button>
        <?php if ($editArticle): ?>
        <a href="admin_pef.php" class="btn" style="background:#666">Annuler</a>
        <?php endif; ?>
      </div>
    </form>
  </section>

  <!-- ── TABLEAU DES ARTICLES ──────────────────────────────────────────── -->
  <section class="bloc">
    <h3>Articles publiés (<?= count($liste) ?>)</h3>

    <?php if (empty($liste)): ?>
      <p style="color:#888;font-style:italic">Aucun article pour le moment.</p>
    <?php else: ?>
    <div class="table-wrap">
      <table>
        <thead>
          <tr>
            <th>Image</th>
            <th>Titre</th>
            <th>Thème</th>
            <th>Date</th>
            <th style="min-width:160px">Actions</th>
          </tr>
        </thead>
        <tbody>
        <?php foreach ($liste as $art):
          $img  = !empty($art['image_couverture_url']) ? htmlspecialchars($art['image_couverture_url']) : '';
          $date = (new DateTime($art['date_publication']))->format('d/m/Y');
        ?>
          <tr>
            <td>
              <?php if ($img): ?>
              <img src="<?= $img ?>" alt="" class="thumb-table">
              <?php else: ?>
              <span style="color:#ccc;font-size:.8rem">—</span>
              <?php endif; ?>
            </td>
            <td><strong><?= htmlspecialchars($art['titre']) ?></strong></td>
            <td><span class="badge-theme" style="background:#fee2e2;color:#991b1b"><?= htmlspecialchars($art['theme']) ?></span></td>
            <td style="white-space:nowrap"><?= $date ?></td>
            <td class="actions-cell">
              <!-- Modifier -->
              <a href="admin_pef.php?action=edit&id=<?= (int)$art['id'] ?>" class="btn edit-btn" title="Modifier">
                <i class="fa-solid fa-pen"></i> Modifier
              </a>
              <!-- Supprimer -->
              <form method="post" action="admin_pef.php" class="form-inline"
                    onsubmit="return confirm('Supprimer cet article définitivement ?')">
                <input type="hidden" name="csrf_pef" value="<?= htmlspecialchars($_SESSION['csrf_pef']) ?>">
                <input type="hidden" name="action"   value="delete">
                <input type="hidden" name="del_id"   value="<?= (int)$art['id'] ?>">
                <button type="submit" class="btn btn-danger">
                  <i class="fa-solid fa-trash-can"></i> Supprimer
                </button>
              </form>
            </td>
          </tr>
        <?php endforeach; ?>
        </tbody>
      </table>
    </div>
    <?php endif; ?>
  </section>
</main>

<?php include 'footer.php'; ?>
