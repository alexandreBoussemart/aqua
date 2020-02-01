<?php

/**
 * Toutes les minutes
 */

require 'helper/functions.php';

try {
    //check si la cron est activé
    if (!getStatus($link, 'ecumeur')) {
        return false;
    }

    //heure d'execution max
    $date = new DateTime();
    $end = $date->format('Y-m-d H:i:59');

    // controle toutes les 1/2 secondes
    for ($i = 0; $i <= 120; $i++) {
        //si on passe la minute en cours on arrête
        $date = new DateTime();
        $now = $date->format('Y-m-d H:i:s');
        if ($now > $end) {
            break;
        }

        //on execute la commande
        exec("python " . __DIR__ . "/../scripts/ecumeur.py");
        usleep(500000);
    }

} catch (Exception $e) {
    setState($link, 'ecumeur', 'state_4', 1, "Cron ecumeur - ERREUR - " . $e->getMessage());
    setLog($link, $e->getMessage());
}



