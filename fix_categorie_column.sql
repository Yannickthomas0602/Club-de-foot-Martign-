-- ============================================================
-- CORRECTIF : Rendre l'ancienne colonne 'categorie' nullable
-- À exécuter dans phpMyAdmin si la migration principale a déjà
-- été faite mais que la colonne n'a pas encore été supprimée.
-- ============================================================

-- Option A (recommandée) : Supprimer définitivement l'ancienne colonne texte
-- (sûr à faire maintenant que categorie_id est en place)
ALTER TABLE `club_membres` DROP COLUMN `categorie`;

-- ─────────────────────────────────────────────────────────────
-- Option B : Si vous préférez garder la colonne temporairement,
-- rendez-la juste nullable au lieu de la supprimer :
-- ALTER TABLE `club_membres` MODIFY COLUMN `categorie` VARCHAR(100) NULL DEFAULT NULL;
-- ─────────────────────────────────────────────────────────────
