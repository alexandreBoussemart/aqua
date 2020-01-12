<?php

/**
 * Toutes les minutes
 */

require 'helper/functions.php';

try {
    //check si la cron est activé
    if (!getStatus($link, 'ecumeur')) {
        // on set comme quoi on est bien passé dans la cron
        setControle($link, 'controle_ecumeur');

        return false;
    }

    // controle toutes les 1/2 secondes
    for ($i = 0; $i <= 120; $i++) {
        exec("python " . __DIR__ . "/../scripts/ecumeur.py");
        usleep(500000);
    }

} catch (Exception $e) {
    setState($link, 'ecumeur', 'state_4', 1, "Cron ecumeur - ERREUR - " . $e->getMessage());
    setLog($link, $e->getMessage());
}



