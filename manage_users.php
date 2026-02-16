<?php

session_start();
require "fonctions.php";
$pdo = getDB();
if (!isset($_SESSION['user_id']) || $_SESSION['role_slug'] !== 'admin') {
    header("Location: login.php");
    exit;
}
$stmt = $pdo->query("SELECT u.id, u.last_name, u.first_name, u.username, u.email, r.slug FROM users u JOIN roles r ON u.role_id = r.id");
$admin = $stmt->fetchAll();

?>

<?php include 'header.php'; ?>
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
                        <a class="btn" href="#">Modifer</a>
                        <a class="btn" href="#">Changer rôle</a>
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
            <form method="POST" action="add_coach.php">
                <input type="text" name="username" placeholder="Insérer l'identifiant du coach" required>
                <input type="email" name="email" placeholder="Insérer l'email du coach" required>
                <input type="password" name="password" placeholder="Insérer le mot de passe du coach" required>
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
                    <th>Email</th>
                    <th>Rôle</th>
                    <th>Action</th>
                </tr>
                <?php foreach ($admin as $u): 
                    if ($u['slug'] == 'user'){?>
                <tr>
                    <td><?= $u['id']?></td>
                    <td><?= $u['last_name']?></td>
                    <td><?= $u['username']?></td>
                    <td><?= $u['email']?></td>
                    <td><?= $u['slug']?></td>
                    <td>
                        <a class="btn" href="#">Modifer</a>
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
            <form method="POST" action="add_user.php">
                <input type="text" name="username" placeholder="Insérer le identifiant de l'équipe" required>
                <input type="password" name="password" placeholder="Insérer le mot de passe de l'équipe" required>
                <button type="submit">Ajouter un équipe</button>
            </form>
        </div>
    </main>
    </body>
</html>