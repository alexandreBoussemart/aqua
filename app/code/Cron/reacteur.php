<?php

/**
 * Toutes les minutes
 */

require __DIR__ . '/../Helper/functions.php';

try {
    //check si la cron est activé
    if (!getStatus($link, 'reacteur')) {
        setControle($link, 'controle_reacteur');
        setState($link, 'reacteur', 'state_99', 0, "Réacteur - Désactivé");

        exit;
    }

    //heure d'execution max
    $date = new DateTime();
    $end = $date->format('Y-m-d H:i:59');

    // controle toutes les 5 secondes
    for ($i = 0; $i <= 12; $i++) {
        //si on passe la minute en cours on arrête
        $date = new DateTime();
        $now = $date->format('Y-m-d H:i:s');
        if ($now > $end) {
            break;
        }

        // on execute la commande
        exec("python " . __DIR__ . "/../../../scripts/reacteur.py");
        sleep(5);
    }

    exit;

} catch (Exception $e) {
    setState($link, 'reacteur', 'state_5', 1, "Cron reacteur - ERREUR - " . $e->getMessage());
    setLog($link, $e->getMessage());

    exit;
}



