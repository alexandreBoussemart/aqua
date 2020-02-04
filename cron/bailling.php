<?php

/**
 * Toutes les minutes
 */

require 'helper/functions.php';

try {
    //check si la cron est activé
    if (!getStatus($link, 'bailling')) {
        setControle($link, 'controle_bailling');

        return false;
    }

    // on execute la commande
    exec("python " . __DIR__ . "/../scripts/bailling.py");

} catch (Exception $e) {
    setState($link, 'bailling', 'state_10', 1, "Cron bailling - ERREUR - " . $e->getMessage());
    setLog($link, $e->getMessage());
}



