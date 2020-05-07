<?php

/**
 * Toutes les minutes
 */

require '../Helper/functions.php';

try {
    //check si la cron est activé
    if (!getStatus($link, 'bailling')) {
        setControle($link, 'controle_bailling');
        setState($link, 'bailling', 'state_99', 0, "Baillling - Désactivé");

        exit;
    }

    // on execute la commande
    exec("python " . __DIR__ . "/../../../scripts/bailling.py");

    exit;

} catch (Exception $e) {
    setState($link, 'bailling', 'state_10', 1, "Cron bailling - ERREUR - " . $e->getMessage());
    setLog($link, $e->getMessage());

    exit;
}



