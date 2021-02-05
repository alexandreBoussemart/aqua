<?php

/**
 * Toutes les minutes
 */

require __DIR__ . '/../helper/functions.php';

try {
    // désactive toutes les crons
    if (getStatus($link, DISABLE_ALL_CRON)) {
        exit;
    }

    if(isOn()) {
        if (getStatus($link, 'reacteur_eclairage')) {
            exec("python " . __DIR__ . "/../../../scripts/reacteur_eclairage/on.py");
        }else {
            exec("python " . __DIR__ . "/../../../scripts/reacteur_eclairage/off.py");
        }

        if (getStatus($link, 'reacteur_ventilateur')) {
            exec("python ".__DIR__."/../../../scripts/reacteur_ventilateur/on.py");
        }else {
            exec("python " . __DIR__ . "/../../../scripts/reacteur_ventilateur/off.py");
        }
    }else{
        exec("python " . __DIR__ . "/../../../scripts/reacteur_eclairage/off.py");
        exec("python " . __DIR__ . "/../../../scripts/reacteur_ventilateur/off.py");
    }

    exit;

} catch (Exception $e) {
    try {
        setLog($link, $e->getMessage());
        sendMail($data, $transport, "Error script reacteur_gestion.php", $e->getMessage(), $link);
    } catch (Exception $e) {
        setLog($link, $e->getMessage());
    }

    exit;
}



