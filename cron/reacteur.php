<?php

/**
 * Toutes les minutes
 */

require 'helper/functions.php';

try {
    //check si la cron est activé
    if (!getStatus($link, 'reacteur')) {
        setControle($link, 'controle_reacteur');
        setState($link, 'reacteur', 'state_99', 0, "Désactivé");

        return false;
    }

    // controle toutes les 1/2 secondes
    for ($i = 0; $i <= 120; $i++) {
        //si on passe la minute en cours on arrête
        $date = new DateTime();
        $now = $date->format('Y-m-d H:i:s');
        if ($now > $end) {
            break;
        }

        // on execute la commande
        exec("python " . __DIR__ . "/../scripts/reacteur.py");
        usleep(500000);
    }

} catch (Exception $e) {
    setState($link, 'reacteur', 'state_5', 1, "Cron reacteur - ERREUR - " . $e->getMessage());
    setLog($link, $e->getMessage());
}



