<?php

/**
 * Toutes les minutes
 */

require '../Helper/functions.php';

try {
    //check si on doit allumer l'éclairage
    if (getStatus($link, 'reacteur_eclairage')) {
        //check les horaires
        if (isOn()) {
            // on allume
            exec("python " . __DIR__ . "/../../../scripts/reacteur_eclairage/on.py");

            exit;
        }
    }

    // on éteint
    exec("python " . __DIR__ . "/../../../scripts/reacteur_eclairage/off.py");

    exit;

} catch (Exception $e) {
    try {
        setLog($link, $e->getMessage());
        sendMail($data, $transport, "Error script reacteur_eclairage.php", $e->getMessage(), $link);
    } catch (Exception $e) {
        setLog($link, $e->getMessage());
    }

    exit;
}



