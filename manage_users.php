<?php

session_start();
require "fonctions.php";
$pdo = getDB();
if (!isset($_SESSION['user_id']) || $_SESSION['role_slug'] !== 'admin') {
    header("Location: login.php");
    exit;
}
// récupérer tous les utilisateurs avec leur rôle pour les afficher dans la liste
$stmt = $pdo->query("SELECT u.id, u.last_name, u.first_name, u.username, u.email, r.slug FROM users u JOIN roles r ON u.role_id = r.id");
$admin = $stmt->fetchAll();

?>

<?php include 'header.php'; ?>
<script src="assets/js/check_password.js"></script>
    <main>
        <div class="liste-coachs">
            <h2>Liste des coachs : </h2>
            <table border="2">
                <tr>
                    <th>ID</th>
                    <th>NOM</th>
                    <th>Prénom</th>
                    <th>Identifiant</th>
                    <th>Email</th>
                    <th>Rôle</th>
                    <th>Actions</th>
                </tr>
                <?php foreach ($admin as $u): 
                    if ($u['slug'] == 'coach'){?>
                <tr>
                    <td><?= $u['id']?></td>
                    <td><?= $u['last_name']?></td>
                    <td><?= $u['first_name']?></td>
                    <td><?= $u['username']?></td>
                    <td><?= $u['email']?></td>
                    <td><?= $u['slug']?></td>
                    <td>
                        <a class="btn" href="edit_user.php?id=<?= $u['id'] ?>">Modifier</a>
                        <form action="delete_user.php" method="POST" style="display:inline;" onsubmit="return confirm('Êtes-vous sûr de vouloir supprimer le coach <?= $u['first_name'] ?> <?= $u['last_name'] ?>');">
                            <input type="hidden" name="id" value="<?= $u['id'] ?>">
                            <button type="submit" class="btn btn-danger">Supprimer</button>
                        </form>
                    </td>
                </tr>
                <?php
                    } 
                    endforeach; ?>
            </table>
        </div>
        <div class="ajout-coachs">
            <h3>Ajouter un coachs</h3>
            <form method="POST" action="add_coach.php" class="js-check-password">
                <div class="nom_coach">
                    <label for="last_name">Nom du coach</label>
                    <input type="text" name="last_name" placeholder="Insérer le nom du coach" required>
                </div>
                <div class="prenom_coach">
                    <label for="first_name">Prénom du coach</label>
                    <input type="text" name="first_name" placeholder="Insérer le prénom du coach" required>
                </div>
                <div class="identifiant_coach">
                    <label for="username">Identifiant du coach</label>
                    <input type="text" name="username" placeholder="Insérer l'identifiant du coach" required>
                </div>
                <div class="email_coach">
                    <label for="email">Email du coach</label>
                    <input type="email" name="email" placeholder="Insérer l'email du coach" required>
                </div>
                <div class="password_coach">
                    <label for="password">Mot de passe du coach</label>
                    <input type="password" name="password" placeholder="Insérer le mot de passe du coach" required>
                </div>
                <div class="confirm_password_coach">
                    <label for="password_confirm">Confirmer le mot de passe</label>
                    <input type="password" name="password_confirm" placeholder="Confirmer le mot de passe" required>
                </div>
                <button type="submit">Ajouter un coach</button>
            </form>
        </div>
        <div class="liste-equipes">
            <h2>Liste des équipes : </h2>
            <table border="2">
                <tr>
                    <th>ID</th>
                    <th>Nom de l'équipe</th>
                    <th>Identifiant</th>
                    <th>Rôle</th>
                    <th>Action</th>
                </tr>
                <?php foreach ($admin as $u): 
                    if ($u['slug'] == 'user'){?>
                <tr>
                    <td><?= $u['id']?></td>
                    <td><?= $u['last_name']?></td>
                    <td><?= $u['username']?></td>
                    <td><?= $u['slug']?></td>
                    <td>
                        <a class="btn" href="edit_user.php?id=<?= $u['id'] ?>">Modifier</a>
                        <form action="delete_user.php" method="POST" style="display:inline;" onsubmit="return confirm('Êtes-vous sûr de vouloir supprimer l\'équipe <?= $u['last_name'] ?>');">
                            <input type="hidden" name="id" value="<?= $u['id'] ?>">
                            <button type="submit" class="btn btn-danger">Supprimer</button>
                        </form>
                    </td>
                </tr>
                <?php
                    } 
                    endforeach; ?>
            </table>
        </div>
        <div class="ajout-equipes">
            <h3>Ajouter une equipe</h3>
            <form method="POST" action="add_user.php" class="js-check-password">
                <div class="nom_equipe">
                    <label for="username">Nom de l'équipe</label>
                    <input type="text" name="last_name" placeholder="Insérer le nom de l'équipe" required>
                </div>
                <div class="Identifiant_equipe">
                    <label for="username">Identifiant de l'équipe</label>
                    <input type="text" name="username" placeholder="Insérer le identifiant de l'équipe" required>
                </div>
                <div class="password_equipe">
                    <label for="password">Mot de passe de l'équipe</label>
                    <input type="password" name="password" placeholder="Insérer le mot de passe de l'équipe" required>
                </div>
                <div class="confirm_password_equipe">
                    <label for="password_confirm">Confirmer le mot de passe</label>
                    <input type="password" name="password_confirm" placeholder="Confirmer le mot de passe" required>
                </div>
                <button type="submit">Ajouter une équipe</button>
            </form>
        </div>
        <div class="liste-admins">
            <h2>Liste des administrateurs : </h2>
            <table border="2">
                <tr>
                    <th>ID</th>
                    <th>NOM</th>
                    <th>Prénom</th>
                    <th>Identifiant</th>
                    <th>Email</th>
                    <th>Rôle</th>
                    <th>Actions</th>
                </tr>
                <?php foreach ($admin as $u): 
                    if ($u['slug'] == 'admin'){?>
                <tr>
                    <td><?= $u['id']?></td>
                    <td><?= $u['last_name']?></td>
                    <td><?= $u['first_name']?></td>
                    <td><?= $u['username']?></td>
                    <td><?= $u['email']?></td>
                    <td><?= $u['slug']?></td>
                    <td>
                        <a class="btn" href="edit_user.php?id=<?= $u['id'] ?>">Modifier</a>
                        <form action="delete_user.php" method="POST" style="display:inline;" onsubmit="return confirm('Êtes-vous sûr de vouloir supprimer l\'administrateur <?= $u['first_name'] ?> <?= $u['last_name'] ?>');">
                            <input type="hidden" name="id" value="<?= $u['id'] ?>">
                            <button type="submit" class="btn btn-danger">Supprimer</button>
                        </form>
                    </td>
                </tr>
                <?php
                    } 
                    endforeach; ?>
            </table>
        </div>
        <div class="ajout-coachs">
            <h3>Ajouter un administrateur</h3>
            <form method="POST" action="add_admin.php" class="js-check-password">
                <div class="nom_admin">
                    <label for="last_name">Nom de l'administrateur</label>
                    <input type="text" name="last_name" placeholder="Insérer le nom de l'administrateur" required>
                </div>
                <div class="prenom_admin">
                    <label for="first_name">Prénom de l'administrateur</label>
                    <input type="text" name="first_name" placeholder="Insérer le prénom de l'administrateur" required>
                </div>
                <div class="identifiant_admin">
                    <label for="username">Identifiant de l'administrateur</label>
                    <input type="text" name="username" placeholder="Insérer l'identifiant de l'administrateur" required>
                </div>
                <div class="email_admin">
                    <label for="email">Email de l'administrateur</label>
                    <input type="email" name="email" placeholder="Insérer l'email de l'administrateur" required>
                </div>
                <div class="password_admin">
                    <label for="password">Mot de passe de l'administrateur</label>
                    <input type="password" name="password" placeholder="Insérer le mot de passe de l'administrateur" required>
                </div>
                <div class="confirm_password_admin">
                    <label for="password_confirm">Confirmer le mot de passe</label>
                    <input type="password" name="password_confirm" placeholder="Confirmer le mot de passe" required>
                </div>
                <button type="submit">Ajouter un administrateur</button>
            </form>
        </div>
    </main>
<?php include 'footer.php'; ?>