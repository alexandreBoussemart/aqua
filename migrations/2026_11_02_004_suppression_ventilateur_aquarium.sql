-- Suppression du relais ventilateur de l'aquarium (pilotage selon la température)
DELETE FROM `core_config_value` WHERE `name` = 'config_temperature_declenchement';
DELETE FROM `core_config_status` WHERE `name` = 'force_turn_off_ventilateur';
