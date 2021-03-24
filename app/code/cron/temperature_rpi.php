<?php

/**
 * Toutes les minutes
 */

require __DIR__ . '/../helper/app.php';

try {
    // désactive toutes les crons
    if (getStatus($link, DISABLE_ALL_CRON)) {
        exit;
    }

    //check si la cron est activé
    if (!getStatus($link, TEMPERATURE_RPI)) {
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

    // temperature rpi
    $f = fopen("/sys/class/thermal/thermal_zone0/temp", "r");
    $temp = fgets($f);
    $temperature_rpi = round($temp / 1000, 2);

    // on log temperature du rpi
    if ($minute % 15 == 0) {
        insertTemperature($link, $temperature_rpi, TABLE_DATA_TEMP_RPI);
    }

    exit;

} catch (Exception $e) {
    try {
        setLog($link, $e->getMessage());
        sendMail($data, $transport, "Erreur script temperature_rpi.php", $e->getMessage(), $link);
    } catch (Exception $e) {
        setLog($link, $e->getMessage());
    }

    exit;
}

