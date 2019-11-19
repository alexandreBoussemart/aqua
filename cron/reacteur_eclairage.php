<?php

/**
 * Toutes les minutes
 */

require 'helper/functions.php';

try {
    //check si on doit allumer l'éclairage
    if (getStatus($link, 'reacteur_eclairage')) {
        //check les horaires
        if(isOn()) {
            // on allume
            exec("python ".__DIR__."/../scripts/on_reacteur_eclairage.py");

            return true;
        }
    }

    // on éteind
    exec("python ".__DIR__."/../scripts/off_reacteur_eclairage.py");


} catch (Exception $e) {
    setLog($link, $e->getMessage());
}



