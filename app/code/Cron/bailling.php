<?php

/**
 * Toutes les minutes
 */

require __DIR__ . '/../helper/app.php';

try {
    // désactive toutes les crons
    if (getStatus($link, DISABLE_ALL_CRON)) {
        setControle($link, CONTROLE_BAILLING);
        setState($link, BAILLING, 'state_98', 0, "Toutes les crons sont désactivées");

        exit;
    }

    //check si la cron est activé
    if (!getStatus($link, BAILLING)) {
        setControle($link, CONTROLE_BAILLING);
        setState($link, BAILLING, 'state_99', 0, "Baillling - Désactivé");

        exit;
    }

    // on execute la commande
    exec("python " . __DIR__ . "/../../../scripts/bailling.py");

    exit;

} catch (Exception $e) {
    setState($link, BAILLING, 'state_10', 1, "Cron bailling - ERREUR - " . $e->getMessage());
    setLog($link, $e->getMessage());

    exit;
}



