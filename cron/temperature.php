<?php

require 'functions.php';

try {
    //check si la cron est activé
    if (!getConfig($link, 'cron_temperature')) {
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
        setState($link, 'temperature','state_2',true,"Cron temperature - ERREUR - Format du fichier incorrect");
        return false;
    }

    // on attend 20 secondes
    sleep(2);

    // deuxième lecture, on quitte si résultat pas ok
    $content = readFileTemperature($link);
    $temperature2 = readTemperature($content);
    if ($temperature2 == false) {
        setState($link, 'temperature','state_2',true, "Cron temperature - ERREUR - Format du fichier incorrect");
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
            setState($link, 'temperature','state_5',true, $message);
        } elseif ($temperature2 > 28) {
            //trop chaud
            $message = "Temperature - ERREUR - Trop chaud " . $temperature2 . "°C";
            setState($link, 'temperature','state_6',true, $message);
        } else {
            //ok
            $message = "Temperature - OK -  " . $temperature2 . "°C";
            setState($link, 'temperature','state_7',0, $message);
        }

        print_r($message);

	    // on set comme quoi on est bien passé dans la cron
    	setControle($link, 'controle_temperature');

    } else {
        setState($link, 'temperature','state_3',true, "Cron temperature - ERREUR - Plus de 10% d'écart");
        return false;
    }

} catch (Exception $e) {
    setState($link, 'temperature','state_4',true,"Cron temperature - ERREUR - ".$e->getMessage());
}

