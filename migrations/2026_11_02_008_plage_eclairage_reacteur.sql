-- Éclairage du réacteur : plage horaire configurable, sans cycles ni pauses
INSERT INTO `core_config_value` (`name`, `value`, `type`, `groupe`, `label`, `show_time_left`, `description`)
SELECT 'eclairage_reacteur_debut', '22:30', 'time', 3, 'Éclairage du réacteur : allumage', 0, 'Heure à laquelle l''éclairage du réacteur s''allume (si « Éclairage du réacteur » est activé). La plage peut passer minuit.'
WHERE NOT EXISTS (SELECT 1 FROM `core_config_value` WHERE `name` = 'eclairage_reacteur_debut');
INSERT INTO `core_config_value` (`name`, `value`, `type`, `groupe`, `label`, `show_time_left`, `description`)
SELECT 'eclairage_reacteur_fin', '10:00', 'time', 3, 'Éclairage du réacteur : extinction', 0, 'Heure à laquelle l''éclairage du réacteur s''éteint.'
WHERE NOT EXISTS (SELECT 1 FROM `core_config_value` WHERE `name` = 'eclairage_reacteur_fin');
UPDATE `core_config_status` SET `label` = 'Éclairage du réacteur (plage horaire)', `description` = 'Allume l''éclairage du réacteur entre les heures d''allumage et d''extinction définies dans la configuration.' WHERE `name` = 'reacteur_eclairage';
UPDATE `core_config_status` SET `description` = 'Allume tout de suite l''éclairage du réacteur ; le cron le rééteint hors de la plage horaire ou si l''éclairage du réacteur est désactivé.' WHERE `name` = 'force_turn_on_eclairage';
