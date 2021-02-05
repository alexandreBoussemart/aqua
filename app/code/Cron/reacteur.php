<?php

/**
 * Toutes les minutes
 */

require __DIR__ . '/../helper/functions.php';

try {
    // désactive toutes les crons
    if (getStatus($link, DISABLE_ALL_CRON)) {
        setControle($link, CONTROLE_REACTEUR);
        setState($link, REACTEUR, 'state_98', 0, "Toutes les crons sont désactivées");

        exit;
    }

    //check si la cron est activé
    if (!getStatus($link, REACTEUR)) {
        setControle($link, CONTROLE_REACTEUR);
        setState($link, REACTEUR, 'state_99', 0, "Réacteur - Désactivé");

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
    setState($link, REACTEUR, 'state_5', 1, "Cron reacteur - ERREUR - " . $e->getMessage());
    setLog($link, $e->getMessage());

    exit;
}



