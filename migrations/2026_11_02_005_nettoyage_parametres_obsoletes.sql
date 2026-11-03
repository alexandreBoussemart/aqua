-- Paramètres devenus inutiles : pause du réacteur (débit supprimé) et logs SQL dans des fichiers (ancienne appli)
DELETE FROM `core_config_value` WHERE `name` = 'timer_reacteur';
DELETE FROM `core_config_status` WHERE `name` = 'log_in_files';
