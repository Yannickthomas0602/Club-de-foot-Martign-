-- ============================================================
-- FICHIER D'INITIALISATION COMPLET DE LA BASE DE DONNÉES
-- ============================================================

-- 1. Création et Sélection de la base de données
CREATE DATABASE IF NOT EXISTS appdb
  CHARACTER SET utf8mb4
  COLLATE utf8mb4_unicode_ci;

USE appdb;

-- ============================================================
-- 2. TABLES SANS CLÉS ÉTRANGÈRES (Indépendantes)
-- ============================================================

-- Table: roles
CREATE TABLE IF NOT EXISTS `roles` (
  `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `slug` VARCHAR(50) NOT NULL,
  `label` VARCHAR(100) NOT NULL,
  UNIQUE KEY `uq_roles_slug` (`slug`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Table: teams
CREATE TABLE IF NOT EXISTS `teams` (
  `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `name` VARCHAR(255) NOT NULL UNIQUE,
  `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Table: pictures
CREATE TABLE IF NOT EXISTS `pictures` (
  `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `picture_path` VARCHAR(1000) NOT NULL,
  `link` VARCHAR(255) NULL,
  `type` VARCHAR(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Table: annonces_popup
CREATE TABLE IF NOT EXISTS `annonces_popup` (
  `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `titre` VARCHAR(255) NOT NULL,
  `contenu` LONGTEXT NOT NULL,
  `date_fin` DATETIME NOT NULL,
  `actif` TINYINT(1) NOT NULL DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Table: pef_articles
CREATE TABLE IF NOT EXISTS `pef_articles` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `titre` VARCHAR(255) NOT NULL,
  `theme` ENUM(
      'Santé',
      'Engagement Citoyen',
      'Environnement',
      'Fair-Play',
      'Règles du Jeu',
      'Culture Foot'
  ) NOT NULL,
  `image_couverture_url` VARCHAR(500) NOT NULL DEFAULT '',
  `contenu_html` MEDIUMTEXT NOT NULL,
  `date_publication` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Table: club_categories_organigramme
CREATE TABLE IF NOT EXISTS `club_categories_organigramme` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `nom_categorie` VARCHAR(100) NOT NULL,
  `ordre_priorite` INT UNSIGNED NOT NULL DEFAULT 100,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_nom` (`nom_categorie`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================
-- 3. TABLES AVEC CLÉS ÉTRANGÈRES (Dépendantes)
-- ============================================================

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
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

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
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

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
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Table: convocation_absences
CREATE TABLE IF NOT EXISTS `convocation_absences` (
  `convocation_id` INT UNSIGNED NOT NULL,
  `player_id` INT UNSIGNED NOT NULL,
  PRIMARY KEY (`convocation_id`, `player_id`),
  CONSTRAINT `fk_absences_convocation`
    FOREIGN KEY (`convocation_id`) REFERENCES `convocations`(`id`)
    ON UPDATE CASCADE
    ON DELETE CASCADE,
  CONSTRAINT `fk_absences_player`
    FOREIGN KEY (`player_id`) REFERENCES `players`(`id`)
    ON UPDATE CASCADE
    ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Table: club_membres
CREATE TABLE IF NOT EXISTS `club_membres` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `nom` VARCHAR(100) NOT NULL,
  `prenom` VARCHAR(100) NOT NULL,
  `role` VARCHAR(150) NOT NULL COMMENT 'Ex : Président, Entraîneur principal',
  `categorie_id` INT UNSIGNED NOT NULL,
  `photo_url` VARCHAR(255) NULL DEFAULT NULL,
  `ordre_affichage` SMALLINT UNSIGNED NOT NULL DEFAULT 100,
  `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  CONSTRAINT `fk_membre_categorie`
      FOREIGN KEY (`categorie_id`)
      REFERENCES `club_categories_organigramme` (`id`)
      ON UPDATE CASCADE
      ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Membres de l''organigramme du club';

-- ============================================================
-- 4. INSERTION DES DONNÉES DE BASE
-- ============================================================

-- Rôles par défaut
INSERT INTO `roles` (`slug`, `label`)
VALUES
  ('admin', 'Administrateur'),
  ('coach', 'Coach'),
  ('user', 'Utilisateur')
AS new
ON DUPLICATE KEY UPDATE
  `label` = new.`label`;

-- Catégories de l'organigramme par défaut
INSERT IGNORE INTO `club_categories_organigramme` (`id`, `nom_categorie`, `ordre_priorite`) VALUES
  (1, 'Bureau', 10),
  (2, 'Staff Technique', 20),
  (3, 'Responsables Jeunes', 30);
