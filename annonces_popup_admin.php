<?php
session_start();
require_once "fonctions.php";

/**
 * Optionnel : aligne le fuseau horaire PHP avec ton usage.
 * Tu peux adapter si besoin (ex: UTC).
 */
date_default_timezone_set('Europe/Paris');

checkSessionTimeout();

if (!isset($_SESSION['user_id']) || ($_SESSION['role_slug'] ?? '') !== 'admin') {
    header("Location: login.php");
    exit;
}

$pdo = getDB();
$page_title = "Annonces pop-up";

if (empty($_SESSION['csrf_popup'])) {
    $_SESSION['csrf_popup'] = bin2hex(random_bytes(32));
}

$success = "";
$error = "";
$titre = "";
$contenu = "";
$dateFinInput = "";
$actif = 1;

/* ==========================
   TRAITEMENT DES FORMULAIRES
   ========================== */
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? 'create';
    $token  = $_POST['csrf_popup'] ?? '';

    if (!hash_equals($_SESSION['csrf_popup'], $token)) {
        $error = "Jeton de sécurité invalide.";
    } elseif ($action === 'update_date') {
        $updateId = (int)($_POST['update_id'] ?? 0);
        $newDateFinInput = trim($_POST['new_date_fin'] ?? '');

        if ($updateId <= 0 || $newDateFinInput === '') {
            $error = "Informations invalides pour la mise à jour de la durée.";
        } else {
            $newDateFin = DateTime::createFromFormat('Y-m-d\\TH:i', $newDateFinInput);

            if (!$newDateFin) {
                $error = "Nouvelle date de fin invalide.";
            } else {
                $stmtUpdate = $pdo->prepare(
                    "UPDATE annonces_popup
                     SET date_fin = :date_fin
                     WHERE id = :id"
                );
                $stmtUpdate->execute([
                    ':date_fin' => $newDateFin->format('Y-m-d H:i:s'),
                    ':id'       => $updateId,
                ]);
                $success = "Durée d'affichage mise à jour avec succès.";
            }
        }
    } elseif ($action === 'delete') {
        $deleteId = (int)($_POST['delete_id'] ?? 0);

        if ($deleteId <= 0) {
            $error = "Annonce invalide à supprimer.";
        } else {
            $stmtDelete = $pdo->prepare("DELETE FROM annonces_popup WHERE id = :id");
            $stmtDelete->execute([':id' => $deleteId]);
            $success = "Annonce supprimée avec succès.";
        }
    } else {
        // Création
        $titre        = trim($_POST['titre'] ?? '');
        $contenu      = trim($_POST['contenu'] ?? '');
        $dateFinInput = $_POST['date_fin'] ?? '';
        $actif        = isset($_POST['actif']) ? 1 : 0;

        // On interdit un contenu vide côté serveur aussi
        $contenuTexte = trim(strip_tags($contenu));

        if ($titre === '' || $contenuTexte === '' || $dateFinInput === '') {
            $error = "Merci de remplir tous les champs obligatoires.";
        } else {
            $dateFin = DateTime::createFromFormat('Y-m-d\\TH:i', $dateFinInput);

            if (!$dateFin) {
                $error = "Date de fin invalide.";
            } else {
                $sql = "INSERT INTO annonces_popup (titre, contenu, date_fin, actif)
                        VALUES (:titre, :contenu, :date_fin, :actif)";

                $stmt = $pdo->prepare($sql);
                $stmt->execute([
                    ':titre'    => $titre,
                    ':contenu'  => $contenu,
                    ':date_fin' => $dateFin->format('Y-m-d H:i:s'),
                    ':actif'    => $actif,
                ]);

                $success = "Annonce enregistrée avec succès.";
                // Reset des champs du formulaire après succès
                $titre = "";
                $contenu = "";
                $dateFinInput = "";
                $actif = 1;
            }
        }
    }
}

/* ===========================================================
   MAINTENANCE "LAZY" (au chargement de la page d'admin)
   1) Désactive les annonces dont la date de fin est passée
   2) Supprime les annonces 2 jours après la date de fin
   =========================================================== */
try {
    // 1) Désactiver automatiquement les annonces échues
    $pdo->prepare("
        UPDATE annonces_popup
        SET actif = 0
        WHERE actif = 1
          AND date_fin <= NOW()
    ")->execute();

    // 2) Supprimer les annonces 2 jours après la fin
    $pdo->prepare("
        DELETE FROM annonces_popup
        WHERE date_fin <= DATE_SUB(NOW(), INTERVAL 2 DAY)
    ")->execute();
} catch (Throwable $e) {
    // On n'arrête pas l'affichage pour autant, mais on log si possible
    // error_log($e->getMessage());
}

/* ===========================================================
   LECTURE DES ANNONCES POUR L'AFFICHAGE
   On "blinde" l'affichage via actif_effectif :
   - actif_effectif = 1 seulement si actif=1 ET date_fin > NOW()
   Cela garantit que le tableau affichera "Non" si c'est expiré.
   =========================================================== */
$annonces = $pdo->query(
    "SELECT
        id,
        titre,
        date_fin,
        actif,
        (actif = 1 AND date_fin > NOW()) AS actif_effectif
     FROM annonces_popup
     ORDER BY id DESC
     LIMIT 10"
)->fetchAll();
?>

<?php include 'header.php'; ?>
<link href="https://cdn.quilljs.com/1.3.6/quill.snow.css" rel="stylesheet">
<link rel="stylesheet" href="assets/css/pop_up_admin.css">

<main>
  <h2>Créer une annonce pop-up</h2>

  <?php if ($success !== ''): ?>
    <p class="alert alert--success"><?= htmlspecialchars($success, ENT_QUOTES, 'UTF-8') ?></p>
  <?php endif; ?>

  <?php if ($error !== ''): ?>
    <p class="alert alert--error"><?= htmlspecialchars($error, ENT_QUOTES, 'UTF-8') ?></p>
  <?php endif; ?>

  <section class="bloc bloc--form">
    <form method="post" action="" class="form-popup">
      <input type="hidden" name="csrf_popup" value="<?= htmlspecialchars($_SESSION['csrf_popup'], ENT_QUOTES, 'UTF-8') ?>">
      <input type="hidden" name="action" value="create">

      <div class="form-row">
        <label for="titre">Titre</label>
        <input
          type="text"
          id="titre"
          name="titre"
          maxlength="255"
          required
          value="<?= htmlspecialchars($titre, ENT_QUOTES, 'UTF-8') ?>">
      </div>

      <div class="form-row">
        <label for="editor">Contenu</label>
        <div id="editor" class="quill-editor"><?= $contenu !== '' ? $contenu : '<p></p>' ?></div>
        <textarea id="contenu" name="contenu" class="u-hidden"></textarea>
      </div>

      <div class="form-row form-row--inline">
        <div>
          <label for="date_fin">Date de fin d'affichage</label>
          <input
            type="datetime-local"
            id="date_fin"
            name="date_fin"
            required
            value="<?= htmlspecialchars($dateFinInput, ENT_QUOTES, 'UTF-8') ?>">
        </div>

        <label class="checkbox">
          <input type="checkbox" name="actif" <?= $actif ? 'checked' : '' ?>>
          <span>Annonce active</span>
        </label>
      </div>

      <div class="form-actions">
        <button type="submit" class="btn">Enregistrer l'annonce</button>
      </div>
    </form>
  </section>

  <section class="bloc">
    <h3>Dernières annonces</h3>

    <div class="table-wrap">
      <table border="2">
        <thead>
          <tr>
            <th>ID</th>
            <th>Titre</th>
            <th>Date de fin</th>
            <th>Actif</th>
            <th>Actions</th>
          </tr>
        </thead>
        <tbody>
        <?php foreach ($annonces as $annonce): ?>
          <tr>
            <td><?= (int)$annonce['id'] ?></td>
            <td><?= htmlspecialchars($annonce['titre'], ENT_QUOTES, 'UTF-8') ?></td>
            <td><?= htmlspecialchars($annonce['date_fin'], ENT_QUOTES, 'UTF-8') ?></td>
            <!-- Affichage blindé via actif_effectif -->
            <td><?= ((int)$annonce['actif_effectif'] === 1) ? 'Oui' : 'Non' ?></td>
            <td class="actions-cell">
              <!-- Mettre à jour la date de fin -->
              <form method="post" action="" class="form-inline">
                <input type="hidden" name="csrf_popup" value="<?= htmlspecialchars($_SESSION['csrf_popup'], ENT_QUOTES, 'UTF-8') ?>">
                <input type="hidden" name="action" value="update_date">
                <input type="hidden" name="update_id" value="<?= (int)$annonce['id'] ?>">
                <input
                  type="datetime-local"
                  name="new_date_fin"
                  value="<?= htmlspecialchars((new DateTime($annonce['date_fin']))->format('Y-m-d\TH:i'), ENT_QUOTES, 'UTF-8') ?>"
                  required>
                <button type="submit" class="btn">Mettre à jour</button>
              </form>

              <!-- Supprimer l'annonce -->
              <form method="post" action="" onsubmit="return confirm('Supprimer cette annonce ?');" class="form-inline">
                <input type="hidden" name="csrf_popup" value="<?= htmlspecialchars($_SESSION['csrf_popup'], ENT_QUOTES, 'UTF-8') ?>">
                <input type="hidden" name="action" value="delete">
                <input type="hidden" name="delete_id" value="<?= (int)$annonce['id'] ?>">
                <button type="submit" class="btn btn-danger">Supprimer</button>
              </form>
            </td>
          </tr>
        <?php endforeach; ?>
        </tbody>
      </table>
    </div>
  </section>
</main>

<script src="https://cdn.quilljs.com/1.3.6/quill.min.js"></script>
<script>
const quill = new Quill('#editor', {
  theme: 'snow',
  modules: {
    toolbar: [
      [{ header: [1, 2, false] }],
      ['bold', 'italic', 'underline'],
      [{ list: 'ordered' }, { list: 'bullet' }],
      ['link'],
      ['clean']
    ]
  }
});

const form = document.querySelector('.form-popup');
const contenuField = document.getElementById('contenu');

form.addEventListener('submit', function (event) {
  const texte = quill.getText().trim();
  if (!texte) {
    event.preventDefault();
    alert('Merci de saisir un contenu pour l\'annonce.');
    return;
  }
  contenuField.value = quill.root.innerHTML;
});
</script>

<?php include 'footer.php'; ?>