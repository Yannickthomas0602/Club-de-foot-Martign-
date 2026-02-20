
<?php
session_start();
require "fonctions.php";

$pdo = getDB();

// si pas connecté ou pas admin, redirige vers login
if (!isset($_SESSION['user_id']) || ($_SESSION['role_slug'] ?? null) !== 'admin') {
    header("Location: login.php");
    exit;
}

// Récupérer l'utilisateur à éditer
$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
if ($id <= 0) {
    header("Location: manage_users.php");
    exit;
}

// Récupérer les données de l'utilisateur
$user = getUserById($pdo, $id);
if (!$user) {
    header("Location: manage_users.php?error=user_not_found");
    exit;
}

// CSRF pour protéger le formulaire : il permet de vérifier que la requête POST vient bien de notre formulaire et 
// pas d'une source externe
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(16));
}
$csrf = $_SESSION['csrf_token'];

$isTeam = ($user['role_slug'] === 'user');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    if (!isset($_POST['csrf_token']) || !hash_equals($_SESSION['csrf_token'], $_POST['csrf_token'])) {
        header("Location: manage_users.php?error=csrf");
        exit;
    }

    $last_name = trim($_POST['last_name'] ?? '');
    $username  = trim($_POST['username']  ?? '');

// si c'est une équipe, on ne change pas le prénom et email
    if (!$isTeam) {
        $first_name = trim($_POST['first_name'] ?? '');
        $email      = trim($_POST['email']      ?? '');
    } else {
        $first_name = $user['first_name']; // conserver la valeur existante
        $email      = $user['email'];      // conserver la valeur existante
    }

// si c'est une équipe, on ne vérifie pas l'email et le prénom
    if ($last_name === '' || $username === '') {
        header("Location: edit_user.php?id={$id}&error=missing_fields");
        exit;
    }
    if (!$isTeam) {
        if ($email === '' || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
            header("Location: edit_user.php?id={$id}&error=invalid_email");
            exit;
        }
    }

// si l'email ou le username ont changé, vérifier qu'ils ne sont pas déjà pris par un autre utilisateur
    if ($email !== $user['email']) {
        $stmt = $pdo->prepare("SELECT 1 FROM users WHERE email = ? AND id <> ? LIMIT 1");
        $stmt->execute([$email, $id]);
        if ($stmt->fetchColumn()) {
            header("Location: edit_user.php?id={$id}&error=email_exists");
            exit;
        }
    }

    if ($username !== $user['username']) {
        $stmt = $pdo->prepare("SELECT 1 FROM users WHERE username = ? AND id <> ? LIMIT 1");
        $stmt->execute([$username, $id]);
        if ($stmt->fetchColumn()) {
            header("Location: edit_user.php?id={$id}&error=username_exists");
            exit;
        }
    }

    $fields = [
        'last_name' => $last_name,
        'username'  => $username,
    ];
    if (!$isTeam) {
        $fields['first_name'] = $first_name;
        $fields['email']      = $email;
    }

// si l'administrateur a coché la case pour changer le mot de passe, on vérifie les champs et on hash le nouveau mot de passe
    $changePwd = isset($_POST['change_password']) && $_POST['change_password'] === '1';
    if ($changePwd) {
        $password = trim($_POST['password'] ?? '');
        $confirm  = trim($_POST['password_confirm'] ?? '');

        if (!preg_match("/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[\W_]).{12,}$/", $password)) {
            header("Location: edit_user.php?id={$id}&error=weak_password");
            exit;
        }
        if ($password !== $confirm) {
            header("Location: edit_user.php?id={$id}&error=password_mismatch");
            exit;
        }

        $fields['password_hash'] = password_hash($password, PASSWORD_DEFAULT);
    }
// faire la mise à jour de la BDD et rediriger vers la page précédente 
    try {
        $ok = updateUser($pdo, $id, $fields);
        // Même si rien n'a changé, on retourne à la liste
        header("Location: manage_users.php?updated=1");
        exit;
    } catch (Throwable $e) {
        $msg = urlencode("update_failed");
        header("Location: edit_user.php?id={$id}&error={$msg}");
        exit;
    }
}
?> 

<?php include 'header.php'; ?>

<style>
    .hidden {
        display: none;
    }
</style>
<main>
  <h2>Modifier le compte
  </h2>

  <form method="POST" class="js-edit-user-form" autocomplete="off">
    <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrf, ENT_QUOTES) ?>">

    <?php if ($isTeam): ?>
      <!-- ÉQUIPE -->
      <div class="form-row">
        <label class="label" for="last_name">Nom de l'équipe</label>
        <input type="text" id="last_name" name="last_name" required
               value="<?= htmlspecialchars($user['last_name'] ?? '', ENT_QUOTES) ?>">
      </div>

      <div class="form-row">
        <label class="label" for="username">Identifiant</label>
        <input type="text" id="username" name="username" required
               value="<?= htmlspecialchars($user['username'] ?? '', ENT_QUOTES) ?>">
      </div>


    <?php else: ?>
      <div class="form-row">
        <label class="label" for="last_name">Nom</label>
        <input type="text" id="last_name" name="last_name" required
               value="<?= htmlspecialchars($user['last_name'] ?? '', ENT_QUOTES) ?>">
      </div>

      <div class="form-row">
        <label class="label" for="first_name">Prénom</label>
        <input type="text" id="first_name" name="first_name" required
               value="<?= htmlspecialchars($user['first_name'] ?? '', ENT_QUOTES) ?>">
      </div>

      <div class="form-row">
        <label class="label" for="email">Email</label>
        <input type="email" id="email" name="email" required
               value="<?= htmlspecialchars($user['email'] ?? '', ENT_QUOTES) ?>">
      </div>

      <div class="form-row">
        <label class="label" for="username">Identifiant</label>
        <input type="text" id="username" name="username" required
               value="<?= htmlspecialchars($user['username'] ?? '', ENT_QUOTES) ?>">
      </div>
    <?php endif; ?>

    <div class="form-row">
      <button type="button" class="btn btn-secondary" id="togglePwd">Modifier le MDP</button>
      <input type="hidden" name="change_password" id="change_password" value="0">
    </div>

    <div id="pwd_block" class="hidden">
      <div class="form-row">
        <label class="js-check-password" for="password">Nouveau mot de passe</label>
        <input type="password" id="password" name="password"
               placeholder="12+ caractères, MAJ, min, chiffre, spécial">
      </div>
      <div class="form-row">
        <label class="js-check-password" for="password_confirm">Confirmer le mot de passe</label>
        <input type="password" id="password_confirm" name="password_confirm">
      </div>
    </div>

    <div class="form-row">
      <button type="submit" class="btn btn-primary" onclick="return confirm('Êtes-vous sûr de vouloir modifier ce compte ?')">Enregistrer</button>
      <a class="btn btn-danger" href="manage_users.php">Annuler</a>
    </div>
  </form>
</main>

<?php include 'footer.php'; ?>
<script src="assets/js/edit_user.js"></script>
<script src="assets/js/check_password.js"></script>
</body>
</html>
