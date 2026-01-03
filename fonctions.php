
<?php
/**
 * Fonctions d'accès et utilitaires pour la base "appdb"
 * Schéma supporté :
 * - roles(id, slug, label, created_at)
 * - teams(id, name, created_at)
 * - users(id, last_name, first_name, username, email, password_hash, role_id, is_active, created_at, updated_at)
 * - user_teams(user_id, team_id, role_attribution, assigned_at)
 */

// ---------------------------------------
// Connexion PDO à la base de données
// ---------------------------------------
function getDB(): PDO {
    $host     = "localhost";
    $port     = 3306;
    $dbname   = "appdb";
    $username = "root";
    $password = "";

    $dsn = "mysql:host={$host};port={$port};dbname={$dbname};charset=utf8mb4";

    try {
        return new PDO(
            $dsn,
            $username,
            $password,
            [
                PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES   => false
            ]
        );
    } catch (PDOException $e) {
        exit("Erreur de connexion BDD : " . $e->getMessage());
    }
}

// Récuppère l'id d'un rôle à partir de son slug (ex: admin, user). Retourne null si aucun rôle ne correspond.
function getRoleIdBySlug(PDO $pdo, string $slug): ?int {
    $stmt = $pdo->prepare("SELECT id FROM roles WHERE slug = ? LIMIT 1");
    $stmt->execute([$slug]);
    $id = $stmt->fetchColumn();
    return $id === false ? null : (int)$id;
}

// Récuppère les informations du rôle (id, slug, label) à partir de son identifiant. Retourne null si non trouvé.
function getRoleById(PDO $pdo, int $roleId): ?array {
    $stmt = $pdo->prepare("SELECT id, slug, label FROM roles WHERE id = ? LIMIT 1");
    $stmt->execute([$roleId]);
    $row = $stmt->fetch();
    return $row ?: null;
}

// Vérifie si un email existe déjà dans la table users. Retourne true ou false.
function emailExiste(PDO $pdo, string $email): bool {
    $stmt = $pdo->prepare("SELECT 1 FROM users WHERE email = ? LIMIT 1");
    $stmt->execute([$email]);
    return (bool)$stmt->fetchColumn();
}
// Vérifie si un email existe déjà dans la table users. Retourne true ou false.
function usernameExiste(PDO $pdo, string $username): bool {
    $stmt = $pdo->prepare("SELECT 1 FROM users WHERE username = ? LIMIT 1");
    $stmt->execute([$username]);
    return (bool)$stmt->fetchColumn();
}

// Crée un nouvel utilisateur avec les champs fournis. Convertit d'abord le role_slug en role_id (et lève une exception
// si inconnu), puis insère l'utilisateur, et renvoie l'id nouvellement créé.
function creerUtilisateur(
    PDO $pdo,
    string $lastName,
    string $firstName,
    string $username,
    string $email,
    string $passwordHash,
    string $roleSlug = 'user',
    int $isActive = 1
): int {
    $roleId = getRoleIdBySlug($pdo, $roleSlug);
    if ($roleId === null) {
        throw new InvalidArgumentException("Rôle inconnu: '{$roleSlug}'");
    }

    $stmt = $pdo->prepare(
        "INSERT INTO users (last_name, first_name, username, email, password_hash, role_id, is_active)
         VALUES (:ln, :fn, :un, :em, :ph, :rid, :ia)"
    );

    $stmt->execute([
        ':ln'  => $lastName,
        ':fn'  => $firstName,
        ':un'  => $username,
        ':em'  => $email,
        ':ph'  => $passwordHash,
        ':rid' => $roleId,
        ':ia'  => $isActive
    ]);

    return (int)$pdo->lastInsertId();
}

// Récupère un utilisateur par son email avec ses infos et celles du rôle (jointure sur roles). Retourne null si non trouvé.
function getUserByEmail(PDO $pdo, string $email): ?array {
    $stmt = $pdo->prepare(
        "SELECT u.*, r.slug AS role_slug, r.label AS role_label
         FROM users u
         JOIN roles r ON u.role_id = r.id
         WHERE u.email = ?
         LIMIT 1"
    );
    $stmt->execute([$email]);
    $row = $stmt->fetch();
    return $row ?: null;
}

// Pareil que getUserByEmail mais par username
function getUserByUsername(PDO $pdo, string $username): ?array {
    $stmt = $pdo->prepare(
        "SELECT u.*, r.slug AS role_slug, r.label AS role_label
         FROM users u
         JOIN roles r ON u.role_id = r.id
         WHERE u.username = ?
         LIMIT 1"
    );
    $stmt->execute([$username]);
    $row = $stmt->fetch();
    return $row ?: null;
}
// Pareil que getUserByEmail mais par id
function getUserById(PDO $pdo, int $id): ?array {
    $stmt = $pdo->prepare(
        "SELECT u.*, r.slug AS role_slug, r.label AS role_label
         FROM users u
         JOIN roles r ON u.role_id = r.id
         WHERE u.id = ?
         LIMIT 1"
    );
    $stmt->execute([$id]);
    $row = $stmt->fetch();
    return $row ?: null;
}

// Liste tous les utilisateurs (avex rôle), triés par date de création décroissante.
function getAllUsers(PDO $pdo): array {
    $stmt = $pdo->query(
        "SELECT
           u.id,
           u.last_name,
           u.first_name,
           u.username,
           u.email,
           u.is_active,
           u.created_at,
           u.updated_at,
           r.slug  AS role_slug,
           r.label AS role_label
         FROM users u
         JOIN roles r ON u.role_id = r.id
         ORDER BY u.created_at DESC"
    );
    return $stmt->fetchAll();
}

// Met à jour de manière flexible un utilisateur via une white_list de colonnes autorisées. Accepte role_slug et le convertit en 
// role_id. Ignore les colonnes non autorisées. Retourne false si aucun champ valide n'est fourni.
function updateUser(PDO $pdo, int $id, array $fields): bool {
    $allowed = [
        'last_name',
        'first_name',
        'username',
        'email',
        'password_hash',
        'role_id',
        'is_active'
    ];

    // Si on a reçu un role_slug, on le convertit en role_id
    if (isset($fields['role_slug'])) {
        $roleId = getRoleIdBySlug($pdo, (string)$fields['role_slug']);
        if ($roleId === null) {
            throw new InvalidArgumentException("Rôle inconnu: '{$fields['role_slug']}'");
        }
        $fields['role_id'] = $roleId;
        unset($fields['role_slug']);
    }

    $setParts = [];
    $params   = [':id' => $id];

    foreach ($fields as $col => $val) {
        if (in_array($col, $allowed, true)) {
            $setParts[]           = "{$col} = :{$col}";
            $params[":{$col}"]    = $val;
        }
    }

    if (empty($setParts)) {
        return false;
    }

    $sql  = "UPDATE users SET " . implode(', ', $setParts) . " WHERE id = :id";
    $stmt = $pdo->prepare($sql);
    return $stmt->execute($params);
}

// Supprime un utilisateur par son id. Retourne true si succès.
function deleteAccount(PDO $pdo, int $id): bool {
    // user_teams est en ON DELETE CASCADE => pas besoin d'effacer les liaisons à la main
    $stmt = $pdo->prepare("DELETE FROM users WHERE id = ?");
    return $stmt->execute([$id]);
}

// Vérifie si un utilisateur est connecté (session active avec user_id) et si $_SESSION est démarrée.
function isLogged(): bool {
    if (session_status() !== PHP_SESSION_ACTIVE) {
        @session_start();
    }
    return isset($_SESSION['user_id']);
}
// Redirige vers login.php si l'utilisateur n'est pas connecté.
function requireLogin(): void {
    if (!isLogged()) {
        header("Location: login.php");
        exit;
    }
}

// Ajoute (ou met à jour) l'attribution d'un utilisateur à une équipe (membre ou coach) via INSERT ... ON DUPLICATE KEY UPDATE.
function addUserToTeam(PDO $pdo, int $userId, int $teamId, string $roleAttribution = 'member'): bool {
    // role_attribution: 'member'|'coach'
    $stmt = $pdo->prepare(
        "INSERT INTO user_teams (user_id, team_id, role_attribution)
         VALUES (?, ?, ?)
         ON DUPLICATE KEY UPDATE role_attribution = VALUES(role_attribution)"
    );
    return $stmt->execute([$userId, $teamId, $roleAttribution]);
}

// Retire un utilisateur d'une équipe.
function removeUserFromTeam(PDO $pdo, int $userId, int $teamId): bool {
    $stmt = $pdo->prepare("DELETE FROM user_teams WHERE user_id = ? AND team_id = ?");
    return $stmt->execute([$userId, $teamId]);
}

// Liste les équipes d'un utilisateur avec son rôle dans l'équipe et la date d'attribution, triées par nom d'équipe.
function getTeamsByUser(PDO $pdo, int $userId): array {
    $stmt = $pdo->prepare(
        "SELECT t.id, t.name, ut.role_attribution, ut.assigned_at
         FROM user_teams ut
         JOIN teams t ON ut.team_id = t.id
         WHERE ut.user_id = ?
         ORDER BY t.name ASC"
    );
    $stmt->execute([$userId]);
    return $stmt->fetchAll();
}
// Liste les utilisateurs d'une équipe avec leur attribution et date d'affectation, triés par nom et prénom.
function getUsersByTeam(PDO $pdo, int $teamId): array {
    $stmt = $pdo->prepare(
        "SELECT u.id, u.last_name, u.first_name, u.username, u.email, ut.role_attribution, ut.assigned_at
         FROM user_teams ut
         JOIN users u ON ut.user_id = u.id
         WHERE ut.team_id = ?
         ORDER BY u.last_name ASC, u.first_name ASC"
    );
    $stmt->execute([$teamId]);
    return $stmt->fetchAll();
}