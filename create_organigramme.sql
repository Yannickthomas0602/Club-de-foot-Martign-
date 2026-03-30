-- ============================================================
--  Table : club_membres
--  Base   : appdb
--  Usage  : Organigramme du club (Bureau, Staff, Responsables)
-- ============================================================

CREATE TABLE IF NOT EXISTS `club_membres` (
    `id`               INT UNSIGNED     NOT NULL AUTO_INCREMENT,
    `nom`              VARCHAR(100)     NOT NULL,
    `prenom`           VARCHAR(100)     NOT NULL,
    `role`             VARCHAR(150)     NOT NULL COMMENT 'Ex : Président, Entraîneur principal',
    `categorie`        VARCHAR(100)     NOT NULL COMMENT 'Ex : Bureau, Staff Technique, Responsables Jeunes',
    `photo_url`        VARCHAR(255)         NULL DEFAULT NULL,
    `ordre_affichage`  SMALLINT UNSIGNED NOT NULL DEFAULT 100,
    `created_at`       TIMESTAMP        NOT NULL DEFAULT CURRENT_TIMESTAMP,

    PRIMARY KEY (`id`),
    KEY `idx_categorie_ordre` (`categorie`, `ordre_affichage`)
) ENGINE=InnoDB
  DEFAULT CHARSET=utf8mb4
  COLLATE=utf8mb4_unicode_ci
  COMMENT='Membres de l\'organigramme du club';

-- Données d'exemple (à supprimer ou adapter)
INSERT INTO `club_membres` (`nom`, `prenom`, `role`, `categorie`, `photo_url`, `ordre_affichage`) VALUES
('Dupont',   'Jean',    'Président',               'Bureau',                  NULL, 1),
('Martin',   'Marie',   'Vice-Présidente',          'Bureau',                  NULL, 2),
('Bernard',  'Paul',    'Trésorier',                'Bureau',                  NULL, 3),
('Leroy',    'Sophie',  'Secrétaire',               'Bureau',                  NULL, 4),
('Garcia',   'Lucas',   'Entraîneur Principal',     'Staff Technique',         NULL, 1),
('Robert',   'Emma',    'Entraîneur Adjoint',       'Staff Technique',         NULL, 2),
('Petit',    'Thomas',  'Préparateur Physique',     'Staff Technique',         NULL, 3),
('Moreau',   'Claire',  'Responsable U13',          'Responsables Jeunes',     NULL, 1),
('Simon',    'Antoine', 'Responsable U15',          'Responsables Jeunes',     NULL, 2),
('Laurent',  'Julie',   'Responsable U18',          'Responsables Jeunes',     NULL, 3);
