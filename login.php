<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <!-- <link rel="stylesheet" href="assets/css/style.css"> -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <title>Cadets Chelun Martigné-Ferchaud</title>
    <!-- Logo du club devant le titre de l'onglet -->
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
                    <li><a href="#">&Eacute;quipes</a></li>
                    <li><a href="#">Photos</a></li>
                    <li><a href="#">Boutique</a></li>
                </ul>
            </nav>
        </div>
    </header>
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
                    </div>
                </form>
            </section>
        </div>
    </main>
<?php
include "footer.php";
?>