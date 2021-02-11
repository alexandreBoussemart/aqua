<?php

/**
 * Toutes les minutes
 */

require __DIR__ . '/../helper/app.php';

try {
    // désactive toutes les crons
    if (getStatus($link, DISABLE_ALL_CRON)) {
        setControle($link, CONTROLE_ECUMEUR);
        setState($link, ECUMEUR, 'state_98', 0, "Toutes les crons sont désactivées");

        exit;
    }

    //check si la cron est activé
    if (!getStatus($link, ECUMEUR)) {
        setControle($link, CONTROLE_ECUMEUR);
        setState($link, ECUMEUR, 'state_99', 0, "Écumeur - Désactivé");

        exit;
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

        if(haveTimer($link, ECUMEUR)){
            // on éteint
            exec("python " . __DIR__ . "/../../../scripts/ecumeur/off.py");
            setControle($link, CONTROLE_ECUMEUR);
            setState($link, ECUMEUR, 'state_97', 0, "Écumeur - Pause timer");

            continue;
        }

        //on execute la commande
        exec("python " . __DIR__ . "/../../../scripts/ecumeur.py");
        usleep(500000);

        if (getStatus($link, 'on_off_ecumeur') != true) {
            // on éteint
            exec("python " . __DIR__ . "/../../../scripts/ecumeur/off.py");
        } else {
            if (isNiveauToHigh($link)) {
                // si osmolateur niveau to high, on éteint l'écumeur
                exec("python " . __DIR__ . "/../../../scripts/ecumeur/off.py");
            } elseif (isRunEcumeur($link)) {
                exec("python " . __DIR__ . "/../../../scripts/ecumeur/on.py");
            } else {
                exec("python " . __DIR__ . "/../../../scripts/ecumeur/off.py");
            }
        }
    }

    exit;

} catch (Exception $e) {
    setState($link, ECUMEUR, 'state_4', 1, "Cron ecumeur - ERREUR - " . $e->getMessage());
    setLog($link, $e->getMessage());

    exit;
}



