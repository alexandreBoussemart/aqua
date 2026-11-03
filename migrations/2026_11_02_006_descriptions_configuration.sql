-- Texte d'aide affiché sous chaque réglage et chaque interrupteur de la page Configuration
ALTER TABLE `core_config_value` ADD COLUMN IF NOT EXISTS `description` text DEFAULT NULL;
ALTER TABLE `core_config_status` ADD COLUMN IF NOT EXISTS `description` text DEFAULT NULL;

UPDATE `core_config_value` SET `description` = 'Délai sans changement d''eau après lequel un rappel s''affiche sur le dashboard et part par mail.' WHERE `name` = 'check_changement_eau';
UPDATE `core_config_value` SET `description` = 'Délai après le dernier nettoyage du réacteur (bouton « Nettoyage réacteur ») avant rappel.' WHERE `name` = 'check_clean_reacteur';
UPDATE `core_config_value` SET `description` = 'Délai après le dernier nettoyage de l''écumeur avant rappel.' WHERE `name` = 'check_clean_ecumeur';
UPDATE `core_config_value` SET `description` = 'Délai après le dernier nettoyage des pompes avant rappel.' WHERE `name` = 'check_clean_pompes';
UPDATE `core_config_value` SET `description` = 'Délai sans mesure d''un paramètre (Ca, Mg, Kh, densité, potassium, nitrate, phosphate) avant rappel.' WHERE `name` = 'check_analyse_eau';
UPDATE `core_config_value` SET `description` = 'Au-dessus, la température passe en erreur « trop chaud » (alerte mail). Sert aussi de limite haute du graphique.' WHERE `name` = 'temperature_max';
UPDATE `core_config_value` SET `description` = 'En dessous, la température passe en erreur « trop froid » (alerte mail). Sert aussi de limite basse du graphique.' WHERE `name` = 'temperature_min';
UPDATE `core_config_value` SET `description` = 'Une erreur toujours active est relancée par mail à cet intervalle. Format : durée négative, ex. « -45 minutes ».' WHERE `name` = 'temps_rappel_mail';
UPDATE `core_config_value` SET `description` = 'Durée de la pause de l''écumeur (bouton « Pause écumeur » et « Changement d''eau »). Ex. « 25 minutes ».' WHERE `name` = 'timer_ecumeur';
UPDATE `core_config_value` SET `description` = 'Durée de la pause de l''osmolateur (bouton « Pause osmolateur » et « Changement d''eau »). Ex. « 25 minutes ».' WHERE `name` = 'timer_osmolateur';
UPDATE `core_config_value` SET `description` = 'Sécurité anti-débordement : si la pompe tourne plus longtemps sans que le niveau soit atteint, elle est coupée et l''osmolateur passe en erreur (bloqué jusqu''à intervention). Format : « -25 seconds ».' WHERE `name` = 'temps_coupure_pompe_osmolateur';
UPDATE `core_config_value` SET `description` = 'Quand le niveau redevient OK, la pompe continue encore ce nombre de secondes pour faire un petit appoint.' WHERE `name` = 'temps_mini_pompe_osmolateur';

UPDATE `core_config_status` SET `description` = 'Surveillance de l''osmolateur : lecture des capteurs de niveau et pilotage de la pompe.' WHERE `name` = 'osmolateur';
UPDATE `core_config_status` SET `description` = 'Surveillance du niveau du godet de l''écumeur.' WHERE `name` = 'ecumeur';
UPDATE `core_config_status` SET `description` = 'Surveillance des 3 réservoirs de bailling (alerte si un niveau est bas).' WHERE `name` = 'bailling';
UPDATE `core_config_status` SET `description` = 'Mesure de la température de l''eau toutes les 5 min et alertes trop chaud / trop froid.' WHERE `name` = 'temperature';
UPDATE `core_config_status` SET `description` = 'Enregistrement de la température de l''air (sonde du boîtier) toutes les 15 min.' WHERE `name` = 'temperature_air';
UPDATE `core_config_status` SET `description` = 'Enregistrement de la température du processeur du Raspberry toutes les 15 min.' WHERE `name` = 'temperature_rpi';
UPDATE `core_config_status` SET `description` = 'Allume l''éclairage du réacteur la nuit, par cycles d''1 h entrecoupés de 10 min de pause.' WHERE `name` = 'reacteur_eclairage';
UPDATE `core_config_status` SET `description` = 'Vérifie chaque minute que les crons d''équipement ont tourné ; sinon le contrôle concerné passe en erreur.' WHERE `name` = 'cron_controle';
UPDATE `core_config_status` SET `description` = 'Mail quotidien à 19h : nourrissage du jour et entretiens en retard.' WHERE `name` = 'cron_rappel';
UPDATE `core_config_status` SET `description` = 'Envoi réel des mails. Désactivé : ils sont seulement enregistrés dans les logs. Coupé automatiquement au-delà de 10 mails en 2 min.' WHERE `name` = 'mail';
UPDATE `core_config_status` SET `description` = 'Interrupteur général de l''osmolateur : éteint, la pompe n''est plus relancée.' WHERE `name` = 'on_off_osmolateur';
UPDATE `core_config_status` SET `description` = 'Interrupteur général de l''écumeur : éteint, son alimentation est coupée.' WHERE `name` = 'on_off_ecumeur';
UPDATE `core_config_status` SET `description` = 'Allume tout de suite l''éclairage du réacteur ; le cron le rééteint hors de la plage nocturne ou si l''éclairage nocturne est désactivé.' WHERE `name` = 'force_turn_on_eclairage';
UPDATE `core_config_status` SET `description` = 'Met en pause toutes les tâches automatiques (mesures, pilotage, mails). Le planificateur continue de tourner.' WHERE `name` = 'disable_all_cron';
