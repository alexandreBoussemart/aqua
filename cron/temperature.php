<?php

/**
 * Toutes les minutes
 *
 * /sys/bus/w1/devices/28-0213191aabaa/w1_slave
 */

require 'helper/functions.php';

try {
    //check si la cron est activé
    if (!getStatus($link, 'temperature')) {
        setControle($link, 'controle_temperature');

        return false;
    }

    //si on est pas toutes les 5 minutes on quitte
    $date = new DateTime();
    $minute = $date->format('i');
    if ($minute % 5 != 0) {
        // mais on set comme quoi on est bien passé dans la cron
        setControle($link, 'controle_temperature');

        return false;
    }

    // on défini le chemin du fichier
    if (!defined("THERMOMETER_SENSOR_PATH")) {
        define("THERMOMETER_SENSOR_PATH", $data['file_temperature']);
    }

    // première lecture, on quitte si résultat pas ok
    $content = readFileTemperature($link);
    $temperature1 = readTemperature($content);
    if ($temperature1 == false) {
        setState($link, 'temperature', 'state_2', 1, "Cron temperature - ERREUR - Format du fichier incorrect");

        return false;
    }

    // on attend 20 secondes
    sleep(20);

    // deuxième lecture, on quitte si résultat pas ok
    $content = readFileTemperature($link);
    $temperature2 = readTemperature($content);
    if ($temperature2 == false) {
        setState($link, 'temperature', 'state_2', 1, "Cron temperature - ERREUR - Format du fichier incorrect");

        return false;
    }

    $temp_min = $temperature1 * 0.90;
    $temp_max = $temperature1 * 1.10;

    // si les deux temperatures on moins de 10% d'écart
    if ($temp_min < $temperature2 && $temperature2 < $temp_max) {

        // on insère la temperature en bdd
        insertTemperature($link, $temperature2);

        if ($temperature2 < 23) {
            //trop froid
            $message = "Temperature - ERREUR - Trop froid " . $temperature2 . "°C";
            setState($link, 'temperature', 'state_5', 1, $message);
        } elseif ($temperature2 > 28) {
            //trop chaud
            $message = "Temperature - ERREUR - Trop chaud " . $temperature2 . "°C";
            setState($link, 'temperature', 'state_6', 1, $message);
        } else {
            //ok
            $message = "Temperature - OK -  " . $temperature2 . "°C";
            setState($link, 'temperature', 'state_7', 0, $message);
        }

        // on check si on doit allumer le ventilateur de l'aquarium
        if (getStatusVentilateur($link, $temperature2)) {
            exec("python " . __DIR__ . "/../scripts/aquarium_ventilateur/on.py");
        } else {
            exec("python " . __DIR__ . "/../scripts/aquarium_ventilateur/off.py");
        }

        // on set comme quoi on est bien passé dans la cron
        setControle($link, 'controle_temperature');

    } else {
        setState($link, 'temperature', 'state_3', 1, "Cron temperature - ERREUR - Plus de 10% d'écart");

        return false;
    }

} catch (Exception $e) {
    setLog($link, $e->getMessage());
    setState($link, 'temperature', 'state_4', 1, "Cron temperature - ERREUR - " . $e->getMessage());
}

