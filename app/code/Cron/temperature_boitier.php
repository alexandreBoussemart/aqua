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

    if (getStatus($link, 'force_stop_refroidissement')) {
        // on force à éteindre
        exec("python " . __DIR__ . "/../../../scripts/refroidissement/off.py");
        $force_stop = true;
    }

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

    // temerature rpi
    $f = fopen("/sys/class/thermal/thermal_zone0/temp", "r");
    $temp = fgets($f);
    $temperature_rpi = round($temp / 1000, 2);

    // si les deux temperatures on moins de 10% d'écart
    if ($temp_min < $temperature2 && $temperature2 < $temp_max) {
        // trop chaud on allume le ventilateur
        if ($temperature2 > 40 || $temperature_rpi > getConfig($link, "temperature_max_rpi")) {
            exec("python " . __DIR__ . "/../../../scripts/refroidissement/on.py");
        } else if (!$force_stop && $temperature2 > getConfig($link, "temperature_max_boitier") && (($now < $date->format('Y-m-d 22:30:00') && $now > $date->format('Y-m-d 10:00:00')) || getStatus($link, 'refroidissement'))) {
            exec("python " . __DIR__ . "/../../../scripts/refroidissement/on.py");
        } else {
            exec("python " . __DIR__ . "/../../../scripts/refroidissement/off.py");
        }

        // on insère la temperature en bdd 1 fois toutes les 15 minutes
        if ($minute % 15 == 0) {
            insertTemperature($link, $temperature2, "`data_temperature_boitier`");
            insertTemperature($link, $temperature_rpi, "`data_temperature_rpi`");
        }
    }

    exit;

} catch (Exception $e) {
    setLog($link, $e->getMessage());

    exit;
}

