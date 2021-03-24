<?php

/**
 * Toutes les minutes
 *
 * /sys/bus/w1/devices/28-0213191aabaa/w1_slave
 */

require __DIR__ . '/../helper/app.php';

try {
    // désactive toutes les crons
    if (getStatus($link, DISABLE_ALL_CRON)) {
        exit;
    }

    //check si la cron est activé
    if (!getStatus($link, TEMPERATURE_AIR)) {
        return false;
    }

    $date = new DateTime();
    $now = $date->format('Y-m-d H:i:s');

    //si on est pas toutes les 5 minutes on quitte
    $date = new DateTime();
    $minute = $date->format('i');
    if ($minute % 5 != 0) {
        exit;
    }

    // on défini le chemin du fichier
    if (!defined("THERMOMETER_SENSOR_PATH_BOITIER")) {
        define("THERMOMETER_SENSOR_PATH_BOITIER", $data['file_temperature_air']);
    }

    // première lecture, on quitte si résultat pas ok
    $content = readFileTemperatureBoitier($link);
    $temperature1 = readTemperature($content);
    if ($temperature1 == false) {
        exit;
    }

    // on attend 20 secondes
    sleep(20);

    // deuxième lecture, on quitte si résultat pas ok
    $content = readFileTemperatureBoitier($link);
    $temperature2 = readTemperature($content);
    if ($temperature2 == false) {
        exit;
    }

    $temp_min = $temperature1 * 0.90;
    $temp_max = $temperature1 * 1.10;

    // si les deux temperatures on moins de 10% d'écart
    if ($temp_min < $temperature2 && $temperature2 < $temp_max) {
        // on insère la temperature en bdd 1 fois toutes les 15 minutes
        if ($minute % 15 == 0) {
            insertTemperature($link, $temperature2, TABLE_DATA_TEMP_AIR);
        }
    }

    exit;

} catch (Exception $e) {
    try {
        setLog($link, $e->getMessage());
        sendMail($data, $transport, "Erreur script temperature_air.php", $e->getMessage(), $link);
    } catch (Exception $e) {
        setLog($link, $e->getMessage());
    }

    exit;
}

