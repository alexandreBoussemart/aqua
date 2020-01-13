<?php

/**
 * Toutes les minutes
 */

require 'helper/functions.php';

// on set comme quoi on est bien passé dans la cron
setControle($link, 'controle_bailling');

try {
    //check si la cron est activé
    if (!getStatus($link, 'bailling')) {
        return false;
    }

    // controle toutes les 1/2 secondes
    for ($i = 0; $i <= 120; $i++) {
        exec("python " . __DIR__ . "/../scripts/bailling.py");
        usleep(500000);
    }

} catch (Exception $e) {
    setState($link, 'bailling', 'state_10', 1, "Cron bailling - ERREUR - " . $e->getMessage());
    setLog($link, $e->getMessage());
}



