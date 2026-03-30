-- ============================================================
--  PEF Articles — Table de création
--  Base : appdb | Moteur : InnoDB | Encodage : utf8mb4
--  À importer via phpMyAdmin ou CLI :
--      mysql -u root -proot appdb < create_pef.sql
-- ============================================================

CREATE TABLE IF NOT EXISTS `pef_articles` (
  `id`                   INT UNSIGNED    NOT NULL AUTO_INCREMENT,
  `titre`                VARCHAR(255)    NOT NULL,
  `theme`                ENUM(
                           'Santé',
                           'Engagement Citoyen',
                           'Environnement',
                           'Fair-Play',
                           'Règles du Jeu',
                           'Culture Foot'
                         )               NOT NULL,
  `image_couverture_url` VARCHAR(500)    NOT NULL DEFAULT '',
  `contenu_html`         MEDIUMTEXT      NOT NULL,
  `date_publication`     DATETIME        NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB
  DEFAULT CHARSET=utf8mb4
  COLLATE=utf8mb4_unicode_ci;
