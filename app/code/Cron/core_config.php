<?php

/**
 * Toutes les minutes
 */

require __DIR__ . '/../Helper/functions.php';

try {
    //désavtiver toutes les cron
    if (getStatus($link, 'disable_all_cron')) {
        exit;
    }

    //heure d'execution max
    $date = new DateTime();
    $end = $date->format('Y-m-d H:i:59');

    // controle toutes les 1 seconde
    for ($i = 0; $i <= 60; $i++) {
        //si on passe la minute en cours on arrête
        $date = new DateTime();
        $now = $date->format('Y-m-d H:i:s');
        if ($now > $end) {
            break;
        }

        sleep(1);

        if (getStatus($link, 'on_off_ecumeur') == true) {
            // on allume
            exec("python " . __DIR__ . "/../../../scripts/ecumeur/off.py");
        } else {
            // on éteint
            exec("python " . __DIR__ . "/../../../scripts/ecumeur/on.py");
        }
    }

    exit;

} catch (Exception $e) {
    try {
        setLog($link, $e->getMessage());
        sendMail($data, $transport, "Error script core_config.php", $e->getMessage(), $link);
    } catch (Exception $e) {
        setLog($link, $e->getMessage());
    }

    exit;
}
