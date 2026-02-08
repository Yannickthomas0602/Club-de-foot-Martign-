
-- -- 1) Créer la base
-- CREATE DATABASE IF NOT EXISTS appdb
--   CHARACTER SET utf8mb4
--   COLLATE utf8mb4_unicode_ci;

-- USE appdb;

-- -- 2) Table des rôles
-- CREATE TABLE roles (
--   id         INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
--   slug       VARCHAR(50) NOT NULL UNIQUE,     -- "admin", "user", "coach"
--   label      VARCHAR(100) NOT NULL,           -- Libellé lisible (ex: "Administrateur")
--   created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
-- ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- -- 3) Table des équipes
-- CREATE TABLE teams (
--   id         INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
--   name       VARCHAR(100) NOT NULL UNIQUE,
--   created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
-- ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- -- 4) Table des utilisateurs
-- CREATE TABLE users (
--   id              BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
--   last_name       VARCHAR(100) NOT NULL,      -- Nom
--   first_name      VARCHAR(100) NOT NULL,      -- Prénom
--   username        VARCHAR(100) NOT NULL,      -- Identifiant (unique pour la connexion)
--   email           VARCHAR(190) NOT NULL,      -- Optionnel mais recommandé pour la connexion/notifications
--   password_hash   VARCHAR(255) NOT NULL,      -- Stocker hash (ex: bcrypt/argon2)
--   role_id         INT UNSIGNED NOT NULL,      -- Clé étrangère vers roles.id
--   is_active       TINYINT(1) NOT NULL DEFAULT 1,  -- Actif/inactif
--   created_at      TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
--   updated_at      TIMESTAMP NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,

--   CONSTRAINT uq_users_username UNIQUE (username),
--   CONSTRAINT uq_users_email UNIQUE (email),
--   CONSTRAINT fk_users_role FOREIGN KEY (role_id) REFERENCES roles(id)
--     ON UPDATE CASCADE
--     ON DELETE RESTRICT
-- ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- -- 5) Table de liaison utilisateur ↔ équipes
-- -- Un utilisateur "coach" peut être affecté à plusieurs équipes.
-- -- Un "user" classique peut aussi être rattaché à une équipe (si besoin).
-- CREATE TABLE user_teams (
--   user_id  BIGINT UNSIGNED NOT NULL,
--   team_id  INT UNSIGNED NOT NULL,
--   role_attribution ENUM('member','coach') NOT NULL DEFAULT 'member',
--   assigned_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,

--   PRIMARY KEY (user_id, team_id),
--   CONSTRAINT fk_userteams_user FOREIGN KEY (user_id) REFERENCES users(id)
--     ON UPDATE CASCADE
--     ON DELETE CASCADE,
--   CONSTRAINT fk_userteams_team FOREIGN KEY (team_id) REFERENCES teams(id)
--     ON UPDATE CASCADE
--     ON DELETE CASCADE
-- ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- -- 6) Insérer les rôles par défaut
-- INSERT INTO roles (slug, label) VALUES
--   ('admin', 'Administrateur'),
--   ('coach', 'Coach'),
--   ('user',  'Utilisateur')
-- ON DUPLICATE KEY UPDATE label = VALUES(label);

-- -- 7) (Optionnel) Équipes de démonstration
-- INSERT INTO teams (name) VALUES
--   ('Equipe A'), ('Equipe B'), ('Equipe C')
-- ON DUPLICATE KEY UPDATE name = VALUES(name);

-- -- 8) Table des Joueurs (Licenciés)
-- CREATE TABLE players (
--     id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
--     last_name VARCHAR(100) NOT NULL,
--     first_name VARCHAR(100) NOT NULL,
--     category VARCHAR(50), -- ex: U18, Senior [cite: 85, 86]
--     license_number VARCHAR(50), -- Numéro de licence obligatoire [cite: 360]
--     created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
-- ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- -- 9) Table des Convocations
-- CREATE TABLE convocations (
--     id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
--     match_date DATETIME NOT NULL,
--     opponent VARCHAR(100) NOT NULL,
--     location VARCHAR(100) NOT NULL,
--     team_id INT UNSIGNED NOT NULL, -- Lien vers l'équipe concernée
--     CONSTRAINT fk_convocations_team FOREIGN KEY (team_id) REFERENCES teams(id)
-- ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- -- 10) Table des Photos (Galerie)
-- CREATE TABLE photos (
--     id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
--     file_path VARCHAR(255) NOT NULL, -- Chemin du fichier [cite: 361]
--     album_name VARCHAR(100), -- Catégorie (ex: U6-U7) [cite: 272, 337]
--     upload_date DATE NOT NULL,
--     created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
-- ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- -- 11) Table des Sponsors (Bandeau défilant)
-- CREATE TABLE sponsors (
--     id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
--     name VARCHAR(100),
--     logo_path VARCHAR(255), -- Chemin vers le logo (Savouré, Pigeon, etc.) [cite: 72, 74, 362]
--     link VARCHAR(255) -- Lien vers le site du partenaire [cite: 362]
-- ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;


-- 1) Création BDD
CREATE DATABASE IF NOT EXISTS appdb
  CHARACTER SET utf8mb4
  COLLATE utf8mb4_unicode_ci;

USE appdb;

-----------------------------------------------------------
-- TABLE ROLES
-----------------------------------------------------------
CREATE TABLE roles (
  id         INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  slug       VARCHAR(50) NOT NULL UNIQUE,
  label      VARCHAR(100) NOT NULL,
  created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-----------------------------------------------------------
-- TABLE TEAMS
-----------------------------------------------------------
CREATE TABLE teams (
  id         INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  name       VARCHAR(100) NOT NULL UNIQUE,
  created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-----------------------------------------------------------
-- TABLE USERS (schema + ajout convocation_id)
-----------------------------------------------------------
CREATE TABLE users (
  id              INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  last_name       VARCHAR(100) NOT NULL,
  first_name      VARCHAR(100) NOT NULL,
  username        VARCHAR(100) NOT NULL UNIQUE,
  email           VARCHAR(190) NOT NULL UNIQUE,
  password_hash   VARCHAR(255) NOT NULL,
  role_id         INT UNSIGNED NOT NULL,
  is_active       TINYINT(1) NOT NULL DEFAULT 1,
  convocation_id  INT UNSIGNED DEFAULT NULL,
  created_at      TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at      TIMESTAMP NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,

  CONSTRAINT fk_users_role FOREIGN KEY (role_id)
    REFERENCES roles(id)
    ON UPDATE CASCADE
    ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-----------------------------------------------------------
-- TABLE DE LIAISON user_teams
-----------------------------------------------------------
CREATE TABLE user_teams (
  user_id  INT UNSIGNED NOT NULL,
  team_id  INT UNSIGNED NOT NULL,
  role_attribution VARCHAR(50) NOT NULL DEFAULT 'member',
  assigned_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,

  PRIMARY KEY (user_id, team_id),
  CONSTRAINT fk_userteams_user FOREIGN KEY (user_id)
    REFERENCES users(id) ON UPDATE CASCADE ON DELETE CASCADE,
  CONSTRAINT fk_userteams_team FOREIGN KEY (team_id)
    REFERENCES teams(id) ON UPDATE CASCADE ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-----------------------------------------------------------
-- TABLE CONVOCATION (alignée avec le schéma)
-----------------------------------------------------------
CREATE TABLE convocations (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  team_id INT UNSIGNED NOT NULL,
  match_place VARCHAR(100) NOT NULL,
  match_date VARCHAR(100) NOT NULL,  -- Saisie manuelle
  opposing_team VARCHAR(100) NOT NULL,
  player_name JSON NOT NULL,         -- Liste des joueurs

  CONSTRAINT fk_convocations_team FOREIGN KEY (team_id)
    REFERENCES teams(id)
    ON UPDATE CASCADE ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-----------------------------------------------------------
-- TABLE PICTURE (correspond au schéma)
-----------------------------------------------------------
CREATE TABLE pictures (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  picture_path VARCHAR(255) NOT NULL,
  link VARCHAR(255)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-----------------------------------------------------------
-- INSERTION DES RÔLES
-----------------------------------------------------------
INSERT INTO roles (slug, label) VALUES
  ('admin', 'Administrateur'),
  ('coach', 'Coach'),
  ('user', 'Utilisateur')
ON DUPLICATE KEY UPDATE label = VALUES(label);

-----------------------------------------------------------
-- INSERTION DES ÉQUIPES (exemple)
-----------------------------------------------------------
INSERT INTO teams (name) VALUES
  ('Equipe A'), ('Equipe B'), ('Equipe C')
ON DUPLICATE KEY UPDATE name = VALUES(name);

-----------------------------------------------------------
-- AJOUT DE L’ADMIN UNIQUE
-----------------------------------------------------------
INSERT INTO users (last_name, first_name, username, email, password_hash, role_id)
VALUES (
  'Admin',
  'Super',
  'admin',
  'admin@example.com',
  '$2y$10$U7bc8wWzF1dW2a4QmG5yeO2pTqXx5lBc1NaHkQXbPsdPyAplMJ0iW',
  (SELECT id FROM roles WHERE slug='admin')
);