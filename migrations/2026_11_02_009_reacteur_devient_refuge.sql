-- Le réacteur devient un refuge : nettoyage, éclairage, réglages et tâche planifiée renommés
RENAME TABLE `data_clean_reacteur` TO `data_clean_refuge`;

UPDATE `core_config_value` SET `name` = 'check_clean_refuge' WHERE `name` = 'check_clean_reacteur';
UPDATE `core_config_value` SET `name` = 'eclairage_refuge_debut' WHERE `name` = 'eclairage_reacteur_debut';
UPDATE `core_config_value` SET `name` = 'eclairage_refuge_fin' WHERE `name` = 'eclairage_reacteur_fin';
UPDATE `core_config_status` SET `name` = 'refuge_eclairage' WHERE `name` = 'reacteur_eclairage';
UPDATE `cron_task` SET `name` = 'app:cron:refuge-eclairage' WHERE `name` = 'app:cron:reacteur-gestion';

-- libellés et aides
UPDATE `core_config_value` SET `label` = REPLACE(REPLACE(REPLACE(`label`, 'du réacteur', 'du refuge'), 'réacteur', 'refuge'), 'Réacteur', 'Refuge'),
    `description` = REPLACE(REPLACE(REPLACE(`description`, 'du réacteur', 'du refuge'), 'réacteur', 'refuge'), 'Réacteur', 'Refuge');
UPDATE `core_config_status` SET `label` = REPLACE(REPLACE(REPLACE(`label`, 'du réacteur', 'du refuge'), 'réacteur', 'refuge'), 'Réacteur', 'Refuge'),
    `description` = REPLACE(REPLACE(REPLACE(`description`, 'du réacteur', 'du refuge'), 'réacteur', 'refuge'), 'Réacteur', 'Refuge');
UPDATE `core_config_value` SET `description` = 'Délai après le dernier nettoyage du refuge (carte « Nettoyage refuge ») avant rappel.' WHERE `name` = 'check_clean_refuge';
UPDATE `core_config_value` SET `description` = 'Heure à laquelle l''éclairage du refuge s''allume (si « Éclairage du refuge » est activé). La plage peut passer minuit.' WHERE `name` = 'eclairage_refuge_debut';
