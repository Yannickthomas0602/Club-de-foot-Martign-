
-- 1) Créer la base
CREATE DATABASE IF NOT EXISTS appdb
  CHARACTER SET utf8mb4
  COLLATE utf8mb4_unicode_ci;

USE appdb;

-- 2) Table des rôles
CREATE TABLE roles (
  id         INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  slug       VARCHAR(50) NOT NULL UNIQUE,     -- "admin", "user", "coach"
  label      VARCHAR(100) NOT NULL,           -- Libellé lisible (ex: "Administrateur")
  created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 3) Table des équipes
CREATE TABLE teams (
  id         INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  name       VARCHAR(100) NOT NULL UNIQUE,
  created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 4) Table des utilisateurs
CREATE TABLE users (
  id              BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  last_name       VARCHAR(100) NOT NULL,      -- Nom
  first_name      VARCHAR(100) NOT NULL,      -- Prénom
  username        VARCHAR(100) NOT NULL,      -- Identifiant (unique pour la connexion)
  email           VARCHAR(190) NOT NULL,      -- Optionnel mais recommandé pour la connexion/notifications
  password_hash   VARCHAR(255) NOT NULL,      -- Stocker hash (ex: bcrypt/argon2)
  role_id         INT UNSIGNED NOT NULL,      -- Clé étrangère vers roles.id
  is_active       TINYINT(1) NOT NULL DEFAULT 1,  -- Actif/inactif
  created_at      TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at      TIMESTAMP NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,

  CONSTRAINT uq_users_username UNIQUE (username),
  CONSTRAINT uq_users_email UNIQUE (email),
  CONSTRAINT fk_users_role FOREIGN KEY (role_id) REFERENCES roles(id)
    ON UPDATE CASCADE
    ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 5) Table de liaison utilisateur ↔ équipes
-- Un utilisateur "coach" peut être affecté à plusieurs équipes.
-- Un "user" classique peut aussi être rattaché à une équipe (si besoin).
CREATE TABLE user_teams (
  user_id  BIGINT UNSIGNED NOT NULL,
  team_id  INT UNSIGNED NOT NULL,
  role_attribution ENUM('member','coach') NOT NULL DEFAULT 'member',
  assigned_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,

  PRIMARY KEY (user_id, team_id),
  CONSTRAINT fk_userteams_user FOREIGN KEY (user_id) REFERENCES users(id)
    ON UPDATE CASCADE
    ON DELETE CASCADE,
  CONSTRAINT fk_userteams_team FOREIGN KEY (team_id) REFERENCES teams(id)
    ON UPDATE CASCADE
    ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 6) Insérer les rôles par défaut
INSERT INTO roles (slug, label) VALUES
  ('admin', 'Administrateur'),
  ('coach', 'Coach'),
  ('user',  'Utilisateur')
ON DUPLICATE KEY UPDATE label = VALUES(label);

-- 7) (Optionnel) Équipes de démonstration
INSERT INTO teams (name) VALUES
  ('Equipe A'), ('Equipe B'), ('Equipe C')
ON DUPLICATE KEY UPDATE name = VALUES(name);

