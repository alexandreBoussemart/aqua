<?php

/**
 * Toutes les minutes
 */

require 'helper/functions.php';

try {
    //check si on doit allumer l'éclairage
    if (getStatus($link, 'reacteur_ventilateur')) {
        //check les horaires
        if(isOn()) {
            // on allume
            exec("python ".__DIR__."/../scripts/on_reacteur_ventilateur.py");

            return true;
        }
    }

    // on éteint
    exec("python ".__DIR__."/../scripts/off_reacteur_ventilateur.py");

} catch (Exception $e) {
    setLog($link, $e->getMessage());
}
