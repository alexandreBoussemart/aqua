-- Suppression du suivi du débit du réacteur (capteur abandonné)
DROP TABLE IF EXISTS `data_reacteur`;
DELETE FROM `core_config_value` WHERE `name` = 'debit_reacteur_min';
DELETE FROM `core_config_status` WHERE `name` = 'reacteur';
DELETE FROM `core_config_state` WHERE `path` IN ('reacteur', 'controle_reacteur');
DELETE FROM `last_activity` WHERE `value` = 'controle_reacteur';
DELETE FROM `timer` WHERE `type` = 'reacteur';

-- Suppression de la gestion des ventilateurs du réacteur (la nuit)
DELETE FROM `core_config_status` WHERE `name` = 'reacteur_ventilateur';
