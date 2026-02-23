
<?php
session_start();
require_once __DIR__ . "/fonctions.php";

$pdo = getDB();

// si déjà connecté, redirige vers la page compte associé au role 

if (isset($_SESSION['user_id'])) {
    switch ($_SESSION['role_slug']) {
        case 'admin':
            header("Location: admin.php");
            exit;
        case 'coach':
            header("Location: coach.php");
            exit;
        default:
            header("Location: user.php");
            exit;
    }
}



if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $password = trim($_POST['password'] ?? '');

    if ($username === '' || $password === '') {
        die("Veuillez remplir tous les champs.");
    }

    // Récupérer l'utilisateur via le username (joint avec roles pour avoir role_slug)
    $user = getUserByUsername($pdo, $username);

    if (!$user) {
        die("Identifiant ou mot de passe incorrect.");
    }

    // Vérifier le mot de passe (colonne password_hash dans la DB)
    if (!isset($user['password_hash']) || !password_verify($password, $user['password_hash'])) {
        die("Identifiant ou mot de passe incorrect.");
    }

    // Optionnel : vérifier si le compte est actif
    if (isset($user['is_active']) && (int)$user['is_active'] !== 1) {
        die("Compte inactif. Contacte un administrateur.");
    }

    // Poser les variables de session
    $_SESSION['user_id']   = (int)$user['id'];
    $_SESSION['username']  = $user['username'];
    $_SESSION['role_slug'] = $user['role_slug'] ?? null;

    // Redirection vers le bon espace (admin, coach, user)
    $role = $user['role_slug'] ?? 'user';
    if ($role === 'admin') {
        header("Location: admin.php");
    } elseif ($role === 'coach') {
        header("Location: coach.php");
    } else {
        header("Location: user.php");
    }
    exit;
}
?>

    <?php include 'header.php'; ?>
    <main class="page_login">
        <div class="form_login">
            <section class="card_login">
                <h3>Se connecter</h3>
                <form method="POST">
                    <div class="field">
                        <label for="text">Identifiant</label>
                        <input id="text" type="text" name="username" required>
                    </div>
                    <div class="field">
                        <label for="password">Mot de passe</label>
                        <input id="password" type="password" name="password" required>
                    </div>
                    <div class="actions">
                        <button class="btn" type="submit">Se connecter</button>
                        <a class="btn secondary" href="register.php">Créer un compte</a>
                    </div>
                </form>
            </section>
        </div>
    </main>

<?php
include "footer.php";
?>
</body>
</html>
