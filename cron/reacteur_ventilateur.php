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
            exec("python ".__DIR__."/../scripts/reacteur_ventilateur/on.py");

            exit;
        }
    }

    // on éteint
    exec("python ".__DIR__."/../scripts/reacteur_ventilateur/off.py");

    exit;

} catch (Exception $e) {
    try{
        setLog($link, $e->getMessage());
        sendMail($data, $transport, "Error script reacteur_ventilateur.php", $e->getMessage(), $link);
    } catch (Exception $e) {
        setLog($link, $e->getMessage());
    }

    exit;
}
