

<?php
session_start();
require "fonctions.php";

$pdo = getDB();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username        = trim($_POST['username'] ?? '');
    $email           = trim($_POST['email'] ?? '');
    $password        = trim($_POST['password'] ?? '');
    $passwordConfirm = trim($_POST['password_confirm'] ?? '');

    // Vérifications
    if ($username === '' || $email === '' || $password === '' || $passwordConfirm === '') {
        die("Tous les champs sont obligatoires.");
    }

    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        die("Email invalide.");
    }

    if (usernameExiste($pdo, $username)) {
        die("Cet identifiant existe déjà.");
    }

    if (emailExiste($pdo, $email)) {
        die("Cet email existe déjà.");
    }

    if (!preg_match("/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[\W_]).{8,}$/", $password)) {
        die("Le mot de passe doit contenir au minimum une MAJUSCULE, une minuscule, un chiffre et un caractère spécial, et faire 8 caractères ou plus.");
    }

    if ($password !== $passwordConfirm) {
        die("Les mots de passe ne correspondent pas.");
    }

    $passwordHash = password_hash($password, PASSWORD_DEFAULT);

    try {
        // On passe des valeurs vides pour nom/prénom
        $newUserId = creerUtilisateur(
            $pdo,
            '',             // last_name (vide)
            '',             // first_name (vide)
            $username,      // username
            $email,         // email
            $passwordHash,  // password_hash
            'user',         // role_slug
            1               // is_active
        );

        echo "Inscription réussie (ID: {$newUserId}). login.phpSe connecter</a>";
    } catch (Throwable $e) {
        die("Erreur lors de l'inscription: " . htmlspecialchars($e->getMessage(), ENT_QUOTES));
    }
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Inscription_Test</title>
</head>
<body>
<main>
    <section>
        <h3>Créer un compte (test)</h3>
        <form method="POST">
            <div>
                <label for="username">Identifiant</label>
                <input id="username" type="text" name="username" required>
            </div>
            <div>
                <label for="email">Email</label>
                <input id="email" type="email" name="email" required>
            </div>
            <div>
                <label for="password">Mot de passe</label>
                <input id="password" type="password" name="password" required>
            </div>
            <div>
                <label for="password_confirm">Confirmer le mot de passe</label>
                <input id="password_confirm" type="password" name="password_confirm" required>
            </div>
            <div>
                <button type="submit">S'inscrire</button>
                <a href="login.php">Déjà membre ?</a>
            </div>
        </form>
    </section>
</main>
</body>
</html>

