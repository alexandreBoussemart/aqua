<?php

/**
 * Toutes les minutes
 *
 * /sys/bus/w1/devices/28-0213191aabaa/w1_slave
 */

require __DIR__ . '/../Helper/functions.php';

try {
    //désavtiver toutes les cron
    if (getStatus($link, 'disable_all_cron')) {
        setControle($link, 'controle_temperature');
        setState($link, 'temperature', 'state_98', 0, "Toutes les crons sont désactivées");

        exit;
    }

    //check si la cron est activé
    if (!getStatus($link, 'temperature')) {
        setControle($link, 'controle_temperature');
        setState($link, 'temperature', 'state_99', 0, "Température - Désactivé");

        return false;
    }

    //si on est pas toutes les 5 minutes on quitte
    $date = new DateTime();
    $minute = $date->format('i');
    if ($minute % 5 != 0) {
        // mais on set comme quoi on est bien passé dans la cron
        setControle($link, 'controle_temperature');

        exit;
    }

    // on défini le chemin du fichier
    if (!defined("THERMOMETER_SENSOR_PATH")) {
        define("THERMOMETER_SENSOR_PATH", $data['file_temperature']);
    }

    // première lecture, on quitte si résultat pas ok
    $content = getContentTempFileCron($link);
    $temperature1 = readTemperature($content);
    if ($temperature1 == false) {
        setState($link, 'temperature', 'state_2', 1, "Cron temperature - ERREUR - Format du fichier incorrect (" . $content . ")");

        exit;
    }

    // on attend 20 secondes
    sleep(20);

    // deuxième lecture, on quitte si résultat pas ok
    $content = getContentTempFileCron($link);
    $temperature2 = readTemperature($content);
    if ($temperature2 == false) {
        setState($link, 'temperature', 'state_2', 1, "Cron temperature - ERREUR - Format du fichier incorrect (" . $content . ")");

        exit;
    }

    $temp_min = $temperature1 * 0.90;
    $temp_max = $temperature1 * 1.10;

    // si les deux temperatures on moins de 10% d'écart
    if ($temp_min < $temperature2 && $temperature2 < $temp_max) {

        if ($temperature2 < getConfig($link, "temperature_min")) {
            //trop froid
            $message = "Temperature - ERREUR Trop froid - " . $temperature2 . "°C";
            $result = setState($link, 'temperature', 'state_5', 1, $message);
        } elseif ($temperature2 > getConfig($link, "temperature_max")) {
            //trop chaud
            $message = "Temperature - ERREUR Trop chaud - " . $temperature2 . "°C";
            $result = setState($link, 'temperature', 'state_6', 1, $message);
        } else {
            //ok
            $message = "Temperature - OK - " . $temperature2 . "°C";
            $result = setState($link, 'temperature', 'state_7', 0, $message);
        }

        // on insère la temperature en bdd 1 fois toutes les 15 minutes
        if ($minute % 15 == 0 || $result) {
            insertTemperature($link, $temperature2);
        }

        // on check si on doit allumer le ventilateur de l'aquarium
        if (getStatusVentilateur($link, $temperature2)) {
            exec("python " . __DIR__ . "/../../../scripts/aquarium_ventilateur/on.py");
        } else {
            exec("python " . __DIR__ . "/../../../scripts/aquarium_ventilateur/off.py");
        }

        // on set comme quoi on est bien passé dans la cron
        setControle($link, 'controle_temperature');

    } else {
        setState($link, 'temperature', 'state_3', 1, "Cron temperature - ERREUR - Plus de 10% d'écart");
    }

    exit;

} catch (Exception $e) {
    setLog($link, $e->getMessage());
    setState($link, 'temperature', 'state_4', 1, "Cron temperature - ERREUR - " . $e->getMessage());

    exit;
}

