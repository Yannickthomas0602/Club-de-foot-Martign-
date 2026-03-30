<?php
// ─── Sécurité ──────────────────────────────────────────────────────────────
session_start();
require_once __DIR__ . '/fonctions.php';
checkSessionTimeout();

if (!isset($_SESSION['user_id']) || ($_SESSION['role_slug'] ?? '') !== 'admin') {
    header('Location: login.php');
    exit;
}

$pdo = getDB();

// ─── Token CSRF ────────────────────────────────────────────────────────────
if (empty($_SESSION['csrf_orga'])) {
    $_SESSION['csrf_orga'] = bin2hex(random_bytes(32));
}

// ─── Catégories disponibles ────────────────────────────────────────────────
const CATEGORIES = ['Bureau', 'Staff Technique', 'Responsables Jeunes'];

// ─── Dossier upload ────────────────────────────────────────────────────────
const UPLOAD_DIR = __DIR__ . '/uploads/trombinoscope/';
const UPLOAD_URL = 'uploads/trombinoscope/';
const MAX_SIZE   = 5 * 1024 * 1024; // 5 Mo
const ALLOWED_TYPES = ['image/jpeg', 'image/png', 'image/webp', 'image/gif'];

if (!is_dir(UPLOAD_DIR)) {
    mkdir(UPLOAD_DIR, 0755, true);
}

// ─── Flash messages ────────────────────────────────────────────────────────
$flash = '';
$flashType = '';
if (isset($_SESSION['orga_flash'])) {
    $flash     = $_SESSION['orga_flash']['msg']  ?? '';
    $flashType = $_SESSION['orga_flash']['type'] ?? 'success';
    unset($_SESSION['orga_flash']);
}

// ─── Helpers ───────────────────────────────────────────────────────────────
function setFlash(string $msg, string $type = 'success'): void {
    $_SESSION['orga_flash'] = ['msg' => $msg, 'type' => $type];
}

function uploadPhoto(array $file): ?string {
    if ($file['error'] !== UPLOAD_ERR_OK) { return null; }
    if ($file['size'] > MAX_SIZE) {
        throw new RuntimeException('Fichier trop volumineux (max 5 Mo).');
    }
    $mime = mime_content_type($file['tmp_name']);
    if (!in_array($mime, ALLOWED_TYPES, true)) {
        throw new RuntimeException('Format non autorisé. Utilisez JPEG, PNG, WebP ou GIF.');
    }
    $ext      = pathinfo($file['name'], PATHINFO_EXTENSION);
    $filename = uniqid('mbr_', true) . '.' . strtolower($ext);
    $dest     = UPLOAD_DIR . $filename;
    if (!move_uploaded_file($file['tmp_name'], $dest)) {
        throw new RuntimeException('Erreur lors de la sauvegarde de l\'image.');
    }
    return UPLOAD_URL . $filename;
}

// ─── Traitement POST ───────────────────────────────────────────────────────
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $token  = $_POST['csrf_orga'] ?? '';
    $action = $_POST['action']    ?? '';

    if (!hash_equals($_SESSION['csrf_orga'], $token)) {
        setFlash('Jeton de sécurité invalide.', 'error');
        header('Location: admin_organigramme.php');
        exit;
    }

    // ── Ajouter ────────────────────────────────────────────────────────────
    if ($action === 'add') {
        $nom      = trim($_POST['nom']      ?? '');
        $prenom   = trim($_POST['prenom']   ?? '');
        $role     = trim($_POST['role']     ?? '');
        $categorie= trim($_POST['categorie']?? '');
        $ordre    = (int)($_POST['ordre']   ?? 100);

        if ($nom === '' || $prenom === '' || $role === '' || $categorie === '') {
            setFlash('Veuillez remplir tous les champs obligatoires.', 'error');
        } elseif (!in_array($categorie, CATEGORIES, true)) {
            setFlash('Catégorie invalide.', 'error');
        } else {
            try {
                $photoUrl = null;
                if (!empty($_FILES['photo']['name'])) {
                    $photoUrl = uploadPhoto($_FILES['photo']);
                }

                $stmt = $pdo->prepare(
                    'INSERT INTO club_membres (nom, prenom, role, categorie, photo_url, ordre_affichage)
                     VALUES (:n, :p, :r, :c, :ph, :o)'
                );
                $stmt->execute([
                    ':n'  => $nom,
                    ':p'  => $prenom,
                    ':r'  => $role,
                    ':c'  => $categorie,
                    ':ph' => $photoUrl,
                    ':o'  => $ordre,
                ]);
                setFlash("Membre « {$prenom} {$nom} » ajouté avec succès.");
            } catch (RuntimeException $e) {
                setFlash($e->getMessage(), 'error');
            }
        }
        header('Location: admin_organigramme.php');
        exit;
    }

    // ── Modifier ───────────────────────────────────────────────────────────
    if ($action === 'edit') {
        $id       = (int)($_POST['id']        ?? 0);
        $nom      = trim($_POST['nom']        ?? '');
        $prenom   = trim($_POST['prenom']     ?? '');
        $role     = trim($_POST['role']       ?? '');
        $categorie= trim($_POST['categorie']  ?? '');
        $ordre    = (int)($_POST['ordre']     ?? 100);
        $oldPhoto  = trim($_POST['old_photo'] ?? '');

        if ($id <= 0 || $nom === '' || $prenom === '' || $role === '' || $categorie === '') {
            setFlash('Données invalides.', 'error');
        } elseif (!in_array($categorie, CATEGORIES, true)) {
            setFlash('Catégorie invalide.', 'error');
        } else {
            try {
                $photoUrl = $oldPhoto ?: null;

                if (!empty($_FILES['photo']['name'])) {
                    // Supprimer l'ancienne photo si elle existe
                    if ($oldPhoto && file_exists(__DIR__ . '/' . $oldPhoto)) {
                        @unlink(__DIR__ . '/' . $oldPhoto);
                    }
                    $photoUrl = uploadPhoto($_FILES['photo']);
                }

                $stmt = $pdo->prepare(
                    'UPDATE club_membres
                        SET nom=:n, prenom=:p, role=:r, categorie=:c, photo_url=:ph, ordre_affichage=:o
                      WHERE id=:id'
                );
                $stmt->execute([
                    ':n'  => $nom,
                    ':p'  => $prenom,
                    ':r'  => $role,
                    ':c'  => $categorie,
                    ':ph' => $photoUrl,
                    ':o'  => $ordre,
                    ':id' => $id,
                ]);
                setFlash("Membre « {$prenom} {$nom} » mis à jour.");
            } catch (RuntimeException $e) {
                setFlash($e->getMessage(), 'error');
            }
        }
        header('Location: admin_organigramme.php');
        exit;
    }

    // ── Supprimer ──────────────────────────────────────────────────────────
    if ($action === 'delete') {
        $id = (int)($_POST['id'] ?? 0);
        if ($id > 0) {
            $stmt = $pdo->prepare('SELECT photo_url FROM club_membres WHERE id=? LIMIT 1');
            $stmt->execute([$id]);
            $row = $stmt->fetch();

            $pdo->prepare('DELETE FROM club_membres WHERE id=?')->execute([$id]);

            // Supprimer la photo physique
            if ($row && !empty($row['photo_url'])) {
                $path = __DIR__ . '/' . $row['photo_url'];
                if (file_exists($path)) { @unlink($path); }
            }
            setFlash('Membre supprimé avec succès.');
        }
        header('Location: admin_organigramme.php');
        exit;
    }
}

// ─── Données affichage ─────────────────────────────────────────────────────
$membres = $pdo->query(
    'SELECT id, nom, prenom, role, categorie, photo_url, ordre_affichage
       FROM club_membres
      ORDER BY categorie ASC, ordre_affichage ASC, nom ASC'
)->fetchAll();

$page_title = 'Gérer l\'organigramme';
?>
<?php include 'header.php'; ?>
<link rel="stylesheet" href="assets/css/organigramme.css">

<main>
<div class="oadmin-wrap">

    <!-- ── En-tête ── -->
    <a href="admin.php" class="oadmin-back">
        <i class="fa-solid fa-arrow-left"></i> Retour au tableau de bord
    </a>
    <h1 class="oadmin-title"><i class="fa-solid fa-sitemap"></i> Gestion de l'organigramme</h1>
    <p class="oadmin-subtitle">Ajoutez, modifiez ou supprimez les membres affichés sur la page publique.</p>

    <!-- ── Flash ── -->
    <?php if ($flash !== ''): ?>
        <div class="flash-msg flash-<?= $flashType === 'error' ? 'error' : 'success' ?>">
            <i class="fa-solid fa-<?= $flashType === 'error' ? 'circle-xmark' : 'circle-check' ?>"></i>
            <?= htmlspecialchars($flash, ENT_QUOTES, 'UTF-8') ?>
        </div>
    <?php endif; ?>

    <!-- ── Layout 2 colonnes ── -->
    <div class="oadmin-layout">

        <!-- ════════ PANNEAU GAUCHE : Formulaire d'ajout ════════ -->
        <div class="oadmin-panel">
            <div class="oadmin-panel-header">
                <i class="fa-solid fa-user-plus"></i>
                <h2>Ajouter un membre</h2>
            </div>
            <div class="oadmin-panel-body">
                <form method="post" enctype="multipart/form-data" action="">
                    <input type="hidden" name="csrf_orga" value="<?= htmlspecialchars($_SESSION['csrf_orga'], ENT_QUOTES, 'UTF-8') ?>">
                    <input type="hidden" name="action"    value="add">

                    <div class="oadmin-form-group">
                        <label class="oadmin-label" for="add-prenom">Prénom <span style="color:var(--orga-red)">*</span></label>
                        <input id="add-prenom" type="text" name="prenom" class="oadmin-input"
                               placeholder="ex : Jean" required maxlength="100">
                    </div>

                    <div class="oadmin-form-group">
                        <label class="oadmin-label" for="add-nom">Nom <span style="color:var(--orga-red)">*</span></label>
                        <input id="add-nom" type="text" name="nom" class="oadmin-input"
                               placeholder="ex : Dupont" required maxlength="100">
                    </div>

                    <div class="oadmin-form-group">
                        <label class="oadmin-label" for="add-role">Rôle <span style="color:var(--orga-red)">*</span></label>
                        <input id="add-role" type="text" name="role" class="oadmin-input"
                               placeholder="ex : Président" required maxlength="150">
                    </div>

                    <div class="oadmin-form-group">
                        <label class="oadmin-label" for="add-categorie">Catégorie <span style="color:var(--orga-red)">*</span></label>
                        <select id="add-categorie" name="categorie" class="oadmin-select" required>
                            <option value="" disabled selected>Choisir une catégorie…</option>
                            <?php foreach (CATEGORIES as $cat): ?>
                                <option value="<?= htmlspecialchars($cat, ENT_QUOTES, 'UTF-8') ?>">
                                    <?= htmlspecialchars($cat, ENT_QUOTES, 'UTF-8') ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="oadmin-form-group">
                        <label class="oadmin-label" for="add-ordre">Ordre d'affichage</label>
                        <input id="add-ordre" type="number" name="ordre" class="oadmin-input"
                               value="100" min="1" max="999">
                    </div>

                    <div class="oadmin-form-group">
                        <label class="oadmin-label">Photo de profil</label>
                        <div class="oadmin-upload-zone" id="add-upload-zone">
                            <input type="file" name="photo" accept="image/jpeg,image/png,image/webp,image/gif"
                                   id="add-photo-input">
                            <i class="fa-solid fa-cloud-arrow-up"></i>
                            <p><strong>Cliquez</strong> ou glissez une image ici<br>
                               <span>JPEG, PNG, WebP · max 5 Mo</span></p>
                        </div>
                        <div class="oadmin-photo-preview" id="add-preview">
                            <img src="" alt="" id="add-preview-img">
                            <span id="add-preview-name"></span>
                        </div>
                    </div>

                    <button type="submit" class="btn-orga-primary">
                        <i class="fa-solid fa-plus"></i> Ajouter le membre
                    </button>
                </form>
            </div>
        </div>

        <!-- ════════ PANNEAU DROITE : Liste des membres ════════ -->
        <div class="oadmin-panel">
            <div class="oadmin-panel-header">
                <i class="fa-solid fa-list-ul"></i>
                <h2>Membres actuels (<?= count($membres) ?>)</h2>
            </div>
            <div class="oadmin-panel-body" style="padding:0;">
                <?php if (empty($membres)): ?>
                    <p style="padding:24px; color:var(--orga-muted); text-align:center;">
                        <i class="fa-regular fa-face-sad-tear"></i> Aucun membre enregistré.
                    </p>
                <?php else: ?>
                <div class="oadmin-table-wrap">
                    <table class="oadmin-table">
                        <thead>
                            <tr>
                                <th class="avatar-cell"></th>
                                <th>Nom</th>
                                <th>Rôle</th>
                                <th>Catégorie</th>
                                <th style="text-align:center;">Ordre</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                        <?php foreach ($membres as $m): ?>
                            <tr>
                                <!-- Avatar -->
                                <td class="avatar-cell">
                                    <?php if (!empty($m['photo_url'])): ?>
                                        <img src="<?= htmlspecialchars($m['photo_url'], ENT_QUOTES, 'UTF-8') ?>"
                                             alt="" class="oadmin-table-avatar">
                                    <?php else: ?>
                                        <div class="oadmin-table-placeholder">
                                            <?= htmlspecialchars(
                                                mb_strtoupper(mb_substr($m['prenom'], 0, 1)) .
                                                mb_strtoupper(mb_substr($m['nom'],    0, 1)),
                                                ENT_QUOTES, 'UTF-8'
                                            ) ?>
                                        </div>
                                    <?php endif; ?>
                                </td>
                                <!-- Nom -->
                                <td>
                                    <strong><?= htmlspecialchars($m['prenom'] . ' ' . $m['nom'], ENT_QUOTES, 'UTF-8') ?></strong>
                                </td>
                                <!-- Rôle -->
                                <td><?= htmlspecialchars($m['role'], ENT_QUOTES, 'UTF-8') ?></td>
                                <!-- Catégorie -->
                                <td>
                                    <span class="badge-category">
                                        <?= htmlspecialchars($m['categorie'], ENT_QUOTES, 'UTF-8') ?>
                                    </span>
                                </td>
                                <!-- Ordre -->
                                <td style="text-align:center;"><?= (int)$m['ordre_affichage'] ?></td>
                                <!-- Actions -->
                                <td class="actions-cell" style="display:flex;gap:6px;padding:10px 16px;align-items:center;">
                                    <button type="button"
                                            class="btn-orga-sm btn-edit"
                                            onclick="openEditModal(<?= htmlspecialchars(json_encode($m), ENT_QUOTES, 'UTF-8') ?>)">
                                        <i class="fa-solid fa-pen"></i> Modifier
                                    </button>
                                    <form method="post" action=""
                                          onsubmit="return confirm('Supprimer ce membre définitivement ?');"
                                          style="margin:0;">
                                        <input type="hidden" name="csrf_orga" value="<?= htmlspecialchars($_SESSION['csrf_orga'], ENT_QUOTES, 'UTF-8') ?>">
                                        <input type="hidden" name="action" value="delete">
                                        <input type="hidden" name="id"     value="<?= (int)$m['id'] ?>">
                                        <button type="submit" class="btn-orga-sm btn-delete">
                                            <i class="fa-solid fa-trash"></i>
                                        </button>
                                    </form>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
                <?php endif; ?>
            </div>
        </div>

    </div><!-- fin .oadmin-layout -->

</div><!-- fin .oadmin-wrap -->
</main>

<!-- ══════════════════════════════════════════════════════════════════
     MODALE ÉDITION
══════════════════════════════════════════════════════════════════ -->
<div class="orga-modal-overlay" id="edit-modal-overlay">
    <div class="orga-modal" role="dialog" aria-labelledby="edit-modal-title" aria-modal="true">
        <div class="orga-modal-header">
            <h3 id="edit-modal-title"><i class="fa-solid fa-pen-to-square"></i> Modifier le membre</h3>
            <button class="orga-modal-close" onclick="closeEditModal()" aria-label="Fermer">&times;</button>
        </div>
        <div class="orga-modal-body">
            <form method="post" enctype="multipart/form-data" action="" id="edit-form">
                <input type="hidden" name="csrf_orga"  value="<?= htmlspecialchars($_SESSION['csrf_orga'], ENT_QUOTES, 'UTF-8') ?>">
                <input type="hidden" name="action"     value="edit">
                <input type="hidden" name="id"         id="edit-id">
                <input type="hidden" name="old_photo"  id="edit-old-photo">

                <div class="oadmin-form-group">
                    <label class="oadmin-label" for="edit-prenom">Prénom *</label>
                    <input id="edit-prenom" type="text" name="prenom" class="oadmin-input" required maxlength="100">
                </div>
                <div class="oadmin-form-group">
                    <label class="oadmin-label" for="edit-nom">Nom *</label>
                    <input id="edit-nom" type="text" name="nom" class="oadmin-input" required maxlength="100">
                </div>
                <div class="oadmin-form-group">
                    <label class="oadmin-label" for="edit-role">Rôle *</label>
                    <input id="edit-role" type="text" name="role" class="oadmin-input" required maxlength="150">
                </div>
                <div class="oadmin-form-group">
                    <label class="oadmin-label" for="edit-categorie">Catégorie *</label>
                    <select id="edit-categorie" name="categorie" class="oadmin-select" required>
                        <?php foreach (CATEGORIES as $cat): ?>
                            <option value="<?= htmlspecialchars($cat, ENT_QUOTES, 'UTF-8') ?>">
                                <?= htmlspecialchars($cat, ENT_QUOTES, 'UTF-8') ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="oadmin-form-group">
                    <label class="oadmin-label" for="edit-ordre">Ordre d'affichage</label>
                    <input id="edit-ordre" type="number" name="ordre" class="oadmin-input" min="1" max="999">
                </div>
                <div class="oadmin-form-group">
                    <label class="oadmin-label">Nouvelle photo (laisser vide pour conserver)</label>
                    <div class="oadmin-upload-zone" id="edit-upload-zone">
                        <input type="file" name="photo" accept="image/jpeg,image/png,image/webp,image/gif"
                               id="edit-photo-input">
                        <i class="fa-solid fa-cloud-arrow-up"></i>
                        <p><strong>Cliquez</strong> ou glissez une image ici</p>
                    </div>
                    <div class="oadmin-photo-preview" id="edit-preview">
                        <img src="" alt="" id="edit-preview-img">
                        <span id="edit-preview-name"></span>
                    </div>
                    <!-- Aperçu de la photo actuelle -->
                    <div id="edit-current-photo-wrap" style="display:none; margin-top:10px;">
                        <p style="font-size:0.8rem; color:var(--orga-muted); margin:0 0 6px;">Photo actuelle :</p>
                        <img id="edit-current-photo-img" src="" alt="" style="width:56px;height:56px;border-radius:50%;object-fit:cover;border:2px solid var(--orga-border);">
                    </div>
                </div>
            </form>
        </div>
        <div class="orga-modal-footer">
            <button type="button" class="btn-orga-secondary" onclick="closeEditModal()">
                <i class="fa-solid fa-xmark"></i> Annuler
            </button>
            <button type="submit" form="edit-form" class="btn-orga-save">
                <i class="fa-solid fa-floppy-disk"></i> Enregistrer
            </button>
        </div>
    </div>
</div>

<!-- ── Scripts ── -->
<script>
// ── Preview upload dans formulaire ajout ────────────────────────────────────
(function() {
    const input   = document.getElementById('add-photo-input');
    const preview = document.getElementById('add-preview');
    const previewImg  = document.getElementById('add-preview-img');
    const previewName = document.getElementById('add-preview-name');

    if (!input) return;

    input.addEventListener('change', function() {
        const file = this.files[0];
        if (!file) { preview.classList.remove('has-file'); return; }
        previewName.textContent = file.name;
        const reader = new FileReader();
        reader.onload = e => { previewImg.src = e.target.result; preview.classList.add('has-file'); };
        reader.readAsDataURL(file);
    });
})();

// ── Preview upload dans modale édition ──────────────────────────────────────
(function() {
    const input   = document.getElementById('edit-photo-input');
    const preview = document.getElementById('edit-preview');
    const previewImg  = document.getElementById('edit-preview-img');
    const previewName = document.getElementById('edit-preview-name');

    if (!input) return;

    input.addEventListener('change', function() {
        const file = this.files[0];
        if (!file) { preview.classList.remove('has-file'); return; }
        previewName.textContent = file.name;
        const reader = new FileReader();
        reader.onload = e => { previewImg.src = e.target.result; preview.classList.add('has-file'); };
        reader.readAsDataURL(file);
    });
})();

// ── Modale édition ──────────────────────────────────────────────────────────
function openEditModal(data) {
    document.getElementById('edit-id').value         = data.id;
    document.getElementById('edit-prenom').value     = data.prenom;
    document.getElementById('edit-nom').value        = data.nom;
    document.getElementById('edit-role').value       = data.role;
    document.getElementById('edit-ordre').value      = data.ordre_affichage;
    document.getElementById('edit-old-photo').value  = data.photo_url || '';

    // Catégorie
    const sel = document.getElementById('edit-categorie');
    for (let i = 0; i < sel.options.length; i++) {
        if (sel.options[i].value === data.categorie) {
            sel.selectedIndex = i;
            break;
        }
    }

    // Photo actuelle
    const currentWrap = document.getElementById('edit-current-photo-wrap');
    const currentImg  = document.getElementById('edit-current-photo-img');
    if (data.photo_url) {
        currentImg.src = data.photo_url;
        currentWrap.style.display = 'block';
    } else {
        currentWrap.style.display = 'none';
    }

    // Reset preview nouvelle photo
    document.getElementById('edit-preview').classList.remove('has-file');
    document.getElementById('edit-photo-input').value = '';

    document.getElementById('edit-modal-overlay').classList.add('open');
    document.body.style.overflow = 'hidden';
}

function closeEditModal() {
    document.getElementById('edit-modal-overlay').classList.remove('open');
    document.body.style.overflow = '';
}

// Fermer en cliquant sur l'overlay
document.getElementById('edit-modal-overlay').addEventListener('click', function(e) {
    if (e.target === this) closeEditModal();
});

// Fermer avec Échap
document.addEventListener('keydown', function(e) {
    if (e.key === 'Escape') closeEditModal();
});
</script>

<footer>
    <?php include 'footer.php'; ?>
</footer>
