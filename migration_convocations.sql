-- =========== MIGRATION BDD =================
-- À exécuter dans votre base de données locales ou phpMyAdmin
-- ===========================================

-- 1. Suppression de l'ancienne colonne JSON, si présente (Ajustez si elle s'appelle autrement)
ALTER TABLE `convocations` DROP COLUMN IF EXISTS `player_name`;

-- 2. Création de la table des absences (pour pister les joueurs cochés)
CREATE TABLE IF NOT EXISTS `convocation_absences` (
  `convocation_id` INT UNSIGNED NOT NULL,
  `player_id` INT UNSIGNED NOT NULL,
  PRIMARY KEY (`convocation_id`, `player_id`),
  CONSTRAINT `fk_absences_convocation` FOREIGN KEY (`convocation_id`) REFERENCES `convocations`(`id`) ON UPDATE CASCADE ON DELETE CASCADE,
  CONSTRAINT `fk_absences_player` FOREIGN KEY (`player_id`) REFERENCES `players`(`id`) ON UPDATE CASCADE ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
