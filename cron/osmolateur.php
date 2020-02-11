<?php

/**
 * Toutes les minutes
 */

require 'helper/functions.php';

try {
    //check si la cron est activé
    if (!getStatus($link, 'osmolateur')) {
        setControle($link, 'controle_osmolateur');
        setState($link, 'osmolateur', 'state_99', 0, "Osmolateur - Désactivé");

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

        // on execute la commande pour vérifier le statut de l'osmolateur
        exec("python " . __DIR__ . "/../scripts/osmolateur.py");

        // on exécute la commande pour savoir si la pompe est allumée depuis plus de 20 secondes
        if (isRunOver20seconds($link)) {
            // on eteint
            exec("python " . __DIR__ . "/../scripts/pompe_osmolateur/off.py");
        } else {
            // on allume
            exec("python " . __DIR__ . "/../scripts/pompe_osmolateur/on.py");
        }

        usleep(500000);
    }

} catch (Exception $e) {
    setState($link, 'osmolateur', 'state_7', 1, "Cron osmolateur - ERREUR - " . $e->getMessage());
    setLog($link, $e->getMessage());
}



