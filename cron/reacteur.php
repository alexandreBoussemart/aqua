<?php

/**
 * Toutes les minutes
 */

require 'helper/functions.php';

try {
    //check si la cron est activé
    if (!getStatus($link, 'reacteur')) {
        setControle($link, 'controle_reacteur');

        return false;
    }

    // on execute la commande
    exec("python " . __DIR__ . "/../scripts/reacteur.py");

} catch (Exception $e) {
    setState($link, 'reacteur', 'state_5', 1, "Cron reacteur - ERREUR - " . $e->getMessage());
    setLog($link, $e->getMessage());
}



