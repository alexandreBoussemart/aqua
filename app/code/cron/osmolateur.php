<?php

/**
 * Toutes les minutes
 */

require __DIR__ . '/../helper/app.php';

try {
    // désactive toutes les crons
    if (getStatus($link, DISABLE_ALL_CRON)) {
        setControle($link, CONTROLE_OSMOLATEUR);
        setState($link, OSMOLATEUR, 'state_98', 0, "Toutes les crons sont désactivées");

        exit;
    }

    //check si la cron est activé
    if (!getStatus($link, OSMOLATEUR)) {
        setControle($link, CONTROLE_OSMOLATEUR);
        setState($link, OSMOLATEUR, 'state_99', 0, "Osmolateur - Désactivé");

        exit;
    }

    $tempsMaxPompeOsmolateur = getConfig($link, 'temps_coupure_pompe_osmolateur');

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

        if (haveTimer($link, OSMOLATEUR)) {
            setControle($link, CONTROLE_OSMOLATEUR);
            setState($link, OSMOLATEUR, 'state_97', 0, "Osmolateur - Pause timer");

            continue;
        }

        // on execute la commande pour vérifier le statut de l'osmolateur
        exec("python " . __DIR__ . "/../../../scripts/osmolateur.py");

        // on exécute la commande pour savoir si la pompe est allumée depuis plus de 20 secondes
        if (isRun($link) && !isRunOver20seconds($link, $tempsMaxPompeOsmolateur)) {
            // on allume
            exec("python " . __DIR__ . "/../../../scripts/pompe_osmolateur/on.py");
        } else {
            // on éteint
            exec("python " . __DIR__ . "/../../../scripts/pompe_osmolateur/off.py");
        }

        usleep(500000);
    }

    exit;

} catch (Exception $e) {
    setState($link, OSMOLATEUR, 'state_7', 1, "Cron osmolateur - ERREUR - " . $e->getMessage());
    setLog($link, $e->getMessage());

    exit;
}



