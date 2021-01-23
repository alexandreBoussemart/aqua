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
        exit;
    }

    $force_stop = false;
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
        define("THERMOMETER_SENSOR_PATH_BOITIER", $data['file_temperature_boitier']);
    }

    // temerature rpi
    $f = fopen("/sys/class/thermal/thermal_zone0/temp", "r");
    $temp = fgets($f);
    $temperature_rpi = round($temp / 1000, 2);

    // on log temperature du rpi
    if ($minute % 15 == 0) {
        insertTemperature($link, $temperature_rpi, "`data_temperature_rpi`");
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
            insertTemperature($link, $temperature2, "`data_temperature_boitier`");
        }
    }

    exit;

} catch (Exception $e) {
    setLog($link, $e->getMessage());

    exit;
}

