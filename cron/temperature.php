#!/usr/bin/env php
<?php

require 'functions.php';

try {
    //check si la cron est activé
    if (!getConfig($link, 'cron_temperature')) {
        return false;
    }

    if (!defined("THERMOMETER_SENSOR_PATH")) {
        define("THERMOMETER_SENSOR_PATH", "/sys/bus/w1/devices/28-0213191aabaa/w1_slave");
    }

    // Open resource file for thermometer
    $thermometer = fopen(THERMOMETER_SENSOR_PATH, "r");
    $content = fread($thermometer, filesize(THERMOMETER_SENSOR_PATH));
    fclose($thermometer);

    $temperature1 = readTemperature($content);
    if ($temperature1 == false) {
        return false;
    }

    sleep(20);

    $temperature2 = readTemperature($content);
    if ($temperature2 == false) {
        return false;
    }

    $temp_min = $temperature1 * 0.90;
    $temp_max = $temperature1 * 1.10;

    // si les deux temperatures on moins de 10% d'écart
    if ($temp_min < $temperature2 && $temperature2 < $temp_max) {

        $erreur = false;
        if ($temperature2 < 23) {
            //trop froid
            $message = "Temperature - ERREUR - trop froid " . $temperature2 . "°C";
            $erreur = true;
        } elseif ($temperature2 > 28) {
            //trop chaud
            $message = "Temperature - ERREUR - trop chaud " . $temperature2 . "°C";
            $erreur = true;
        } else {
            //ok
            $message = "Temperature - OK -  " . $temperature2 . "°C";
        }

        if ($erreur) {
            $body = "<p style='color:red;text-transform:uppercase;'>" . $message . "</p>";
        } else {
            $body = "<p style='color:green;'>" . $message . "</p>";
        }

        insertTemperature($link, $temperature2);
        setControle($link, 'controle_temperature');
        sendMail($data, $transport, $message, $body);
        print_r($message);

    } else {
        return false;
    }

} catch (Exception $e) {
    sendMail($data, $transport, "Cron temperature - ERREUR", $e->getMessage());
}

