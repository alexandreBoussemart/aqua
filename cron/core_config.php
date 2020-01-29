<?php

/**
 * Toutes les minutes
 */

require 'helper/functions.php';

try {
    // controle toutes les 1 seconde
    for ($i = 0; $i <= 60; $i++) {
        sleep(1);

        if (getConfig($link, 'config_on_off_osmolateur') == true) {
            // on allume
            exec("python " . __DIR__ . "/../scripts/osmolateur/on.py");
        } else {
            // on éteint
            exec("python " . __DIR__ . "/../scripts/osmolateur/off.py");
        }

        if (getConfig($link, 'config_on_off_reacteur') == true) {
            // on allume
            exec("python " . __DIR__ . "/../scripts/reacteur/on.py");
        } else {
            // on éteint
            exec("python " . __DIR__ . "/../scripts/reacteur/off.py");
        }
    }

} catch (Exception $e) {
    setLog($link, $e->getMessage());
}



