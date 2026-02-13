
<?php
session_start();
require "fonctions.php";

$pdo = getDB();

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

<!-- <!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0"> -->
    <!-- <link rel="stylesheet" href="assets/css/style.css"> -->
    <!-- <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <title>Cadets Chelun Martigné-Ferchaud</title>
    <link rel="icon" type="image/png" href="assets/img/Logo_club/logo_rogne.png"> 
</head>
<body>
    <header>
        <div class="bar_nav_login">
            <button class="burger" aria-label="Menu">☰</button>
            <img id="logo_accueil" src="assets/img/Logo_club/logo_rogne.png" alt="Image du logo du club">
            <nav class="nav_bar_login">
                <ul>
                    <li><a href="index.php">Accueil</a></li>
                    <li><a href="#">Équipes</a></li>
                    <li><a href="#">Photos</a></li>
                    <li><a href="#">Boutique</a></li>
                </ul>
            </nav>
        </div>
    </header> -->

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
