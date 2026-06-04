-- S2-T1: puntenkolom toevoegen aan bestaande database
-- Voer dit uit in phpMyAdmin als je database.sql al eerder hebt geïmporteerd.

USE wk_poule;

ALTER TABLE predictions
    ADD COLUMN points TINYINT DEFAULT NULL;
