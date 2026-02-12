<?php

session_start();
require "fonctions.php";
$pdo = getDB();
if (!isset($_SESSION['id']) || $_SESSION['role_id'] !== 1) {    // 1 => Admin
    header(header: "Location : login.php");
    exit;
}
$stmt = $pdo->query("SELECT u.id, u.last_name, u.first_name, u.username, u.email, r.slug FROM users u JOIN roles r ON u.role_id = r.id");
$admin = $stmt->fetchAll();

?>



<?php include 'header.php'; ?>
    <main>
        <div class="liste-coachs">
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
                <?php foreach ($admin as $u): ?>
                <tr>
                    <td><?= $u['id']?></td>
                    <td><?= $u['last_name']?></td>
                    <td><?= $u['first_name']?></td>
                    <td><?= $u['username']?></td>
                    <td><?= $u['email']?></td>
                    <td><?= $u['role_name']?></td>
                    <td>
                        <a class="btn" href="#">Modifer</a>
                        <a class="btn" href="#">Changer rôle</a>
                        <!-- <form action="delete_admin.php" method="POST" style="display:inline;"> -->
                            <!-- <input type="hidden" name="id" value=" //$u['id']"> -->
                            <!-- <button type="submit" onclick="return confirm('Êtes-vous sûr de vouloir supprimer cet utilisateur ?');">Supprimer</button> -->
                        <!-- </form> -->
                    </td>
                </tr>
                <?php endforeach; ?>
            </table>
        </div>
        <div class="ajout-coachs">
            <h3>Ajouter un coachs</h3>
            <form method="POST" action="add_user.php">
                <input type="text" name="nom" placeholder="Insérer le nom de l'utilisateur" required>
                <input type="email" name="email" placeholder="Insérer l'email de l'utilisateur" required>
                <input type="text" name="adresse" placeholder="Insérer l'adresse de l'utilisateur" required>
                <input type="password" name="password" placeholder="Insérer le mot de passe de l'utilisateur" required>
                <select name="role_id">
                    <option value="1">Administateur</option>
                    <option value="2">Utilisateur</option>
                </select>
                <button type="submit">Ajouter un utilisateur</button>
            </form>
        </div>
    </main>
    </body>
</html>