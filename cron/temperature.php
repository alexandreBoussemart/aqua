#!/usr/bin/env php
<?php

require 'functions.php';

try {
    // on set comme quoi on est bien passé dans la cron
    setControle($link, 'controle_temperature');

    //check si la cron est activé
    if (!getConfig($link, 'cron_temperature')) {
        return false;
    }

    // on défini le chemin du fichier
    if (!defined("THERMOMETER_SENSOR_PATH")) {
        define("THERMOMETER_SENSOR_PATH", $data['file_temperature']);
    }

    // première lecture, on quitte si résultat pas ok
    $content = readFileTemperature($data, $transport);
    $temperature1 = readTemperature($content);
    if ($temperature1 == false) {
        return false;
    }

    // on attend 20 secondes
    sleep(20);

    // deuxième lecture, on quitte si résultat pas ok
    $content = readFileTemperature($data, $transport);
    $temperature2 = readTemperature($content);
    if ($temperature2 == false) {
        return false;
    }

    $temp_min = $temperature1 * 0.90;
    $temp_max = $temperature1 * 1.10;

    // si les deux temperatures on moins de 10% d'écart
    if ($temp_min < $temperature2 && $temperature2 < $temp_max) {

        // on insère la temperature en bdd
        insertTemperature($link, $temperature2);
        $erreur = false;
        $state = "";

        if ($temperature2 < 23) {
            //trop froid
            $message = "Temperature - ERREUR - trop froid " . $temperature2 . "°C";
            $erreur = true;
            $state = "state_1";
        } elseif ($temperature2 > 28) {
            //trop chaud
            $message = "Temperature - ERREUR - trop chaud " . $temperature2 . "°C";
            $erreur = true;
            $state = "state_2";
        } else {
            //ok
            $message = "Temperature - OK -  " . $temperature2 . "°C";
            $state = "state_3";
        }

        if ($erreur) {
            $body = "<p style='color:red;text-transform:uppercase;'>" . $message . "</p>";
        } else {
            $body = "<p style='color:green;'>" . $message . "</p>";
        }


        sendMail($data, $transport, $message, $body);
        print_r($message);

    } else {
        return false;
    }

} catch (Exception $e) {
    sendMail($data, $transport, "Cron temperature - ERREUR", $e->getMessage());
}

