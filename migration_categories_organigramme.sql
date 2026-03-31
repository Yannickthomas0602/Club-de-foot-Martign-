-- ============================================================
-- Migration : Catégories dynamiques pour l'organigramme
-- À exécuter UNE SEULE FOIS dans phpMyAdmin ou via CLI mysql
-- ============================================================

-- 1. Créer la table des catégories
CREATE TABLE IF NOT EXISTS `club_categories_organigramme` (
    `id`              INT UNSIGNED NOT NULL AUTO_INCREMENT,
    `nom_categorie`   VARCHAR(100) NOT NULL,
    `ordre_priorite`  INT UNSIGNED NOT NULL DEFAULT 100,
    PRIMARY KEY (`id`),
    UNIQUE KEY `uq_nom` (`nom_categorie`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 2. Insérer les catégories existantes (celles codées en dur dans l'ancienne version)
INSERT IGNORE INTO `club_categories_organigramme` (`nom_categorie`, `ordre_priorite`) VALUES
    ('Bureau', 10),
    ('Staff Technique', 20),
    ('Responsables Jeunes', 30);

-- 3. Ajouter la colonne categorie_id dans club_membres
ALTER TABLE `club_membres`
    ADD COLUMN `categorie_id` INT UNSIGNED NULL AFTER `categorie`;

-- 4. Peupler categorie_id à partir de la valeur texte existante
UPDATE `club_membres` m
    JOIN `club_categories_organigramme` c ON c.`nom_categorie` = m.`categorie`
    SET m.`categorie_id` = c.`id`;

-- 5. Si un membre a une catégorie inconnue, on lui assigne la première catégorie existante
UPDATE `club_membres`
    SET `categorie_id` = (SELECT `id` FROM `club_categories_organigramme` ORDER BY `ordre_priorite` ASC LIMIT 1)
    WHERE `categorie_id` IS NULL;

-- 6. Rendre categorie_id NOT NULL et ajouter la clé étrangère
ALTER TABLE `club_membres`
    MODIFY COLUMN `categorie_id` INT UNSIGNED NOT NULL,
    ADD CONSTRAINT `fk_membre_categorie`
        FOREIGN KEY (`categorie_id`)
        REFERENCES `club_categories_organigramme` (`id`)
        ON UPDATE CASCADE
        ON DELETE RESTRICT;

-- 7. Supprimer l'ancienne colonne texte 'categorie' (devenue inutile)
ALTER TABLE `club_membres` DROP COLUMN `categorie`;
