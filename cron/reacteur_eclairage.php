<?php

/**
 * Toutes les minutes
 */

require 'helper/functions.php';

try {
    $date = new DateTime();
    $today = $date->format('Y-m-d H:i:s');

    //check si on doit allumer l'éclairage
    if (getStatus($link, 'reacteur_eclairage')) {
        //check les horaires
        /*
            22h30 à 23h30
            23h40 à 00h40
            00h50 à 01h50
            02h00 à 03h00
            03h10 à 04h10
            04h20 à 05h20
            05h30 à 06h30
            06h40 à 07h40
            07h50 à 08h50
            08h00 à 10h00
        */

    } else {
        // on éteind
    }

} catch (Exception $e) {
    setLog($link, $e->getMessage());
}



