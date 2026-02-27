<?php
session_start();
require_once "fonctions.php";

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

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? 'create';
    $token = $_POST['csrf_popup'] ?? '';

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
                    ':id' => $updateId,
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
        $titre = trim($_POST['titre'] ?? '');
        $contenu = trim($_POST['contenu'] ?? '');
        $dateFinInput = $_POST['date_fin'] ?? '';
        $actif = isset($_POST['actif']) ? 1 : 0;
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
                    ':titre' => $titre,
                    ':contenu' => $contenu,
                    ':date_fin' => $dateFin->format('Y-m-d H:i:s'),
                    ':actif' => $actif,
                ]);

                $success = "Annonce enregistrée avec succès.";
            }
        }
    }
}

$annonces = $pdo->query(
    "SELECT id, titre, date_fin, actif
     FROM annonces_popup
     ORDER BY id DESC
     LIMIT 10"
)->fetchAll();
?>

<?php include 'header.php'; ?>
<link href="https://cdn.quilljs.com/1.3.6/quill.snow.css" rel="stylesheet">
<main style="max-width:900px;margin:30px auto;padding:0 16px;">
    <h1>Créer une annonce pop-up</h1>

    <?php if ($success !== ''): ?>
        <p style="color:#1f7a1f;"><?= htmlspecialchars($success, ENT_QUOTES, 'UTF-8') ?></p>
    <?php endif; ?>

    <?php if ($error !== ''): ?>
        <p style="color:#b30000;"><?= htmlspecialchars($error, ENT_QUOTES, 'UTF-8') ?></p>
    <?php endif; ?>

    <form method="post" action="">
        <input type="hidden" name="csrf_popup" value="<?= htmlspecialchars($_SESSION['csrf_popup'], ENT_QUOTES, 'UTF-8') ?>">
        <input type="hidden" name="action" value="create">

        <label for="titre">Titre</label><br>
        <input type="text" id="titre" name="titre" maxlength="255" required value="<?= htmlspecialchars($titre, ENT_QUOTES, 'UTF-8') ?>" style="width:100%;max-width:600px;padding:8px;margin:8px 0 16px;"><br>

        <label for="editor">Contenu</label><br>
        <div id="editor" style="height:320px;"><?= $contenu !== '' ? $contenu : '<p></p>' ?></div>
        <textarea id="contenu" name="contenu" style="display:none;"></textarea><br>

        <label for="date_fin">Date de fin d'affichage</label><br>
        <input type="datetime-local" id="date_fin" name="date_fin" required value="<?= htmlspecialchars($dateFinInput, ENT_QUOTES, 'UTF-8') ?>" style="padding:8px;margin:8px 0 16px;"><br>

        <label>
            <input type="checkbox" name="actif" <?= $actif ? 'checked' : '' ?>>
            Annonce active
        </label><br><br>

        <button type="submit">Enregistrer l'annonce</button>
    </form>

    <hr style="margin:30px 0;">

    <h2>Dernières annonces</h2>
    <table border="1" cellpadding="8" cellspacing="0" style="width:100%;max-width:800px;border-collapse:collapse;">
        <tr>
            <th>ID</th>
            <th>Titre</th>
            <th>Date de fin</th>
            <th>Actif</th>
            <th>Actions</th>
        </tr>
        <?php foreach ($annonces as $annonce): ?>
            <tr>
                <td><?= (int)$annonce['id'] ?></td>
                <td><?= htmlspecialchars($annonce['titre'], ENT_QUOTES, 'UTF-8') ?></td>
                <td><?= htmlspecialchars($annonce['date_fin'], ENT_QUOTES, 'UTF-8') ?></td>
                <td><?= ((int)$annonce['actif'] === 1) ? 'Oui' : 'Non' ?></td>
                <td>
                    <form method="post" action="" style="display:inline-block;margin-right:8px;">
                        <input type="hidden" name="csrf_popup" value="<?= htmlspecialchars($_SESSION['csrf_popup'], ENT_QUOTES, 'UTF-8') ?>">
                        <input type="hidden" name="action" value="update_date">
                        <input type="hidden" name="update_id" value="<?= (int)$annonce['id'] ?>">
                        <input
                            type="datetime-local"
                            name="new_date_fin"
                            value="<?= htmlspecialchars((new DateTime($annonce['date_fin']))->format('Y-m-d\\TH:i'), ENT_QUOTES, 'UTF-8') ?>"
                            required
                        >
                        <button type="submit">Mettre à jour</button>
                    </form>

                    <form method="post" action="" onsubmit="return confirm('Supprimer cette annonce ?');" style="display:inline;">
                        <input type="hidden" name="csrf_popup" value="<?= htmlspecialchars($_SESSION['csrf_popup'], ENT_QUOTES, 'UTF-8') ?>">
                        <input type="hidden" name="action" value="delete">
                        <input type="hidden" name="delete_id" value="<?= (int)$annonce['id'] ?>">
                        <button type="submit">Supprimer</button>
                    </form>
                </td>
            </tr>
        <?php endforeach; ?>
    </table>
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

const form = document.querySelector('form');
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
