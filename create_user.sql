-- CREATE DATABASE IF NOT EXISTS appdb
--   CHARACTER SET utf8mb4
--   COLLATE utf8mb4_unicode_ci;

-- USE appdb;


-- CREATE TABLE roles (
--   id         INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
--   slug       VARCHAR(50) NOT NULL UNIQUE,
--   label      VARCHAR(100) NOT NULL,
--   created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
-- ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;


-- CREATE TABLE teams (
--   id         INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
--   name       VARCHAR(100) NOT NULL UNIQUE,
--   created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
-- ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;


-- CREATE TABLE users (
--   id              INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
--   last_name       VARCHAR(100) NOT NULL,
--   first_name      VARCHAR(100) NOT NULL,
--   username        VARCHAR(100) NOT NULL UNIQUE,
--   email           VARCHAR(190) NOT NULL UNIQUE,
--   password_hash   VARCHAR(255) NOT NULL,
--   role_id         INT UNSIGNED NOT NULL,
--   is_active       TINYINT(1) NOT NULL DEFAULT 1,
--   convocation_id  INT UNSIGNED DEFAULT NULL,
--   created_at      TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
--   updated_at      TIMESTAMP NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,

--   CONSTRAINT fk_users_role FOREIGN KEY (role_id)
--     REFERENCES roles(id)
--     ON UPDATE CASCADE
--     ON DELETE RESTRICT
-- ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;


-- CREATE TABLE user_teams (
--   user_id  INT UNSIGNED NOT NULL,
--   team_id  INT UNSIGNED NOT NULL,
--   role_attribution VARCHAR(50) NOT NULL DEFAULT 'member',
--   assigned_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,

--   PRIMARY KEY (user_id, team_id),
--   CONSTRAINT fk_userteams_user FOREIGN KEY (user_id)
--     REFERENCES users(id) ON UPDATE CASCADE ON DELETE CASCADE,
--   CONSTRAINT fk_userteams_team FOREIGN KEY (team_id)
--     REFERENCES teams(id) ON UPDATE CASCADE ON DELETE CASCADE
-- ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;


-- CREATE TABLE convocations (
--   id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
--   team_id INT UNSIGNED NOT NULL,
--   match_place VARCHAR(100) NOT NULL,
--   match_date VARCHAR(100) NOT NULL,  -- saisir dans le formulaire
--   opposing_team VARCHAR(100) NOT NULL,
--   player_name JSON NOT NULL,         -- Liste des joueurs (à revoir)

--   CONSTRAINT fk_convocations_team FOREIGN KEY (team_id)
--     REFERENCES teams(id)
--     ON UPDATE CASCADE ON DELETE CASCADE
-- ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;


-- CREATE TABLE pictures (
--   id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
--   picture_path VARCHAR(255) NOT NULL,
--   link VARCHAR(255)
-- ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- INSERT INTO roles (slug, label) VALUES
--   ('admin', 'Administrateur'),
--   ('coach', 'Coach'),
--   ('user', 'Utilisateur')
-- ON DUPLICATE KEY UPDATE label = VALUES(label);

-- INSERT INTO teams (name) VALUES
--   ('Equipe A'), ('Equipe B'), ('Equipe C')
-- ON DUPLICATE KEY UPDATE name = VALUES(name);


-- CREATE TABLE IF NOT EXISTS annonces_popup (
--     id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
--     titre VARCHAR(255) NOT NULL,
--     contenu LONGTEXT NOT NULL,
--     date_fin DATETIME NOT NULL,
--     actif TINYINT(1) NOT NULL DEFAULT 1
-- ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Créer la base si nécessaire
CREATE DATABASE IF NOT EXISTS appdb
  CHARACTER SET utf8mb4
  COLLATE utf8mb4_unicode_ci;

USE appdb;

-- Table: roles
CREATE TABLE IF NOT EXISTS `roles` (
  `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `slug` VARCHAR(50) NOT NULL,
  `label` VARCHAR(100) NOT NULL,
  UNIQUE KEY `uq_roles_slug` (`slug`)
) ENGINE=InnoDB
  DEFAULT CHARSET=utf8mb4
  COLLATE=utf8mb4_unicode_ci;

-- Table: users
CREATE TABLE IF NOT EXISTS `users` (
  `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `last_name` VARCHAR(255) NOT NULL,
  `first_name` VARCHAR(255) NOT NULL,
  `username` VARCHAR(150) NOT NULL,
  `email` VARCHAR(255) NOT NULL UNIQUE,
  `password_hash` VARCHAR(255) NOT NULL,
  `role_id` INT UNSIGNED NOT NULL,
  `is_active` TINYINT(1) NOT NULL DEFAULT 1,
  `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` DATETIME NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
  CONSTRAINT `fk_users_role`
    FOREIGN KEY (`role_id`) REFERENCES `roles`(`id`)
    ON UPDATE CASCADE
    ON DELETE RESTRICT,
  UNIQUE KEY `uq_users_username` (`username`)
) ENGINE=InnoDB
  DEFAULT CHARSET=utf8mb4
  COLLATE=utf8mb4_unicode_ci;

-- Table: pictures
CREATE TABLE IF NOT EXISTS `pictures` (
  `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `picture_path` VARCHAR(1000) NOT NULL,
  `link` VARCHAR(255) NULL,
  `type` VARCHAR(255) NOT NULL
) ENGINE=InnoDB
  DEFAULT CHARSET=utf8mb4
  COLLATE=utf8mb4_unicode_ci;

-- Table: annonces_popup
CREATE TABLE IF NOT EXISTS `annonces_popup` (
  `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `titre` VARCHAR(255) NOT NULL,
  `contenu` LONGTEXT NOT NULL,
  `date_fin` DATETIME NOT NULL,
  `actif` TINYINT(1) NOT NULL DEFAULT 1
) ENGINE=InnoDB
  DEFAULT CHARSET=utf8mb4
  COLLATE=utf8mb4_unicode_ci;

-- Table: teams
CREATE TABLE IF NOT EXISTS `teams` (
  `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `name` VARCHAR(255) NOT NULL UNIQUE,
  `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB
  DEFAULT CHARSET=utf8mb4
  COLLATE=utf8mb4_unicode_ci;

-- Table: convocations
CREATE TABLE IF NOT EXISTS `convocations` (
  `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `team_id` INT UNSIGNED NOT NULL,
  `match_place` VARCHAR(255) NOT NULL,
  `match_date` DATETIME NOT NULL,
  `opposing_team` VARCHAR(255) NOT NULL,
  CONSTRAINT `fk_convocations_team`
    FOREIGN KEY (`team_id`) REFERENCES `teams`(`id`)
    ON UPDATE CASCADE
    ON DELETE RESTRICT
) ENGINE=InnoDB
  DEFAULT CHARSET=utf8mb4
  COLLATE=utf8mb4_unicode_ci;

-- Table: players
CREATE TABLE IF NOT EXISTS `players` (
  `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `first_name` VARCHAR(255) NOT NULL,
  `initial_name` VARCHAR(10) NOT NULL,
  `team_id` INT UNSIGNED NOT NULL,
  CONSTRAINT `fk_player_team`
    FOREIGN KEY (`team_id`) REFERENCES `teams`(`id`)
    ON UPDATE CASCADE
    ON DELETE RESTRICT
) ENGINE=InnoDB
  DEFAULT CHARSET=utf8mb4
  COLLATE=utf8mb4_unicode_ci;

-- Données de base pour roles
-- Note: VALUES(col) est déprécié en 8.0.20+. On utilise un alias de ligne.
INSERT INTO `roles` (`slug`, `label`)
VALUES
  ('admin', 'Administrateur'),
  ('coach', 'Coach'),
  ('user', 'Utilisateur')
AS new
ON DUPLICATE KEY UPDATE
  `label` = new.`label`;