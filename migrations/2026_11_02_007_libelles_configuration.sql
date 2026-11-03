-- Libellés plus explicites pour les réglages et les interrupteurs de la page Configuration
UPDATE `core_config_value` SET `label` = 'Rappel changement d''eau (jours)' WHERE `name` = 'check_changement_eau';
UPDATE `core_config_value` SET `label` = 'Rappel nettoyage du réacteur (jours)' WHERE `name` = 'check_clean_reacteur';
UPDATE `core_config_value` SET `label` = 'Rappel nettoyage de l''écumeur (jours)' WHERE `name` = 'check_clean_ecumeur';
UPDATE `core_config_value` SET `label` = 'Rappel nettoyage des pompes (jours)' WHERE `name` = 'check_clean_pompes';
UPDATE `core_config_value` SET `label` = 'Rappel analyse d''eau (jours)' WHERE `name` = 'check_analyse_eau';
UPDATE `core_config_value` SET `label` = 'Température de l''eau maximale (°C)' WHERE `name` = 'temperature_max';
UPDATE `core_config_value` SET `label` = 'Température de l''eau minimale (°C)' WHERE `name` = 'temperature_min';
UPDATE `core_config_value` SET `label` = 'Relance mail d''une erreur toujours active' WHERE `name` = 'temps_rappel_mail';
UPDATE `core_config_value` SET `label` = 'Durée de pause de l''écumeur' WHERE `name` = 'timer_ecumeur';
UPDATE `core_config_value` SET `label` = 'Durée de pause de l''osmolateur' WHERE `name` = 'timer_osmolateur';
UPDATE `core_config_value` SET `label` = 'Sécurité : durée max. de remplissage de l''osmolateur' WHERE `name` = 'temps_coupure_pompe_osmolateur';
UPDATE `core_config_value` SET `label` = 'Appoint après niveau atteint (secondes)' WHERE `name` = 'temps_mini_pompe_osmolateur';

UPDATE `core_config_status` SET `label` = 'Surveillance de l''osmolateur' WHERE `name` = 'osmolateur';
UPDATE `core_config_status` SET `label` = 'Surveillance de l''écumeur' WHERE `name` = 'ecumeur';
UPDATE `core_config_status` SET `label` = 'Surveillance du bailling' WHERE `name` = 'bailling';
UPDATE `core_config_status` SET `label` = 'Contrôle de la température de l''eau' WHERE `name` = 'temperature';
UPDATE `core_config_status` SET `label` = 'Relevé de la température de l''air' WHERE `name` = 'temperature_air';
UPDATE `core_config_status` SET `label` = 'Relevé de la température du Raspberry' WHERE `name` = 'temperature_rpi';
UPDATE `core_config_status` SET `label` = 'Éclairage nocturne du réacteur (22h30 → 10h)' WHERE `name` = 'reacteur_eclairage';
UPDATE `core_config_status` SET `label` = 'Contrôle du bon fonctionnement des crons' WHERE `name` = 'cron_controle';
UPDATE `core_config_status` SET `label` = 'Mail de rappel quotidien (19h)' WHERE `name` = 'cron_rappel';
UPDATE `core_config_status` SET `label` = 'Envoi des mails d''alerte' WHERE `name` = 'mail';
UPDATE `core_config_status` SET `label` = 'Osmolateur en marche' WHERE `name` = 'on_off_osmolateur';
UPDATE `core_config_status` SET `label` = 'Écumeur en marche' WHERE `name` = 'on_off_ecumeur';
UPDATE `core_config_status` SET `label` = 'Allumer l''éclairage du réacteur maintenant' WHERE `name` = 'force_turn_on_eclairage';
UPDATE `core_config_status` SET `label` = 'Mettre en pause toutes les tâches automatiques' WHERE `name` = 'disable_all_cron';
